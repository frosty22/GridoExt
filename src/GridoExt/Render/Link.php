<?php

namespace GridoExt\Render;

use Ale\Entities\BaseEntity;

/**
 * Render for link
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Link extends \Nette\Object implements IRender {


	/**
	 * Target URL
	 * @var string|callback
	 */
	private $url;


	/**
	 * @param string|callback $url
	 */
	public function __construct($url)
	{
		$this->url = $url;
	}


	/**
	 * Render modification
	 * @param string $value
	 * @param BaseEntity $entity
	 * @return string
	 */
	public function render($value, BaseEntity $entity)
	{
		if (is_callable($this->url)) {
			$url = call_user_func($this->url, $entity);
		} else {
			$url = $this->url;
		}

		return '<a href="' . $url . '">' . $value . '</a>';
	}

}