<?php

namespace GridoExt;
use GridoExt\Render\IRender;
use GridoExt\Render\Link;

/**
 * Grido cols mapper
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Mapper extends \Nette\Object
{


	/**
	 * @var \Doctrine\ORM\QueryBuilder
	 */
	private $queryBuilder;


	/**
	 * List of renders
	 * @var array
	 */
	private $renders = array();


	/**
	 * List to hide
	 * @var array
	 */
	private $hide = array();


	/**
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 */
	public function __construct(\Doctrine\ORM\QueryBuilder $qb)
	{
		$this->queryBuilder = $qb;
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getQueryBuilder()
	{
		return $this->queryBuilder;
	}


	/**
	 * Get all selected entities
	 * @return array
	 */
	public function getEntities()
	{
		$selected = $this->getSelectedParts();
		$entities = array_merge($this->getRootEntities($selected), $this->getJoinedEntities($selected));
		return $entities;
	}


	/**
	 * Return filtered root entities
	 * @param array $aliases Allowed aliases
	 * @return array
	 */
	public function getRootEntities(array $aliases = NULL)
	{
		$entities = array();

		$froms = $this->queryBuilder->getDQLPart("from");
		foreach ($froms as $from) {
			/** @var \Doctrine\ORM\Query\Expr\From $from */
			if (is_null($aliases) || in_array($from->getAlias(), $aliases)) {
				$entities[$from->getAlias()] = $from->getFrom();
			}
		}

		return $entities;
	}


	/**
	 * Return joined entities
	 * @param array $aliases
	 * @return array
	 */
	protected function getJoinedEntities(array $aliases)
	{
		$entities = array();

		$rootEntities = $this->getRootEntities();

		$joinRoots = $this->queryBuilder->getDQLPart("join");
		foreach ($joinRoots as $root => $joins) {
			foreach ($joins as $join) {
				/** @var \Doctrine\ORM\Query\Expr\Join $join */
				if (in_array($join->getAlias(), $aliases)) {
					$fieldName = mb_substr($join->getJoin(), mb_strpos($join->getJoin(), ".") + 1);
					$metadata = $this->queryBuilder->getEntityManager()
									->getClassMetadata($rootEntities[$root])
									->getAssociationMapping($fieldName);

					$entities[$join->getAlias()] = $metadata["targetEntity"];
				}
			}
		}

		return $entities;
	}


	/**
	 * Get selected parts of query
	 * @return array
	 */
	public function getSelectedParts()
	{
		$parts = array();

		$selects = $this->queryBuilder->getDQLPart("select");
		foreach ($selects as $select) {
			/** @var \Doctrine\ORM\Query\Expr\Select $select */
			foreach ($select->getParts() as $part) {
				$parts[] = $part;
			}
		}

		return $parts;
	}


	/**
	 * Add link
	 * @param string $entity
	 * @param string $property
	 * @param string|callback $link
	 * @internal param string $alias
	 */
	public function addLink($entity, $property, $link)
	{
		$this->addCustomRender($this->sanitizeNamespace($entity), $property, new Link($link));
	}


	/**
	 * Add custom render modificator
	 * @param string $entity
	 * @param string $property
	 * @param IRender|callable $render
	 * @throws InvalidValueException
	 */
	public function addCustomRender($entity, $property, $render)
	{
		$entity = $this->sanitizeNamespace($entity);

		if (!$render instanceof IRender && !is_callable($render))
			throw new \GridoExt\InvalidValueException("Render must be callback or IRender, but " . gettype($render) . " given");

		if (!$this->isSelected($entity, $property))
			throw new \GridoExt\InvalidValueException("Entity '$entity' or '$property' of this entity not found or not selected.");

		if (!isset($this->renders[$entity]))
			$this->renders[$entity] = array();

		$this->renders[$entity][$property] = $render;
	}


	/**
	 * Own render
	 * @param string $entity
	 * @param string $property
	 * @return null|IRender
	 */
	public function getRender($entity, $property)
	{
		$entity = $this->sanitizeNamespace($entity);
		return isset($this->renders[$entity][$property]) ? $this->renders[$entity][$property] : NULL;
	}


	/**
	 * Hide property
	 * @param string $entity
	 * @param string $property
	 * @return $this
	 */
	public function hide($entity, $property)
	{
		$entity = $this->sanitizeNamespace($entity);

		if (!isset($this->hide[$entity]))
			$this->hide[$entity] = array();

		$this->hide[$entity][$property] = TRUE;

		return $this;
	}


	/**
	 * Is hidden
	 * @param string $entity
	 * @param string $property
	 * @return bool
	 */
	public function isHidden($entity, $property)
	{
		return isset($this->hide[$this->sanitizeNamespace($entity)][$property]);
	}


	/**
	 * Check if is selected property of entity
	 * @param string $entity
	 * @param string $property
	 * @return bool
	 */
	private function isSelected($entity, $property)
	{
		// TODO: Checking if is selected
		return TRUE;
	}


	/**
	 * Sanitize namespace
	 * @param string $namespace
	 * @return string
	 */
	private function sanitizeNamespace($namespace)
	{
		return $namespace[0] === '\\' ? mb_substr($namespace, 1) : $namespace;
	}


}
