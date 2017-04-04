<?php

namespace com\peterbodnar\reselty;

use Nette\Database\Table\Selection as TableSelection;
use Nette\SmartObject;



abstract class Selection
{
	use SmartObject;


	/** @var TableSelection */
	protected $tableSelection;


	/** @return utils\SelectionDefinition */
	private function def()
	{
		return utils\SelectionDefinition::get(get_class($this));
	}


	/**
	 * @param TableSelection $tableSelection
	 */
	public function __construct(TableSelection $tableSelection)
	{
		$this->tableSelection = $tableSelection;
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


	/** @return Entity|NULL */
	public function fetchOne()
	{
		$entityClass = $this->def()->getEntityClassName();
		$row = $this->tableSelection->fetch();
		return $row ? new $entityClass($row) : NULL;
	}


	/** @return Entity[] */
	public function fetch()
	{
		$entityClass = $this->def()->getEntityClassName();
		$result = [];
		foreach ($this->tableSelection->fetchAll() as $row) {
			$result[] = new $entityClass($row);
		}
		return $result;
	}

}
