<?php

namespace com\peterbodnar\reselty\utils;

use Nette\Reflection;
use Nette\SmartObject;



final class ClassAnnotations
{
	use SmartObject;


	/** @var Reflection\ClassType */
	private $type;
	/** @var ClassAnnotations_Method[] */
	private $methods = [];
	/** @var ClassAnnotations_Property[] */
	private $properties = [];


	/**
	 * @param Reflection\ClassType $type
	 * @param string[] $annotations
	 * @return void
	 */
	private function parseMethods(Reflection\ClassType $type, array $annotations)
	{
		foreach ($annotations as $annotaiton) {
			$method = ClassAnnotations_Method::parse($type, $annotaiton);
			if (!$method) {
				continue;
			}
			$lName = strtolower($method->getName());
			if (!isset($this->methods[$lName])) {
				$this->methods[$lName] = $method;
			}
		}
	}


	/**
	 * @param Reflection\ClassType $type
	 * @param string[] $annotaitons
	 * @param bool $readOnly
	 * @return void
	 */
	private function parseProperties(Reflection\ClassType $type, array $annotaitons, $readOnly = FALSE)
	{
		foreach ($annotaitons as $annotation) {
			$property = ClassAnnotations_Property::parse($type, $annotation, $readOnly);
			if (!$property) {
				continue;
			}
			$lName = strtolower($property->getName());
			if (!isset($this->properties[$lName])) {
				$this->properties[$lName] = $property;
			}
		}
	}


	public function __construct($className)
	{
		$this->type = new Reflection\ClassType($className);

		$type = $this->type;
		while ($type) {
			$annotations = $type->getAnnotations();
			if (isset($annotations["method"])) {
				$this->parseMethods($type, $annotations['method']);
			}
			if (isset($annotations["property"])) {
				$this->parseProperties($type, $annotations["property"]);
			}
			if (isset($annotations["property-read"])) {
				$this->parseProperties($type, $annotations["property-read"], TRUE);
			}
			$type = $type->getParentClass();
		}
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function get($name)
	{
		return (string) $this->type->getAnnotation($name);
	}


	/**
	 * @param string $name
	 * @return ClassAnnotations_Method|null
	 */
	public function getMethod($name)
	{
		$lName = strtolower($name);
		return isset($this->methods[$lName]) ? $this->methods[$lName] : NULL;
	}


	/**
	 * @param string $name
	 * @return ClassAnnotations_Property|null
	 */
	public function getProperty($name)
	{
		$lName = strtolower($name);
		return isset($this->properties[$lName]) ? $this->properties[$lName] : null;
	}

}



final class ClassAnnotations_Method
{
	use SmartObject;


	/** @var string */
	private $name;
	/** @var ClassAnnotations_Type */
	private $returnType;
	/** @var string */
	private $extraInfo;


	private function __construct() { }


	/** @return string */
	public function getName()
	{
		return $this->name;
	}


	/** @return ClassAnnotations_Type */
	public function getReturnType()
	{
		return $this->returnType;
	}


	/**
	 * @param Reflection\ClassType $type
	 * @param string $annotaiton
	 * @return null|static
	 */
	public static function parse(Reflection\ClassType $type, $annotaiton)
	{
		if (!preg_match('~([^\\s]+)\s+([^\\)]+\\))(.*)$~', $annotaiton, $m)) {
			return null;
		}

		$result = new static();
		$result->name = $m[2];
		$result->returnType = ClassAnnotations_Type::parse($type, $m[1]);
		$result->extraInfo = trim($m[3]);
		return $result;
	}

}



final class ClassAnnotations_Property
{
	use SmartObject;


	/** @var string */
	private $name;
	/** @var ClassAnnotations_Type */
	private $type;
	/** @var bool */
	private $readOnly;
	/** @var string */
	private $extraInfo;


	private function __construct() { }


	/** @return string */
	public function getName()
	{
		return $this->name;
	}


	/** @return ClassAnnotations_Type */
	public function getType()
	{
		return $this->type;
	}


	/** @return bool */
	public function isReadOnly()
	{
		return $this->readOnly;
	}


	/** @return string */
	public function getExtraInfo()
	{
		return $this->extraInfo;
	}


	/**
	 * @param Reflection\ClassType $type
	 * @param string $annotation
	 * @param bool $readOnly
	 * @return static|null
	 */
	public static function parse(Reflection\ClassType $type, $annotation, $readOnly)
	{
		if (!preg_match('~([^\\s]+)\s+\\$([^\\s]+)(.*)$~', $annotation, $m)) {
			return null;
		}

		$result = new static();
		$result->name = $m[2];
		$result->type = ClassAnnotations_Type::parse($type, $m[1]);
		$result->extraInfo = trim($m[3]);
		$result->readOnly = $readOnly;

		return $result;
	}

}



final class ClassAnnotations_Type
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
