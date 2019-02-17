<?php

namespace Action;

use Model\Interval;

use DB\Where;
use DB\Clause;
use DB\Combined;

class Clear extends AbstractAction
{

    /**
     * Identify the changes
     * @return void
     */
    protected function identifyChanges()
    {

        /**
         * @var Intervar $newInterval
         */
        $newInterval = $this->interval;

        /**
         * @var array $affectedIntervals
         */
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

            /**
             * Type of interval comparison
             * @var string $type
             */
            $type = $newInterval->compareTo($oldInterval);
            
            switch($type)
            {
                case Interval::TYPE_COVERED:

                    $coveredStartDate = $oldInterval->getStartDate();
                    $coveredEndDate = $oldInterval->getEndDate();
                    
                    // Update $oldInterval to start - 1
                    $oldInterval
                        ->setEndDate($newInterval->getBeforeDate());
                    $this->forSave[] = $oldInterval;

                    // Create one additional from end + 1
                    $this->forSave[] = new Interval([
                        'price' => $oldInterval->getPrice(),
                        'start' => $newInterval->getAfterDate(),
                        'end' => $coveredEndDate,
                    ]);

                    return;

                case Interval::TYPE_LEFT_CROSS:

                    // Update $oldInterval
                    $oldInterval->setEndDate($newInterval->getBeforeDate());
                    $this->forSave[] = $oldInterval;
                    break;

                case Interval::TYPE_IN:
                    // Put in queue for removal
                    $this->forDelete[] = $oldInterval;
                    break;

                case Interval::TYPE_RIGHT_CROSS:

                    // Update $oldInterval
                    $oldInterval->setStartDate($newInterval->getAfterDate());
                    $this->forSave[] = $oldInterval;
                    break;

                default:

                    // in case of missed case
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
