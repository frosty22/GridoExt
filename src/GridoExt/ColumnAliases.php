<?php

namespace GridoExt;

use Nette\Utils\Strings;

/**
 * Simple mapper columns for Grido
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class ColumnAliases implements \ArrayAccess
{

	const TYPE_FILTER = "filter";
	const TYPE_SORT = "sort";


	/**
	 * @var string
	 */
	private $type;


	/**
	 * @param string $type
	 */
	public function __construct($type)
	{
		$this->type = $type;
	}


	/**
	 * @param string $offset
	 * @param mixed $value
	 * @throws InvalidCallException
	 */
	public function offsetSet($offset, $value) {
		throw new InvalidCallException("Cannot set dynamic array access item.");
	}


	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return strpos($offset, GridoFactory::ALIAS_DELIMITER) !== FALSE ||
			   strpos($offset, GridoFactory::PRIMARY_DELIMITER) !== FALSE;
	}


	/**
	 * @param string $offset
	 * @throws InvalidCallException
	 */
	public function offsetUnset($offset) {
		throw new InvalidCallException("Cannot unset dynamic array access item.");
	}


	/**
	 * @param string $offset
	 * @return string
	 */
	public function offsetGet($offset) {
		if ($matches = Strings::match($offset, "~([A-Z0-9]+)" . GridoFactory::ALIAS_DELIMITER . "([A-Z0-9]+)$~i")) {
			return $matches[1] . "." . $this->replacePrimaryKey($matches[2]);
		}
		return $this->replacePrimaryKey($offset);
	}


	/**
	 * Replace primary keys
	 * @param string $key
	 * @return string
	 */
	protected function replacePrimaryKey($key)
	{
		if (strpos($key, GridoFactory::PRIMARY_DELIMITER)) {
			$explode = Explode(GridoFactory::PRIMARY_DELIMITER, $key);
			return $this->type === self::TYPE_FILTER ? $explode[1] : $explode[0];
		}
		return $key;
	}

}
