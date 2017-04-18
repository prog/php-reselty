<?php

namespace com\peterbodnar\reselty\utils;

use com\peterbodnar\reselty\Selection;
use Nette\SmartObject;



/** @internal */
final class RepositoryDefinition
{
	use SmartObject;


	/** @var string[] */
	private $selectionClassNames = [];
	/** @var string[]|null */
	private $entityClassNames = NULL;
	/** @return string[] */


	public function getEntityClassNames()
	{
		if (NULL === $this->entityClassNames) {
			$this->entityClassNames = [];
			foreach ($this->selectionClasses as $propName => $className) {
				$entityClassNames[$propName] = SelectionDefinition::get($className)->getEntityClassName();
			}
		}
		return $this->entityClassNames;
	}


	/**
	 * @param string $className
	 */
	private function __construct($className)
	{
		$classDef = new ClassDefinition($className);
		$properties = $classDef->getProperties();
		foreach ($properties as $prop) {
			$type = $prop->getType();
			if ($prop->getType()->isClass(Selection::class)) {
				$this->selectionClassNames[$prop->getName()] = $type->getClassName();
			}
		}
	}


	/**
	 * @param string $name
	 * @return string|null
	 */
	public function getSelectionClassName($name)
	{
		return isset($this->selectionClassNames[$name]) ? $this->selectionClassNames[$name] : NULL;
	}


	// -- static


	/** @var RepositoryDefinition[] */
	private static $cache = [];


	/**
	 * @param string $className
	 * @return RepositoryDefinition
	 */
	public static function get($className)
	{
		if (!isset(self::$cache[$className])) {
			self::$cache[$className] = new self($className);
		}
		return self::$cache[$className];
	}

}
