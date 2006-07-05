<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     François Zaninotto <francois.zaninotto@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelDatabaseSchema
{
  protected $schema = array();

  public function loadYAML($file)
  {
    $this->schema = sfYaml::load($file);

    if (count($this->schema) > 1)
    {
      throw new sfException('A schema.yml must only contain 1 database entry.');
    }

    $this->fixYAML();
  }

  public function asArray()
  {
    return $this->schema;
  }

  public function asYAML()
  {
    return sfYaml::dump($this->schema);
  }

  public function asXML()
  {
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

    foreach ($this->schema as $db_name => $database)
    {
      $xml .= "\n<database name=\"$db_name\"".$this->getAttributesFor($database).">\n";

      // tables
      foreach ($this->getChildren($database) as $tb_name => $table)
      {
        $xml .= "\n  <table name=\"$tb_name\"".$this->getAttributesFor($table).">\n";

        // columns
        foreach ($this->getChildren($table) as $col_name => $column)
        {
          $xml .= "    <column name=\"$col_name\"".$this->getAttributesForColumn($col_name, $column);
        }

        // indexes
        if (isset($table['_indexes']))
        {
          foreach ($table['_indexes'] as $index_name => $index)
          {
            $xml .= "    <index name=\"$index_name\">\n";
            foreach ($index as $index_column)
            {
              $xml .= "      <index-column name=\"$index_column\" />\n";
            }
            $xml .= "    </index>\n";
          }
        }

        // foreign-keys
        if (isset($table['_foreign_keys']))
        {
          foreach ($table['_foreign_keys'] as $fkey_table => $fkey)
          {
            $xml .= "    <foreign-key foreignTable=\"$fkey_table\"".$this->getAttributesFor($fkey).">\n";
            if (isset($fkey['references']))
            {
              foreach ($fkey['references'] as $reference)
              {
                $xml .= "      <reference local=\"$reference[local]\" foreign=\"$reference[foreign]\" />\n";
              }
            }
            $xml .= "    </foreign-key>\n";
          }
        }

        $xml .= "  </table>\n";
      }
      $xml .= "\n</database>\n";
    }

    return $xml;
  }

  private function fixYAML()
  {
    $this->fixYAMLDatabase();
    $this->fixYAMLI18n();
    $this->fixYAMLColumns();
  }

  private function fixYAMLDatabase()
  {
    foreach ($this->schema as $db_name => &$database)
    {
      if (!isset($database['_attributes']))
      {
        $database['_attributes'] = array();
      }

      $this->setIfNotSet($database['_attributes'], 'defaultIdMethod', 'native');
      $this->setIfNotSet($database['_attributes'], 'noXsd', true);
    }
  }

  private function fixYAMLI18n()
  {
    foreach ($this->schema as $db_name => &$database)
    {
      foreach ($this->getChildren($database) as $i18n_table => $columns)
      {
        $pos = strpos($i18n_table, '_i18n');
        if ($pos > 0 && $pos == strlen($i18n_table) - 5)
        {
          // i18n automatism
          $main_table = substr($i18n_table, 0, $pos);
          if (isset($database[$main_table]))
          {
            // set attributes for main table
            $database[$main_table]['_attributes']['isI18N'] = 1;
            $this->setIfNotSet($database[$main_table]['_attributes'], 'i18nTable', $i18n_table);

            // set id and culture columns for i18n table
            $this->setIfNotSet($database[$i18n_table], 'id', $database[$main_table]['id']);
            $this->setIfNotSet($database[$i18n_table], 'culture', array( 'isCulture' => 'true', 'type' => 'varchar', 'size' => '7', 'required' => true, 'primaryKey' => true));

            // set id as foreign key for i18n table
            $this->setIfNotSet($database[$i18n_table]['id'], 'foreignTable', $main_table);
            $this->setIfNotSet($database[$i18n_table]['id'], 'foreignReference', 'id');
          }
          else
          {
            throw new sfException(sprintf('Missing main table for internationalized table "%s".', $i18n_table));
          }
        }
      }
    }
  }

  private function fixYAMLColumns()
  {
    foreach ($this->schema as $db_name => &$database)
    {
      foreach ($database as $table => &$columns)
      {
        foreach ($columns as $name => &$attributes)
        {
          if ($attributes == null && $table != '_attributes' && ($name == 'created_at' || $name == 'updated_at'))
          {
            // timestamp automatism
            $columns[$name]['type']= 'timestamp';
          }

          if ($attributes == null && $name == 'id' && $table != '_attributes')
          {
            // primary key automatism
            $columns['id']['type']= 'integer';
            $columns['id']['required'] = true;
            $columns['id']['primaryKey'] = true;
            $columns['id']['autoincrement'] = true;
          }

          $pos = strpos($name, '_id');
          if ($attributes == null && $pos > 0 && $pos == strlen($name) - 3 && $table != '_attributes')
          {
            // foreign key automatism
            $foreign_table_phpName = sfInflector::camelize(substr($name, 0, $pos));
            $foreign_table = '';
            foreach ($this->getChildren($database) as $fk_tb_name => $fk_table)
            {
              if (isset($fk_table['_attributes']['phpName']) && $fk_table['_attributes']['phpName'] == $foreign_table_phpName)
              {
                $foreign_table = $fk_tb_name;
              }
            }
            if ($foreign_table)
            {
              $columns[$name]['type'] = 'integer';
              $columns[$name]['foreignTable'] = $foreign_table;
              $columns[$name]['foreignReference'] = 'id';
            }
            else
            {
              throw new sfException(sprintf('Unable to resolve foreign table for column "%s"', $name));
            }
          }
        }
      }
    }
  }

  private function setIfNotSet(&$entry, $key, $value)
  {
    if (!isset($entry[$key]))
    {
      $entry[$key] = $value;
    }
  }

  function getAttributesForColumn($col_name, $column)
  {
    $attributes_string = '';
    if (!is_array($column) && $column != null)
    {
      // simple type definition
      preg_match('/varchar\(([\d]+)\)/', $column, $matches);
      if (isset($matches[1]))
      {
        $attributes_string .= " type=\"varchar\" size=\"$matches[1]\" />\n";
      }
      else
      {
        $attributes_string .= " type=\"$column\" />\n";
      }
    }
    elseif (is_array($column))
    {
      foreach ($column as $key => $value)
      {
        if (!in_array($key, array('foreignTable', 'foreignReference', 'onDelete', 'index')))
        {
          $attributes_string .= " $key=\"".$this->getCorrectValueFor($key, $value)."\"";
        }
      }
      $attributes_string .= " />\n";
    }
    else
    {
      throw new sfException('Incorrect settings for column '.$col_name);
    }

    if (is_array($column) && isset($column['foreignTable']))
    {
      $attributes_string .= "    <foreign-key foreignTable=\"$column[foreignTable]\"";
      if (isset($column['onDelete']))
      {
        $attributes_string .= " onDelete=\"$column[onDelete]\"";
      }
      $attributes_string .= ">\n";
      $attributes_string .= "      <reference local=\"$col_name\" foreign=\"$column[foreignReference]\" />\n";
      $attributes_string .= "    </foreign-key>\n";  
    }

    if (is_array($column) && isset($column['index']))
    {
      $attributes_string .= "    <index name=\"${col_name}_index\">\n";
      $attributes_string .= "      <index-column name=\"$col_name\" />\n";
      $attributes_string .= "    </index>\n"; 
    }

    return $attributes_string;
  }

  function getAttributesFor($tag)
  {
    if (!isset($tag['_attributes']))
    {
      return '';
    }
    $attributes = $tag['_attributes'];
    $attributes_string = '';
    foreach ($attributes as $key => $value)
    {
      $attributes_string .= ' '.$key.'="'.$this->getCorrectValueFor($key, $value).'"'; 
    }

    return $attributes_string;
  }

  function getCorrectValueFor($key, $value)
  {
    $booleans = array('required', 'primaryKey', 'autoincrement', 'noXsd', 'isI18N');
    if (in_array($key, $booleans))
    {
      return ($value == 1) ? 'true' : 'false';
    }
    else
    {
      return $value;
    } 
  }

  private function getChildren($hash)
  {
    foreach ($hash as $key => $value)
    {
      // ignore special children (starting with _)
      if ($key[0] == '_')
      {
        unset($hash[$key]);
      }
    }

    return $hash;
  }
}
