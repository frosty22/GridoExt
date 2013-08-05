GridoExt
========

Továrnička pro datagrid Grido, která na základě vlastních anotací u entit vytváří generovaný datagrid pro danou query.
Založeno na super datagridu Grido: http://o5.github.io/grido-sandbox/


Z hlediska API je zde podstatný pouze objekt GridoFactory, který je základní kámen této komponenty a vytváří instanci komponenty Grido, která již může být poté vykreslena. Tato továrnička přijímá GridoExt\Mapper, který přijímá QueryBuilder.


Příklad
-------

Jednoduché vytvoření datagridu, například pomocí továrničky:

```php

	/**
	 * @var \GridoExt\GridoFactory
	 */
	protected $gridoFactory;


	protected function createComponentProductGrid($name)
	{
		// Vytvoření query pomocí QueryBuilderu
		$qb = $this->entityManager->createQueryBuilder()
			->select("product", "order", "category")  // Slouží zároveň i jako definice, entit které se mají zobrazit v gridu
			->from("Product", "product")
			->leftJoin("product.order", "order")
			->leftJoin("product.category", "category")
			->orderBy("product.created", "DESC"); // Defaultní řazení výsledků, datagridu může přepsat

		$map = new \GridoExt\Mapper($qb); // Vytvoření mapperu pro továrničku
		$grido = $this->gridoFactory->create($map); // Vytvoření instance Grido pomocí továrničky

		$grido->addActionHref("foo", "Foo"); // Vytvoření odkazu na actionFoo a předání parametru product
		$grido->addActionDetail(); // Zkratka pro vytvoření odkazu na actionDetail (příslušná barva, ikona tlačítka)
		$grido->addActionEdit();   // Zkratka pro vytvoření odkazu na actionEdit (příslušná barva, ikona tlačítka)
		$grido->addActionRemove(); // Zkratka pro vytvoření odkazu na handleRemove (příslušná barva, ikona tlačítka, JS potvrzení)

		return $grido;
	}


	public function actionFoo(Product $product)
	{
	   ...
	}


	public function actionDetail(Product $product)
	{
	   ...
	}


	public function actionEdit(Product $product)
	{
	   ...
	}


	public function handleRemove(Product $product)
	{
	   ...
	}

```