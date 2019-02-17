<?php

namespace Action;

use Model\Interval;

use DB\Where;
use DB\Clause;
use DB\Combined;
use DB\Connection;

class Clear extends AbstractAction
{

    /**
     * Identify the changes
     * @return void
     */
    protected function identifyChanges()
    {

        $newInterval = $this->interval;

        if(!$affectedIntervals = $this->getAffectedIntervals())
        {
            // nothing todo
            return;
        }

        /**
         * @var Interval $oldInterval
         */
        foreach($affectedIntervals as $oldInterval)
        {
            $type = $newInterval->compareTo($oldInterval);
            
            switch($type)
            {

                case Interval::TYPE_COVERED:

                    $coveredStartDate = $oldInterval->getStartDate();
                    $coveredEndDate = $oldInterval->getEndDate();
                    
                    $oldInterval
                        ->setEndDate($newInterval->getBeforeDate());
                    $this->forSave[] = $oldInterval;
                    $this->forSave[] = new Interval([
                        'price' => $oldInterval->getPrice(),
                        'start' => $newInterval->getAfterDate(),
                        'end' => $coveredEndDate,
                    ]);

                    return;

                case Interval::TYPE_LEFT_CROSS:

                    $oldInterval->setEndDate($newInterval->getBeforeDate());
                    $this->forSave[] = $oldInterval;
                    break;

                case Interval::TYPE_IN:
                    $this->forDelete[] = $oldInterval;
                    break;

                case Interval::TYPE_RIGHT_CROSS:

                    $oldInterval->setStartDate($newInterval->getAfterDate());
                    $this->forSave[] = $oldInterval;
                    break;

                default:
                    throw new \LogicException();
            }
        }

    }

    /**
     * Get array of Interval affected by new Interval
     * @return array
     */
    private function getAffectedIntervals(): array
    {

        $startDay = $this->interval->getStartDate();
        $endDay = $this->interval->getEndDate();
        
        $where = new Combined('or', [
            new Combined('and', [
                new Clause('start', $startDay, '>='),
                new Clause('start', $endDay, '<=')
            ]),
            new Combined('and', [ 
                new Clause('end', $startDay, '>='),
                new Clause('end', $endDay, '<=')
            ]),
            new Combined('and', [
                new Clause('start', $startDay, '<'),
                new Clause('end', $endDay, '>')
            ])
        ]);
        
        return Interval::query()
            ->where($where)
            ->order(['start' => 'asc'])
            ->find();

    }

}
