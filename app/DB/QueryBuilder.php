<?php

namespace DB;
use Exceptions\SQLException;
use Interfaces\Statement;

class QueryBuilder
{

    /**
     * @var string $table
     * @var string $model
     */
    private $table;
    private $model;

    /**
     * @var array where
     * @var array $order
     * @var int $limit
     */
    private $where = [];
    private $order = [];
    private $limit;

    /**
     * @param string $table
     * @param string $modelClass
     */
    public function __construct(string $table, string $modelClass)
    {
        $this->table = $table;
        $this->model = $modelClass;
    }

    /**
     * Set limit for query
     * @param int $num
     * @return self
     */
    public function limit(int $num)
    {
        $this->limit = $num;
        return $this;
    }

    /**
     * Set where statement
     * @param Statement $statement
     * @return self
     */
    public function where(Statement $statement)
    {
        $this->where = $statement;
        return $this;
    }

    /**
     * Set order statement
     * @param array $order
     * @return self
     */
    public function order(array $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get list of records
     * @return array
     */
    public function find()
    {
        
        $connection = Connection::get();
        $query = $this->toSQL();
        $result = $connection->execute($query); 

        $collection = [];

        for($i = 0, $n = $result->num_rows; $i < $n; $i++)
        {
            $values = $result->fetch_assoc();
            $collection[] = new $this->model($values);
        }

        $result->free();

        return $collection;

    }

    /**
     * Insert data into $table
     * @param array $data
     * @return instanceof $modelClass 
     */
    public function insert(array $data)
    {
        
        $query = 'insert into `' . $this->table . '` set ';

        foreach($data as $name => $value)
        {
            $query .= '`' . $name . '`='
                .((is_int($value) or is_float($value)) ? 
                    $value : 
                    '\'' . $value . '\'')
                .', ';
        }
        $query = rtrim($query, ', ');
        
        $connection = Connection::get();
        $connection->execute($query);

        $values = array_merge($data, ['id' => $connection->getLastId()]);
        return new $this->model($values);

    }

    /**
     * Update record
     * @param int $pk 
     * @param array $data
     */
    public function update(int $pk, array $data)
    {

        $query = 'update `' . $this->table . '` set ';
        foreach($data as $name => $value)
        {
            $query .= '`' . $name . '`='
                .((is_int($value) or is_float($value)) ? 
                    $value : 
                    '\'' . $value . '\'')
                .', ';
        }
        $query = rtrim($query, ', ');
        $query .= ' where id='.$pk;
        
        $connection = Connection::get();
        $connection->execute($query);

        $values = array_merge($data, ['id' => $connection->getLastId()]);
        return new $this->model($values);

    }

    /**
     * Delete record by PK
     * @param int $pk
     * @return bool
     */
    public function delete(int $pk): bool
    {
        $connection = Connection::get();
        $query = 'delete from `'.$this->table.'` where id='.$pk;
        return $connection->execute($query);
    }

    /**
     * Truncate table
     * @return bool
     */
    public function truncate(): bool
    {
        $connection = Connection::get();
        $query = 'truncate table `'.$this->table.'`';
        return $connection->execute($query);
    }

    /**
     * Prepare select query
     * @return string
     */
    protected function toSQL(): string
    {

        $sql = 'select `' . $this->table . '`.* '
            .'from `'.$this->table.'` ';

        if($this->where)
        {
            $sql .= 'where '.$this->where->toString().' ';
        }

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