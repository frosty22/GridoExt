<?php

namespace GridoExt\Components\Actions;

use Ale\Entities\BaseEntity;
use GridoExt\Grid;
use GridoExt\InvalidStateException;

/**
 * Extended type of action href
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Href extends \Grido\Components\Actions\Href {


	/**
	 * @var string
	 */
	protected $name;


	/**
	 * @param Grid $grid
	 * @param string $name
	 * @param string $label
	 * @param string $destination - first param for method $presenter->link()
	 * @param array $args - second param for method $presenter->link()
	 */
	public function __construct(Grid $grid, $name, $label, $destination = NULL, array $args = NULL)
	{
		parent::__construct($grid, $name, $label, $destination, $args);
		$this->name = $name;
	}


	/**
	 * @param mixed $item
	 * @throws \GridoExt\InvalidStateException
	 */
	public function render($item)
	{
		$rootEntity = $this->grid->getRootEntity();
		if (!$rootEntity)
			throw new InvalidStateException("Root entity must be set for Href action use.");

		$destination = $this->destination ? $this->destination : $this->name;
		$args = $this->arguments;
		$pk = $this->getPrimaryKey();

		$presenter = $this->grid->getPresenter(TRUE);
		$this->setCustomHref(function(BaseEntity $entity) use ($presenter, $destination, $args, $rootEntity, $pk) {
			$target = array(lcfirst($rootEntity) => $entity->$pk);
			$args = $args === NULL ? $target : array_merge($args, $target);
			return $presenter->link($destination, $args);
		});

		parent::render($item);
	}


}