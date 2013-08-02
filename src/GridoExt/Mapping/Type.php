<?php

namespace GridoExt\Mapping;

use GridoExt\UnexceptedMappingException;

/**
 * Type mapping
 *
 * @Annotation
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Type implements \EntityMetaReader\Annotation
{

	const TYPE_SELECT = "select";

	/**
	 * @var string
	 */
	protected $type;


	/**
	 * @var string
	 */
	protected $mappedBy;


	/**
	 * @var string
	 */
	protected $primaryKey;


	/**
	 * @var string
	 */
	protected $orderBy;


	/**
	 * @var string
	 */
	protected $orderDirection = "ASC";


	/**
	 * @param array $args
	 * @throws UnexceptedMappingException
	 */
	public function __construct(array $args)
	{
		$this->type = isset($args["type"]) ? $args["type"] : self::TYPE_SELECT;
		$this->primaryKey = isset($args["primaryKey"]) ? $args["primaryKey"] : "id";

		if (!isset($args["mappedBy"]) && ($this->type === self::TYPE_SELECT))
			throw new UnexceptedMappingException("For type SELECT must by mappedBy defined.");

		if (isset($args["mappedBy"])) {
			$this->mappedBy = $args["mappedBy"];
			$this->orderBy = $this->mappedBy;
		}

		if (isset($args["orderBy"])) {
			$order = Explode(" ", $args["orderBy"]);
			$this->orderBy = $order[0];
			if (isset($order[1])) $this->orderDirection = $order[1];
		}

	}


	/**
	 * @return string
	 */
	public function getMappedBy()
	{
		return $this->mappedBy;
	}


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}


	/**
	 * @return string
	 */
	public function getOrderBy()
	{
		return $this->orderBy;
	}


	/**
	 * @return string
	 */
	public function getOrderDirection()
	{
		return $this->orderDirection;
	}




}