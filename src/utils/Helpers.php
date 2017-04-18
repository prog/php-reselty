<?php

namespace com\peterbodnar\reselty\utils;

use Nette\StaticClass;



final class Helpers
{
	use StaticClass;


	/**
	 * @param string $s
	 * @return mixed|string
	 */
	public static function camelToUnder($s)
	{
		$s = preg_replace('/(?<!^)[A-Z]/', '_$0', $s);
		$s = strtolower($s);
		return $s;
	}


	/**
	 * @param string $s
	 * @return string
	 */
	public static function underToCamel($s)
	{
		$s = str_replace('_', ' ', $s);
		$s = substr(ucwords('x' . $s), 1);
		$s = str_replace(' ', '', $s);
		return $s;
	}

}