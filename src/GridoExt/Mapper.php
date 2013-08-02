<?php

namespace GridoExt;

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

}
