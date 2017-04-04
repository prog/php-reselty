<?php

namespace com\peterbodnar\reselty;

use Latte\RuntimeException;
use Nette\Database\Context;
use Nette\Database\Table\Selection as TableSelection;
use Nette\Database\Table\Selection;
use Nette\SmartObject;



abstract class Repository
{
	use SmartObject;


	/** @var Context */
	protected $db;


	/** @return utils\RepositoryDefinition */
	private function def()
	{
		return utils\RepositoryDefinition::get(get_class($this));
	}


	/** @return TableSelection */
	protected function table()
	{
		$table = $this->def()->getTableName();
		return $this->db->table($table);
	}


	/** @param Context $dbContext */
	public function __construct(Context $dbContext)
	{
		$this->db = $dbContext;
	}


	/** @return Selection */
	public function selection()
	{
		$selectionClass = $this->def()->getSelectionClassName();
		return new $selectionClass($this->table());
	}


	/** @param Entity $entity */
	public function save(Entity $entity)
	{
		$entityClass = $this->def()->getEntityClassName();
		if (!is_a($entity, $entityClass)) {
			throw new \InvalidArgumentException();
		}

		throw new \RuntimeException("Not implemented");
	}

}
