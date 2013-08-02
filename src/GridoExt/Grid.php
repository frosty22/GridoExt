<?php

namespace GridoExt;

use GridoExt\Components\Filters\Boolean;
use GridoExt\Components\Actions\Href;

/**
 * Grido component.
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Grid extends \Grido\Grid
{


	/**
	 * @var string
	 */
	protected $rootEntity;


	/**
	 * @param string $rootEntity
	 */
	public function setRootEntity($rootEntity)
	{
		$this->rootEntity = $rootEntity;
	}


	/**
	 * @return string
	 */
	public function getRootEntity()
	{
		return $this->rootEntity;
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @return Boolean
	 */
	public function addFilterBoolean($name, $label)
	{
		return new Boolean($this, $name, $label);
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param string $destination
	 * @param array $args
	 * @return Href
	 */
	public function addActionHref($name, $label, $destination = NULL, array $args = NULL)
	{
		return new Href($this, $name, $label, $destination, $args);
	}


	/**
	 * Shortcut for detail
	 * @param string|null $name
	 * @param string|null $label
	 * @return Href
	 */
	public function addActionDetail($name = NULL, $label = NULL)
	{
		$href = $this->addActionHref($name ? $name : "detail", $label ? $label : "Detail");
		$href->setIcon("info-sign icon-white")
			 ->getElementPrototype()->class = "btn btn-mini btn-primary";
		return $href;
	}


	/**
	 * Shortcut for edit
	 * @param string|null $name
	 * @param string|null $label
	 * @return Href
	 */
	public function addActionEdit($name = NULL, $label = NULL)
	{
		$href = $this->addActionHref($name ? $name : "edit", $label ? $label : "Upravit");
		$href->setIcon("edit icon-white")
			 ->getElementPrototype()->class = "btn btn-mini btn-warning";
		return $href;
	}


	/**
	 * Shortcut for remove
	 * @param string|null $name
	 * @param string|null $label
	 * @param string|null|callback $confirm
	 * @return Href
	 */
	public function addActionRemove($name = NULL, $label = NULL, $confirm = NULL)
	{
		$href = $this->addActionHref($name ? $name : "remove", $label ? $label : "Odstranit", "remove!");
		$href->setIcon("remove icon-white")
			->setConfirm($confirm ? $confirm : "Skutečně si přejete odstranit tento element?")
			->getElementPrototype()->class = "btn btn-mini btn-danger";
		return $href;
	}


}
