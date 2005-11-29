<?php

require_once 'symfony/addon/sfShoppingCart/sfShoppingCart.class.php';
require_once 'symfony/addon/sfShoppingCart/sfShoppingCartItem.class.php';

Mock::generate('sfContext');

class sfShoppingCartTest extends UnitTestCase
{
  private $context;
  private $routing;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
    $this->cart = new sfShoppingCart();
  }

  public function test_basic()
  {
    $this->assertEqual(0, $this->cart->getTotal());
    $this->assertEqual(array(), $this->cart->getItems());
  }

  public function test_unit_price_with_taxes()
  {
    $this->cart->setUnitPriceWithTaxes(true);

    $item = new sfShoppingCartItem('Product', 1);
    $item->setQuantity(10);
    $item->setPrice(10);
    $this->cart->addItem($item);
    $this->assertEqual(100 / (1 + $this->cart->getTax() / 100), $this->cart->getTotal());
    $this->assertEqual(100, $this->cart->getTotalWithTaxes());

    $this->cart->clear();

    $this->cart->setUnitPriceWithTaxes(false);

    $item = new sfShoppingCartItem('Product', 1);
    $item->setQuantity(10);
    $item->setPrice(10);
    $this->cart->addItem($item);
    $this->assertEqual(100 * (1 + $this->cart->getTax() / 100), $this->cart->getTotalWithTaxes());
    $this->assertEqual(100, $this->cart->getTotal());
  }

  public function test_modify_items()
  {
    $item = new sfShoppingCartItem('Product', 1);
    $item->setQuantity(1);
    $item->setPrice(10);
    $item->setWeight(0.1);
    $this->cart->addItem($item);

    $item->setQuantity(10);
    $this->assertEqual(100, $this->cart->getTotal());

    $item->setDiscount(10);
    $this->assertEqual(90, $this->cart->getTotal());

    $this->assertEqual(1, $this->cart->getTotalWeight());

    $item->setWeight(0.5);
    $this->assertEqual(5, $this->cart->getTotalWeight());
  }

  public function test_add_items()
  {
    $item = new sfShoppingCartItem('Product', 1);
    $item->setQuantity(1);
    $item->setPrice(10);
    $this->cart->addItem($item);
    $this->assertEqual(10, $this->cart->getTotal());

    $itema = new sfShoppingCartItem('Product', 2);
    $itema->setQuantity(1);
    $itema->setPrice(10);
    $this->cart->addItem($itema);
    $this->assertEqual(false, $this->cart->isEmpty());
    $this->assertEqual(20, $this->cart->getTotal());

    $item->setQuantity(0);
    $itema->setQuantity(0);
    $this->assertEqual(0, $this->cart->getTotal());
    $this->assertEqual(array(), $this->cart->getItems());

    $item = new sfShoppingCartItem('Product', 1);
    $item->setQuantity(2);
    $item->setPrice(1.30);
    $this->cart->addItem($item);
    $this->assertEqual(2.60, $this->cart->getTotal());
  }

  public function test_delete_items()
  {
    $item1 = new sfShoppingCartItem('Product', 1);
    $item1->setQuantity(1);
    $item1->setPrice(10);
    $this->cart->addItem($item1);

    $item2 = new sfShoppingCartItem('Product', 2);
    $item2->setQuantity(1);
    $item2->setPrice(10);
    $this->cart->addItem($item2);

    $item3 = new sfShoppingCartItem('Product', 3);
    $item3->setQuantity(1);
    $item3->setPrice(10);
    $this->cart->addItem($item3);

    $item4 = new sfShoppingCartItem('Product', 4);
    $item4->setQuantity(1);
    $item4->setPrice(10);
    $this->cart->addItem($item4);

    $this->cart->deleteItem('Product', 2);

    $this->assertEqual(false, $this->cart->isEmpty());
    $this->assertEqual(30, $this->cart->getTotal());

    $this->cart->deleteItem('Product', 3);

    $item4->setQuantity(2);
    $this->assertEqual(30, $this->cart->getTotal());
  }

  public function test_update_quantity()
  {
    $item = new sfShoppingCartItem('Product', 2);
    $item->setQuantity(1);
    $item->setPrice(10);
    $this->cart->addItem($item);
    $item->setQuantity(5);
    $this->assertEqual(50, $this->cart->getTotal());
  }

  public function test_clear()
  {
    $item = new sfShoppingCartItem('Product', 2);
    $item->setQuantity(1);
    $item->setPrice(10);
    $this->cart->addItem($item);

    $this->cart->clear();
    $this->assertEqual(0, $this->cart->getTotal());
    $this->assertEqual(array(), $this->cart->getItems());

    $item = new sfShoppingCartItem('Product', 2);
    $item->setQuantity(1);
    $item->setPrice(10);
    $this->cart->addItem($item);

    $item = new sfShoppingCartItem('AnotherProduct', 10);
    $item->setQuantity(3);
    $item->setPrice(100.5);
    $this->cart->addItem($item);

    $this->cart->clear();
    $this->assertEqual(0, $this->cart->getTotal());
    $this->assertEqual(array(), $this->cart->getItems());
    $this->assertEqual(true, $this->cart->isEmpty());
  }

  public function test_discount()
  {
    $item = new sfShoppingCartItem('Product', 100);
    $item->setQuantity(1);
    $item->setPrice(10);
    $item->setDiscount(10);
    $this->cart->addItem($item);
    $this->assertEqual(9, $this->cart->getTotal());

    $item = new sfShoppingCartItem('AnotherProduct', 33);
    $item->setQuantity(1);
    $item->setPrice(5.3);
    $item->setDiscount(10);
    $this->cart->addItem($item);
    $this->assertEqual(13.77, $this->cart->getTotal());
  }
}

?>
