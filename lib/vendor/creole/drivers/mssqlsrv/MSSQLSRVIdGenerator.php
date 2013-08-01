<?php

require_once 'creole/IdGenerator.php';

/**
 * MSSQL IdGenerator implimenation.
 *
 * @author    Piotr Plenik <piotr.plenik@teamlab.pl>
 * @version   $Revision: 447 $
 * @package   creole.drivers.mssqlsrv
 */
class MSSQLSRVIdGenerator implements IdGenerator {

    /** Connection object that instantiated this class */
    private $conn;

    /**
     * Creates a new IdGenerator class, saves passed connection for use
     * later by getId() method.
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @see IdGenerator::isBeforeInsert()
     */
    public function isBeforeInsert()
    {
        return false;
    }

    /**
     * @see IdGenerator::isAfterInsert()
     */
    public function isAfterInsert()
    {
        return true;
    }

    /**
     * @see IdGenerator::getIdMethod()
     */
    public function getIdMethod()
    {
        return self::AUTOINCREMENT;
    }

    /**
     * @see IdGenerator::getId()
     */
    public function getId($unused = null)
    { 
        return $this->conn->getLastInsertedId();
    }

}

