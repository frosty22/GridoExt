<?php

namespace GridoExt\Mapping;

/**
 *
 * Select filter mapping
 *
 * @Annotation
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Select implements \EntityMetaReader\Annotation {


	const MSG_ALL = "-- vše --";


	/**
	 * @var array
	 */
	private $mapped = array();


	/**
	 * @param array $args
	 */
	public function __construct(array $args)
	{
		if (isset($args["mapping"])) {
			$this->mapped = $args["mapping"];
		}

	}


	/**
	 * @return array
	 */
	public function getMapped()
	{
		return array("" => self::MSG_ALL) + $this->mapped;
	}


	/**
	 * @return array
	 */
	public function getReplacement()
	{
		return $this->mapped;
	}

}