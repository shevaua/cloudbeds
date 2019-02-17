<?php

namespace Action;

use Model\Interval;

use DB\Where;
use DB\Clause;
use DB\Combined;
use DB\Connection;

abstract class AbstractAction
{

    /**
     * @var Interval
     */
    protected $interval;

    /**
     * List of Intervals with changes
     * @var array $forSave 
     */
    protected $forSave = [];

    /**
     * List of Intervals for removal
     * @var array $forDelete
     */
    protected $forDelete = [];

    public function __construct(Interval $interval)
    {

        $this->interval = $interval;
        $this->identifyChanges();
        $this->saveChanges();

    }

    /**
     * Save the changes
     * @return void
     */
    private function saveChanges()
    {

        if(
            !$this->forSave
            and !$this->forDelete
        ) {
            return;
        }

        $conn = Connection::get();

        $conn->transaction();

        foreach($this->forDelete as $interval)
        {
            $interval->delete();
        }

        foreach($this->forSave as $interval)
        {
            $interval->save();
        }

        $conn->commit();

    }

    /**
     * Identify the changes
     * @return void
     */
    abstract protected function identifyChanges();
    
}
