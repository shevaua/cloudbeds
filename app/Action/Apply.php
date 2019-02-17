<?php

namespace Action;

use Model\Interval;

use DB\Where;
use DB\Clause;
use DB\Combined;

class Apply extends AbstractAction
{

    /**
     * Identify the changes
     * @return void
     */
    protected function identifyChanges()
    {

        /**
         * @var Interval $interval
         */
        $newInterval = $this->interval;

        /**
         * @var array $affectedIntervals
         */
        if(!$affectedIntervals = $this->getAffectedIntervals())
        {
            // Save only new Interval
            $this->forSave[] = $newInterval;
            return;
        }
   
        /**
         * @var bool $newShouldBeSaved - Identify whether new Interval should be inserted
         */
        $newShouldBeSaved = true;

        /**
         * @var string $startDate
         * @var string $endDate
         */
        $startDate = $newInterval->getStartDate();
        $endDate = $newInterval->getEndDate();

        /**
         * Set if new Interval was joined
         * @var Interval|null $leftJoin 
         */
        $leftJoin = null;

        /**
         * @var Interval $oldInterval
         */
        foreach($affectedIntervals as $oldInterval)
        {
            /**
             * Type of range comparison 
             * @var string $type
             */
            $type = $newInterval->compareTo($oldInterval);
            
            switch($type)
            {
                case Interval::TYPE_COVERED:

                    // Do not change anything if price is the same
                    if($newInterval->getPrice() == $oldInterval->getPrice())
                    {
                        return;
                    }

                    $coveredStartDate = $oldInterval->getStartDate();
                    $coveredEndDate = $oldInterval->getEndDate();
                    
                    // Update $oldInterval to start - 1 day
                    $oldInterval
                        ->setEndDate($newInterval->getBeforeDate());
                    $this->forSave[] = $oldInterval;

                    // Insert $newInterval from end + 1 
                    $this->forSave[] = new Interval([
                        'price' => $oldInterval->getPrice(),
                        'start' => $newInterval->getAfterDate(),
                        'end' => $coveredEndDate,
                    ]);

                    // Insert new Interval
                    $this->forSave[] = $newInterval;

                    return;

                case Interval::TYPE_LEFT_JOIN:

                    // Update $oldInterval
                    $oldInterval->setEndDate($endDate);
                    $this->forSave[] = $oldInterval;

                    // Pass joined for next interval
                    $leftJoin = $oldInterval;

                    // Skip saving of
                    $newShouldBeSaved = false;

                    break;

                case Interval::TYPE_LEFT_CROSS:

                    if($newInterval->getPrice() == $oldInterval->getPrice())
                    {
                        // Update $oldInterval
                        $oldInterval->setEndDate($newInterval->getEndDate());

                        // Skip saving of 
                        $newShouldBeSaved = false;

                        // Pass joined for next interval
                        $leftJoin = $oldInterval;
                    }
                    else
                    {
                        // Update $oldInterval
                        $oldInterval->setEndDate($newInterval->getBeforeDate());
                    }
                    // $oldInterval should be updated
                    $this->forSave[] = $oldInterval;
                    break;

                case Interval::TYPE_IN:
                    
                    // Put interval in queue for removal
                    $this->forDelete[] = $oldInterval;
                    break;

                case Interval::TYPE_RIGHT_CROSS:

                    if($newInterval->getPrice() == $oldInterval->getPrice())
                    {
                        if($leftJoin)
                        {
                            // Updated already joined interval
                            $leftJoin->setEndDate($oldInterval->getEndDate());

                            // Put interval in queue for removal
                            $this->forDelete[] = $oldInterval;
                        }
                        else
                        {
                            // Update $oldInterval
                            $oldInterval->setStartDate($newInterval->getStartDate());
                            $this->forSave[] = $oldInterval;

                            // Skip saving of $newInterval
                            $newShouldBeSaved = false;
                        }
                    }
                    else
                    {
                        // Update $oldInterval
                        $oldInterval->setStartDate($newInterval->getAfterDate());
                        $this->forSave[] = $oldInterval;
                    }
                    break;

                case Interval::TYPE_RIGHT_JOIN:

                    if($leftJoin)
                    {
                        // Updated already joined interval
                        $leftJoin->setEndDate($oldInterval->getEndDate());

                        // Put interval in queue for removal
                        $this->forDelete[] = $oldInterval;
                    }
                    else
                    {
                        // Update $oldInterval
                        $oldInterval->setStartDate($newInterval->getStartDate());
                        $this->forSave[] = $oldInterval;

                        // Skip saving of $newInterval
                        $newShouldBeSaved = false;
                    }
                    break;

                default:

                    // in case of missed cases
                    throw new \LogicException();

            }
        }

        if($newShouldBeSaved)
        {
            if($this->forDelete)
            {
                // Perform update in case of
                // insert and delete at the same time
                $firstInterval = array_shift($this->forDelete);
                $firstInterval
                    ->setStartDate($newInterval->getStartDate())
                    ->setEndDate($newInterval->getEndDate())
                    ->setPrice($newInterval->getPrice());
                $this->forSave[] = $firstInterval;
            }
            else
            {
                $this->forSave[] = $newInterval;
            }
        }

    }

    /**
     * Get array of Interval affected by new Interval
     * @return array
     */
    private function getAffectedIntervals(): array
    {

        /*
         * See notes/raw_query.sql
         */
        $startDay = $this->interval->getStartDate();
        $endDay = $this->interval->getEndDate();
        $price = $this->interval->getPrice();
        $prevDay = $this->interval->getBeforeDate();
        $nextDay = $this->interval->getAfterDate();
        
        $where = new Combined('or', [
            new Combined('and', [
                new Clause('end', $prevDay),
                new Clause('price', $price),
            ]),
            new Combined('and', [
                new Clause('start', $nextDay),
                new Clause('price', $price),
            ]),
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
