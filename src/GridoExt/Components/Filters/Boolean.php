<?php

namespace GridoExt\Components\Filters;

/**
 * Check filter, support for boolean values (checked is condition for TRUE)
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Boolean extends \Grido\Components\Filters\Check
{


	/**
	 * @param string $column
	 * @param string $value
	 * @return array
	 */
	public function _makeFilter($column, $value)
	{
		return array("[$column] = %i", 1);
	}


}
