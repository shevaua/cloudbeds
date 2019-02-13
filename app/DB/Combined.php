<?php

namespace DB;

use Interfaces\Statement;

class Combined implements Statement
{

    const TYPE_OR = 'or';
    const TYPE_END = 'and';

    private $type;
    private $statements = [];

    public function __construct(string $type, array $statements)
    {

        $this->type = $type;
        $this->statements = $statements;

    }

    /**
     * Getting prepared statement
     * @return string
     */
    public function toString(): string
    {
        
        if(!$this->statements)
        {
            return '';
        }

        if(count($this->statements) == 1)
        {
            return $this->statements[0]->toString();
        }

        $result = '(' . $this->statements[0]->toString();

        $n = count($this->statements);
        for($i = 1; $i < $n; $i++)
        {
            $result .= ' ' . $this->type . ' ' . $this->statements[$i]->toString();
        }

        $result .= ')';

        return $result;

    }

}
