<?php

Mock::generate('sfContext');

class sfBreadCrumbTest extends UnitTestCase
{
  private $context;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
  }

  public function test_simple()
  {
    $bc = new sfBreadCrumb($this->context);
    $home_node = new sfBreadCrumbNode('Accueil', 'Vehicles/Index');
    $bc->addBranchNode($home_node);
    $product_node = new sfBreadCrumbNode('Liste des rayons', 'Category/Index');
    $home_node->addChildNode($product_node);
    $reference_node = new sfBreadCrumbNode('Rayon $rayon', 'Category/List');
    $product_node->addChildNode($reference_node);

    $cart_node = new sfBreadCrumbNode('Panier', 'ShoppingCart/Index');
    $bc->addSlipNode($cart_node);

//    $this->assertEqual($url, $r->generate($params, '/', '/'));
  }
}

?>