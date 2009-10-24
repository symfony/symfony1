<?php

/**
 * Translation behavior.
 * 
 * @package     sfPropelPlugin
 * @subpackage  behavior
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id$
 */
class SfPropelBehaviorI18nTranslation extends SfPropelBehaviorBase
{
  protected $parameters = array(
    'culture_column' => null,
  );

  public function objectMethods()
  {
    $column = $this->getTable()->getColumn($this->getParameter('culture_column'));

    return <<<EOF

/**
 * Sets the culture of the current translation.
 */
public function setSymfonyI18nCulture(\$culture)
{
  return \$this->set{$column->getPhpName()}(\$culture);
}

/**
 * Returns the culture of the current translation.
 */
public function getSymfonyI18nCulture()
{
  return \$this->get{$column->getPhpName()}();
}

EOF;
  }

  public function objectFilter(& $script)
  {
    if ($this->isDisabled())
    {
      return;
    }

    $class = new sfClassManipulator($script);
    $class->filterMethod('doSave', array($this, 'filterDoSave'));

    $script = $class->getCode();
  }

  /**
   * Filters each line of the generated doSave method.
   * 
   * @param string $line
   * 
   * @return string
   */
  public function filterDoSave($line)
  {
    $foreignKey = $this->getForeignKey();
    $phpName = $foreignKey->getPhpName() ? $foreignKey->getPhpName() : $foreignKey->getForeignTable()->getPhpName();
    $refPhpName = $foreignKey->getRefPhpName() ? $foreignKey->getRefPhpName() : $this->getTable()->getPhpName();
    $search = sprintf('$this->a%s->isModified()', $phpName);
    $insert = sprintf(' || ($this->a%s->getCulture() && $this->a%1$s->getCurrent%s()->isModified())', $phpName, $refPhpName);

    if (false !== strpos($line, $search))
    {
      $line = str_replace($search, $search.$insert, $line);
    }

    return $line;
  }

  /**
   * Returns the foreign key that references the translated model.
   * 
   * @return ForeignKey
   * 
   * @throws LogicException If the foreign key cannot be found
   */
  public function getForeignKey()
  {
    foreach ($this->getTable()->getForeignKeys() as $fk)
    {
      $behaviors = $fk->getForeignTable()->getBehaviors();
      if (isset($behaviors['symfony_i18n']))
      {
        return $fk;
      }
    }

    throw new Exception('The foreign key that references the I18N model could not be found.');
  }

  /**
   * Returns the current table's culture column.
   * 
   * @return Column
   */
  public function getCultureColumn()
  {
    return $this->getTable()->getColumn($this->getParameter('culture_column'));
  }
}
