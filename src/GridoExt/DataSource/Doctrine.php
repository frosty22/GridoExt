<?php

namespace GridoExt\DataSource;

use Nette\Utils\Strings;
use	Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Extended data source for better support of Doctrine nad performance.
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Doctrine extends \Grido\DataSources\Doctrine
{

	const COUNT_DISTINCT = "distinct";
	const COUNT_SIMPLE_COUNT = "count";


	/**
	 * @var bool
	 */
	private $fetchJoinCollection = TRUE;


	/**
	 * @var int|string
	 */
	private $count = self::COUNT_DISTINCT;


	/**
	 * @param boolean $fetch
	 */
	public function setFetchJoinCollection($fetch)
	{
		$this->fetchJoinCollection = $fetch;
	}


	/**
	 * @param int|string $count
	 */
	public function setCount($count)
	{
		$this->count = $count;
	}


	/**
	 * It is possible to use query builder with additional columns.
	 * In this case, only item at index [0] is returned, because
	 * it should be an entity object.
	 * @return array
	 */
	public function getData()
	{
		// Paginator is better if the query uses ManyToMany associations
		$usePaginator = $this->qb->getMaxResults() !== NULL || $this->qb->getFirstResult() !== NULL;
		$data = array();

		if ($usePaginator) {
			$paginator = new Paginator($this->getQuery(), $this->fetchJoinCollection);

			// Convert paginator to the array
			foreach ($paginator as $result) {
				// Return only entity itself
				$data[] = is_array($result)
					? $result[0]
					: $result;
			}
		} else {

			foreach ($this->qb->getQuery()->getResult() as $result) {
				// Return only entity itself
				$data[] = is_array($result)
					? $result[0]
					: $result;
			}
		}

		return $data;
	}


	/**
	 * @return int
	 */
	public function getCount()
	{
		if ($this->count === self::COUNT_DISTINCT) {
			$paginator = new Paginator($this->getQuery());
			return $paginator->count();
		}

		if ($this->count === self::COUNT_SIMPLE_COUNT) {
			$qb = clone $this->getQb();
			$alias = current($qb->getRootAliases());
			return $qb->select("COUNT({$alias}.id)")->getQuery()->getSingleScalarResult();
		}

		return $this->count;
	}


}
