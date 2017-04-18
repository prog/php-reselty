<?php

namespace com\peterbodnar\reselty\utils;

use Nette\Reflection;
use Nette\SmartObject;



/** @internal */
final class MethodDefinition
{
	use SmartObject;


	/** @var string */
	private $name;
	/** @var TypeDefinition */
	private $returnType;
	/** @var string */
	private $extraInfo;


	private function __construct() { }


	/** @return string */
	public function getName()
	{
		return $this->name;
	}


	/** @return TypeDefinition */
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
		$result->returnType = TypeDefinition::parse($type, $m[1]);
		$result->extraInfo = trim($m[3]);
		return $result;
	}

}
