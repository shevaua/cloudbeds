<?php

namespace Model;

use DB\Connection;
use Exceptions\DataException;
use DB\QueryBuilder;

class Price
{

    private static $table = 'price';

    public static function create($columns = [])
    {

        return self::query()
            ->insert($columns);

    }

    public static function query()
    {

        return new QueryBuilder(self::$table, self::class);

    }

    public static function truncate()
    {

        $connection = Connection::get();
        $isSuccess = $connection
            ->execute('truncate table `'.self::$table.'`');

    }

    
    private $id;
    private $start;
    private $end;
    private $price;

    public function __construct(array $values)
    {

        if(isset($values['id']))
        {
            $this->id = (int) $values['id'];
        }

        if(isset($values['start']))
        {
            $this->start = $values['start'];
        }

        if(isset($values['end']))
        {
            $this->end = $values['end'];
        }

        if(isset($values['price']))
        {
            $this->price = (int) $values['price'];
        }

    }

    public function __toString()
    {
        return '('.$this->start. ' - '. $this->end.' : '.$this->price.')';
    }

}
