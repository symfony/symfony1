[?php

/**
 * <?php echo $this->table->getPhpName() ?> form base class.
 *
 * @package    form
 * @subpackage <?php echo $this->table->getName() ?>

 * @version    SVN: $Id$
 */
class Base<?php echo $this->table->getPhpName() ?>Form extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
<?php foreach ($this->table->getColumns() as $column): ?>
      '<?php echo strtolower($column->getColumnName()) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($column->getColumnName())) ?> => new <?php echo $this->getWidgetClassForColumn($column) ?>(<?php echo $this->getWidgetOptionsForColumn($column) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyTables() as $tables): ?>
      '<?php echo $tables['relatedTable']->getName() ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($tables['relatedTable']->getName().'_list')) ?> => new sfWidgetFormSelectMany(array('choices' => new sfCallable(array($this, 'get<?php echo $tables['middleTable']->getPhpName() ?>Choices')))),
<?php endforeach; ?>
    ));

    $this->setValidators(array(
<?php foreach ($this->table->getColumns() as $column): ?>
      '<?php echo strtolower($column->getColumnName()) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($column->getColumnName())) ?> => new <?php echo $this->getValidatorClassForColumn($column) ?>(<?php echo $this->getValidatorOptionsForColumn($column) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyTables() as $tables): ?>
      '<?php echo $tables['relatedTable']->getName() ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($tables['relatedTable']->getName().'_list')) ?> => new sfValidatorChoiceMany(array('choices' => new sfCallable(array($this, 'get<?php echo $tables['middleTable']->getPhpName() ?>IdentifierChoices')), 'required' => false)),
<?php endforeach; ?>
    ));

    $this->widgetSchema->setNameFormat('<?php echo $this->table->getName() ?>[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return '<?php echo $this->table->getPhpName() ?>';
  }

<?php if ($this->isI18n()): ?>
  public function getI18nModelName()
  {
    return '<?php echo $this->getI18nModel() ?>';
  }

  public function getI18nFormClass()
  {
    return '<?php echo $this->getI18nModel() ?>Form';
  }
<?php endif; ?>

<?php foreach ($this->getForeignKeyNames() as $info): $name = $info[1] ?>
  public function get<?php echo $name ?>IdentifierChoices()
  {
    return array_keys($this->get<?php echo $name ?>Choices());
  }

  public function get<?php echo $name ?>Choices()
  {
    if (!isset($this-><?php echo $name ?>Choices))
    {
      $this-><?php echo $name ?>Choices = array(<?php !$info[2] && !$info[3] and print "'' => ''" ?>);
      foreach (<?php echo $info[0] ?>Peer::doSelect(new Criteria(), $this->getConnection()) as $object)
      {
        $this-><?php echo $name ?>Choices[$object->get<?php echo $this->getPrimaryKey()->getPhpName() ?>()] = $object->__toString();
      }
    }

    return $this-><?php echo $name ?>Choices;
  }
<?php endforeach; ?>

<?php if ($this->getManyToManyTables()): ?>
  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

<?php foreach ($this->getManyToManyTables() as $tables): ?>
    if (isset($this->widgetSchema['<?php echo $tables['relatedTable']->getName() ?>_list']))
    {
      $values = array();
      foreach ($this->object->get<?php echo $tables['middleTable']->getPhpName() ?>s() as $obj)
      {
        $values[] = $obj->get<?php echo $tables['relatedColumn']->getPhpName() ?>();
      }

      $this->setDefault('<?php echo $tables['relatedTable']->getName() ?>_list', $values);
    }
<?php endforeach; ?>
  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

<?php foreach ($this->getManyToManyTables() as $tables): ?>
    $this->save<?php echo $tables['relatedTable']->getPhpName() ?>List($con);
<?php endforeach; ?>
  }

<?php foreach ($this->getManyToManyTables() as $tables): ?>
  public function save<?php echo $tables['relatedTable']->getPhpName() ?>List($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['<?php echo $tables['relatedTable']->getName() ?>_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(<?php echo $tables['middleTable']->getPhpName() ?>Peer::<?php echo strtoupper($tables['column']->getColumnName()) ?>, $this->object->getPrimaryKey());
    <?php echo $tables['middleTable']->getPhpName() ?>Peer::doDelete($c, $con);

    $values = $this->getValues();
    if (is_array($values['<?php echo $tables['relatedTable']->getName() ?>_list']))
    {
      foreach ($values['<?php echo $tables['relatedTable']->getName() ?>_list'] as $value)
      {
        $obj = new <?php echo $tables['middleTable']->getPhpName() ?>();
        $obj->set<?php echo $tables['column']->getPhpName() ?>($this->object->getPrimaryKey());
        $obj->set<?php echo $tables['relatedColumn']->getPhpName() ?>($value);
        $obj->save();
      }
    }
  }
<?php endforeach; ?>
<?php endif; ?>
}
