<?php

namespace DB;

use Interfaces\Statement;

class Clause implements Statement
{

    /**
     * @var string $column
     * @var string|float|int $value
     * @var string $comparison
     */
    private $column;
    private $value;
    private $comparison;

    public function __construct(string $column, $value, string $comparison = '=')
    {

        $this->column = $column;
        $this->value = $value;
        $this->comparison = $comparison;

    }

    /**
     * Getting prepared statement
     * @return string
     */
    public function toString(): string
    {
        
        return '`'.$this->column.'`' . $this->comparison
            . ( (is_int($this->value) or is_float($this->value)) ? 
                $this->value : 
                '\''.$this->value.'\'' );

    }

}
