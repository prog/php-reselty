<?php

namespace com\peterbodnar\reselty\utils;

use com\peterbodnar\reselty\Selection;
use com\peterbodnar\reselty\utils\SelectionDefinition;
use Nette\SmartObject;



/** @internal */
final class RepositoryDefinition
{
	use SmartObject;


	/** @var string */
	private $selectionClassName;


	/**
	 * @param string $className
	 */
	private function __construct($className)
	{
		$annotations = new ClassAnnotations($className);
		$selectionMethod = $annotations->getMethod("selection()");
		if (!$selectionMethod) {
			throw new \RuntimeException("Missing @method <SelectionClass> selection() annotation: {$className}");
		}
		$selectionClass = $selectionMethod->getReturnType()->getClassName();
		if (NULL === $selectionClass || !is_a($selectionClass, Selection::class, TRUE)) {
			throw new \RuntimeException("Invalid return type in annotation @method <SelectionClass> selection() in {$className}");
		}
		$this->selectionClassName = $selectionClass;
	}


	/** @return string */
	public function getSelectionClassName()
	{
		return $this->selectionClassName;
	}


	/** @return SelectionDefinition */
	public function getSelectionDefinition()
	{
		return SelectionDefinition::get($this->selectionClassName);
	}


	/** @return string */
	public function getEntityClassName()
	{
		return $this->getSelectionDefinition()->getEntityClassName();
	}


	/** @return EntityDefinition */
	public function getEntityDefinition()
	{
		return $this->getSelectionDefinition()->getEntityDefinition();
	}


	/** @return string */
	public function getTableName()
	{
		return $this->getSelectionDefinition()->getTableName();
	}


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
