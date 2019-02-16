<?php

namespace DB;

use Config;
use mysqli;

class Connection
{

    private static $instance;

    public static function get()
    {

        if(!self::$instance)
        {
            self::$instance = new self;
        }
        return self::$instance;

    }


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

    public function execute($query)
    {
        
        return $this->conn
            ->query($query);

    }

    public function getError()
    {
        return $this->conn->error;
    }

    public function getLastId()
    {
        return $this->conn->insert_id;
    }

    public function transaction()
    {
        return $this->conn->begin_transaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

}