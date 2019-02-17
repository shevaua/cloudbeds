<?php

namespace DB;

use Config;
use mysqli;
use Pattern\Observer\Observable;

class Connection extends Observable
{

    /**
     * @var DB\Connection $instance
     */
    private static $instance;

    /**
     * @return DB\Connection
     */
    public static function get()
    {

        if(!self::$instance)
        {
            self::$instance = new self;
        }
        return self::$instance;

    }


    /**
     * @var mysqli
     */
    private $conn;

    public function __construct()
    {

        $config = Config::getInstance();

        $host = $config->get('db.host');
        $user = $config->get('db.user');
        $pass = $config->get('db.pass');
        $db = $config->get('db.name');

        $this->conn = new mysqli($host, $user, $pass, $db);
        $this->conn
            ->autocommit(false);

    }

    /**
     * Execute query
     * @return void
     */
    public function execute($query)
    {
        
        $return = $this->conn
            ->query($query);

        if(!$return)
        {
            throw new SQLException($this->getError(), $query);
        }

        $this->notifyObservers($query);

        return $return;

    }

    /**
     * Get last error message
     */
    public function getError()
    {
        return $this->conn->error;
    }

    /**
     * Get last inserted id
     */
    public function getLastId()
    {
        return $this->conn->insert_id;
    }

    /**
     * Start transaction
     */
    public function transaction()
    {
        return $this->conn->begin_transaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->conn->commit();
    }

}
