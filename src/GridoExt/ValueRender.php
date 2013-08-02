<?php

namespace GridoExt;

use \Ale\Entities\BaseEntity;

/**
 * Custom value render.
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class ValueRender extends \Nette\Object
{

	const FORMAT_DATETIME = "j.n.Y H:i:s";
	const FORMAT_DATE = "j.n.Y";
	const FORMAT_TIME = "H:i:s";


	/**
	 * List of parents entities from root
	 * @var array
	 */
	protected $parents = array();


	/**
	 * @var \EntityMetaReader\ColumnReader
	 */
	protected $column;


	/**
	 * @param \EntityMetaReader\ColumnReader $column
	 * @param array $parents
	 */
	public function __construct(\EntityMetaReader\ColumnReader $column, array $parents)
	{
		$this->column = $column;
		$this->parents = $parents;
	}


	/**
	 * Render string format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderString(BaseEntity $item)
	{
		$value = $this->getValue($item);
		return is_null($value) || $value === "" ? $this->getEmptyValue() : $value;
	}


	/**
	 * Render array format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderArray(BaseEntity $item)
	{
		$value = $this->getValue($item);
		return is_array($value) && count($value) ? Implode(", ", $value) : $this->getEmptyValue();
	}


	/**
	 * Render boolean format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderBoolean(BaseEntity $item)
	{
		$value = $this->getValue($item);
		$format = $this->column->getAnnotation('GridoExt\Mapping\Format', TRUE);
		/** @var \GridoExt\Mapping\Format $format */
		return empty($value) ? $format->getMessageFalse() : $format->getMessageTrue();
	}


	/**
	 * Render integer format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderInteger(BaseEntity $item)
	{
		$value = $this->getValue($item);
		return is_null($value) ? $this->getEmptyValue() : $value;
	}


	/**
	 * Render float format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderFloat(BaseEntity $item)
	{
		$value = $this->getValue($item);
		if (is_null($value)) return $this->getEmptyValue();

		$format	= $this->column->getAnnotation('GridoExt\Mapping\Format', TRUE);
		/** @var \GridoExt\Mapping\Format $format */

		return number_format($value, $format->getDecimals(), $format->getDecimalPoint(), $format->getThousands());
	}


	/**
	 * Render Time format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderTime(BaseEntity $item)
	{
		return $this->processRenderDateTime($item, "time");
	}


	/**
	 * Render Date format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderDate(BaseEntity $item)
	{
		return $this->processRenderDateTime($item, "date");
	}


	/**
	 * Render DateTime format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderDateTime(BaseEntity $item)
	{
		return $this->processRenderDateTime($item, "datetime");
	}


	/**
	 * Render DateTime format
	 * @param BaseEntity $item
	 * @param string $type
	 * @return string
	 */
	protected function processRenderDateTime(BaseEntity $item, $type)
	{
		$value = $this->getValue($item);
		if (is_null($value)) return $this->getEmptyValue();

		if (!$value instanceof \DateTime)
			throw new InvalidValueException("Invalid type of {$this->column->getName()}.");

		if ($format = $this->getDateTimeFormat())
			return $value->format($format);

		switch ($type) {
			case "datetime":
				return $value->format(self::FORMAT_DATETIME);
			case "time":
				return $value->format(self::FORMAT_TIME);
			case "date":
				return $value->format(self::FORMAT_DATE);
			default:
				throw new InvalidValueException();
		}

	}


	/**
	 * Return value from item
	 * @param BaseEntity $item
	 * @return mixed
	 */
	protected function getValue(BaseEntity $item)
	{
		for ($i = 1; $i < count($this->parents); $i++) {
			$item = $item->{$this->parents[$i]};
			if (!$item) return NULL;
		}

		return $item->{$this->column->getName()};
	}


	/**
	 * Get DateTime format
	 * @return string|NULL
	 */
	protected function getDateTimeFormat()
	{
		$format	= $this->column->getAnnotation('GridoExt\Mapping\Format');
		if ($format instanceof \GridoExt\Mapping\Format) {
			return $format->getValue();
		}

		return NULL;
	}


	/**
	 * Get empty value
	 * @return string
	 */
	protected function getEmptyValue()
	{
		$format	= $this->column->getAnnotation('GridoExt\Mapping\Format', TRUE);
		/** @var \GridoExt\Mapping\Format $format */
		return $format->getEmptyValue();
	}

}
