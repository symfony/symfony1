[?php

/**
 * <?php echo $this->table->getPhpName() ?> form.
 *
 * @package    form
 * @subpackage <?php echo $this->table->getName() ?>

 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class <?php echo $this->table->getPhpName() ?>Form extends Base<?php echo $this->table->getPhpName() ?>Form
{
  public function configure()
  {
  }
}
