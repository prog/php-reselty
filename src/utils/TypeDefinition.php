<?php

namespace com\peterbodnar\reselty\utils;

use Nette\Reflection;
use Nette\SmartObject;



/** @internal */
final class TypeDefinition
{
	use SmartObject;


	/** @var string[] */
	private $types;
	/** @var bool */
	private $arrays;
	/** @var bool */
	private $classes;
	/** @var bool */
	private $scallars;
	/** @var bool */
	private $nullable;


	private function __construct() { }


	/**
	 * Multiple mixed types
	 */
	public function isMixed()
	{
		return count($this->types) > 1;
	}


	public function isArray()
	{
		if (0 === count($this->types)) {
			return false;
		}
		foreach ($this->types as $type) {
			if (!$type[1]) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Single scalar type
	 *
	 * @return bool
	 */
	public function isScalar()
	{
		return $this->scallars && !$this->classes && !$this->isMixed();
	}


	/**
	 * Multiple, scalar-only types
	 *
	 * @return bool
	 */
	public function isMixedScalar()
	{
		return $this->scallars && !$this->classes && $this->isMixed();
	}


	/**
	 * Single class type
	 *
	 * @return bool
	 */
	public function isClass($className = NULL)
	{
		$isSingleClass = ($this->classes && !$this->scallars && !$this->isMixed());
		if (!$isSingleClass || $className === NULL) {
			return $isSingleClass;
		}
		return is_a($this->types[0][0], $className, TRUE);
	}


	public function isClassArray($className = NULL)
	{
		return $this->isClass($className) && $this->types[0][1];
	}



	/**
	 * Multiple, class-only types
	 *
	 * @return bool
	 */
	public function isMixedClasses($className = NULL)
	{
		$isMixedClasses = ($this->classes && !$this->scallars && $this->isMixed());
		if (!$isMixedClasses || $className === NULL) {
			return $isMixedClasses;
		}
		foreach ($this->types as $name) {
			if (!is_a($name, $className, TRUE)) {
				return FALSE;
			}
		}
		return TRUE;
	}


	/** @return string|null */
	public function getClassName()
	{
		return $this->isClass() ? $this->types[0][0] : NULL;
	}


	/** @return string[]|null */
	public function getClassNames()
	{
		return $this->isMixedClasses() ? $this->types : NULL;
	}


	/** @return bool */
	public function isNullable()
	{
		return $this->nullable;
	}


	public function __toString()
	{
		$result = [];
		foreach ($this->types as $type) {
			$result[] = $type[0] . ($type[1] ? "[]" : "");
		}
		if ($this->nullable) {
			$result[] = "null";
		}
		return implode("|", $result);
	}


	private static $SCALARS = [
		'string' => 'string',
		'int' => 'int',
		'integer' => 'int',
		'bool' => 'bool',
		'boolean' => 'bool',
		'float' => 'float',
		'double' => 'float',
	];


	/**
	 * @param Reflection\ClassType $classType
	 * @param $name
	 * @return string
	 */
	private static function fullClassName(Reflection\ClassType $classType, $name)
	{
		return $classType->namespaceName . "\\" . $name;
	}


	/**
	 * @param Reflection\ClassType $type
	 * @param string $type
	 * @return string
	 */
	public static function parse(Reflection\ClassType $classType, $typeName)
	{
		$names = explode('|', $typeName);

		$result = new self();
		$result->nullable = false;
		$result->scallars = false;
		$result->classes = false;
		$result->arrays = false;

		foreach ($names as $name) {
			if (($isArray = (bool) preg_match('~(.+)\\[\\]$~',$name, $m))) {
				$name = $m[1];
				$result->arrays = true;
			}
			$scName = strtolower(ltrim($name, '\\'));
			if (isset(self::$SCALARS[$scName])) {
				$result->types[] = [self::$SCALARS[$scName], $isArray];
				$result->scallars = TRUE;
			} elseif ('null' === $scName) {
				if (!$isArray) {
					$result->nullable = true;
				}
			} else {
				$className = self::fullClassName($classType, $name);
				$result->types[] = [$className, $isArray];
				$result->classes = TRUE;
			}
		}

		return $result;
	}

}
