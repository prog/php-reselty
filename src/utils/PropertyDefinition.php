<?php

namespace com\peterbodnar\reselty\utils;

use Nette\Reflection;
use Nette\SmartObject;



/** @internal */
final class PropertyDefinition
{
	use SmartObject;


	/** @var string */
	private $name;
	/** @var TypeDefinition */
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


	/** @return TypeDefinition */
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
		$result->type = TypeDefinition::parse($type, $m[1]);
		$result->extraInfo = trim($m[3]);
		$result->readOnly = $readOnly;

		return $result;
	}

}
