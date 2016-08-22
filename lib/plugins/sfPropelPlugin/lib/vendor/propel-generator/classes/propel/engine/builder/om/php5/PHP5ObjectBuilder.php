<?php

/*
 *  $Id: PHP5ObjectBuilder.php 1639 2010-03-25 22:11:59Z francois $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://propel.phpdb.org>.
 */

require_once 'propel/engine/builder/om/ObjectBuilder.php';

/**
 * Generates a PHP5 base Object class for user object model (OM).
 *
 * This class produces the base object class (e.g. BaseMyTable) which contains all
 * the custom-built accessor and setter methods.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.engine.builder.om.php5
 */
class PHP5ObjectBuilder extends ObjectBuilder {

	/**
	 * Gets the package for the [base] object classes.
	 * @return     string
	 */
	public function getPackage()
	{
		return parent::getPackage() . ".om";
	}

	/**
	 * Returns the name of the current class being built.
	 * @return     string
	 */
	public function getUnprefixedClassname()
	{
		return $this->getBuildProperty('basePrefix') . $this->getStubObjectBuilder()->getUnprefixedClassname();
	}

	/**
	 * Validates the current table to make sure that it won't
	 * result in generated code that will not parse.
	 *
	 * This method may emit warnings for code which may cause problems
	 * and will throw exceptions for errors that will definitely cause
	 * problems.
	 */
	protected function validateModel()
	{
		parent::validateModel();

		$table = $this->getTable();

		// Check to see whether any generated foreign key names
		// will conflict with column names.

		$colPhpNames = array();
		$fkPhpNames = array();

		foreach ($table->getColumns() as $col) {
			$colPhpNames[] = $col->getPhpName();
		}

		foreach ($table->getForeignKeys() as $fk) {
			$fkPhpNames[] = $this->getFKPhpNameAffix($fk, $plural = false);
		}

		$intersect = array_intersect($colPhpNames, $fkPhpNames);
		if (!empty($intersect)) {
			throw new EngineException("One or more of your column names for [" . $table->getName() . "] table conflict with foreign key names (" . implode(", ", $intersect) . ")");
		}

		// Check foreign keys to see if there are any foreign keys that
		// are also matched with an inversed referencing foreign key
		// (this is currently unsupported behavior)
		// see: http://propel.phpdb.org/trac/ticket/549

		foreach ($table->getForeignKeys() as $fk) {
			if ($fk->isMatchedByInverseFK()) {
				throw new EngineException("The 1:1 relationship expressed by foreign key " . $fk->getName() . " is defined in both directions; Propel does not currently support this (if you must have both foreign key constraints, consider adding this constraint with a custom SQL file.)" );
			}
		}
	}

	/**
	 * Returns the appropriate formatter (from platform) for a date/time column.
	 * @param      Column $col
	 * @return     string
	 */
	protected function getTemporalFormatter(Column $col)
	{
		$fmt = null;
		if ($col->getType() === PropelTypes::DATE) {
			$fmt = $this->getPlatform()->getDateFormatter();
		} elseif ($col->getType() === PropelTypes::TIME) {
			$fmt = $this->getPlatform()->getTimeFormatter();
		} elseif ($col->getType() === PropelTypes::TIMESTAMP) {
			$fmt = $this->getPlatform()->getTimestampFormatter();
		}
		return $fmt;
	}

	/**
	 * Returns the type-casted and stringified default value for the specified Column.
	 * This only works for scalar default values currently.
	 * @return     string The default value or 'NULL' if there is none.
	 */
	protected function getDefaultValueString(Column $col)
	{
		$defaultValue = var_export(null, true);
		if (($val = $col->getPhpDefaultValue()) !== null) {
			if ($col->isTemporalType()) {
				$fmt = $this->getTemporalFormatter($col);
				try {
					if (!($this->getPlatform() instanceof MysqlPlatform &&
					($val === '0000-00-00 00:00:00' || $val === '0000-00-00'))) {
						// while technically this is not a default value of NULL,
						// this seems to be closest in meaning.
						$defDt = new DateTime($val);
						$defaultValue = var_export($defDt->format($fmt), true);
					}
				} catch (Exception $x) {
					// prevent endless loop when timezone is undefined
					date_default_timezone_set('America/Los_Angeles');
					throw new EngineException("Unable to parse default temporal value for " . $col->getFullyQualifiedName() . ": " .$this->getDefaultValueString($col), $x);
				}
			} else {
				if ($col->isPhpPrimitiveType()) {
					settype($val, $col->getPhpType());
					$defaultValue = var_export($val, true);
				} elseif ($col->isPhpObjectType()) {
					$defaultValue = 'new '.$col->getPhpType().'(' . var_export($val, true) . ')';
				} else {
					throw new EngineException("Cannot get default value string for " . $col->getFullyQualifiedName());
				}
			}
		}
		return $defaultValue;
	}

	/**
	 * Adds the include() statements for files that this class depends on or utilizes.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addIncludes(&$script)
	{
	} // addIncludes()

	/**
	 * Adds class phpdoc comment and openning of class.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addClassOpen(&$script)
	{

		$table = $this->getTable();
		$tableName = $table->getName();
		$tableDesc = $table->getDescription();
		$interface = $this->getInterface();

		$script .= "
/**
 * Base class that represents a row from the '$tableName' table.
 *
 * $tableDesc
 *";
		if ($this->getBuildProperty('addTimeStamp')) {
			$now = strftime('%c');
			$script .= "
 * This class was autogenerated by Propel " . $this->getBuildProperty('version') . " on:
 *
 * $now
 *";
		}
		$script .= "
 * @package    ".$this->getPackage()."
 */
abstract class ".$this->getClassname()." extends ".ClassTools::classname($this->getBaseClass())." ";

		$interface = ClassTools::getInterface($table);
		if ($interface) {
			$script .= " implements " . ClassTools::classname($interface);
		}

		$script .= " {

";
	}

	/**
	 * Specifies the methods that are added as part of the basic OM class.
	 * This can be overridden by subclasses that wish to add more methods.
	 * @see        ObjectBuilder::addClassBody()
	 */
	protected function addClassBody(&$script)
	{
		$table = $this->getTable();
		if (!$table->isAlias()) {
			$this->addConstants($script);
			$this->addAttributes($script);
		}

    if ($this->hasDefaultValues()) {
      $this->addApplyDefaultValues($script);
      $this->addConstructor($script);
    }

		$this->addColumnAccessorMethods($script);
		$this->addColumnMutatorMethods($script);

		$this->addHasOnlyDefaultValues($script);

		$this->addHydrate($script);
		$this->addEnsureConsistency($script);

		if (!$table->isReadOnly()) {
			$this->addManipulationMethods($script);
		}

    if ($this->isAddValidateMethod())
    {
      $this->addValidationMethods($script);
    }

		if ($this->isAddGenericAccessors()) {
			$this->addGetByName($script);
			$this->addGetByPosition($script);
			$this->addToArray($script);
		}

		if ($this->isAddGenericMutators()) {
			$this->addSetByName($script);
			$this->addSetByPosition($script);
			$this->addFromArray($script);
		}

		$this->addBuildCriteria($script);
		$this->addBuildPkeyCriteria($script);
		$this->addGetPrimaryKey($script);
		$this->addSetPrimaryKey($script);

		$this->addCopy($script);

		if (!$table->isAlias()) {
			$this->addGetPeer($script);
		}

		$this->addFKMethods($script);
		$this->addRefFKMethods($script);
		$this->addClearAllReferences($script);
		
		// apply behaviors
    $this->applyBehaviorModifier('objectMethods', $script, "	");
		
		$this->addPrimaryString($script);
	}

	/**
	 * Closes class.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addClassClose(&$script)
	{
		$script .= "
} // " . $this->getClassname() . "
";
    $this->applyBehaviorModifier('objectFilter', $script, "");
	}

	/**
	 * Adds any constants to the class.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addConstants(&$script)
	{
		// nothing to do here any more
		// fieldnameTypeConstants have been moved to class BasePeer [sv]
	}

	/**
	 * Adds class attributes.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addAttributes(&$script)
	{
		$table = $this->getTable();

		$script .= "
	/**
	 * The Peer class.
	 * Instance provides a convenient way of calling static methods on a class
	 * that calling code may not be able to identify.
	 * @var        ".$this->getPeerClassname()."
	 */
	protected static \$peer;
";
		if (!$table->isAlias()) {
			$this->addColumnAttributes($script);
		}

		foreach ($table->getForeignKeys() as $fk) {
			$this->addFKAttributes($script, $fk);
		}

		foreach ($table->getReferrers() as $refFK) {
			// if ($refFK->getTable()->getName() != $table->getName()) {
			$this->addRefFKAttributes($script, $refFK);
			// }
		}

		$this->addAlreadyInSaveAttribute($script);
		$this->addAlreadyInValidationAttribute($script);
		
		// apply behaviors
    $this->applyBehaviorModifier('objectAttributes', $script, "	");
	}

	/**
	 * Adds variables that store column values.
	 * @param      string &$script The script will be modified in this method.
	 * @see        addColumnNameConstants()
	 */
	protected function addColumnAttributes(&$script) {

		$table = $this->getTable();

		foreach ($table->getColumns() as $col) {
			$this->addColumnAttributeComment($script, $col);
			$this->addColumnAttributeDeclaration($script, $col);
			if ($col->isLazyLoad() ) {
				$this->addColumnAttributeLoaderComment($script, $col);
				$this->addColumnAttributeLoaderDeclaration($script, $col);
			}
		}
	}

	/**
	 * Add comment about the attribute (variable) that stores column values
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col
	 **/
	protected function addColumnAttributeComment(&$script, Column $col) {
		$cptype = $col->getPhpType();
		$clo = strtolower($col->getName());

		$script .= "
	/**
	 * The value for the $clo field.";
		if ($col->getDefaultValue()) {
			if ($col->getDefaultValue()->isExpression()) {
				$script .= "
	 * Note: this column has a database default value of: (expression) ".$col->getDefaultValue()->getValue();
			} else {
				$script .= "
	 * Note: this column has a database default value of: ". $this->getDefaultValueString($col);
			}
		}
		$script .= "
	 * @var        $cptype
	 */";
	}

	/**
	 * Adds the declaration of a column value storage attribute
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col
	 **/
	protected function addColumnAttributeDeclaration(&$script, Column $col) {
		$clo = strtolower($col->getName());
		$script .= "
	protected \$" . $clo . ";
";
	}

	/**
	 * Adds the comment about the attribute keeping track if an attribute value has been loaded
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col
	 **/
	protected function addColumnAttributeLoaderComment(&$script, Column $col) {
		$script .= "
	/**
	 * Whether the lazy-loaded $clo value has been loaded from database.
	 * This is necessary to avoid repeated lookups if $clo column is NULL in the db.
	 * @var        boolean
	 */";
	}

	/**
	 * Adds the declaration of the attribute keeping track of an attribute's loaded state
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col
	 **/
	protected function addColumnAttributeLoaderDeclaration(&$script, Column $col) {
		$clo = strtolower($col->getName());
		$script .= "
	protected \$".$clo."_isLoaded = false;
";
	}

	/**
	 * Adds the getPeer() method.
	 * This is a convenient, non introspective way of getting the Peer class for a particular object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addGetPeer(&$script)
	{
		$this->addGetPeerComment($script);
		$this->addGetPeerFunctionOpen($script);
		$this->addGetPeerFunctionBody($script);
		$this->addGetPeerFunctionClose($script);
	}

	/**
	 * Add the comment for the getPeer method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addGetPeerComment(&$script) {
		$script .= "
	/**
	 * Returns a peer instance associated with this om.
	 *
	 * Since Peer classes are not to have any instance attributes, this method returns the
	 * same instance for all member of this class. The method could therefore
	 * be static, but this would prevent one from overriding the behavior.
	 *
	 * @return     ".$this->getPeerClassname()."
	 */";
	}

	/**
	 * Adds the function declaration (function opening) for the getPeer method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addGetPeerFunctionOpen(&$script) {
		$script .= "
	public function getPeer()
	{";
	}

	/**
	 * Adds the body of the getPeer method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addGetPeerFunctionBody(&$script) {
		$script .= "
		if (self::\$peer === null) {
			" . $this->buildObjectInstanceCreationCode('self::$peer', $this->getPeerClassname()) . "
		}
		return self::\$peer;";
	}

	/**
	 * Add the function close for the getPeer method
	 * Note: this is just a } and the body ends with a return statement, so it's quite useless. But it's here anyway for consisency, cause there's a close function for all functions and in some other instances, they are useful
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addGetPeerFunctionClose(&$script) {
		$script .= "
	}
";
	}

	/**
	 * Adds the constructor for this object.
	 * @param      string &$script The script will be modified in this method.
	 * @see        addConstructor()
	 */
	protected function addConstructor(&$script)
	{
		$this->addConstructorComment($script);
		$this->addConstructorOpen($script);
		$this->addConstructorBody($script);
		$this->addConstructorClose($script);
	}

	/**
	 * Adds the comment for the constructor
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addConstructorComment(&$script) {
		$script .= "
	/**
	 * Initializes internal state of ".$this->getClassname()." object.
	 * @see        applyDefaults()
	 */";
	}

	/**
	 * Adds the function declaration for the constructor
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addConstructorOpen(&$script) {
		$script .= "
	public function __construct()
	{";
	}

	/**
	 * Adds the function body for the constructor
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addConstructorBody(&$script) {
		$script .= "
		parent::__construct();
		\$this->applyDefaultValues();";
	}

	/**
	 * Adds the function close for the constructor
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addConstructorClose(&$script) {
		$script .= "
	}
";
	}

	/**
	 * Adds the applyDefaults() method, which is called from the constructor.
	 * @param      string &$script The script will be modified in this method.
	 * @see        addConstructor()
	 */
	protected function addApplyDefaultValues(&$script)
	{
		$this->addApplyDefaultValuesComment($script);
		$this->addApplyDefaultValuesOpen($script);
		$this->addApplyDefaultValuesBody($script);
		$this->addApplyDefaultValuesClose($script);
	}

	/**
	 * Adds the comment for the applyDefaults method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addApplyDefaultValues()
	 **/
	protected function addApplyDefaultValuesComment(&$script) {
		$script .= "
	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or
	 * equivalent initialization method).
	 * @see        __construct()
	 */";
	}

	/**
	 * Adds the function declaration for the applyDefaults method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addApplyDefaultValues()
	 **/
	protected function addApplyDefaultValuesOpen(&$script) {
		 $script .= "
	public function applyDefaultValues()
	{";
	}

	/**
	 * Adds the function body of the applyDefault method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addApplyDefaultValues()
	 **/
	protected function addApplyDefaultValuesBody(&$script) {
		$table = $this->getTable();
		// FIXME - Apply support for PHP default expressions here
		// see: http://propel.phpdb.org/trac/ticket/378

		$colsWithDefaults = array();
		foreach ($table->getColumns() as $col) {
			$def = $col->getDefaultValue();
			if ($def !== null && !$def->isExpression()) {
				$colsWithDefaults[] = $col;
			}
		}

		$colconsts = array();
		foreach ($colsWithDefaults as $col) {
			$clo = strtolower($col->getName());
			$script .= "
		\$this->".$clo." = ".$this->getDefaultValueString($col).";";

		}
	}


	/**
	 * Adds the function close for the applyDefaults method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addApplyDefaultValues()
	 **/
	protected function addApplyDefaultValuesClose(&$script) {
		$script .= "
	}
";
	}

	// --------------------------------------------------------------
	//
	// A C C E S S O R    M E T H O D S
	//
	// --------------------------------------------------------------

	/**
	 * Adds a date/time/timestamp getter method.
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        parent::addColumnAccessors()
	 */
	protected function addTemporalAccessor(&$script, Column $col)
	{
		$this->addTemporalAccessorComment($script, $col);
		$this->addTemporalAccessorOpen($script, $col);
		$this->addTemporalAccessorBody($script, $col);
		$this->addTemporalAccessorClose($script, $col);
	} // addTemporalAccessor


	/**
	 * Adds the comment for a temporal accessor
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addTemporalAccessor
	 **/
	protected function addTemporalAccessorComment(&$script, Column $col) {
		$clo = strtolower($col->getName());
		$useDateTime = $this->getBuildProperty('useDateTimeClass');

		$dateTimeClass = $this->getBuildProperty('dateTimeClass');
		if (!$dateTimeClass) {
			$dateTimeClass = 'DateTime';
		}

		$handleMysqlDate = false;
		if ($this->getPlatform() instanceof MysqlPlatform) {
			if ($col->getType() === PropelTypes::TIMESTAMP) {
				$handleMysqlDate = true;
				$mysqlInvalidDateString = '0000-00-00 00:00:00';
			} elseif ($col->getType() === PropelTypes::DATE) {
				$handleMysqlDate = true;
				$mysqlInvalidDateString = '0000-00-00';
			}
			// 00:00:00 is a valid time, so no need to check for that.
		}

		$script .= "
	/**
	 * Get the [optionally formatted] temporal [$clo] column value.
	 * ".$col->getDescription();
		if (!$useDateTime) {
			$script .= "
	 * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
	 * option in order to avoid converstions to integers (which are limited in the dates they can express).";
		}
		$script .= "
	 *
	 * @param      string \$format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw ".($useDateTime ? 'DateTime object' : 'unix timestamp integer')." will be returned.";
		if ($useDateTime) {
            if($this->getTemporalFormat($col) !== null) {
			    $script .= "
	 * @return     mixed Formatted date/time value as string or $dateTimeClass object (if format is NULL), NULL if column is NULL" .($handleMysqlDate ? ', and 0 if column value is ' . $mysqlInvalidDateString : '');
            } else {
			    $script .= "
	 * @return     DateTime";
            }
		} else {
			$script .= "
	 * @return     mixed Formatted date/time value as string or (integer) unix timestamp (if format is NULL), NULL if column is NULL".($handleMysqlDate ? ', and 0 if column value is ' . $mysqlInvalidDateString : '');
		}
		$script .= "
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */";
	}

    protected function getTemporalFormat(Column $col) {
        $defaultfmt = null;
		// Default date/time formatter strings are specified in build.properties
		if ($col->getType() === PropelTypes::DATE) {
			$defaultfmt = $col->getAttribute('defaultDateFormat', $col->getTable()->getAttribute('defaultDateFormat', $this->getBuildProperty('defaultDateFormat')));
		} elseif ($col->getType() === PropelTypes::TIME) {
			$defaultfmt = $col->getAttribute('defaultTimeFormat', $col->getTable()->getAttribute('defaultTimeFormat', $this->getBuildProperty('defaultTimeFormat')));
		} elseif ($col->getType() === PropelTypes::TIMESTAMP) {
			$defaultfmt = $col->getAttribute('defaultTimeStampFormat', $col->getTable()->getAttribute('defaultTimeStampFormat', $this->getBuildProperty('defaultTimeStampFormat')));
		}
		if (empty($defaultfmt) || 'null' === $defaultfmt) {
            $defaultfmt = null;
        }
        return $defaultfmt;
    }

	/**
	 * Adds the function declaration for a temporal accessor
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addTemporalAccessor
	 **/
	protected function addTemporalAccessorOpen(&$script, Column $col) {
		$cfc = $col->getPhpName();

		$defaultfmt = $this->getTemporalFormat($col);
        $visibility = $col->getAccessorVisibility();

		$script .= "
	".$visibility." function get$cfc(\$format = ".var_export($defaultfmt, true)."";
		if ($col->isLazyLoad()) $script .= ", \$con = null";
		$script .= ")
	{";
	}

	/**
	 * Adds the body of the temporal accessor
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addTemporalAccessor
	 **/
	protected function addTemporalAccessorBody(&$script, Column $col) {
		$cfc = $col->getPhpName();
		$clo = strtolower($col->getName());

		$useDateTime = $this->getBuildProperty('useDateTimeClass');

		$dateTimeClass = $this->getBuildProperty('dateTimeClass');
		if (!$dateTimeClass) {
			$dateTimeClass = 'DateTime';
		}

		$handleMysqlDate = false;
		if ($this->getPlatform() instanceof MysqlPlatform) {
			if ($col->getType() === PropelTypes::TIMESTAMP) {
				$handleMysqlDate = true;
				$mysqlInvalidDateString = '0000-00-00 00:00:00';
			} elseif ($col->getType() === PropelTypes::DATE) {
				$handleMysqlDate = true;
				$mysqlInvalidDateString = '0000-00-00';
			}
			// 00:00:00 is a valid time, so no need to check for that.
		}

		if ($col->isLazyLoad()) {
			$script .= "
		if (!\$this->".$clo."_isLoaded && \$this->$clo === null && !\$this->isNew()) {
			\$this->load$cfc(\$con);
		}
";
		}

		if ($col->isNotNull() && $col->getTable()->getAttribute('throwOnNull', false)) {
            $script .= "
		if (\$this->$clo === null) {
            throw new Exception('Trying to fetch date that is not set');
		}
";
        } else {
            $script .= "
		if (\$this->$clo === null) {
			return null;
		}
";
        }

		if ($handleMysqlDate) {
			$script .= "
		if (\$this->$clo === '$mysqlInvalidDateString') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				\$dt = new $dateTimeClass(\$this->$clo);
			} catch (Exception \$x) {
				throw new PropelException(\"Internally stored date/time/timestamp value could not be converted to $dateTimeClass: \" . var_export(\$this->$clo, true), \$x);
			}
		}
";
		} else {
			$script .= "

		try {
			\$dt = new $dateTimeClass(\$this->$clo);
		} catch (Exception \$x) {
			throw new PropelException(\"Internally stored date/time/timestamp value could not be converted to $dateTimeClass: \" . var_export(\$this->$clo, true), \$x);
		}
";
		} // if handleMyqlDate

		$script .= "
		if (\$format === null) {";
		if ($useDateTime) {
			$script .= "
			// Because propel.useDateTimeClass is TRUE, we return a $dateTimeClass object.
			return \$dt;";
		} else {
			$script .= "
			// We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
			return (int) \$dt->format('U');";
		}
		$script .= "
		} elseif (strpos(\$format, '%') !== false) {
			return strftime(\$format, \$dt->format('U'));
		} else {
			return \$dt->format(\$format);
		}
";

        if($col->isNotNull()) {
		    $script .= "    }

   ".$visibility." function getHas$cfc()
	{
        return null !== \$this->$clo;";
        }

	}


	/**
	 * Adds the body of the temporal accessor
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addTemporalAccessorClose
	 **/
	protected function addTemporalAccessorClose(&$script, Column $col) {
		$script .= "
	}
";
	}

	/**
	 * Adds a normal (non-temporal) getter method.
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        parent::addColumnAccessors()
	 */
	protected function addDefaultAccessor(&$script, Column $col)
	{
		$this->addDefaultAccessorComment($script, $col);
		$this->addDefaultAccessorOpen($script, $col);
		$this->addDefaultAccessorBody($script, $col);
		$this->addDefaultAccessorClose($script, $col);
	}

	/**
	 * Add the comment for a default accessor method (a getter)
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addDefaultAccessor()
	 **/
	protected function addDefaultAccessorComment(&$script, Column $col) {
		$clo=strtolower($col->getName());

		$script .= "
	/**
	 * Get the [$clo] column value.
	 * ".$col->getDescription();
		if ($col->isLazyLoad()) {
			$script .= "
	 * @param      PropelPDO An optional PropelPDO connection to use for fetching this lazy-loaded column.";
		}
		$script .= "
	 * @return     ".$col->getPhpType()."
	 */";
	}

	/**
	 * Adds the function declaration for a default accessor
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addDefaultAccessor()
	 **/
	protected function addDefaultAccessorOpen(&$script, Column $col) {
		$cfc = $col->getPhpName();
		$visibility = $col->getAccessorVisibility();

		$script .= "
	".$visibility." function get$cfc(";
		if ($col->isLazyLoad()) $script .= "PropelPDO \$con = null";
		$script .= ")
	{";
	}

	/**
	 * Adds the function body for a default accessor method
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addDefaultAccessor()
	 **/
	protected function addDefaultAccessorBody(&$script, Column $col) {
		$cfc = $col->getPhpName();
		$clo = strtolower($col->getName());
		if ($col->isLazyLoad()) {
			$script .= "
		if (!\$this->".$clo."_isLoaded && \$this->$clo === null && !\$this->isNew()) {
			\$this->load$cfc(\$con);
		}
";
		}

		$script .= "
		return \$this->$clo;";
	}

	/**
	 * Adds the function close for a default accessor method
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addDefaultAccessor()
	 **/
	protected function addDefaultAccessorClose(&$script, Column $col) {
		$script .= "
	}
";
	}

	/**
	 * Adds the lazy loader method.
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        parent::addColumnAccessors()
	 */
	protected function addLazyLoader(&$script, Column $col)
	{
		$this->addLazyLoaderComment($script, $col);
		$this->addLazyLoaderOpen($script, $col);
		$this->addLazyLoaderBody($script, $col);
		$this->addLazyLoaderClose($script, $col);
	}

	/**
	 * Adds the comment for the lazy loader method
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addLazyLoader()
	 **/
	protected function addLazyLoaderComment(&$script, Column $col) {
		$clo = strtolower($col->getName());

		$script .= "
	/**
	 * Load the value for the lazy-loaded [$clo] column.
	 *
	 * This method performs an additional query to return the value for
	 * the [$clo] column, since it is not populated by
	 * the hydrate() method.
	 *
	 * @param      \$con PropelPDO (optional) The PropelPDO connection to use.
	 * @return     void
	 * @throws     PropelException - any underlying error will be wrapped and re-thrown.
	 */";
	}

	/**
	 * Adds the function declaration for the lazy loader method
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addLazyLoader()
	 **/
	protected function addLazyLoaderOpen(&$script, Column $col) {
		$cfc = $col->getPhpName();
		$script .= "
	protected function load$cfc(PropelPDO \$con = null)
	{";
	}

	/**
	 * Adds the function body for the lazy loader method
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addLazyLoader()
	 **/
	protected function addLazyLoaderBody(&$script, Column $col) {
		$platform = $this->getPlatform();
		$clo = strtolower($col->getName());

		$script .= "
		\$c = \$this->buildPkeyCriteria();
		\$c->addSelectColumn(".$this->getColumnConstant($col).");
		try {
			\$stmt = ".$this->getPeerClassname()."::doSelectStmt(\$c, \$con);
			\$row = \$stmt->fetch(PDO::FETCH_NUM);
			\$stmt->closeCursor();";

		if ($col->isLobType() && !$platform->hasStreamBlobImpl()) {
			$script .= "
			if (\$row[0] !== null) {
				\$this->$clo = fopen('php://memory', 'r+');
				fwrite(\$this->$clo, \$row[0]);
				rewind(\$this->$clo);
			} else {
				\$this->$clo = null;
			}";
		} elseif ($col->isPhpPrimitiveType()) {
			$script .= "
			\$this->$clo = (\$row[0] !== null) ? (".$col->getPhpType().") \$row[0] : null;";
		} elseif ($col->isPhpObjectType()) {
			$script .= "
			\$this->$clo = (\$row[0] !== null) ? new ".$col->getPhpType()."(\$row[0]) : null;";
		} else {
			$script .= "
			\$this->$clo = \$row[0];";
		}

		$script .= "
			\$this->".$clo."_isLoaded = true;
		} catch (Exception \$e) {
			throw new PropelException(\"Error loading value for [$clo] column on demand.\", \$e);
		}";
	}

	/**
	 * Adds the function close for the lazy loader
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addLazyLoader()
	 **/
	protected function addLazyLoaderClose(&$script, Column $col) {
		$script .= "
	}";
	} // addLazyLoader()

	// --------------------------------------------------------------
	//
	// M U T A T O R    M E T H O D S
	//
	// --------------------------------------------------------------

	/**
	 * Adds the open of the mutator (setter) method for a column.
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 */
	protected function addMutatorOpen(&$script, Column $col)
	{
		$this->addMutatorComment($script, $col);
		$this->addMutatorOpenOpen($script, $col);
		$this->addMutatorOpenBody($script, $col);
	}

	/**
	 * Adds the comment for a mutator
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addMutatorOpen()
	 **/
	protected function addMutatorComment(&$script, Column $col) {
		$clo = strtolower($col->getName());
		$script .= "
	/**
	 * Set the value of [$clo] column.
	 * ".$col->getDescription()."
	 * @param      ".$col->getPhpType()." \$v new value
	 * @return     ".$this->getObjectClassname()." The current object (for fluent API support)
	 */";
	}

	/**
	 * Adds the mutator function declaration
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addMutatorOpen()
	 **/
	protected function addMutatorOpenOpen(&$script, Column $col) {
		$cfc = $col->getPhpName();
		$visibility = $col->getMutatorVisibility();

		$script .= "
	".$visibility." function set$cfc(\$v)
	{";
	}

	/**
	 * Adds the mutator open body part
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addMutatorOpen()
	 **/
	protected function addMutatorOpenBody(&$script, Column $col) {
		$clo = strtolower($col->getName());
				$cfc = $col->getPhpName();
		if ($col->isLazyLoad()) {
			$script .= "
		// explicitly set the is-loaded flag to true for this lazy load col;
		// it doesn't matter if the value is actually set or not (logic below) as
		// any attempt to set the value means that no db lookup should be performed
		// when the get$cfc() method is called.
		\$this->".$clo."_isLoaded = true;
";
		}
	}

	/**
	 * Adds the close of the mutator (setter) method for a column.
	 *
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 */
	protected function addMutatorClose(&$script, Column $col)
	{
		$this->addMutatorCloseBody($script, $col);
		$this->addMutatorCloseClose($script, $col);
	}

	/**
	 * Adds the body of the close part of a mutator
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addMutatorClose()
	 **/
	protected function addMutatorCloseBody(&$script, Column $col) {
		$table = $this->getTable();
		$cfc = $col->getPhpName();
		$clo = strtolower($col->getName());

		if ($col->isForeignKey()) {

			foreach ($col->getForeignKeys() as $fk) {

				$tblFK =  $table->getDatabase()->getTable($fk->getForeignTableName());
				$colFK = $tblFK->getColumn($fk->getMappedForeignColumn($col->getName()));

				$varName = $this->getFKVarName($fk);

				$script .= "
		if (\$this->$varName !== null && \$this->".$varName."->get".$colFK->getPhpName()."() !== \$v) {
			\$this->$varName = null;
		}
";
			} // foreach fk
		} /* if col is foreign key */

		foreach ($col->getReferrers() as $refFK) {

			$tblFK = $this->getDatabase()->getTable($refFK->getForeignTableName());

			if ( $tblFK->getName() != $table->getName() ) {

				foreach ($col->getForeignKeys() as $fk) {

					$tblFK = $table->getDatabase()->getTable($fk->getForeignTableName());
					$colFK = $tblFK->getColumn($fk->getMappedForeignColumn($col->getName()));

					if ($refFK->isLocalPrimaryKey()) {
						$varName = $this->getPKRefFKVarName($refFK);
						$script .= "
		// update associated ".$tblFK->getPhpName()."
		if (\$this->$varName !== null) {
			\$this->{$varName}->set".$colFK->getPhpName()."(\$v);
		}
";
					} else {
						$collName = $this->getRefFKCollVarName($refFK);
						$script .= "

		// update associated ".$tblFK->getPhpName()."
		if (\$this->$collName !== null) {
			foreach (\$this->$collName as \$referrerObject) {
				  \$referrerObject->set".$colFK->getPhpName()."(\$v);
			  }
		  }
";
					} // if (isLocalPrimaryKey
				} // foreach col->getPrimaryKeys()
			} // if tablFk != table

		} // foreach
	}

	/**
	 * Adds the close for the mutator close
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addMutatorClose()
	 **/
	protected function addMutatorCloseClose(&$script, Column $col) {
		$cfc = $col->getPhpName();
		$script .= "
		return \$this;
	} // set$cfc()
";
	}

	/**
	 * Adds a setter for BLOB columns.
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        parent::addColumnMutators()
	 */
	protected function addLobMutator(&$script, Column $col)
	{
		$this->addMutatorOpen($script, $col);
		$clo = strtolower($col->getName());
		$script .= "
		// Because BLOB columns are streams in PDO we have to assume that they are
		// always modified when a new value is passed in.  For example, the contents
		// of the stream itself may have changed externally.
		if (!is_resource(\$v) && \$v !== null) {
			\$this->$clo = fopen('php://memory', 'r+');
			fwrite(\$this->$clo, \$v);
			rewind(\$this->$clo);
		} else { // it's already a stream
			\$this->$clo = \$v;
		}
		\$this->modifiedColumns[] = ".$this->getColumnConstant($col).";
";
		$this->addMutatorClose($script, $col);
	} // addLobMutatorSnippet

	/**
	 * Adds a setter method for date/time/timestamp columns.
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        parent::addColumnMutators()
	 */
	protected function addTemporalMutator(&$script, Column $col)
	{
		$cfc = $col->getPhpName();
		$clo = strtolower($col->getName());
		$visibility = $col->getMutatorVisibility();

		$dateTimeClass = $this->getBuildProperty('dateTimeClass');
		if (!$dateTimeClass) {
			$dateTimeClass = 'DateTime';
		}

		$script .= "
	/**
	 * Sets the value of [$clo] column to a normalized version of the date/time value specified.
	 * ".$col->getDescription()."
	 * @param      mixed \$v string, integer (timestamp), or DateTime value.  Empty string will
	 *						be treated as NULL for temporal objects.
	 * @return     ".$this->getObjectClassname()." The current object (for fluent API support)
	 */
	".$visibility." function set$cfc(\$v)
	{";
		if ($col->isLazyLoad()) {
			$script .= "
		// explicitly set the is-loaded flag to true for this lazy load col;
		// it doesn't matter if the value is actually set or not (logic below) as
		// any attempt to set the value means that no db lookup should be performed
		// when the get$cfc() method is called.
		\$this->".$clo."_isLoaded = true;
";
		}

		$fmt = var_export($this->getTemporalFormatter($col), true);

		$script .= "
		// we treat '' as NULL for temporal objects because DateTime('') == DateTime('now')
		// -- which is unexpected, to say the least.
		if (\$v === null || \$v === '') {
			\$dt = null;
		} elseif (\$v instanceof DateTime) {
			\$dt = \$v;
		} else {
			// some string/numeric value passed; we normalize that so that we can
			// validate it.
			try {
				if (is_numeric(\$v)) { // if it's a unix timestamp
					\$dt = new $dateTimeClass('@'.\$v, new DateTimeZone('UTC'));
					// We have to explicitly specify and then change the time zone because of a
					// DateTime bug: http://bugs.php.net/bug.php?id=43003
					\$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					\$dt = new $dateTimeClass(\$v);
				}
			} catch (Exception \$x) {
				throw new PropelException('Error parsing date/time value: ' . var_export(\$v, true), \$x);
			}
		}

		if ( \$this->$clo !== null || \$dt !== null ) {
			// (nested ifs are a little easier to read in this case)

			\$currNorm = (\$this->$clo !== null && \$tmpDt = new $dateTimeClass(\$this->$clo)) ? \$tmpDt->format($fmt) : null;
			\$newNorm = (\$dt !== null) ? \$dt->format($fmt) : null;

			if ( (\$currNorm !== \$newNorm) // normalized values don't match ";

		if (($def = $col->getDefaultValue()) !== null && !$def->isExpression()) {
			$defaultValue = $this->getDefaultValueString($col);
			$script .= "
					|| (\$dt->format($fmt) === $defaultValue) // or the entered value matches the default";
		}

		$script .= "
					)
			{
				\$this->$clo = (\$dt ? \$dt->format($fmt) : null);
				\$this->modifiedColumns[] = ".$this->getColumnConstant($col).";
			}
		} // if either are not null
";
		$this->addMutatorClose($script, $col);
	}

	/**
	 * Adds setter method for "normal" columns.
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        parent::addColumnMutators()
	 */
	protected function addDefaultMutator(&$script, Column $col)
	{
		$clo = strtolower($col->getName());

		$this->addMutatorOpen($script, $col);

		// Perform type-casting to ensure that we can use type-sensitive
		// checking in mutators.
		if ($col->isPhpPrimitiveType()) {
			$script .= "
		if (\$v !== null) {
			\$v = (".$col->getPhpType().") \$v;
		}
";
		}

		$script .= "
		if (\$this->$clo !== \$v";
		if (($def = $col->getDefaultValue()) !== null && !$def->isExpression()) {
			$script .= " || \$this->isNew()";
		}
		$script .= ") {
			\$this->$clo = \$v;
			\$this->modifiedColumns[] = ".$this->getColumnConstant($col).";
		}
";
		$this->addMutatorClose($script, $col);
	}

	/**
	 * Adds the hasOnlyDefaultValues() method.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addHasOnlyDefaultValues(&$script)
	{
		$this->addHasOnlyDefaultValuesComment($script);
		$this->addHasOnlyDefaultValuesOpen($script);
		$this->addHasOnlyDefaultValuesBody($script);
		$this->addHasOnlyDefaultValuesClose($script);
	}

	/**
	 * Adds the comment for the hasOnlyDefaultValues method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addHasOnlyDefaultValues
	 **/
	protected function addHasOnlyDefaultValuesComment(&$script) {
		$script .= "
	/**
	 * Indicates whether the columns in this object are only set to default values.
	 *
	 * This method can be used in conjunction with isModified() to indicate whether an object is both
	 * modified _and_ has some values set which are non-default.
	 *
	 * @return     boolean Whether the columns in this object are only been set with default values.
	 */";
	}

	/**
	 * Adds the function declaration for the hasOnlyDefaultValues method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addHasOnlyDefaultValues
	 **/
	protected function addHasOnlyDefaultValuesOpen(&$script) {
		$script .= "
	public function hasOnlyDefaultValues()
	{";
	}

	/**
	 * Adds the function body for the hasOnlyDefaultValues method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addHasOnlyDefaultValues
	 **/
	protected function addHasOnlyDefaultValuesBody(&$script) {
		$table = $this->getTable();
		$colsWithDefaults = array();
		foreach ($table->getColumns() as $col) {
			$def = $col->getDefaultValue();
			if ($def !== null && !$def->isExpression()) {
				$colsWithDefaults[] = $col;
			}
		}

		foreach ($colsWithDefaults as $col) {

			$clo = strtolower($col->getName());
			$def = $col->getDefaultValue();

			$script .= "
			if (\$this->$clo !== " . $this->getDefaultValueString($col).") {
				return false;
			}
";
		}
	}

	/**
	 * Adds the function close for the hasOnlyDefaultValues method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addHasOnlyDefaultValues
	 **/
	protected function addHasOnlyDefaultValuesClose(&$script) {
		$script .= "
		// otherwise, everything was equal, so return TRUE
		return true;";
		$script .= "
	} // hasOnlyDefaultValues()
";
	}

	/**
	 * Adds the hydrate() method, which sets attributes of the object based on a ResultSet.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addHydrate(&$script)
	{
		$this->addHydrateComment($script);
		$this->addHydrateOpen($script);
		$this->addHydrateBody($script);
		$this->addHydrateClose($script);
	}

	/**
	 * Adds the comment for the hydrate method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addHydrate()
	 */
	protected function addHydrateComment(&$script) {
		$script .= "
	/**
	 * Hydrates (populates) the object variables with values from the database resultset.
	 *
	 * An offset (0-based \"start column\") is specified so that objects can be hydrated
	 * with a subset of the columns in the resultset rows.  This is needed, for example,
	 * for results of JOIN queries where the resultset row includes columns from two or
	 * more tables.
	 *
	 * @param      array \$row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
	 * @param      int \$startcol 0-based offset column which indicates which restultset column to start with.
	 * @param      boolean \$rehydrate Whether this object is being re-hydrated from the database.
	 * @return     int next starting column
	 * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
	 */";
	}

	/**
	 * Adds the function declaration for the hydrate method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addHydrate()
	 */
	protected function addHydrateOpen(&$script) {
		$script .= "
	public function hydrate(\$row, \$startcol = 0, \$rehydrate = false)
	{";
	}

	/**
	 * Adds the function body for the hydrate method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addHydrate()
	 */
	protected function addHydrateBody(&$script) {
		$table = $this->getTable();
		$platform = $this->getPlatform();
		$script .= "
		try {
";
		$n = 0;
		foreach ($table->getColumns() as $col) {
			if (!$col->isLazyLoad()) {
				$clo = strtolower($col->getName());
				if ($col->isLobType() && !$platform->hasStreamBlobImpl()) {
					$script .= "
			if (\$row[\$startcol + $n] !== null) {
				\$this->$clo = fopen('php://memory', 'r+');
				fwrite(\$this->$clo, \$row[\$startcol + $n]);
				rewind(\$this->$clo);
			} else {
				\$this->$clo = null;
			}";
				} elseif ($col->isPhpPrimitiveType()) {
					$script .= "
			\$this->$clo = (\$row[\$startcol + $n] !== null) ? (".$col->getPhpType().") \$row[\$startcol + $n] : null;";
				} elseif ($col->isPhpObjectType()) {
					$script .= "
			\$this->$clo = (\$row[\$startcol + $n] !== null) ? new ".$col->getPhpType()."(\$row[\$startcol + $n]) : null;";
				} else {
					$script .= "
			\$this->$clo = \$row[\$startcol + $n];";
				}
				$n++;
			} // if col->isLazyLoad()
		} /* foreach */

		if ($this->getBuildProperty("addSaveMethod")) {
			$script .= "
			\$this->resetModified();
";
		}

		$script .= "
			\$this->setNew(false);

			if (\$rehydrate) {
				\$this->ensureConsistency();
			}

			// FIXME - using NUM_COLUMNS may be clearer.
			return \$startcol + $n; // $n = ".$this->getPeerClassname()."::NUM_COLUMNS - ".$this->getPeerClassname()."::NUM_LAZY_LOAD_COLUMNS).

		} catch (Exception \$e) {
			throw new PropelException(\"Error populating ".$this->getStubObjectBuilder()->getClassname()." object\", \$e);
		}";
	}

	/**
	 * Adds the function close for the hydrate method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addHydrate()
	 */
	protected function addHydrateClose(&$script) {
		$script .= "
	}
";
	}

	/**
	 * Adds the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addBuildPkeyCriteria(&$script) {
		$this->addBuildPkeyCriteriaComment($script);
		$this->addBuildPkeyCriteriaOpen($script);
		$this->addBuildPkeyCriteriaBody($script);
		$this->addBuildPkeyCriteriaClose($script);
	}

	/**
	 * Adds the comment for the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildPkeyCriteria()
	 **/
	protected function addBuildPkeyCriteriaComment(&$script) {
		$script .= "
	/**
	 * Builds a Criteria object containing the primary key for this object.
	 *
	 * Unlike buildCriteria() this method includes the primary key values regardless
	 * of whether or not they have been modified.
	 *
	 * @return     Criteria The Criteria object containing value(s) for primary key(s).
	 */";
	}

	/**
	 * Adds the function declaration for the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildPkeyCriteria()
	 **/
	protected function addBuildPkeyCriteriaOpen(&$script) {
		$script .= "
	public function buildPkeyCriteria()
	{";
	}

	/**
	 * Adds the function body for the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildPkeyCriteria()
	 **/
	protected function addBuildPkeyCriteriaBody(&$script) {
		$script .= "
		\$criteria = new Criteria(".$this->getPeerClassname()."::DATABASE_NAME);
";
		foreach ($this->getTable()->getColumns() as $col) {
			$clo = strtolower($col->getName());
			if ($col->isPrimaryKey()) {
				$script .= "
		\$criteria->add(".$this->getColumnConstant($col).", \$this->$clo);";
			}
		}
	}

	/**
	 * Adds the function close for the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildPkeyCriteria()
	 **/
	protected function addBuildPkeyCriteriaClose(&$script) {
		$script .= "

		return \$criteria;
	}
";
	}

	/**
	 * Adds the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addBuildCriteria(&$script)
	{
		$this->addBuildCriteriaComment($script);
		$this->addBuildCriteriaOpen($script);
		$this->addBuildCriteriaBody($script);
		$this->addBuildCriteriaClose($script);
	}

	/**
	 * Adds comment for the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildCriteria()
	 **/
	protected function addBuildCriteriaComment(&$script) {
		$script .= "
	/**
	 * Build a Criteria object containing the values of all modified columns in this object.
	 *
	 * @return     Criteria The Criteria object containing all modified values.
	 */";
	}

	/**
	 * Adds the function declaration of the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildCriteria()
	 **/
	protected function addBuildCriteriaOpen(&$script) {
		$script .= "
	public function buildCriteria()
	{";
	}

	/**
	 * Adds the function body of the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildCriteria()
	 **/
	protected function addBuildCriteriaBody(&$script) {
		$script .= "
		\$criteria = new Criteria(".$this->getPeerClassname()."::DATABASE_NAME);
";
		foreach ($this->getTable()->getColumns() as $col) {
			$clo = strtolower($col->getName());
			$script .= "
		if (\$this->isColumnModified(".$this->getColumnConstant($col).")) \$criteria->add(".$this->getColumnConstant($col).", \$this->$clo);";
		}
	}

	/**
	 * Adds the function close of the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildCriteria()
	 **/
	protected function addBuildCriteriaClose(&$script) {
		$script .= "

		return \$criteria;
	}
";
	}

	/**
	 * Adds the toArray method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addToArray(&$script)
	{
		$this->addToArrayComment($script);
		$this->addToArrayOpen($script);
		$this->addToArrayBody($script);
		$this->addToArrayClose($script);
	}

	/**
	 * Adds the comment of the toArray method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addToArray()
	 **/
	protected function addToArrayComment(&$script) {
		$script .= "
	/**
	 * Exports the object as an array.
	 *
	 * You can specify the key type of the array by passing one of the class
	 * type constants.
	 *
	 * @param      string \$keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                        BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. Defaults to BasePeer::TYPE_PHPNAME.
	 * @param      boolean \$includeLazyLoadColumns (optional) Whether to include lazy loaded columns.  Defaults to TRUE.
	 * @return     an associative array containing the field names (as keys) and field values
	 */";
	}

	/**
	 * Adds the function declaration of the toArray method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addToArray()
	 **/
	protected function addToArrayOpen(&$script) {
		$script .= "
	public function toArray(\$keyType = BasePeer::TYPE_PHPNAME, \$includeLazyLoadColumns = true)
	{";
	}

	/**
	 * Adds the function body of the toArray method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addToArray()
	 **/
	protected function addToArrayBody(&$script) {
		$script .= "
		\$keys = ".$this->getPeerClassname()."::getFieldNames(\$keyType);
		\$result = array(";
		foreach ($this->getTable()->getColumns() as $num => $col) {
			if ($col->isLazyLoad()) {
				 $script .= "
			\$keys[$num] => (\$includeLazyLoadColumns) ? \$this->get".$col->getPhpName()."() : null,";
			} else {
				$script .= "
			\$keys[$num] => \$this->get".$col->getPhpName()."(),";
			}
		}
		$script .= "
		);";
	}

	/**
	 * Adds the function close of the toArray method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addToArray()
	 **/
	protected function addToArrayClose(&$script) {
		$script .= "
		return \$result;
	}
";
	} // addToArray()

	/**
	 * Adds the getByName method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addGetByName(&$script)
	{
		$this->addGetByNameComment($script);
		$this->addGetByNameOpen($script);
		$this->addGetByNameBody($script);
		$this->addGetByNameClose($script);
	}

	/**
	 * Adds the comment for the getByName method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addGetByName
	 **/
	protected function addGetByNameComment(&$script) {
		$script .= "
	/**
	 * Retrieves a field from the object by name passed in as a string.
	 *
	 * @param      string \$name name
	 * @param      string \$type The type of fieldname the \$name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     mixed Value of field.
	 */";
	}

	/**
	 * Adds the function declaration for the getByName method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addGetByName
	 **/
	protected function addGetByNameOpen(&$script) {
		$script .= "
	public function getByName(\$name, \$type = BasePeer::TYPE_PHPNAME)
	{";
	}

	/**
	 * Adds the function body for the getByName method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addGetByName
	 **/
	protected function addGetByNameBody(&$script) {
		$script .= "
		\$pos = ".$this->getPeerClassname()."::translateFieldName(\$name, \$type, BasePeer::TYPE_NUM);
		\$field = \$this->getByPosition(\$pos);";
	}

	/**
	 * Adds the function close for the getByName method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addGetByName
	 **/
	protected function addGetByNameClose(&$script) {
		$script .= "
		return \$field;
	}
";
	}

	/**
	 * Adds the getByPosition method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addGetByPosition(&$script)
	{
		$this->addGetByPositionComment($script);
		$this->addGetByPositionOpen($script);
		$this->addGetByPositionBody($script);
		$this->addGetByPositionClose($script);
	}

	/**
	 * Adds comment for the getByPosition method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addGetByPosition
	 **/
	protected function addGetByPositionComment(&$script) {
		$script .= "
	/**
	 * Retrieves a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int \$pos position in xml schema
	 * @return     mixed Value of field at \$pos
	 */";
	}

	/**
	 * Adds the function declaration for the getByPosition method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addGetByPosition
	 **/
	protected function addGetByPositionOpen(&$script) {
		$script .= "
	public function getByPosition(\$pos)
	{";
	}

	/**
	 * Adds the function body for the getByPosition method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addGetByPosition
	 **/
	protected function addGetByPositionBody(&$script) {
		$table = $this->getTable();
		$script .= "
		switch(\$pos) {";
		$i = 0;
		foreach ($table->getColumns() as $col) {
			$cfc = $col->getPhpName();
			$cptype = $col->getPhpType();// not safe to use it because some methods may return objects (Blob)
			$script .= "
			case $i:
				return \$this->get$cfc();
				break;";
			$i++;
		} /* foreach */
		$script .= "
			default:
				return null;
				break;
		} // switch()";
	}

	/**
	 * Adds the function close for the getByPosition method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addGetByPosition
	 **/
	protected function addGetByPositionClose(&$script) {
		$script .= "
	}
";
	}

	protected function addSetByName(&$script)
	{
		$table = $this->getTable();
		$script .= "
	/**
	 * Sets a field from the object by name passed in as a string.
	 *
	 * @param      string \$name peer name
	 * @param      mixed \$value field value
	 * @param      string \$type The type of fieldname the \$name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     void
	 */
	public function setByName(\$name, \$value, \$type = BasePeer::TYPE_PHPNAME)
	{
		\$pos = ".$this->getPeerClassname()."::translateFieldName(\$name, \$type, BasePeer::TYPE_NUM);
		return \$this->setByPosition(\$pos, \$value);
	}
";
	}

	protected function addSetByPosition(&$script)
	{
		$table = $this->getTable();
		$script .= "
	/**
	 * Sets a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int \$pos position in xml schema
	 * @param      mixed \$value field value
	 * @return     void
	 */
	public function setByPosition(\$pos, \$value)
	{
		switch(\$pos) {";
		$i = 0;
		foreach ($table->getColumns() as $col) {
			$cfc = $col->getPhpName();
			$cptype = $col->getPhpType();
			$script .= "
			case $i:
				\$this->set$cfc(\$value);
				break;";
			$i++;
		} /* foreach */
		$script .= "
		} // switch()
	}
";
	} // addSetByPosition()

	protected function addFromArray(&$script)
	{
		$table = $this->getTable();
		$script .= "
	/**
	 * Populates the object using an array.
	 *
	 * This is particularly useful when populating an object from one of the
	 * request arrays (e.g. \$_POST).  This method goes through the column
	 * names, checking to see whether a matching key exists in populated
	 * array. If so the setByName() method is called for that column.
	 *
	 * You can specify the key type of the array by additionally passing one
	 * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
	 * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
	 * The default key type is the column's phpname (e.g. 'AuthorId')
	 *
	 * @param      array  \$arr     An array to populate the object from.
	 * @param      string \$keyType The type of keys the array uses.
	 * @return     void
	 */
	public function fromArray(\$arr, \$keyType = BasePeer::TYPE_PHPNAME)
	{
		\$keys = ".$this->getPeerClassname()."::getFieldNames(\$keyType);
";
		foreach ($table->getColumns() as $num => $col) {
			$cfc = $col->getPhpName();
			$cptype = $col->getPhpType();
			$script .= "
		if (array_key_exists(\$keys[$num], \$arr)) \$this->set$cfc(\$arr[\$keys[$num]]);";
		} /* foreach */
		$script .= "
	}
";
	} // addFromArray

	/**
	 * Adds a delete() method to remove the object form the datastore.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addDelete(&$script)
	{
		$this->addDeleteComment($script);
		$this->addDeleteOpen($script);
		$this->addDeleteBody($script);
		$this->addDeleteClose($script);
	}

	/**
	 * Adds the comment for the delete function
	 * @param      string &$script The script will be modified in this method.
	 * @see        addDelete()
	 **/
	protected function addDeleteComment(&$script) {
		$script .= "
	/**
	 * Removes this object from datastore and sets delete attribute.
	 *
	 * @param      PropelPDO \$con
	 * @return     void
	 * @throws     PropelException
	 * @see        BaseObject::setDeleted()
	 * @see        BaseObject::isDeleted()
	 */";
	}

	/**
	 * Adds the function declaration for the delete function
	 * @param      string &$script The script will be modified in this method.
	 * @see        addDelete()
	 **/
	protected function addDeleteOpen(&$script) {
		$script .= "
	public function delete(PropelPDO \$con = null)
	{";
	}

	/**
	 * Adds the function body for the delete function
	 * @param      string &$script The script will be modified in this method.
	 * @see        addDelete()
	 **/
	protected function addDeleteBody(&$script) {
		$script .= "
		if (\$this->isDeleted()) {
			throw new PropelException(\"This object has already been deleted.\");
		}

		if (\$con === null) {
			\$con = Propel::getConnection(".$this->getPeerClassname()."::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		\$con->beginTransaction();
		try {";
		if($this->getGeneratorConfig()->getBuildProperty('addHooks')) {
			$script .= "
			\$ret = \$this->preDelete(\$con);";
			// apply behaviors
			$this->applyBehaviorModifier('preDelete', $script, "			");
			$script .= "
			if (\$ret) {
				".$this->getPeerClassname()."::doDelete(\$this, \$con);
				\$this->postDelete(\$con);";
			// apply behaviors
			$this->applyBehaviorModifier('postDelete', $script, "				");
			$script .= "
				\$this->setDeleted(true);
				\$con->commit();
			} else {
				\$con->commit();
			}";
		} else {
			// apply behaviors
			$this->applyBehaviorModifier('preDelete', $script, "			");
			$script .= "
			".$this->getPeerClassname()."::doDelete(\$this, \$con);";
			// apply behaviors
			$this->applyBehaviorModifier('postDelete', $script, "			");
			$script .= "
			\$this->setDeleted(true);
			\$con->commit();";
		}

		$script .= "
		} catch (PropelException \$e) {
			\$con->rollBack();
			throw \$e;
		}";
	}

	/**
	 * Adds the function close for the delete function
	 * @param      string &$script The script will be modified in this method.
	 * @see        addDelete()
	 **/
	protected function addDeleteClose(&$script) {
		$script .= "
	}
";
	} // addDelete()

	/**
	 * Adds a reload() method to re-fetch the data for this object from the database.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addReload(&$script)
	{
		$table = $this->getTable();
		$script .= "
	/**
	 * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
	 *
	 * This will only work if the object has been saved and has a valid primary key set.
	 *
	 * @param      boolean \$deep (optional) Whether to also de-associated any related objects.
	 * @param      PropelPDO \$con (optional) The PropelPDO connection to use.
	 * @return     void
	 * @throws     PropelException - if this object is deleted, unsaved or doesn't have pk match in db
	 */
	public function reload(\$deep = false, PropelPDO \$con = null)
	{
		if (\$this->isDeleted()) {
			throw new PropelException(\"Cannot reload a deleted object.\");
		}

		if (\$this->isNew()) {
			throw new PropelException(\"Cannot reload an unsaved object.\");
		}

		if (\$con === null) {
			\$con = Propel::getConnection(".$this->getPeerClassname()."::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		// We don't need to alter the object instance pool; we're just modifying this instance
		// already in the pool.

		\$stmt = ".$this->getPeerClassname()."::doSelectStmt(\$this->buildPkeyCriteria(), \$con);
		\$row = \$stmt->fetch(PDO::FETCH_NUM);
		\$stmt->closeCursor();
		if (!\$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		\$this->hydrate(\$row, 0, true); // rehydrate
";

		// support for lazy load columns
		foreach ($table->getColumns() as $col) {
			if ($col->isLazyLoad()) {
				$clo = strtolower($col->getName());
				$script .= "
		// Reset the $clo lazy-load column
		\$this->" . $clo . " = null;
		\$this->".$clo."_isLoaded = false;
";
			}
		}

		$script .= "
		if (\$deep) {  // also de-associate any related objects?
";

		foreach ($table->getForeignKeys() as $fk) {
			$varName = $this->getFKVarName($fk);
			$script .= "
			\$this->".$varName." = null;";
		}

		foreach ($table->getReferrers() as $refFK) {
			if ($refFK->isLocalPrimaryKey()) {
				$script .= "
			\$this->".$this->getPKRefFKVarName($refFK)." = null;
";
			} else {
				$script .= "
			\$this->".$this->getRefFKCollVarName($refFK)." = null;
			\$this->".$this->getRefFKLastCriteriaVarName($refFK)." = null;
";
			}
		}

		$script .= "
		} // if (deep)
	}
";
	} // addReload()

	/**
	 * Adds the methods related to refreshing, saving and deleting the object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addManipulationMethods(&$script)
	{
		$this->addReload($script);
		$this->addDelete($script);
		$this->addSave($script);
		$this->addDoSave($script);
	}

	/**
	 * Adds the methods related to validationg the object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addValidationMethods(&$script)
	{
		$this->addValidationFailuresAttribute($script);
		$this->addGetValidationFailures($script);
		$this->addValidate($script);
		$this->addDoValidate($script);
	}

	/**
	 * Adds the $validationFailures attribute to store ValidationFailed objects.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addValidationFailuresAttribute(&$script)
	{
		$script .= "
	/**
	 * Array of ValidationFailed objects.
	 * @var        array ValidationFailed[]
	 */
	protected \$validationFailures = array();
";
	}

	/**
	 * Adds the getValidationFailures() method.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addGetValidationFailures(&$script)
	{
		$script .= "
	/**
	 * Gets any ValidationFailed objects that resulted from last call to validate().
	 *
	 *
	 * @return     array ValidationFailed[]
	 * @see        validate()
	 */
	public function getValidationFailures()
	{
		return \$this->validationFailures;
	}
";
	} // addGetValidationFailures()

	/**
	 * Adds the correct getPrimaryKey() method for this object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addGetPrimaryKey(&$script)
	{
		$pkeys = $this->getTable()->getPrimaryKey();
		if (count($pkeys) == 1) {
			$this->addGetPrimaryKey_SinglePK($script);
		} elseif (count($pkeys) > 1) {
			$this->addGetPrimaryKey_MultiPK($script);
		} else {
			// no primary key -- this is deprecated, since we don't *need* this method anymore
			$this->addGetPrimaryKey_NoPK($script);
		}
	}

	/**
	 * Adds the getPrimaryKey() method for tables that contain a single-column primary key.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addGetPrimaryKey_SinglePK(&$script)
	{
		$table = $this->getTable();
		$pkeys = $table->getPrimaryKey();
		$cptype = $pkeys[0]->getPhpType();

		$script .= "
	/**
	 * Returns the primary key for this object (row).
	 * @return     $cptype
	 */
	public function getPrimaryKey()
	{
		return \$this->get".$pkeys[0]->getPhpName()."();
	}
";
	} // addetPrimaryKey_SingleFK

	/**
	 * Adds the setPrimaryKey() method for tables that contain a multi-column primary key.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addGetPrimaryKey_MultiPK(&$script)
	{

		$script .= "
	/**
	 * Returns the composite primary key for this object.
	 * The array elements will be in same order as specified in XML.
	 * @return     array
	 */
	public function getPrimaryKey()
	{
		\$pks = array();
";
		$i = 0;
		foreach ($this->getTable()->getPrimaryKey() as $pk) {
			$script .= "
		\$pks[$i] = \$this->get".$pk->getPhpName()."();
";
			$i++;
		} /* foreach */
		$script .= "
		return \$pks;
	}
";
	} // addGetPrimaryKey_MultiFK()

	/**
	 * Adds the getPrimaryKey() method for objects that have no primary key.
	 * This "feature" is dreprecated, since the getPrimaryKey() method is not required
	 * by the Persistent interface (or used by the templates).  Hence, this method is also
	 * deprecated.
	 * @param      string &$script The script will be modified in this method.
	 * @deprecated
	 */
	protected function addGetPrimaryKey_NoPK(&$script)
	{
		$script .= "
	/**
	 * Returns NULL since this table doesn't have a primary key.
	 * This method exists only for BC and is deprecated!
	 * @return     null
	 */
	public function getPrimaryKey()
	{
		return null;
	}
";
	}
	/**
	 * Adds the correct setPrimaryKey() method for this object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addSetPrimaryKey(&$script)
	{
		$pkeys = $this->getTable()->getPrimaryKey();
		if (count($pkeys) == 1) {
			$this->addSetPrimaryKey_SinglePK($script);
		} elseif (count($pkeys) > 1) {
			$this->addSetPrimaryKey_MultiPK($script);
		} else {
			// no primary key -- this is deprecated, since we don't *need* this method anymore
			$this->addSetPrimaryKey_NoPK($script);
		}
	}

	/**
	 * Adds the setPrimaryKey() method for tables that contain a single-column primary key.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addSetPrimaryKey_SinglePK(&$script)
	{

		$pkeys = $this->getTable()->getPrimaryKey();
		$col = $pkeys[0];
		$clo=strtolower($col->getName());
		$ctype = $col->getPhpType();

		$script .= "
	/**
	 * Generic method to set the primary key ($clo column).
	 *
	 * @param      $ctype \$key Primary key.
	 * @return     void
	 */
	public function setPrimaryKey(\$key)
	{
		\$this->set".$col->getPhpName()."(\$key);
	}
";
	} // addSetPrimaryKey_SinglePK

	/**
	 * Adds the setPrimaryKey() method for tables that contain a multi-columnprimary key.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addSetPrimaryKey_MultiPK(&$script)
	{

		$script .="
	/**
	 * Set the [composite] primary key.
	 *
	 * @param      array \$keys The elements of the composite key (order must match the order in XML file).
	 * @return     void
	 */
	public function setPrimaryKey(\$keys)
	{
";
		$i = 0;
		foreach ($this->getTable()->getPrimaryKey() as $pk) {
			$pktype = $pk->getPhpType();
			$script .= "
		\$this->set".$pk->getPhpName()."(\$keys[$i]);
";
			$i++;
		} /* foreach ($table->getPrimaryKey() */
		$script .= "
	}
";
	} // addSetPrimaryKey_MultiPK

	/**
	 * Adds the setPrimaryKey() method for objects that have no primary key.
	 * This "feature" is dreprecated, since the setPrimaryKey() method is not required
	 * by the Persistent interface (or used by the templates).  Hence, this method is also
	 * deprecated.
	 * @param      string &$script The script will be modified in this method.
	 * @deprecated
	 */
	protected function addSetPrimaryKey_NoPK(&$script)
	{
		$script .="
	/**
	 * Dummy primary key setter.
	 *
	 * This function only exists to preserve backwards compatibility.  It is no longer
	 * needed or required by the Persistent interface.  It will be removed in next BC-breaking
	 * release of Propel.
	 *
	 * @deprecated
	 */
	 public function setPrimaryKey(\$pk)
	 {
		 // do nothing, because this object doesn't have any primary keys
	 }
";
	}

	// --------------------------------------------------------------------
	// Complex OM Methods
	// --------------------------------------------------------------------

	/**
	 * Constructs variable name for fkey-related objects.
	 * @param      ForeignKey $fk
	 * @return     string
	 */
	protected function getFKVarName(ForeignKey $fk)
	{
		return 'a' . $this->getFKPhpNameAffix($fk, $plural = false);
	}

	/**
	 * Constructs variable name for objects which referencing current table by specified foreign key.
	 * @param      ForeignKey $fk
	 * @return     string
	 */
	protected function getRefFKCollVarName(ForeignKey $fk)
	{
		return 'coll' . $this->getRefFKPhpNameAffix($fk, $plural = true);
	}

	/**
	 * Constructs variable name for single object which references current table by specified foreign key
	 * which is ALSO a primary key (hence one-to-one relationship).
	 * @param      ForeignKey $fk
	 * @return     string
	 */
	protected function getPKRefFKVarName(ForeignKey $fk)
	{
		return 'single' . $this->getRefFKPhpNameAffix($fk, $plural = false);
	}

	/**
	 * Gets variable name for the Criteria which was used to fetch the objects which
	 * referencing current table by specified foreign key.
	 * @param      ForeignKey $fk
	 * @return     string
	 */
	protected function getRefFKLastCriteriaVarName(ForeignKey $fk)
	{
		return 'last' . $this->getRefFKPhpNameAffix($fk, $plural = false) . 'Criteria';
	}

	// ----------------------------------------------------------------
	//
	// F K    M E T H O D S
	//
	// ----------------------------------------------------------------

	/**
	 * Adds the methods that get & set objects related by foreign key to the current object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addFKMethods(&$script)
	{
		foreach ($this->getTable()->getForeignKeys() as $fk) {
			$this->addFKMutator($script, $fk);
			$this->addFKAccessor($script, $fk);
		} // foreach fk
	}

	/**
	 * Adds the class attributes that are needed to store fkey related objects.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addFKAttributes(&$script, ForeignKey $fk)
	{
		$className = $this->getForeignTable($fk)->getPhpName();
		$varName = $this->getFKVarName($fk);

		$script .= "
	/**
	 * @var        $className
	 */
	protected $".$varName.";
";
	}

	/**
	 * Adds the mutator (setter) method for setting an fkey related object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addFKMutator(&$script, ForeignKey $fk)
	{
		$table = $this->getTable();
		$tblFK = $this->getForeignTable($fk);

		$joinTableObjectBuilder = $this->getNewObjectBuilder($tblFK);

		$className = $joinTableObjectBuilder->getObjectClassname();
		$varName = $this->getFKVarName($fk);

		$script .= "
	/**
	 * Declares an association between this object and a $className object.
	 *
	 * @param      $className \$v
	 * @return     ".$this->getObjectClassname()." The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function set".$this->getFKPhpNameAffix($fk, $plural = false)."($className \$v = null)
	{";
		foreach ($fk->getLocalColumns() as $columnName) {
			$column = $table->getColumn($columnName);
			$lfmap = $fk->getLocalForeignMapping();
			$colFKName = $lfmap[$columnName];
			$colFK = $tblFK->getColumn($colFKName);
			$script .= "
		if (\$v === null) {
			\$this->set".$column->getPhpName()."(".$this->getDefaultValueString($column).");
		} else {
			\$this->set".$column->getPhpName()."(\$v->get".$colFK->getPhpName()."());
		}
";

		} /* foreach local col */

		$script .= "
		\$this->$varName = \$v;
";

		// Now add bi-directional relationship binding, taking into account whether this is
		// a one-to-one relationship.

		if ($fk->isLocalPrimaryKey()) {
			$script .= "
		// Add binding for other direction of this 1:1 relationship.
		if (\$v !== null) {
			\$v->set".$this->getRefFKPhpNameAffix($fk, $plural = false)."(\$this);
		}
";
		} else {
			$script .= "
		// Add binding for other direction of this n:n relationship.
		// If this object has already been added to the $className object, it will not be re-added.
		if (\$v !== null) {
			\$v->add".$this->getRefFKPhpNameAffix($fk, $plural = false)."(\$this);
		}
";

		}

		$script .= "
		return \$this;
	}
";
	}

	/**
	 * Adds the accessor (getter) method for getting an fkey related object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addFKAccessor(&$script, ForeignKey $fk)
	{
		$table = $this->getTable();

		$varName = $this->getFKVarName($fk);
		$pCollName = $this->getFKPhpNameAffix($fk, $plural = true);
		
		$fkPeerBuilder = $this->getNewPeerBuilder($this->getForeignTable($fk));
		$fkObjectBuilder = $this->getNewObjectBuilder($this->getForeignTable($fk))->getStubObjectBuilder();
		$className = $fkObjectBuilder->getClassname(); // get the Classname that has maybe a prefix
		
		$and = "";
		$comma = "";
		$conditional = "";
		$argmap = array(); // foreign -> local mapping
		$argsize = 0;
		foreach ($fk->getLocalColumns() as $columnName) {
			
			$lfmap = $fk->getLocalForeignMapping();
			
			$localColumn = $table->getColumn($columnName);
			$foreignColumn = $fk->getForeignTable()->getColumn($lfmap[$columnName]);
			
			$column = $table->getColumn($columnName);
			$cptype = $column->getPhpType();
			$clo = strtolower($column->getName());
			
			if ($cptype == "integer" || $cptype == "float" || $cptype == "double") {
				$conditional .= $and . "\$this->". $clo ." != 0";
			} elseif ($cptype == "string") {
				$conditional .= $and . "(\$this->" . $clo ." !== \"\" && \$this->".$clo." !== null)";
			} else {
				$conditional .= $and . "\$this->" . $clo ." !== null";
			}
			
			$argmap[] = array('foreign' => $foreignColumn, 'local' => $localColumn);
			$and = " && ";
			$comma = ", ";
			$argsize = $argsize + 1;
		}
		
		// If the related column is a primary kay and if it's a simple association,
		// The use retrieveByPk() instead of doSelect() to take advantage of instance pooling
		$useRetrieveByPk = count($argmap) == 1 && $argmap[0]['foreign']->isPrimaryKey();

		$script .= "

	/**
	 * Get the associated $className object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     $className The associated $className object.
	 * @throws     PropelException
	 */
	public function get".$this->getFKPhpNameAffix($fk, $plural = false)."(PropelPDO \$con = null)
	{";
		$script .= "
		if (\$this->$varName === null && ($conditional)) {";
		if ($useRetrieveByPk) {
			$script .= "
			\$this->$varName = ".$fkPeerBuilder->getPeerClassname()."::retrieveByPk(\$this->$clo);";
		} else {
			$script .= "
			\$c = new Criteria(".$fkPeerBuilder->getPeerClassname()."::DATABASE_NAME);";
			foreach ($argmap as $el) {
				$fcol = $el['foreign'];
				$lcol = $el['local'];
				$clo = strtolower($lcol->getName());
				$script .= "
			\$c->add(".$fkPeerBuilder->getColumnConstant($fcol).", \$this->".$clo.");";
			}
			$script .= "
			\$this->$varName = ".$fkPeerBuilder->getPeerClassname()."::doSelectOne(\$c, \$con);";
		}
		if ($fk->isLocalPrimaryKey()) {
			$script .= "
			// Because this foreign key represents a one-to-one relationship, we will create a bi-directional association.
			\$this->{$varName}->set".$this->getRefFKPhpNameAffix($fk, $plural = false)."(\$this);";
		} else {
			$script .= "
			/* The following can be used additionally to
			   guarantee the related object contains a reference
			   to this object.  This level of coupling may, however, be
			   undesirable since it could result in an only partially populated collection
			   in the referenced object.
			   \$this->{$varName}->add".$this->getRefFKPhpNameAffix($fk, $plural = true)."(\$this);
			 */";
		}

		$script .= "
		}
		return \$this->$varName;
	}
";

	} // addFKAccessor

	/**
	 * Adds a convenience method for setting a related object by specifying the primary key.
	 * This can be used in conjunction with the getPrimaryKey() for systems where nothing is known
	 * about the actual objects being related.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addFKByKeyMutator(&$script, ForeignKey $fk)
	{
		$table = $this->getTable();

		#$className = $this->getForeignTable($fk)->getPhpName();
		$methodAffix = $this->getFKPhpNameAffix($fk);
		#$varName = $this->getFKVarName($fk);

		$script .= "
	/**
	 * Provides convenient way to set a relationship based on a
	 * key.  e.g.
	 * <code>\$bar->setFooKey(\$foo->getPrimaryKey())</code>
	 *";
		if (count($fk->getLocalColumns()) > 1) {
			$script .= "
	 * Note: It is important that the xml schema used to create this class
	 * maintains consistency in the order of related columns between
	 * ".$table->getName()." and ". $tblFK->getName().".
	 * If for some reason this is impossible, this method should be
	 * overridden in <code>".$table->getPhpName()."</code>.";
		}
		$script .= "
	 * @return     ".$this->getObjectClassname()." The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function set".$methodAffix."Key(\$key)
	{
";
		if (count($fk->getLocalColumns()) > 1) {
			$i = 0;
			foreach ($fk->getLocalColumns() as $colName) {
				$col = $table->getColumn($colName);
				$fktype = $col->getPhpType();
				$script .= "
			\$this->set".$col->getPhpName()."( ($fktype) \$key[$i] );
";
				$i++;
			} /* foreach */
		} else {
			$lcols = $fk->getLocalColumns();
			$colName = $lcols[0];
			$col = $table->getColumn($colName);
			$fktype = $col->getPhpType();
			$script .= "
		\$this->set".$col->getPhpName()."( ($fktype) \$key);
";
		}
		$script .= "
		return \$this;
	}
";
	} // addFKByKeyMutator()

	/**
	 * Adds the method that fetches fkey-related (referencing) objects but also joins in data from another table.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKGetJoinMethods(&$script, ForeignKey $refFK)
	{
		$table = $this->getTable();
		$tblFK = $refFK->getTable();
		$join_behavior = $this->getGeneratorConfig()->getBuildProperty('useLeftJoinsInDoJoinMethods') ? 'Criteria::LEFT_JOIN' : 'Criteria::INNER_JOIN';

		$peerClassname = $this->getStubPeerBuilder()->getClassname();
		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural=true);
		$collName = $this->getRefFKCollVarName($refFK);
		$lastCriteriaName = $this->getRefFKLastCriteriaVarName($refFK);

		$fkPeerBuilder = $this->getNewPeerBuilder($tblFK);

		$lastTable = "";
		foreach ($tblFK->getForeignKeys() as $fk2) {

			$tblFK2 = $this->getForeignTable($fk2);
			$doJoinGet = !$tblFK2->isForReferenceOnly();

			// it doesn't make sense to join in rows from the curent table, since we are fetching
			// objects related to *this* table (i.e. the joined rows will all be the same row as current object)
			if ($this->getTable()->getPhpName() == $tblFK2->getPhpName()) {
				$doJoinGet = false;
			}

			$relCol2 = $this->getFKPhpNameAffix($fk2, $plural = false);

			if ( $this->getRelatedBySuffix($refFK) != "" &&
			($this->getRelatedBySuffix($refFK) == $this->getRelatedBySuffix($fk2))) {
				$doJoinGet = false;
			}

			if ($doJoinGet) {
				$script .= "

	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this ".$table->getPhpName()." is new, it will return
	 * an empty collection; or if this ".$table->getPhpName()." has previously
	 * been saved, it will retrieve related $relCol from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in ".$table->getPhpName().".
	 */
	public function get".$relCol."Join".$relCol2."(\$criteria = null, \$con = null, \$join_behavior = $join_behavior)
	{";
				$script .= "
		if (\$criteria === null) {
			\$criteria = new Criteria($peerClassname::DATABASE_NAME);
		}
		elseif (\$criteria instanceof Criteria)
		{
			\$criteria = clone \$criteria;
		}

		if (\$this->$collName === null) {
			if (\$this->isNew()) {
				\$this->$collName = array();
			} else {
";
				foreach ($refFK->getForeignColumns() as $columnName) {
					$column = $table->getColumn($columnName);
					$flMap = $refFK->getForeignLocalMapping();
					$colFKName = $flMap[$columnName];
					$colFK = $tblFK->getColumn($colFKName);
					if ($colFK === null) {
						throw new EngineException("Column $colFKName not found in " . $tblFK->getName());
					}
					$clo = strtolower($column->getName());
					$script .= "
				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
				} // end foreach ($fk->getForeignColumns()

				$script .= "
				\$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelectJoin$relCol2(\$criteria, \$con, \$join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.
";
				foreach ($refFK->getForeignColumns() as $columnName) {
					$column = $table->getColumn($columnName);
					$flMap = $refFK->getForeignLocalMapping();
					$colFKName = $flMap[$columnName];
					$colFK = $tblFK->getColumn($colFKName);
					$clo = strtolower($column->getName());
					$script .= "
			\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
				} /* end foreach ($fk->getForeignColumns() */

				$script .= "
			if (!isset(\$this->$lastCriteriaName) || !\$this->".$lastCriteriaName."->equals(\$criteria)) {
				\$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelectJoin$relCol2(\$criteria, \$con, \$join_behavior);
			}
		}
		\$this->$lastCriteriaName = \$criteria;

		return \$this->$collName;
	}
";
			} /* end if ($doJoinGet) */

		} /* end foreach ($tblFK->getForeignKeys() as $fk2) { */

	} // function


	// ----------------------------------------------------------------
	//
	// R E F E R R E R    F K    M E T H O D S
	//
	// ----------------------------------------------------------------

	/**
	 * Adds the attributes used to store objects that have referrer fkey relationships to this object.
	 * <code>protected collVarName;</code>
	 * <code>private lastVarNameCriteria = null;</code>
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKAttributes(&$script, ForeignKey $refFK)
	{
		$joinedTableObjectBuilder = $this->getNewObjectBuilder($refFK->getTable());
		$className = $joinedTableObjectBuilder->getObjectClassname();

		if ($refFK->isLocalPrimaryKey()) {
			$script .= "
	/**
	 * @var        $className one-to-one related $className object
	 */
	protected $".$this->getPKRefFKVarName($refFK).";
";
		} else {
			$script .= "
	/**
	 * @var        array {$className}[] Collection to store aggregation of $className objects.
	 */
	protected $".$this->getRefFKCollVarName($refFK).";

	/**
	 * @var        Criteria The criteria used to select the current contents of ".$this->getRefFKCollVarName($refFK).".
	 */
	private $".$this->getRefFKLastCriteriaVarName($refFK)." = null;
";
		}
	}

	/**
	 * Adds the methods for retrieving, initializing, adding objects that are related to this one by foreign keys.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKMethods(&$script)
	{
		foreach ($this->getTable()->getReferrers() as $refFK) {
			if ($refFK->isLocalPrimaryKey()) {
				$this->addPKRefFKGet($script, $refFK);
				$this->addPKRefFKSet($script, $refFK);
			} else {
				$this->addRefFKClear($script, $refFK);
				$this->addRefFKInit($script, $refFK);
				$this->addRefFKGet($script, $refFK);
				$this->addRefFKCount($script, $refFK);
				$this->addRefFKAdd($script, $refFK);
				$this->addRefFKGetJoinMethods($script, $refFK);
			}
		}
	}

	/**
	 * Adds the method that clears the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKClear(&$script, ForeignKey $refFK) {

		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);
		$collName = $this->getRefFKCollVarName($refFK);

		$script .= "
	/**
	 * Clears out the $collName collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        add$relCol()
	 */
	public function clear$relCol()
	{
		\$this->$collName = null; // important to set this to NULL since that means it is uninitialized
	}
";
	} // addRefererClear()

	/**
	 * Adds the method that initializes the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKInit(&$script, ForeignKey $refFK) {

		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);
		$collName = $this->getRefFKCollVarName($refFK);

		$script .= "
	/**
	 * Initializes the $collName collection (array).
	 *
	 * By default this just sets the $collName collection to an empty array (like clear$collName());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function init$relCol()
	{
		\$this->$collName = array();
	}
";
	} // addRefererInit()

	/**
	 * Adds the method that adds an object into the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKAdd(&$script, ForeignKey $refFK)
	{
		$tblFK = $refFK->getTable();

		$joinedTableObjectBuilder = $this->getNewObjectBuilder($refFK->getTable());
		$className = $joinedTableObjectBuilder->getObjectClassname();

		$collName = $this->getRefFKCollVarName($refFK);

		$script .= "
	/**
	 * Method called to associate a $className object to this object
	 * through the $className foreign key attribute.
	 *
	 * @param      $className \$l $className
	 * @return     void
	 * @throws     PropelException
	 */
	public function add".$this->getRefFKPhpNameAffix($refFK, $plural = false)."($className \$l)
	{
		if (\$this->$collName === null) {
			\$this->init".$this->getRefFKPhpNameAffix($refFK, $plural = true)."();
		}
		if (!in_array(\$l, \$this->$collName, true)) { // only add it if the **same** object is not already associated
			array_push(\$this->$collName, \$l);
			\$l->set".$this->getFKPhpNameAffix($refFK, $plural = false)."(\$this);
		}
	}
";
	} // addRefererAdd

	/**
	 * Adds the method that returns the size of the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKCount(&$script, ForeignKey $refFK)
	{
		$table = $this->getTable();
		$tblFK = $refFK->getTable();

		$peerClassname = $this->getStubPeerBuilder()->getClassname();

		$fkPeerBuilder = $this->getNewPeerBuilder($refFK->getTable());
		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);

		$collName = $this->getRefFKCollVarName($refFK);
		$lastCriteriaName = $this->getRefFKLastCriteriaVarName($refFK);

		$className = $fkPeerBuilder->getObjectClassname();

		$script .= "
	/**
	 * Returns the number of related $className objects.
	 *
	 * @param      Criteria \$criteria
	 * @param      boolean \$distinct
	 * @param      PropelPDO \$con
	 * @return     int Count of related $className objects.
	 * @throws     PropelException
	 */
	public function count$relCol(Criteria \$criteria = null, \$distinct = false, PropelPDO \$con = null)
	{";

		$script .= "
		if (\$criteria === null) {
			\$criteria = new Criteria($peerClassname::DATABASE_NAME);
		} else {
			\$criteria = clone \$criteria;
		}

		if (\$distinct) {
			\$criteria->setDistinct();
		}

		\$count = null;

		if (\$this->$collName === null) {
			if (\$this->isNew()) {
				\$count = 0;
			} else {
";
		foreach ($refFK->getLocalColumns() as $colFKName) {
			// $colFKName is local to the referring table (i.e. foreign to this table)
			$lfmap = $refFK->getLocalForeignMapping();
			$localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
			$colFK = $refFK->getTable()->getColumn($colFKName);
			$clo = strtolower($localColumn->getName());
			$script .= "
				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
		} // end foreach ($fk->getForeignColumns()

		$script .= "
				\$count = ".$fkPeerBuilder->getPeerClassname()."::doCount(\$criteria, false, \$con);
			}
		} else {
			// criteria has no effect for a new object
			if (!\$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.
";
		foreach ($refFK->getLocalColumns() as $colFKName) {
			// $colFKName is local to the referring table (i.e. foreign to this table)
			$lfmap = $refFK->getLocalForeignMapping();
			$localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
			$colFK = $refFK->getTable()->getColumn($colFKName);
			$clo = strtolower($localColumn->getName());
			$script .= "

				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
		} // foreach ($fk->getForeignColumns()
		$script .= "
				if (!isset(\$this->$lastCriteriaName) || !\$this->".$lastCriteriaName."->equals(\$criteria)) {
					\$count = ".$fkPeerBuilder->getPeerClassname()."::doCount(\$criteria, false, \$con);
				} else {
					\$count = count(\$this->$collName);
				}
			} else {
				\$count = count(\$this->$collName);
			}
		}
		return \$count;
	}
";
	} // addRefererCount

	/**
	 * Adds the method that returns the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKGet(&$script, ForeignKey $refFK)
	{
		$table = $this->getTable();
		$tblFK = $refFK->getTable();

		$peerClassname = $this->getStubPeerBuilder()->getClassname();
		$fkPeerBuilder = $this->getNewPeerBuilder($refFK->getTable());
		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);

		$collName = $this->getRefFKCollVarName($refFK);
		$lastCriteriaName = $this->getRefFKLastCriteriaVarName($refFK);

		$className = $fkPeerBuilder->getObjectClassname();

		$script .= "
	/**
	 * Gets an array of $className objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this ".$this->getObjectClassname()." has previously been saved, it will retrieve
	 * related $relCol from storage. If this ".$this->getObjectClassname()." is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO \$con
	 * @param      Criteria \$criteria
	 * @return     array {$className}[]
	 * @throws     PropelException
	 */
	public function get$relCol(\$criteria = null, PropelPDO \$con = null)
	{";

		$script .= "
		if (\$criteria === null) {
			\$criteria = new Criteria($peerClassname::DATABASE_NAME);
		}
		elseif (\$criteria instanceof Criteria)
		{
			\$criteria = clone \$criteria;
		}

		if (\$this->$collName === null) {
			if (\$this->isNew()) {
			   \$this->$collName = array();
			} else {
";
		foreach ($refFK->getLocalColumns() as $colFKName) {
			// $colFKName is local to the referring table (i.e. foreign to this table)
			$lfmap = $refFK->getLocalForeignMapping();
			$localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
			$colFK = $refFK->getTable()->getColumn($colFKName);

			$clo = strtolower($localColumn->getName());

			$script .= "
				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
		} // end foreach ($fk->getForeignColumns()

		$script .= "
				".$fkPeerBuilder->getPeerClassname()."::addSelectColumns(\$criteria);
				\$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelect(\$criteria, \$con);
			}
		} else {
			// criteria has no effect for a new object
			if (!\$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.
";
		foreach ($refFK->getLocalColumns() as $colFKName) {
			// $colFKName is local to the referring table (i.e. foreign to this table)
			$lfmap = $refFK->getLocalForeignMapping();
			$localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
			$colFK = $refFK->getTable()->getColumn($colFKName);
			$clo = strtolower($localColumn->getName());
			$script .= "

				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
		} // foreach ($fk->getForeignColumns()
		$script .= "
				".$fkPeerBuilder->getPeerClassname()."::addSelectColumns(\$criteria);
				if (!isset(\$this->$lastCriteriaName) || !\$this->".$lastCriteriaName."->equals(\$criteria)) {
					\$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelect(\$criteria, \$con);
				}
			}
		}
		\$this->$lastCriteriaName = \$criteria;
		return \$this->$collName;
	}
";
	} // addRefererGet()

	/**
	 * Adds the method that gets a one-to-one related referrer fkey.
	 * This is for one-to-one relationship special case.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addPKRefFKGet(&$script, ForeignKey $refFK)
	{
		$table = $this->getTable();
		$tblFK = $refFK->getTable();

		$joinedTableObjectBuilder = $this->getNewObjectBuilder($refFK->getTable());
		$joinedTablePeerBuilder = $this->getNewObjectBuilder($refFK->getTable());
		$className = $joinedTableObjectBuilder->getObjectClassname();

		$varName = $this->getPKRefFKVarName($refFK);

		$script .= "
	/**
	 * Gets a single $className object, which is related to this object by a one-to-one relationship.
	 *
	 * @param      PropelPDO \$con
	 * @return     $className
	 * @throws     PropelException
	 */
	public function get".$this->getRefFKPhpNameAffix($refFK, $plural = false)."(PropelPDO \$con = null)
	{
";
		$script .= "
		if (\$this->$varName === null && !\$this->isNew()) {";

		$lfmap = $refFK->getLocalForeignMapping();

		// remember: this object represents the foreign table,
		// so we need foreign columns of the reffk to know the local columns
		// that we need to set :)

		$localcols = $refFK->getForeignColumns();

		// we know that at least every column in the primary key of the foreign table
		// is represented in this foreign key

		$params = array();
		foreach ($tblFK->getPrimaryKey() as $col) {
			$localColumn = $table->getColumn($lfmap[$col->getName()]);
			$clo = strtolower($localColumn->getName());
			$params[] = "\$this->$clo";
		}

		$script .= "
			\$this->$varName = ".$joinedTableObjectBuilder->getPeerClassname()."::retrieveByPK(".implode(", ", $params).", \$con);
		}

		return \$this->$varName;
	}
";
	} // addPKRefFKGet()

	/**
	 * Adds the method that sets a one-to-one related referrer fkey.
	 * This is for one-to-one relationships special case.
	 * @param      string &$script The script will be modified in this method.
	 * @param      ForeignKey $refFK The referencing foreign key.
	 */
	protected function addPKRefFKSet(&$script, ForeignKey $refFK)
	{
		$tblFK = $refFK->getTable();

		$joinedTableObjectBuilder = $this->getNewObjectBuilder($refFK->getTable());
		$className = $joinedTableObjectBuilder->getObjectClassname();

		$varName = $this->getPKRefFKVarName($refFK);

		$script .= "
	/**
	 * Sets a single $className object as related to this object by a one-to-one relationship.
	 *
	 * @param      $className \$l $className
	 * @return     ".$this->getObjectClassname()." The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function set".$this->getRefFKPhpNameAffix($refFK, $plural = false)."($className \$v)
	{
		\$this->$varName = \$v;

		// Make sure that that the passed-in $className isn't already associated with this object
		if (\$v->get".$this->getFKPhpNameAffix($refFK, $plural = false)."() === null) {
			\$v->set".$this->getFKPhpNameAffix($refFK, $plural = false)."(\$this);
		}

		return \$this;
	}
";
	} // addPKRefFKSet

	// ----------------------------------------------------------------
	//
	// M A N I P U L A T I O N    M E T H O D S
	//
	// ----------------------------------------------------------------

	/**
	 * Adds the workhourse doSave() method.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addDoSave(&$script)
	{
		$table = $this->getTable();

		$reloadOnUpdate = $table->isReloadOnUpdate();
		$reloadOnInsert = $table->isReloadOnInsert();

		$script .= "
	/**
	 * Performs the work of inserting or updating the row in the database.
	 *
	 * If the object is new, it inserts it; otherwise an update is performed.
	 * All related objects are also updated in this method.
	 *
	 * @param      PropelPDO \$con";
		if ($reloadOnUpdate || $reloadOnInsert) {
			$script .= "
	 * @param      boolean \$skipReload Whether to skip the reload for this object from database.";
		}
		$script .= "
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        save()
	 */
	protected function doSave(PropelPDO \$con, \$resolveDependencyProblems = false".($reloadOnUpdate || $reloadOnInsert ? ", \$skipReload = false" : "").")
	{
		\$affectedRows = 0; // initialize var to track total num of affected rows
		if (!\$this->alreadyInSave) {
			\$this->alreadyInSave = true;
";
		if ($reloadOnInsert || $reloadOnUpdate) {
			$script .= "
			\$reloadObject = false;
";
		}

		if (count($table->getForeignKeys())) {

			$script .= "
			// We call the save method on the following object(s) if they
			// were passed to this object by their coresponding set
			// method.  This object relates to these object(s) by a
			// foreign key reference.
";

			foreach ($table->getForeignKeys() as $fk)
			{
				$aVarName = $this->getFKVarName($fk);
				$script .= "
			if (\$this->$aVarName !== null) {
				if (\$this->".$aVarName."->isModified() || \$this->".$aVarName."->isNew()) {
					\$affectedRows += \$this->".$aVarName."->save(\$con, true === \$resolveDependencyProblems ? 'skip-dependants' : false);
				}
				\$this->set".$this->getFKPhpNameAffix($fk, $plural = false)."(\$this->$aVarName);
			}
";
			} // foreach foreign k
		} // if (count(foreign keys))
		
		if ($table->hasAutoIncrementPrimaryKey() ) {
		$script .= "
			if (\$this->isNew() ) {
				\$this->modifiedColumns[] = " . $this->getColumnConstant($table->getAutoIncrementPrimaryKey() ) . ";
			}";
		}

		$script .= "

			// If this object has been modified, then save it to the database.
			if (\$this->isModified()";

		$script .= ") {
				if (\$this->isNew()) {
					\$pk = ".$this->getPeerClassname()."::doInsert(\$this, \$con);";
		if ($reloadOnInsert) {
			$script .= "
					if (!\$skipReload) {
						\$reloadObject = true;
					}";
		}
		$script .= "
					\$affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
										 // should always be true here (even though technically
										 // BasePeer::doInsert() can insert multiple rows).
";
		if ($table->getIdMethod() != IDMethod::NO_ID_METHOD) {

			if (count($pks = $table->getPrimaryKey())) {
				foreach ($pks as $pk) {
					if ($pk->isAutoIncrement()) {
						$script .= "
					\$this->set".$pk->getPhpName()."(\$pk);  //[IMV] update autoincrement primary key
";
					}
				}
			}
		} // if (id method != "none")

		$script .= "
					\$this->setNew(false);
				} else {";
		if ($reloadOnUpdate) {
			$script .= "
					if (!\$skipReload) {
						\$reloadObject = true;
					}";
		}
		$script .= "
					\$affectedRows += ".$this->getPeerClassname()."::doUpdate(\$this, \$con);
				}
";

		// We need to rewind any LOB columns
		foreach ($table->getColumns() as $col) {
			$clo = strtolower($col->getName());
			if ($col->isLobType()) {
				$script .= "
				// Rewind the $clo LOB column, since PDO does not rewind after inserting value.
				if (\$this->$clo !== null && is_resource(\$this->$clo)) {
					rewind(\$this->$clo);
				}
";
			}
		}

		$script .= "
				\$this->resetModified(); // [HL] After being saved an object is no longer 'modified'
			}

			if ('skip-dependants' !== \$resolveDependencyProblems) {
";

		foreach ($table->getReferrers() as $refFK) {

			if ($refFK->isLocalPrimaryKey()) {
				$varName = $this->getPKRefFKVarName($refFK);
				$script .= "
				if (\$this->$varName !== null) {
					if (!\$this->{$varName}->isDeleted()) {
							\$affectedRows += \$this->{$varName}->save(\$con, \$resolveDependencyProblems);
					}
				}
";
			} else {
				$collName = $this->getRefFKCollVarName($refFK);
				$script .= "
				if (\$this->$collName !== null) {
					foreach (\$this->$collName as \$referrerFK) {
						if (!\$referrerFK->isDeleted()) {
							\$affectedRows += \$referrerFK->save(\$con, \$resolveDependencyProblems);
						}
					}
				}
";

			} // if refFK->isLocalPrimaryKey()


		} /* foreach getReferrers() */
		$script .= "
			}
";

		if (count($table->getForeignKeys())) {

			$script .= "
			// To avoid problems with cycles in dependecy graphs
            // we first save all entity dependecies, and afterwards
            // the dependants
			if (true === \$resolveDependencyProblems) {
";

			foreach ($table->getForeignKeys() as $fk)
			{
				$aVarName = $this->getFKVarName($fk);
				$script .= "
				if (\$this->$aVarName !== null) {
					if (\$this->".$aVarName."->isModified() || \$this->".$aVarName."->isNew()) {
						\$affectedRows += \$this->".$aVarName."->save(\$con, \$resolveDependencyProblems);
					}
					\$this->set".$this->getFKPhpNameAffix($fk, $plural = false)."(\$this->$aVarName);
				}
";
			} // foreach foreign k
			$script .= "
			}
";
		} // if (count(foreign keys))

		$script .= "
			\$this->alreadyInSave = false;
";
		if ($reloadOnInsert || $reloadOnUpdate) {
			$script .= "
			if (\$reloadObject) {
				\$this->reload(\$con);
			}
";
		}
		$script .= "
		}
		return \$affectedRows;
	} // doSave()
";

	}

	/**
	 * Adds the $alreadyInSave attribute, which prevents attempting to re-save the same object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addAlreadyInSaveAttribute(&$script)
	{
		$script .= "
	/**
	 * Flag to prevent endless save loop, if this object is referenced
	 * by another object which falls in this transaction.
	 * @var        boolean
	 */
	protected \$alreadyInSave = false;
";
	}

	/**
	 * Adds the save() method.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addSave(&$script)
	{
		$this->addSaveComment($script);
		$this->addSaveOpen($script);
		$this->addSaveBody($script);
		$this->addSaveClose($script);
	}

	/**
	 * Adds the comment for the save method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addSave()
	 **/
	protected function addSaveComment(&$script) {
		$table = $this->getTable();
		$reloadOnUpdate = $table->isReloadOnUpdate();
		$reloadOnInsert = $table->isReloadOnInsert();

		$script .= "
	/**
	 * Persists this object to the database.
	 *
	 * If the object is new, it inserts it; otherwise an update is performed.
	 * All modified related objects will also be persisted in the doSave()
	 * method.  This method wraps all precipitate database operations in a
	 * single transaction.";
		if ($reloadOnUpdate) {
			$script .= "
	 *
	 * Since this table was configured to reload rows on update, the object will
	 * be reloaded from the database if an UPDATE operation is performed (unless
	 * the \$skipReload parameter is TRUE).";
		}
		if ($reloadOnInsert) {
			$script .= "
	 *
	 * Since this table was configured to reload rows on insert, the object will
	 * be reloaded from the database if an INSERT operation is performed (unless
	 * the \$skipReload parameter is TRUE).";
		}
		$script .= "
	 *
	 * @param      PropelPDO \$con";
		if ($reloadOnUpdate || $reloadOnInsert) {
			$script .= "
	 * @param      boolean \$skipReload Whether to skip the reload for this object from database.";
		}
		$script .= "
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        doSave()
	 */";
	}

	/**
	 * Adds the function declaration for the save method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addSave()
	 **/
	protected function addSaveOpen(&$script) {
		$table = $this->getTable();
		$reloadOnUpdate = $table->isReloadOnUpdate();
		$reloadOnInsert = $table->isReloadOnInsert();
		$script .= "
	public function save(PropelPDO \$con = null, \$resolveDependencyProblems = false".($reloadOnUpdate || $reloadOnInsert ? ", \$skipReload = false" : "").")
	{";
	}

	/**
	 * Adds the function body for the save method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addSave()
	 **/
	protected function addSaveBody(&$script) {
		$table = $this->getTable();
		$reloadOnUpdate = $table->isReloadOnUpdate();
		$reloadOnInsert = $table->isReloadOnInsert();

		$script .= "
		if (\$this->isDeleted()) {
			throw new PropelException(\"You cannot save an object that has been deleted.\");
		}

		if (\$con === null) {
			\$con = Propel::getConnection(".$this->getPeerClassname()."::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		\$con->beginTransaction();
		\$isInsert = \$this->isNew();
		try {";
		
		if($this->getGeneratorConfig()->getBuildProperty('addHooks')) {
			// save with runtime hools
			$script .= "
			\$ret = \$this->preSave(\$con);";
			$this->applyBehaviorModifier('preSave', $script, "			");
			$script .= "
			if (\$isInsert) {
				\$ret = \$ret && \$this->preInsert(\$con);";
			$this->applyBehaviorModifier('preInsert', $script, "				");
			$script .= "
			} else {
				\$ret = \$ret && \$this->preUpdate(\$con);";
			$this->applyBehaviorModifier('preUpdate', $script, "				");
			$script .= "
			}
			if (\$ret) {
				\$affectedRows = \$this->doSave(\$con, \$resolveDependencyProblems".($reloadOnUpdate || $reloadOnInsert ? ", \$skipReload" : "").");
				if (\$isInsert) {
					\$this->postInsert(\$con);";
			$this->applyBehaviorModifier('postInsert', $script, "					");
			$script .= "
				} else {
					\$this->postUpdate(\$con);";
			$this->applyBehaviorModifier('postUpdate', $script, "					");
			$script .= "
				}
				\$this->postSave(\$con);";
				$this->applyBehaviorModifier('postSave', $script, "				");
				$script .= "
				".$this->getPeerClassname()."::addInstanceToPool(\$this);
			} else {
				\$affectedRows = 0;
			}
			\$con->commit();
			return \$affectedRows;";
		} else {
			// save without runtime hooks
	    $this->applyBehaviorModifier('preSave', $script, "			");
			if ($this->hasBehaviorModifier('preUpdate'))
			{
			  $script .= "
			if(!\$isInsert) {";
	      $this->applyBehaviorModifier('preUpdate', $script, "				");
	      $script .= "
			}";
			}
			if ($this->hasBehaviorModifier('preInsert'))
			{
			  $script .= "
			if(\$isInsert) {";
	    	$this->applyBehaviorModifier('preInsert', $script, "				");
	      $script .= "
			}";
			}
			$script .= "
			\$affectedRows = \$this->doSave(\$con".($reloadOnUpdate || $reloadOnInsert ? ", \$skipReload" : "").");";
	    $this->applyBehaviorModifier('postSave', $script, "			");
			if ($this->hasBehaviorModifier('postUpdate'))
			{
			  $script .= "
			if(!\$isInsert) {";
	      $this->applyBehaviorModifier('postUpdate', $script, "				");
	      $script .= "
			}";
			}
			if ($this->hasBehaviorModifier('postInsert'))
			{
			  $script .= "
			if(\$isInsert) {";
	      $this->applyBehaviorModifier('postInsert', $script, "				");
	      $script .= "
			}";
			}
			$script .= "
			\$con->commit();
			".$this->getPeerClassname()."::addInstanceToPool(\$this);
			return \$affectedRows;";
		}
		
		$script .= "
		} catch (PropelException \$e) {
			\$con->rollBack();
			throw \$e;
		}";
	}

	/**
	 * Adds the function close for the save method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addSave()
	 **/
	protected function addSaveClose(&$script) {
		$script .= "
	}
";
	}

	/**
	 * Adds the $alreadyInValidation attribute, which prevents attempting to re-validate the same object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addAlreadyInValidationAttribute(&$script)
	{
		$script .= "
	/**
	 * Flag to prevent endless validation loop, if this object is referenced
	 * by another object which falls in this transaction.
	 * @var        boolean
	 */
	protected \$alreadyInValidation = false;
";
	}

	/**
	 * Adds the validate() method.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addValidate(&$script)
	{
		$script .= "
	/**
	 * Validates the objects modified field values and all objects related to this table.
	 *
	 * If \$columns is either a column name or an array of column names
	 * only those columns are validated.
	 *
	 * @param      mixed \$columns Column name or an array of column names.
	 * @return     boolean Whether all columns pass validation.
	 * @see        doValidate()
	 * @see        getValidationFailures()
	 */
	public function validate(\$columns = null)
	{
		\$res = \$this->doValidate(\$columns);
		if (\$res === true) {
			\$this->validationFailures = array();
			return true;
		} else {
			\$this->validationFailures = \$res;
			return false;
		}
	}
";
	} // addValidate()

	/**
	 * Adds the workhourse doValidate() method.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addDoValidate(&$script)
	{
		$table = $this->getTable();

		$script .= "
	/**
	 * This function performs the validation work for complex object models.
	 *
	 * In addition to checking the current object, all related objects will
	 * also be validated.  If all pass then <code>true</code> is returned; otherwise
	 * an aggreagated array of ValidationFailed objects will be returned.
	 *
	 * @param      array \$columns Array of column names to validate.
	 * @return     mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objets otherwise.
	 */
	protected function doValidate(\$columns = null)
	{
		if (!\$this->alreadyInValidation) {
			\$this->alreadyInValidation = true;
			\$retval = null;

			\$failureMap = array();
";
		if (count($table->getForeignKeys()) != 0) {
			$script .= "

			// We call the validate method on the following object(s) if they
			// were passed to this object by their coresponding set
			// method.  This object relates to these object(s) by a
			// foreign key reference.
";
			foreach ($table->getForeignKeys() as $fk) {
				$aVarName = $this->getFKVarName($fk);
				$script .= "
			if (\$this->".$aVarName." !== null) {
				if (!\$this->".$aVarName."->validate(\$columns)) {
					\$failureMap = array_merge(\$failureMap, \$this->".$aVarName."->getValidationFailures());
				}
			}
";
			} /* for () */
		} /* if count(fkeys) */

		$script .= "

			if ((\$retval = ".$this->getPeerClassname()."::doValidate(\$this, \$columns)) !== true) {
				\$failureMap = array_merge(\$failureMap, \$retval);
			}

";

		foreach ($table->getReferrers() as $refFK) {
			if ($refFK->isLocalPrimaryKey()) {
				$varName = $this->getPKRefFKVarName($refFK);
				$script .= "
				if (\$this->$varName !== null) {
					if (!\$this->".$varName."->validate(\$columns)) {
						\$failureMap = array_merge(\$failureMap, \$this->".$varName."->getValidationFailures());
					}
				}
";
			} else {
				$collName = $this->getRefFKCollVarName($refFK);
				$script .= "
				if (\$this->$collName !== null) {
					foreach (\$this->$collName as \$referrerFK) {
						if (!\$referrerFK->validate(\$columns)) {
							\$failureMap = array_merge(\$failureMap, \$referrerFK->getValidationFailures());
						}
					}
				}
";
			}
		} /* foreach getReferrers() */

		$script .= "

			\$this->alreadyInValidation = false;
		}

		return (!empty(\$failureMap) ? \$failureMap : true);
	}
";
	} // addDoValidate()

	/**
	 * Adds the ensureConsistency() method to ensure that internal state is correct.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addEnsureConsistency(&$script)
	{
		$table = $this->getTable();

		$script .= "
	/**
	 * Checks and repairs the internal consistency of the object.
	 *
	 * This method is executed after an already-instantiated object is re-hydrated
	 * from the database.  It exists to check any foreign keys to make sure that
	 * the objects related to the current object are correct based on foreign key.
	 *
	 * You can override this method in the stub class, but you should always invoke
	 * the base method from the overridden method (i.e. parent::ensureConsistency()),
	 * in case your model changes.
	 *
	 * @throws     PropelException
	 */
	public function ensureConsistency()
	{
";
		foreach ($table->getColumns() as $col) {

			$clo=strtolower($col->getName());

			if ($col->isForeignKey()) {
				foreach ($col->getForeignKeys() as $fk) {

					$tblFK = $table->getDatabase()->getTable($fk->getForeignTableName());
					$colFK = $tblFK->getColumn($fk->getMappedForeignColumn($col->getName()));
					$varName = $this->getFKVarName($fk);

					$script .= "
		if (\$this->".$varName." !== null && \$this->$clo !== \$this->".$varName."->get".$colFK->getPhpName()."()) {
			\$this->$varName = null;
		}";
				} // foraech
			} /* if col is foreign key */

		} // foreach

		$script .= "
	} // ensureConsistency
";
	} // addCheckRelConsistency

	/**
	 * Adds the copy() method, which (in complex OM) includes the $deepCopy param for making copies of related objects.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addCopy(&$script)
	{
		$this->addCopyInto($script);

		$table = $this->getTable();

		$script .= "
	/**
	 * Makes a copy of this object that will be inserted as a new row in table when saved.
	 * It creates a new object filling in the simple attributes, but skipping any primary
	 * keys that are defined for the table.
	 *
	 * If desired, this method can also make copies of all associated (fkey referrers)
	 * objects.
	 *
	 * @param      boolean \$deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @return     ".$this->getObjectClassname()." Clone of current object.
	 * @throws     PropelException
	 */
	public function copy(\$deepCopy = false)
	{
		// we use get_class(), because this might be a subclass
		\$clazz = get_class(\$this);
		" . $this->buildObjectInstanceCreationCode('$copyObj', '$clazz') . "
		\$this->copyInto(\$copyObj, \$deepCopy);
		return \$copyObj;
	}
";
	} // addCopy()

	/**
	 * Adds the copyInto() method, which takes an object and sets contents to match current object.
	 * In complex OM this method includes the $deepCopy param for making copies of related objects.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addCopyInto(&$script)
	{
		$table = $this->getTable();

		$script .= "
	/**
	 * Sets contents of passed object to values from current object.
	 *
	 * If desired, this method can also make copies of all associated (fkey referrers)
	 * objects.
	 *
	 * @param      object \$copyObj An object of ".$this->getObjectClassname()." (or compatible) type.
	 * @param      boolean \$deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @throws     PropelException
	 */
	public function copyInto(\$copyObj, \$deepCopy = false)
	{
";

		$autoIncCols = array();
		foreach ($table->getColumns() as $col) {
			/* @var        $col Column */
			if ($col->isAutoIncrement()) {
				$autoIncCols[] = $col;
			}
		}

		foreach ($table->getColumns() as $col) {
			if (!in_array($col, $autoIncCols, true)) {
				$script .= "
		\$copyObj->set".$col->getPhpName()."(\$this->".strtolower($col->getName()).");
";
			}
		} // foreach

		// Avoid useless code by checking to see if there are any referrers
		// to this table:
		if (count($table->getReferrers()) > 0) {
			$script .= "

		if (\$deepCopy) {
			// important: temporarily setNew(false) because this affects the behavior of
			// the getter/setter methods for fkey referrer objects.
			\$copyObj->setNew(false);
";
			foreach ($table->getReferrers() as $fk) {
				//HL: commenting out self-referrential check below
				//		it seems to work as expected and is probably desireable to have those referrers from same table deep-copied.
				//if ( $fk->getTable()->getName() != $table->getName() ) {

				if ($fk->isLocalPrimaryKey()) {

					$afx = $this->getRefFKPhpNameAffix($fk, $plural = false);
					$script .= "
			\$relObj = \$this->get$afx();
			if (\$relObj) {
				\$copyObj->set$afx(\$relObj->copy(\$deepCopy));
			}
";
				} else {

					$script .= "
			foreach (\$this->get".$this->getRefFKPhpNameAffix($fk, true)."() as \$relObj) {
				if (\$relObj !== \$this) {  // ensure that we don't try to copy a reference to ourselves
					\$copyObj->add".$this->getRefFKPhpNameAffix($fk)."(\$relObj->copy(\$deepCopy));
				}
			}
";
				}
				// HL: commenting out close of self-referential check
				// } /* if tblFK != table */
			} /* foreach */
			$script .= "
		} // if (\$deepCopy)
";
		} /* if (count referrers > 0 ) */

		$script .= "

		\$copyObj->setNew(true);
";

		// Note: we're no longer resetting non-autoincrement primary keys to default values
		// due to: http://propel.phpdb.org/trac/ticket/618
		foreach ($autoIncCols as $col) {
				$coldefval = $col->getPhpDefaultValue();
				$coldefval = var_export($coldefval, true);
				$script .= "
		\$copyObj->set".$col->getPhpName() ."($coldefval); // this is a auto-increment column, so set to default value
";
		} // foreach
		$script .= "
	}
";
	} // addCopyInto()


	/**
	 * Adds clearAllReferencers() method which resets all the collections of referencing
	 * fk objects.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addClearAllReferences(&$script)
	{
		$table = $this->getTable();
		$script .= "
	/**
	 * Resets all collections of referencing foreign keys.
	 *
	 * This method is a user-space workaround for PHP's inability to garbage collect objects
	 * with circular references.  This is currently necessary when using Propel in certain
	 * daemon or large-volumne/high-memory operations.
	 *
	 * @param      boolean \$deep Whether to also clear the references on all associated objects.
	 */
	public function clearAllReferences(\$deep = false)
	{
		if (\$deep) {";
		$vars = array();
		foreach ($this->getTable()->getReferrers() as $refFK) {
			if ($refFK->isLocalPrimaryKey()) {
				$varName = $this->getPKRefFKVarName($refFK);
				$vars[] = $varName;
				$script .= "
			if (\$this->$varName) {
				\$this->{$varName}->clearAllReferences(\$deep);
			}";
			} else {
				$varName = $this->getRefFKCollVarName($refFK);
				$vars[] = $varName;
				$script .= "
			if (\$this->$varName) {
				foreach ((array) \$this->$varName as \$o) {
					\$o->clearAllReferences(\$deep);
				}
			}";
			}
		}

		$script .= "
		} // if (\$deep)
";

		foreach ($vars as $varName) {
			$script .= "
		\$this->$varName = null;";
		}

		foreach ($table->getForeignKeys() as $fk) {
			$className = $this->getForeignTable($fk)->getPhpName();
			$varName = $this->getFKVarName($fk);
			$script .= "
			\$this->$varName = null;";
		}

		$script .= "
	}
";
	}

  /**
   * Adds a magic __toString() method if a string column was defined as primary string
	 * @param      string &$script The script will be modified in this method.
   */
  protected function addPrimaryString(&$script)
  {
    foreach ($this->getTable()->getColumns() as $column) {
      if ($column->isPrimaryString()) {
        $script .= "
	/**
	 * Return the string representation of this object
	 *
	 * @return string The value of the '{$column->getName()}' column
	 */
  public function __toString()
  {
    return (string) \$this->get{$column->getPhpName()}();
  }
";
        break;
      }
    }
  }
} // PHP5ObjectBuilder
