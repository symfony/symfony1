<?php

require_once 'propel/engine/builder/om/php5/PHP5ComplexPeerBuilder.php';

class sfPeerBuilder extends PHP5ComplexPeerBuilder
{
  protected function addSelectMethods(&$script)
  {
    parent::addSelectMethods($script);

    if ($this->getTable()->getAttribute('isI18N'))
    {
      $this->addDoSelectWithI18n($script);
    }
  }

  protected function addDoSelectWithI18n(&$script)
  {
    $table = $this->getTable();
    $thisTableObjectBuilder = OMBuilder::getNewObjectBuilder($table);
    $className = $table->getPhpName();
    $pks = $table->getPrimaryKey();
    $pk = PeerBuilder::getColumnName($pks[0], $className);

    // get i18n table name and culture column name
    foreach ($table->getReferrers() as $fk)
    {
      $tblFK = $fk->getTable();
      if ($tblFK->getName() == $table->getAttribute('i18nTable'))
      {
        $i18nClassName = $tblFK->getPhpName();
        // FIXME
        $i18nPeerClassName = $i18nClassName.'Peer';

        $i18nTable = $table->getDatabase()->getTable($tblFK->getName());
        $i18nTableObjectBuilder = OMBuilder::getNewObjectBuilder($i18nTable);
        $i18nTablePeerBuilder = OMBuilder::getNewPeerBuilder($i18nTable);
        $i18nPks = $i18nTable->getPrimaryKey();
        $i18nPk = PeerBuilder::getColumnName($i18nPks[0], $i18nClassName);

        $culturePhpName = '';
        $cultureColumnName = '';
        foreach ($tblFK->getColumns() as $col)
        {
          if (("true" === strtolower($col->getAttribute('isCulture'))))
          {
            $culturePhpName = $col->getPhpName();
            $cultureColumnName = PeerBuilder::getColumnName($col, $i18nClassName);
          }
        }
      }
    }

    $script .= "

  /**
   * Selects a collection of $className objects pre-filled with their i18n objects.
   *
   * @return array Array of $className objects.
   * @throws PropelException Any exceptions caught during processing will be
   *     rethrown wrapped into a PropelException.
   */
  public static function doSelectWithI18n(Criteria \$c, \$culture = null, \$con = null)
  {
    if (\$culture === null)
    {
      \$culture = sfContext::getInstance()->getUser()->getCulture();
    }

    // Set the correct dbName if it has not been overridden
    if (\$c->getDbName() == Propel::getDefaultDB())
    {
      \$c->setDbName(self::DATABASE_NAME);
    }

    ".$this->getPeerClassname()."::addSelectColumns(\$c);
    \$startcol = (".$this->getPeerClassname()."::NUM_COLUMNS - ".$this->getPeerClassname()."::NUM_LAZY_LOAD_COLUMNS) + 1;

    ".$i18nPeerClassName."::addSelectColumns(\$c);

    \$c->addJoin(".$pk.", ".$i18nPk.");
    \$c->add(".$cultureColumnName.", \$culture);

    \$rs = ".$this->basePeerClassname."::doSelect(\$c, \$con);
    \$results = array();

    while(\$rs->next()) {
";
            if ($table->getChildrenColumn()) {
              $script .= "
      \$omClass = ".$this->getPeerClassname()."::getOMClass(\$rs, 1);
";
            } else {
              $script .= "
      \$omClass = ".$this->getPeerClassname()."::getOMClass();
";
            }
            $script .= "
      \$cls = Propel::import(\$omClass);
      \$obj1 = new \$cls();
      \$obj1->hydrate(\$rs);
";
//            if ($i18nTable->getChildrenColumn()) {
              $script .= "
      \$omClass = ".$i18nTablePeerBuilder->getPeerClassname()."::getOMClass(\$rs, \$startcol);
";
//            } else {
//              $script .= "
//      \$omClass = ".$i18nTablePeerBuilder->getPeerClassname()."::getOMClass();
//";
//            }

            $script .= "
      \$cls = Propel::import(\$omClass);
      \$obj2 = new \$cls();
      \$obj2->hydrate(\$rs, \$startcol);

      \$obj1->set".$i18nClassName."ForCulture(\$obj2, \$culture);
      \$obj2->set".$className."(\$obj1);

      \$results[] = \$obj1;
    }
    return \$results;
  }
";
  }

  public function addDoValidate(&$script) {
	    $tmp = '';
      parent::addDoValidate($tmp);

      $script .= str_replace("return {$this->basePeerClassname}::doValidate(".$this->getPeerClassname()."::DATABASE_NAME, ".$this->getPeerClassname()."::TABLE_NAME, \$columns);\n",
        "\$res =  {$this->basePeerClassname}::doValidate(".$this->getPeerClassname()."::DATABASE_NAME, ".$this->getPeerClassname()."::TABLE_NAME, \$columns);\n".
        "    if (\$res !== true) {\n".
        "        \$request = sfContext::getInstance()->getRequest();\n".
        "        foreach (\$res as \$failed) {\n".
        "            \$col = ".$this->getPeerClassname()."::translateFieldname(\$failed->getColumn(), BasePeer::TYPE_COLNAME, BasePeer::TYPE_PHPNAME);\n".
        "            \$request->setError(\$col, \$failed->getMessage());\n".
        "        }\n".
        "    }\n\n".
        "    return \$res;\n", $tmp);
  }
}

?>