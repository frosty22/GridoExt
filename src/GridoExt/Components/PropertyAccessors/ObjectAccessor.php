<?php

namespace GridoExt\PropertyAccessors;

use Ale\Entities\BaseEntity;
use Grido\PropertyAccessors\IPropertyAccessor;
use GridoExt\InvalidValueException;

/**
 * Own object accessor.
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class ObjectAccessor implements IPropertyAccessor{


	/**
	 * @param BaseEntity $object
	 * @param string $name
	 * @return bool
	 * @throws InvalidValueException
	 */
	public static function hasProperty($object, $name)
	{
		if (!$object instanceof BaseEntity)
			throw new InvalidValueException("Object must be instance of BaseEntity.");

		return isset($object->$name);
	}


	/**
	 * @param BaseEntity $object
	 * @param string $name
	 * @return mixed
	 * @throws InvalidValueException
	 */
	public static function getProperty($object, $name)
	{
		if (!$object instanceof BaseEntity)
			throw new InvalidValueException("Object must be instance of BaseEntity.");

		return $object->$name;
	}


	/**
	 * @param BaseEntity $object
	 * @param string $name
	 * @param string $value
	 * @throws InvalidValueException
	 */
	public static function setProperty($object, $name, $value)
	{
		if (!$object instanceof BaseEntity)
			throw new InvalidValueException("Object must be instance of BaseEntity.");

		$object->$name = $value;
	}

}