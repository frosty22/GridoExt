<?php

namespace GridoExt\Mapping;

/**
 * Format mapping
 *
 * @Annotation
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Format implements \EntityMetaReader\Annotation
{

	const FORMAT_DECIMALS = 2;
	const FORMAT_THOUSANDS = ".";
	const FORMAT_DECIMAL_POINT = ",";

	const MSG_TRUE = "Ano";
	const MSG_FALSE = "Ne";

	const EMPTY_VALUE = "";


	/**
	 * @var string
	 */
	private $value = "";


	/**
	 * @var int
	 */
	private $decimals;


	/**
	 * @var string
	 */
	private $decimalPoint;


	/**
	 * @var string
	 */
	private $thousands;


	/**
	 * Message for true
	 * @var string
	 */
	private $messageTrue;


	/**
	 * Message for false
	 * @var string
	 */
	private $messageFalse;


	/**
	 * @var string
	 */
	private $emptyValue;


	/**
	 * @param array $args
	 */
	public function __construct(array $args)
	{
		$this->value = isset($args["value"]) ? $args["value"] : NULL;
		$this->decimals = isset($args["decimals"]) ? $args["decimals"] : self::FORMAT_DECIMALS;
		$this->decimalPoint = isset($args["decimalPoint"]) ? $args["decimalPoint"] : self::FORMAT_DECIMAL_POINT;
		$this->thousands = isset($args["thousands"]) ? $args["thousands"] : self::FORMAT_THOUSANDS;
		$this->messageTrue = isset($args["true"]) ? $args["true"] : self::MSG_TRUE;
		$this->messageFalse = isset($args["false"]) ? $args["false"] : self::MSG_FALSE;
		$this->emptyValue = isset($args["empty"]) ? $args["empty"] : self::EMPTY_VALUE;
	}


	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * @return string
	 */
	public function getDecimalPoint()
	{
		return $this->decimalPoint;
	}


	/**
	 * @return string
	 */
	public function getThousands()
	{
		return $this->thousands;
	}


	/**
	 * @return int
	 */
	public function getDecimals()
	{
		return $this->decimals;
	}


	/**
	 * @return string
	 */
	public function getMessageTrue()
	{
		return $this->messageTrue;
	}


	/**
	 * @return string
	 */
	public function getMessageFalse()
	{
		return $this->messageFalse;
	}


	/**
	 * @return string
	 */
	public function getEmptyValue()
	{
		return $this->emptyValue;
	}


}
