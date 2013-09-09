<?php

namespace GridoExt;

use Grido\Components\Filters\Filter;
use GridoExt\PropertyAccessors\ObjectAccessor;
use EntityMetaReader\ColumnReader;
use EntityMetaReader\Mapping\Name;
use GridoExt\Mapping\Type;
use Nette\Security\User;


/**
 * Grido factory from entities
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class GridoFactory extends \Nette\Object
{


	/**
	 * Alias delimiter of joined entities
	 */
	const ALIAS_DELIMITER = "__";


	/**
	 * Alias for sortable different name
	 */
	const PRIMARY_DELIMITER = "11";


	/**
	 * @var \EntityMetaReader\EntityReader
	 */
	private $reader;


	/**
	 * @var \Kdyby\Doctrine\EntityManager
	 */
	private $entityManager;


	/**
	 * @var \Nette\Security\User
	 */
	private $user;


	/**
	 * @param \EntityMetaReader\EntityReader $reader
	 * @param \Kdyby\Doctrine\EntityManager $entityManager
	 * @param \Nette\Security\User $user
	 */
	public function __construct(\EntityMetaReader\EntityReader $reader, \Kdyby\Doctrine\EntityManager $entityManager, User $user = NULL)
	{
		$this->reader = $reader;
		$this->entityManager = $entityManager;
		$this->user = $user;
	}


	/**
	 * @param Mapper $mapper
	 * @return Grid
	 */
	public function create(Mapper $mapper)
	{
		$grid = new Grid();
		$grid->setPropertyAccessor(new ObjectAccessor());
		$grid->setTranslator(new \Grido\Translations\FileTranslator("cs"));
		$grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

		$selectedParts = $mapper->getSelectedParts();
		foreach ($mapper->getRootEntities($selectedParts) as $alias => $entity) {
			$this->joinEntity($mapper, $grid, $entity, $selectedParts, array($alias));
			$grid->setRootEntity(strpos($entity, "\\") ? substr($entity, strrpos($entity, "\\") + 1) : $entity);
		}

		$model = new \GridoExt\DataSource\Doctrine($mapper->getQueryBuilder(),
						new ColumnAliases(ColumnAliases::TYPE_FILTER),
						new ColumnAliases(ColumnAliases::TYPE_SORT)
				);

		if ($mapper->getCount() !== NULL)
			$model->setCount($mapper->getCount());

		$grid->setModel($model);

		$this->setDefaultSort($grid, $mapper->getQueryBuilder());

		return $grid;
	}


	/**
	 * Join entity to datagrid
	 * @param Mapper $mapper
	 * @param Grid $grid
	 * @param string $entity Entity name
	 * @param array $selectedParts Array of selected parts
	 * @param array $parents Parents
	 */
	protected function joinEntity(Mapper $mapper, Grid $grid, $entity, array &$selectedParts, array $parents)
	{
		$lastParent = end($parents);
		$entityColumns = $this->reader->getEntityColumns($entity);
		foreach ($entityColumns as $column) {
			/** @var \EntityMetaReader\ColumnReader $column */

			// Check access to column
			if (!$this->checkAccess($column)) continue;


			// Check if column is selected
			if (!in_array($lastParent, $selectedParts)) continue;


			if ($column->isEntityType()) {

				$subparents = $parents;
				$subparents[] = $column->getName();

				$type = $column->getAnnotation('GridoExt\Mapping\Type');
				/** @var \GridoExt\Mapping\Type $type */
				if ($type && $type->getType() === $type::TYPE_SELECT) {
					$this->addColumnSelect($mapper, $grid, $column, $subparents);
				} else {
					$this->joinEntity($mapper, $grid, $column->getTargetEntity(), $selectedParts, $subparents);
				}

			}
			elseif ($column->isValueType()) {
				$this->addColumn($mapper, $grid, $column, $parents);
			}
			else {
				// Collections and others are ignored ...
			}

		}
	}


	/**
	 * Add column to datadagrid via reader
	 * @param Mapper $mapper
	 * @param Grid $grid
	 * @param ColumnReader $column
	 * @param array $parents
	 * @throws MissingServiceException
	 * @throws UnexceptedMappingException
	 */
	protected function addColumn(Mapper $mapper, Grid $grid, \EntityMetaReader\ColumnReader $column, array $parents)
	{
		$label = $this->getColumnLabel($column);
		$columnName = $this->getColumnName($column, $parents);

		if ($mapper->isHidden($column->getEntity(), $column->getName()))
			return;

		$render = $mapper->getRender($column->getEntity(), $column->getName());
		$valueRender = new ValueRender($column, $parents, $render);

		$columnMapping = $column->getAnnotation("Doctrine\\ORM\\Mapping\\Column");
		if (!$columnMapping instanceof \Doctrine\ORM\Mapping\Column)
			throw new UnexceptedMappingException("Invalid column mapping.");

		$select = $column->getAnnotation("GridoExt\\Mapping\\Select");
		if ($select !== NULL) {
			/** @var \GridoExt\Mapping\Select $select */
			$col = $grid->addColumnText($columnName, $label);
			$col->setFilterSelect($select->getMapped());
			$col->setReplacement($select->getReplacement());
			$col->setSortable();
			return;
		}

		switch ($columnMapping->type) {
			case "string":
			case "text":
				$grid->addColumnText($columnName, $label)
					->setCustomRender(array($valueRender, "renderString"))
					->setSortable()
					->setFilterText()
					->setSuggestion();
				break;

			case "array":
				$grid->addColumnText($columnName, $label)
					->setCustomRender(array($valueRender, "renderArray"));
				break;

			case "integer":
			case "smallint":
			case "bigint":
				$col = $grid->addColumnNumber($columnName, $label)
					->setCustomRender(array($valueRender, "renderInteger"))
					->setSortable();
				$col->cellPrototype->class[] = 'text-right';
				$col->setFilterNumber();
				break;

			case "boolean":
				$col = $grid->addColumn($columnName, $label)
					->setCustomRender(array($valueRender, "renderBoolean"))
					->setSortable();
				$col->cellPrototype->class[] = 'text-center';
				$col->setFilter('GridoExt\Components\Filters\Boolean');
				break;

			case "decimal":
			case "float":
				$col = $grid->addColumnNumber($columnName, $label)
					->setCustomRender(array($valueRender, "renderFloat"))
					->setSortable();
				$col->cellPrototype->class[] = 'text-right';
				$col->setFilterNumber();
				break;

			case "date":

				$configuration = $this->entityManager->getConfiguration();

				if ($configuration->getCustomDatetimeFunction("YEAR") === NULL)
					throw new \GridoExt\MissingServiceException("Custom DateTime function 'YEAR' in Doctrine is required.");

				if ($configuration->getCustomDatetimeFunction("MONTH") === NULL)
					throw new \GridoExt\MissingServiceException("Custom DateTime function 'MONTH' in Doctrine is required.");

				if ($configuration->getCustomDatetimeFunction("DAY") === NULL)
					throw new \GridoExt\MissingServiceException("Custom DateTime function 'DAY' in Doctrine is required.");

				$col = $grid->addColumnDate($columnName, $label)
					->setCustomRender(array($valueRender, "renderDate"))
					->setSortable();
				$col->cellPrototype->class[] = 'text-right';
				$col->setFilterDate()
					->setCondition(\GridoExt\Components\Filters\Filter::CONDITION_CALLBACK,
					function($value) use ($columnName) {
						$date = \DateTime::createFromFormat("j. n. Y", $value);
						if (!$date) return NULL;
						return array("YEAR([{$columnName}]) = YEAR(%s) AND
									  MONTH([{$columnName}]) = MONTH(%s) AND
									  DAY([{$columnName}]) = DAY(%s)", $date);
					});
				break;

			case "time":
				// TODO: Filter via TIME!
				$col = $grid->addColumnDate($columnName, $label)
					->setCustomRender(array($valueRender, "renderTime"))
					->setSortable();
				$col->cellPrototype->class[] = 'text-center';
				$col->setFilterDate();
				break;

			case "datetime":

				$configuration = $this->entityManager->getConfiguration();

				if ($configuration->getCustomDatetimeFunction("YEAR") === NULL)
					throw new \GridoExt\MissingServiceException("Custom DateTime function 'YEAR' in Doctrine is required.");

				if ($configuration->getCustomDatetimeFunction("MONTH") === NULL)
					throw new \GridoExt\MissingServiceException("Custom DateTime function 'MONTH' in Doctrine is required.");

				if ($configuration->getCustomDatetimeFunction("DAY") === NULL)
					throw new \GridoExt\MissingServiceException("Custom DateTime function 'DAY' in Doctrine is required.");

				$col = $grid->addColumnDate($columnName, $label)
					->setCustomRender(array($valueRender, "renderDateTime"))
					->setSortable();
				$col->cellPrototype->class[] = 'text-center';
				$col->setFilterDate()
					->setCondition(\Grido\Components\Filters\Filter::CONDITION_CALLBACK,
					function($value) use ($columnName) {
						$date = \DateTime::createFromFormat("j. n. Y", $value);
						if (!$date) return NULL;
						return array("YEAR([{$columnName}]) = YEAR(%s) AND
									  MONTH([{$columnName}]) = MONTH(%s) AND
									  DAY([{$columnName}]) = DAY(%s)", $date);
					});
				break;

			case "object":
				// Object type doesn't supported, so ignore it.
				break;

			default:
				throw new UnexceptedMappingException("Invalid column type mapping.");
		}
	}


	/**
	 * @param Mapper $mapper
	 * @param \Grido\Grid $grid
	 * @param ColumnReader $column
	 * @param array $parents
	 */
	protected function addColumnSelect(Mapper $mapper, \Grido\Grid $grid, \EntityMetaReader\ColumnReader $column, array $parents)
	{
		$type = $column->getAnnotation('GridoExt\Mapping\Type');
		/** @var Type $type */

		$targetEntity = $this->reader->getEntityColumns($column->getTargetEntity());

		if ($mapper->isHidden($column->getEntity(), $column->getName()))
			return;

		$label = $this->getColumnLabel($column);
		$columnName = $this->getColumnName($targetEntity[$type->getMappedBy()], $parents, $targetEntity[$type->getPrimaryKey()]);
		$valueRender = new ValueRender($targetEntity[$type->getMappedBy()], $parents);

		$items = array(NULL => "Vše");

		$filterItems = $this->entityManager->getRepository($column->getTargetEntity())
									->findBy(array(), array($type->getOrderBy() => $type->getOrderDirection()));
		foreach ($filterItems as $filterItem) {
			$items[$filterItem->{$type->getPrimaryKey()}] = $filterItem->{$type->getMappedBy()};
		}

		$grid->addColumnText($columnName, $label)
			->setCustomRender(array($valueRender, "renderString"))
			->setSortable()
			->setFilterSelect($items);
	}


	/**
	 * Set default sort from QueryBuilder and remove order from it.
	 * @param Grid $grid
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 */
	protected function setDefaultSort(Grid $grid, \Doctrine\ORM\QueryBuilder $qb)
	{
		$default = array();

		$orders = $qb->getDQLPart("orderBy");
		foreach ($orders as $order) {
			/** @var \Doctrine\ORM\Query\Expr\OrderBy $order */
			foreach ($order->getParts() as $part) {
				$parts = Explode(" ", $part);
				$parts[0] = substr($parts[0], strpos($parts[0], ".") + 1);
				$default[str_replace(".", self::ALIAS_DELIMITER, $parts[0])] = isset($parts[1]) ? $parts[1] : "ASC";
			}
		}
		$qb->resetDQLPart("orderBy");
		$grid->setDefaultSort($default);
	}


	/**
	 * Get column name
	 * @param ColumnReader $column
	 * @param array $parents
	 * @param ColumnReader $primaryColumn
	 * @return string
	 */
	protected function getColumnName(ColumnReader $column, array $parents, ColumnReader $primaryColumn = NULL)
	{
		$columnName = "";
		if (count($parents) > 1) {
			for ($i = 1; $i < count($parents); $i++) $columnName .= $parents[$i] . self::ALIAS_DELIMITER;
		}

		$columnName .= $column->getName();

		if ($primaryColumn !== NULL)
			$columnName .= self::PRIMARY_DELIMITER . $primaryColumn->getName();

		return $columnName;
	}


	/**
	 * Get column label
	 * @param ColumnReader $column
	 * @return string
	 */
	protected function getColumnLabel(ColumnReader $column)
	{
		$name = $column->getAnnotation('EntityMetaReader\Mapping\Name', TRUE, $column->getName());
		/** @var Name $name */
		return $name->getName();
	}


	/**
	 * Check access to column (for read of course)
	 * @param ColumnReader $column
	 * @return bool
	 */
	protected function checkAccess(ColumnReader $column)
	{
		$access = $column->getAnnotation('EntityMetaReader\Mapping\Access', TRUE);
		/** @var \EntityMetaReader\Mapping\Access $access */

		if (!$this->user) return $access->isReadable();
		return $access->checkReadAccess($this->user);
	}



}
