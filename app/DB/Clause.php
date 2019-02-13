<?php

namespace DB;

use Interfaces\Statement;

class Clause implements Statement
{

    private $column;
    private $value;
    private $comparision;

    public function __construct(string $column, $value, string $comparision = '=')
    {

        $this->column = $column;
        $this->value = $value;
        $this->comparision = $comparision;

    }

    /**
     * Getting prepared statement
     * @return string
     */
    public function toString(): string
    {
        
        return '`'.$this->column.'`' . $this->comparision
            . ( is_int($this->value) ? 
                $this->value : 
                '\''.$this->value.'\'' );

    }

}
