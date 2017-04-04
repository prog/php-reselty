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
		$annos = new ClassAnnotations($className);
		$fetchMethod = $annos->getMethod('fetch()');
		$fetchOneMethod = $annos->getMethod('fetchOne()');

		$fetchReturn = $fetchMethod ? $fetchMethod->getReturnType() : NULL;
		$fetchRetClass = $fetchReturn ? $fetchReturn->getClassName() : NULL;
		$fetchOneReturn = $fetchOneMethod ? $fetchOneMethod->getReturnType() : NULL;
		$fetchOneRetClass = $fetchOneMethod ? $fetchOneReturn->getClassName() : NULL;

		if (!$fetchMethod || !$fetchReturn->isArray() || $fetchReturn->isNullable()) {
			throw new \RuntimeException("Missing or invalid @method <EntityClass>[] fetch() annotation: {$className}");
		}
		if (!$fetchOneMethod || $fetchOneReturn->isArray() || !$fetchOneReturn->isNullable()) {
			throw new \RuntimeException("Missing or invalid @method <EntityClass>|null fetchOne() annotation: {$className}");
		}
		if ($fetchRetClass !== $fetchOneRetClass) {
			throw new \RuntimeException("fetch() and fetchOne() annotated return types does not match: {$className}");
		}
		if ($fetchRetClass === Entity::class || !$fetchReturn->isClass(Entity::class)) {
			throw new \RuntimeException("Invalid return type in annotations @method <EntityClass> fetch(), fetchOne() in {$className}");
		}

		$this->entityClass = $fetchRetClass;
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
