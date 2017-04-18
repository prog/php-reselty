<?php

namespace com\peterbodnar\reselty;

use com\peterbodnar\reselty\utils\SelectionDefinition;
use Latte\RuntimeException;
use Nette\Database\Context;
use Nette\Database\Table\Selection as TableSelection;
use Nette\Database\Table\Selection;
use Nette\SmartObject;



abstract class Repository
{
	use SmartObject {
		__get as ___get;
	}


	/** @var Context */
	protected $db;


	/** @return utils\RepositoryDefinition */
	private function def()
	{
		return utils\RepositoryDefinition::get(get_class($this));
	}


	/**
	 * @return void
	 * @throws InvalidArgumentException
	 */
	private function validateEntityType(Entity $entity)
	{
		$allEntityClasses = $this->def()->getEntityClassNames();
		foreach ($allEntityClasses as $entityClassName) {
			if (is_a($entity, $entityClassName)) {
				return;
			}
		}
		throw new \InvalidArgumentException("Invalid entity");
	}


	/**
	 * @param string $name
	 * @return Selection|null
	 */
	protected function createSelection($name)
	{
		$selectionClass = $this->def()->getSelectionClassName($name);
		return (NULL !== $selectionClass) ? new $selectionClass($this->db) : NULL;
	}


	/** @param Context $dbContext */
	public function __construct(Context $dbContext)
	{
		$this->db = $dbContext;
	}


	public function insert(Entity $entity)
	{
		$this->validateEntityType($entity);
		throw new \RuntimeException("Not implemented");
	}


	public function update(Entity $entity)
	{
		$this->validateEntityType($entity);
		throw new \RuntimeException("Not implemented");
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		$selection = $this->createSelection($name);
		if ($selection) {
			return $selection;
		}

		return $this->___get($name);
	}

}
