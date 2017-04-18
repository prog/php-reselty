<?php

namespace com\peterbodnar\reselty;

use Nette\Database\Context as DbContext;
use Nette\Database\Table\Selection as TableSelection;
use Nette\SmartObject;



abstract class Selection implements \IteratorAggregate
{
	use SmartObject {
		__call as ___call;
	}


	/** @var DbContext */
	protected $dbContext;
	/** @var TableSelection */
	protected $tableSelection;


	/** @return utils\SelectionDefinition */
	private function def()
	{
		return utils\SelectionDefinition::get(get_class($this));
	}


	/**
	 * @param string|string[] $condition
	 * @param mixed ...$params
	 * @return static
	 */
	protected function whereRaw($condition, ...$params)
	{
		$this->tableSelection->where($condition, ...$params);
		return $this;
	}


	/**
	 * @param string $fieldName
	 * @param mixed ...$params
	 */
	protected function whereField($fieldName, $params)
	{
		$col = utils\Helpers::camelToUnder($fieldName);
		return $this->whereRaw($col, $params);
	}


	/**
	 * @param  string for example 'column1, column2 DESC'
	 * @return static
	 */
	protected function orderByRaw($columns, ...$params)
	{
		$this->tableSelection->order($columns, ...$params);
		return $this;
	}


	/**
	 * @param string $field
	 * @param int $dirrection
	 * @return static
	 */
	protected function orderByField($fieldName, $dirrection = 1)
	{
		$col = utils\Helpers::camelToUnder($fieldName);
		if ($dirrection === -1) {
			$col .= ' DESC';
		}
		return $this->orderByRaw($col);
	}


	/**
	 * @param DbContext $dbContext
	 * @param TableSelection $tableSelection
	 */
	public function __construct(DbContext $dbContext, TableSelection $tableSelection = NULL)
	{
		$this->dbContext = $dbContext;
		$this->tableSelection = $tableSelection ?: $dbContext->table($this->def()->getTableName());
	}


	/**
	 * Sets limit clause, more calls rewrite old values.
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return static
	 */
	public function limit($limit, $offset = NULL)
	{
		$this->tableSelection->limit($limit, $offset);
		return $this;
	}


	/**
	 * Sets offset using page number, more calls rewrite old values.
	 *
	 * @param int $page
	 * @param int $itemsPerPage
	 * @param int& $numberOfPages
	 * @param int& $numberOfItems
	 * @return static
	 */
	public function page($page, $itemsPerPage, &$numOfPages = NULL, &$numOfItems = NULL)
	{
		if (func_num_args() > 2) {
			$numOfItems = $this->count();
			$numOfPages = (int) ceil($numOfItems / $itemsPerPage);
		}
		if ($page < 1) {
			$itemsPerPage = 0;
		}
		return $this->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
	}


	/**
	 * Counts number of rows by running sql counting query.
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->tableSelection->count("*");
	}


	/** @return Entity|null */
	public function fetch()
	{
		$entityClass = $this->def()->getEntityClassName();
		$row = $this->tableSelection->fetch();
		return $row ? new $entityClass($this->dbContext, $row) : NULL;
	}


	/** @return Entity[] */
	public function fetchAll()
	{
		$entityClass = $this->def()->getEntityClassName();
		$result = [];
		foreach ($this->tableSelection->fetchAll() as $row) {
			$result[] = new $entityClass($this->dbContext, $row);
		}
		return $result;
	}


	/** @return Entity[] */
	public function getIterator()
	{
		return new \ArrayIterator($this->fetchAll());
	}


	public function __call($name, $args)
	{
		if (preg_match("~^where(.+)$~i", $name, $m)) {
			$field = $m[1];
			$annotationExists = TRUE; // todo
			if (!$annotationExists) {
				throw new \InvalidArgumentException("Missing annotation \"@method static {$name}()\"");
			}
			return $this->whereField($field, ...$args);
		}

		if (preg_match("~^orderBy(.+)$~i", $name, $m)) {
			$field = $m[1];
			$annotationExists = TRUE; // todo
			if (!$annotationExists) {
				throw new \InvalidArgumentException("Missing annotation \"@method static {$name}()\"");
			}
			return $this->orderByField($field, ...$args);
		}

		return $this->___call($name, $args);
	}

}
