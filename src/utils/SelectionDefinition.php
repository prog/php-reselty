<?php

namespace com\peterbodnar\reselty\utils;

use com\peterbodnar\reselty\Entity;
use Nette\SmartObject;



/** @internal */
final class SelectionDefinition
{
	use SmartObject;


	/** @var string */
	private $entityClass;


	private function __construct($className)
	{
		$annos = new ClassDefinition($className);
		$fetchOneMethod = $annos->getMethod('fetch()');
		$fetchAllMethod = $annos->getMethod('fetchAll()');
		$getIteratorMethod = $annos->getMethod('getIterator()');

		$fetchAllReturn = $fetchAllMethod ? $fetchAllMethod->getReturnType() : NULL;
		$fetchAllRetClass = $fetchAllReturn ? $fetchAllReturn->getClassName() : NULL;
		$getIteratorReturn = $getIteratorMethod ? $getIteratorMethod->getReturnType() : NULL;
		$getIteratorRetClass = $getIteratorReturn ? $getIteratorReturn->getClassName() : NULL;
		$fetchOneReturn = $fetchOneMethod ? $fetchOneMethod->getReturnType() : NULL;
		$fetchOneRetClass = $fetchOneMethod ? $fetchOneReturn->getClassName() : NULL;

		if (!$fetchAllMethod || !$fetchAllReturn->isArray() || $fetchAllReturn->isNullable()) {
			throw new \RuntimeException("Missing or invalid @method <EntityClass>[] fetchAll() annotation: {$className}");
		}
		if (!$getIteratorMethod || !$getIteratorReturn->isArray() || $getIteratorReturn->isNullable()) {
			throw new \RuntimeException("Missing or invalid @method <EntityClass>[] getIterator() annotation: {$className}");
		}
		if (!$fetchOneMethod || $fetchOneReturn->isArray() || !$fetchOneReturn->isNullable()) {
			throw new \RuntimeException("Missing or invalid @method <EntityClass>|null fetchOne() annotation: {$className}");
		}
		if ($fetchAllRetClass !== $getIteratorRetClass) {
			throw new \RuntimeException("fetchAll() and getIterator() annotated return types does not match: {$className}");
		}
		if ($fetchAllRetClass !== $fetchOneRetClass) {
			throw new \RuntimeException("fetchAll() and fetch() annotated return types does not match: {$className}");
		}
		if ($fetchAllRetClass === Entity::class || !$fetchAllReturn->isClass(Entity::class)) {
			throw new \RuntimeException("Invalid return type in annotations @method <EntityClass> getIterator(), fetchAll(), fetch() in {$className}");
		}

		$this->entityClass = $fetchAllRetClass;
	}


	/** @return string */
	public function getEntityClassName()
	{
		return $this->entityClass;
	}


	/** @return EntityDefinition */
	public function getEntityDefinition()
	{
		return EntityDefinition::get($this->entityClass);
	}


	/** @return string */
	public function getTableName()
	{
		return $this->getEntityDefinition()->getTableName();
	}


	/** @var SelectionDefinition[] */
	private static $cache = [];


	/**
	 * @param string $className
	 * @return SelectionDefinition
	 */
	public static function get($className)
	{
		if (!isset(self::$cache[$className])) {
			self::$cache[$className] = new SelectionDefinition($className);
		}
		return self::$cache[$className];
	}

}
