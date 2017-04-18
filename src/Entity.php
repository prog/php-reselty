<?php

namespace com\peterbodnar\reselty;

use com\peterbodnar\reselty\utils\SelectionDefinition;
use Nette\Database\Context as DbContext;
use Nette\Database\Table\IRow;
use Nette\SmartObject;



abstract class Entity
{
	use SmartObject {
		__get as ___get;
		__set as ___set;
	}


	/** @var DbContext */
	protected $dbContext;
	/** @var IRow|NULL */
	protected $row;
	/** @var array */
	protected $data;


	/** @return utils\EntityDefinition */
	private function def()
	{
		return utils\EntityDefinition::get(get_class($this));
	}


	/**
	 * @param IRow $row
	 */
	public function __construct(DbContext $dbContext, IRow $row = NULL)
	{
		$this->dbContext = $dbContext;
		$this->row = $row;
		$this->data = [];
	}


	/**
	 * It's a kind of magic.
	 *
	 * @param string $var
	 * @return mixed
	 */
	public function &__get($var)
	{
		$def = $this->def();
		$prop = $def->getProperty($var);


		if ($prop) {
			if (method_exists($this, "getProperty_" . $var)) {
				$result = $this->{"getProperty_" . $var}();
				return $result;
			}

			$type = $prop->getType();
			$dbName = utils\Helpers::camelToUnder($prop->getName());

			if ($type->isScalar()) {

				// todo: mapping (bool, Date)
				return $this->row->{$dbName};

			} elseif ($type->isClass(Entity::class)) {

				$entityClassName = $type->getClassName();
				$row = $this->row->ref($dbName);
				$result = new $entityClassName($this->dbContext, $row);
				return $result;

			} elseif ($type->isClass(Selection::class)) {

				$selectionClassName = $type->getClassName();
				$tableName = SelectionDefinition::get($selectionClassName)->getTableName();
				$tableSelection = $this->row->related($tableName);
				$result = new $selectionClassName($this->dbContext, $tableSelection);
				return $result;

				throw new \RuntimeException("Not implemented");
			}
		}

		return $this->___get($var);
	}


	/**
	 * It's a kind of magic.
	 *
	 * @param string $var
	 * @param mixed $val
	 * @return void
	 */
	public function __set($var, $val)
	{
		$this->___set($var, $val);
	}

}
