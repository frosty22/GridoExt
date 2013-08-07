<?php

namespace GridoExt\Render;
use Ale\Entities\BaseEntity;

/**
 * Interface for renders
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
interface IRender {


	/**
	 * Render
	 * @param string $value
	 * @param BaseEntity $entity
	 * @return string
	 */
	public function render($value, BaseEntity $entity);


}