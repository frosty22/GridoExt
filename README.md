GridoExt
========

Továrnička pro datagrid Grido, která na základě vlastních anotací u entit vytváří generovaný datagrid pro danou query.
Založeno na super datagridu Grido: http://o5.github.io/grido-sandbox/


Z hlediska API je zde podstatný pouze objekt GridoFactory, který je základní kámen této komponenty a vytváří instanci komponenty Grido, která již může být poté vykreslena. Tato továrnička přijímá GridoExt\Mapper, který přijímá QueryBuilder.

> Jsem člověk líný a razím pravidlo: "Čím méňě kódu pro implementaci tím lépe.", tudíž z valné většiny nejsou potřeba žádné výchozí anotace (Format, Type, ...), pouze slouží pro přepsání výchozího chování.


Mapping
-------

Součástí knihovny je několik objektů reprezentující anotace daných entit:

> Základem této továrničky je EntityMetaReader a Ale, viz závislosti v composer

- Format - umožňuje definovat vlastní formát pro vykreslení hodnot dané property
- Type - umožňuje definovat datový typ pro hodnotu - vhodné pro kolekce
- Select - slouží k vytvoření pole, které se namapuje na hodnoty


Příklad
-------

Příklad entity:

```php
use EntityMetaReader\Mapping as EMR;
use Doctrine\ORM\Mapping as ORM;
use GridoExt\Mapping as GRID;


class Product extends Ale\Entities\BaseEntity {

	/**
	 * @EMR\Name("Název produktu")
	 * @GRID\Format(empty="nevyplněno")
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $name;


	/**
	 * @EMR\Name("Expirace produktu")
	 * @EMR\Access(read="admin")
	 * @GRID\Format("j.n.Y H:i")
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $expire;


	/**
     * @EMR\Name("Jméno uživatele")
     * @GRID\Type(type="select", mappedBy="name")
     * @ORM\ManyToOne(targetEntity="User")
     * @var User
     */
    protected $user;


    /**
     * @EMR\Name("Stav produktu")
     * @GRID\Select(mapping={1 = 'Schválen', 2 = 'Zamítnut', 3 = 'Čeká'})
     * @ORM\Column(type="smallint")
     * @var int
     */
    protected $state;

}
```


Jednoduché vytvoření datagridu, například pomocí továrničky:

```php
class FooPresenter extends Presenter {


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

		// Některé sloupce můžeme chtít v konkrétním gridu skýt - Skrytí sloupců "sale", "price"
		$map->hide('Entity\Product', array('sale', 'price'));

		// Můžeme chtít přidat odkaz na hodnotu ve sloupci - přidá odkaz na sloupec "name" vedoucí na "detail" s parametrem ID
		$map->link('Entity\Product, 'name', function($product){ return $this->link("detail", $product->id); });

		// Případně můžeme vnutit i zde vlastní render (pokud nechceme globálně přes anotace entity)
		$map->addCustomRender('Entity\Product', 'price', function($product){ return $product->price . ",-"; });

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


}
```


