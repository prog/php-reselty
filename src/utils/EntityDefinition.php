<?php

namespace com\peterbodnar\reselty\utils;

use Nette\SmartObject;



/** @internal */
final class EntityDefinition
{
	use SmartObject;


	/** @var ClassAnnotations */
	private $annotations;


	/** @param string $className */
	private function __construct($className)
	{
		$this->annotations = new ClassAnnotations($className);
	}


	/** @return string */
	public function getTableName()
	{
		return $this->annotations->get("table");
	}


	/**
	 * @param string $name
	 * @return ClassAnnotations_Property|null
	 */
	public function getProperty($name)
	{
		return $this->annotations->getProperty($name);
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
