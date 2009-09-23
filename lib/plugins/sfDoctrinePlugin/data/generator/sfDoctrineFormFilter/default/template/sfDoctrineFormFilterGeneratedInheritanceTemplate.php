[?php

/**
 * <?php echo $this->table->getOption('name') ?> filter form base class.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage filter
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id$
 */
class Base<?php echo $this->table->getOption('name') ?>FormFilter extends <?php echo $this->getFormClassToExtend() . "\n" ?>
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

<?php foreach ($this->getManyToManyRelations() as $relation): ?>
    $this->widgetSchema   ['<?php echo $this->underscore($relation['alias']) ?>_list'] = new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => '<?php echo $relation['table']->getOption('name') ?>'));
    $this->validatorSchema['<?php echo $this->underscore($relation['alias']) ?>_list'] = new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => '<?php echo $relation['table']->getOption('name') ?>', 'required' => false));
<?php endforeach; ?>

    $this->widgetSchema->setNameFormat('<?php echo $this->underscore($this->modelName) ?>_filters[%s]');
  }

<?php foreach ($this->getManyToManyRelations() as $relation): ?>
  public function add<?php echo sfInflector::camelize($relation['alias']) ?>ListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.<?php echo $relation['refTable']->getOption('name') ?> <?php echo $relation['refTable']->getOption('name') ?>')
          ->andWhereIn('<?php echo $relation['refTable']->getOption('name') ?>.<?php echo $relation->getForeignFieldName() ?>', $values);
  }

<?php endforeach; ?>
  public function getModelName()
  {
    return '<?php echo $this->modelName ?>';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
<?php foreach ($this->getManyToManyRelations() as $relation): ?>
      '<?php echo $this->underscore($relation['alias']) ?>_list' => 'ManyKey',
<?php endforeach; ?>
    ));
  }
}
