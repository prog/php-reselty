<?php

namespace com\peterbodnar\reselty\utils;

use Nette\SmartObject;



/** @internal */
final class EntityDefinition
{
	use SmartObject;


	/** @var ClassDefinition */
	private $classDef;


	/** @param string $className */
	private function __construct($className)
	{
		$this->classDef = new ClassDefinition($className);
	}


	/** @return string */
	public function getTableName()
	{
		return $this->classDef->getAnnotation("table");
	}


	/**
	 * @param string $name
	 * @return PropertyDefinition|null
	 */
	public function getProperty($name)
	{
		return $this->classDef->getProperty($name);
	}


	/** @var EntityDefinition[] */
	private static $cache = [];


	/**
	 * @param string $className
	 * @return EntityDefinition
	 */
	public static function get($className)
	{
		if (!isset(self::$cache[$className])) {
			self::$cache[$className] = new self($className);
		}
		return self::$cache[$className];
	}

}
