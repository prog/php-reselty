<?php

namespace com\peterbodnar\reselty\utils;

use Nette\Reflection;
use Nette\SmartObject;



/** @internal */
final class ClassDefinition
{
	use SmartObject;


	/** @var Reflection\ClassType */
	private $type;
	/** @var MethodDefinition[] */
	private $methods = [];
	/** @var PropertyDefinition[] */
	private $properties = [];


	/**
	 * @param Reflection\ClassType $type
	 * @param string[] $annotations
	 * @return void
	 */
	private function parseMethods(Reflection\ClassType $type, array $annotations)
	{
		foreach ($annotations as $annotaiton) {
			$method = MethodDefinition::parse($type, $annotaiton);
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
	 * @param string[] $annotations
	 * @param bool $readOnly
	 * @return void
	 */
	private function parseProperties(Reflection\ClassType $type, array $annotations, $readOnly = FALSE)
	{
		foreach ($annotations as $annotation) {
			$property = PropertyDefinition::parse($type, $annotation, $readOnly);
			if (!$property) {
				continue;
			}
			$lName = strtolower($property->getName());
			if (!isset($this->properties[$lName])) {
				$this->properties[$lName] = $property;
			}
		}
	}


	/**
	 * @param string $className
	 */
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
	public function getAnnotation($name)
	{
		return (string) $this->type->getAnnotation($name);
	}


	/**
	 * @param string $name
	 * @return MethodDefinition|null
	 */
	public function getMethod($name)
	{
		$lName = strtolower($name);
		return isset($this->methods[$lName]) ? $this->methods[$lName] : NULL;
	}


	/** @return PropertyDefinition[] */
	public function getProperties()
	{
		return $this->properties;
	}


	/**
	 * @param string $name
	 * @return PropertyDefinition|null
	 */
	public function getProperty($name)
	{
		$lName = strtolower($name);
		return isset($this->properties[$lName]) ? $this->properties[$lName] : null;
	}

}
