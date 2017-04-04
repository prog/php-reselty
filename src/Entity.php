<?php

namespace com\peterbodnar\reselty;

use com\peterbodnar\reselty\utils\SelectionDefinition;
use Nette\Database\Table\IRow;
use Nette\SmartObject;



abstract class Entity
{
	use SmartObject {
		__get as ___get;
		__set as ___set;
	}


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
	public function __construct(IRow $row = NULL)
	{
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
			$dbName = self::camelToUnder($prop->getName());

			if ($type->isScalar()) {

				// todo: mapping (bool, Date)
				return $this->row->{$dbName};

			} elseif ($type->isClass(Entity::class)) {

				$entityClassName = $type->getClassName();
				$row = $this->row->ref($dbName);
				$result = new $entityClassName($row);
				return $result;

			} elseif ($type->isClass(Selection::class)) {

				$selectionClassName = $type->getClassName();
				$tableName = SelectionDefinition::get($selectionClassName)->getTableName();
				$tableSelection = $this->row->related($tableName);
				$result = new $selectionClassName($tableSelection);
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


	/**
	 * @param string $s
	 * @return mixed|string
	 */
	private static function camelToUnder($s)
	{
		$s = preg_replace('/(?<!^)[A-Z]/', '_$0', $s);
		$s = strtolower($s);
		return $s;
	}


	/**
	 * @param string $s
	 * @return string
	 */
	private static function underToCamel($s)
	{
		$s = str_replace('_', ' ', $s);
		$s = substr(ucwords('x' . $s), 1);
		$s = str_replace(' ', '', $s);
		return $s;
	}

}
