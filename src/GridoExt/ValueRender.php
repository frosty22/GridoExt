<?php

namespace GridoExt;

use \Ale\Entities\BaseEntity;
use GridoExt\Render\IRender;

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
	 * @var Render\IRender
	 */
	protected $customRender;


	/**
	 * @param \EntityMetaReader\ColumnReader $column
	 * @param array $parents
	 * @param IRender $render Custom render modifier
	 */
	public function __construct(\EntityMetaReader\ColumnReader $column, array $parents, IRender $render = NULL)
	{
		$this->column = $column;
		$this->parents = $parents;
		$this->customRender = $render;
	}


	/**
	 * Render string format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderString(BaseEntity $item)
	{
		$value = $this->getValue($item);
		$value = is_null($value) || $value === "" ? $this->getEmptyValue() : $value;
		return $this->customRender($value, $item);
	}


	/**
	 * Render array format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderArray(BaseEntity $item)
	{
		$value = $this->getValue($item);
		$value = is_array($value) && count($value) ? Implode(", ", $value) : $this->getEmptyValue();
		return $this->customRender($value, $item);
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

		$value = empty($value) ? $format->getMessageFalse() : $format->getMessageTrue();
		return $this->customRender($value, $item);
	}


	/**
	 * Render integer format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderInteger(BaseEntity $item)
	{
		$value = $this->getValue($item);
		$value = is_null($value) ? $this->getEmptyValue() : $value;
		return $this->customRender($value, $item);
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

		$value = number_format($value, $format->getDecimals(), $format->getDecimalPoint(), $format->getThousands());
		return $this->customRender($value, $item);
	}


	/**
	 * Render Time format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderTime(BaseEntity $item)
	{
		$value = $this->processRenderDateTime($item, "time");
		return $this->customRender($value, $item);
	}


	/**
	 * Render Date format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderDate(BaseEntity $item)
	{
		$value = $this->processRenderDateTime($item, "date");
		return $this->customRender($value, $item);
	}


	/**
	 * Render DateTime format
	 * @param BaseEntity $item
	 * @return string
	 */
	public function renderDateTime(BaseEntity $item)
	{
		$value = $this->processRenderDateTime($item, "datetime");
		return $this->customRender($value, $item);
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
		$item = $this->getEntity($item);
		if (!$item) return NULL;

		return $item->{$this->column->getName()};
	}


	/**
	 * Return usable entity
	 * @param BaseEntity $item
	 * @return BaseEntity|NULL
	 */
	protected function getEntity(BaseEntity $item)
	{
		for ($i = 1; $i < count($this->parents); $i++) {
			$item = $item->{$this->parents[$i]};
			if (!$item) return NULL;
		}
		return $item;
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


	/**
	 * Custom render modificators
	 * @param string $value
	 * @param BaseEntity $item
	 * @return string
	 */
	protected function customRender($value, BaseEntity $item)
	{
		$item = $this->getEntity($item);
		return $this->customRender ? $this->customRender->render($value, $item) : $value;
	}

}
