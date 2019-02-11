<?php

namespace DB;
use Exceptions\SQLException;

class QueryBuilder
{

    private $table;
    private $model;

    private $order = [];
    private $limit;

    public function __construct(string $table, $modelClass)
    {
        $this->table = $table;
        $this->model = $modelClass;
    }

    public function limit(int $num)
    {
        $this->limit = $num;
        return $this;
    }

    public function order(array $order)
    {
        $this->order = $order;
        return $this;
    }

    public function find()
    {
        
        $connection = Connection::get();
        $query = $this->toSQL();
        $result = $connection->execute($query); 

        if(!$result)
        {
            throw new SQLException($connection->getError(), $query);
        }

        $collection = [];

        for($i = 0, $n = $result->num_rows; $i < $n; $i++)
        {
            $values = $result->fetch_assoc();
            $collection[] = new $this->model($values);
        }

        $result->free();

        return $collection;

    }

    public function first()
    {
        $this->limit(1);
        $connection = Connection::get();
        $query = $this->toSQL();
        $result = $connection->execute($query); 

        if(!$result)
        {
            throw new SQLException($connection->getError(), $query);
        }
        
        if($result->num_rows < 1)
        {
            return null;
        }

        $values = $result->fetch_assoc();
        $result->free();
        return new $this->model($values);
    }

    public function insert(array $data)
    {
        
        $query = 
            'insert into `' . $this->table . '` set ';
        foreach($data as $name => $value)
        {
            $query .= '`' . $name . '`='
                .(is_int($value) ? $value : '\'' . $value . '\'')
                .', ';
        }
        $query = rtrim($query, ', ');
        
        $connection = Connection::get();
        if(!$connection->execute($query))
        {
            throw new SQLException($connection->getError(), $query);
        }

        $values = array_merge($data, ['id' => $connection->getLastId()]);
        return new $this->model($values);

    }

    protected function toSQL()
    {

        $sql = 
            'select `' . $this->table . '`.* '
            .'from `'.$this->table.'` ';

        if($this->order)
        {
            $sql .= 'order by ';
            $orderClause = '';
            foreach($this->order as $column => $order)
            {
                $orderClause .= '`'.$column.'` '.$order.',';
            }
            $sql .= rtrim($orderClause,',');
        }
        
        if($this->limit)
        {
            $sql .= ' limit '.$this->limit;
        }

        return $sql;

    } 

}