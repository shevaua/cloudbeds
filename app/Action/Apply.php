<?php

namespace Action;

use Model\Interval;

use DB\Where;
use DB\Clause;
use DB\Combined;

class Apply
{

    /*
     * See notes/cases.php 
     */
    const TYPE_OUT = 'outside';
    const TYPE_IN = 'inside';
    const TYPE_CROSS = 'cross';
    const TYPE_JOIN = 'join';
    const TYPE_COVERED = 'covered';

    /**
     * @var Interval
     */
    private $interval;

    /**
     * List of Intervals with changes
     * @var array $forSave 
     */
    private $forSave = [];

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

        foreach($this->forSave as $interval)
        {
            $interval->save();
        }
        // var_dump($this->forSave); die;
    }

    /**
     * Identify the changes
     * @return void
     */
    protected function identifyChanges()
    {

        $newInterval = $this->interval;

        if(!$affectedIntervals = $this->getAffectedIntervals())
        {
            $newInterval->save();
            return;
        }
   
        /**
         * @var Interval $oldInterval
         */
        foreach($affectedIntervals as $oldInterval)
        {
            $type = $this->compareIntervals($newInterval, $oldInterval);
            switch($type)
            {
                case self::TYPE_COVERED:

                    // Do not change anything if price is the same
                    if($newInterval->getPrice() == $oldInterval->getPrice())
                    {
                        return;
                    }

                    $coveredStartDate = $oldInterval->getStartDate();
                    $coveredEndDate = $oldInterval->getEndDate();
                    $coveredStartTime = $oldInterval->getStartTime();
                    $coveredEndTime = $oldInterval->getEndTime();  
                    
                    if(
                        $newInterval->getStartTime() == $oldInterval->getStartTime()
                        and $newInterval->getEndTime() == $oldInterval->getEndTime()
                    ) {
                        $oldInterval->setPrice($newInterval->getPrice());
                        $this->forSave[] = $oldInterval;
                        return;
                    }
                    
                    $isOldUpdated = false;

                    if($newInterval->getStartTime() > $coveredStartTime)
                    {
                        $isOldUpdated = true;
                        $oldInterval->setEndDate($newInterval->getBeforeDate());
                        $this->forSave[] = $oldInterval;
                    }

                    if($newInterval->getEndTime() < $coveredEndTime)
                    {
                        if($isOldUpdated)
                        {
                            $this->forSave[] = new Interval([
                                'price' => $oldInterval->getPrice(),
                                'start' => $newInterval->getAfterDate(),
                                'end' => $coveredEndDate,
                            ]);
                        }
                        else
                        {
                            $isOldUpdated = true;
                            $oldInterval->setStartDate($newInterval->getAfterDate());
                            $this->forSave[] = $oldInterval;
                        }
                    }

                    $this->forSave[] = $newInterval;

                    return;

                default:
                    return;
            }
        }

    }

    /**
     * Get type of affecting
     * @return string
     */
    private function compareIntervals(Interval $new, Interval $old): string
    {

        $newStartTime = $new->getStartTime();
        $newEndTime = $new->getEndTime();
        $newPrice = $new->getPrice();
        
        $newPrevTime = $new->getBeforeTime();
        $newNextTime = $new->getAfterTime();

        $oldStartTime = $old->getStartTime();
        $oldEndTime = $old->getEndTime();
        $oldPrice = $old->getPrice();

        // Left Out
        if(
            $newPrevTime > $oldEndTime
            or (
                $newPrevTime == $oldEndTime
                and $newPrice != $oldPrice
            )
        ) {
            return self::TYPE_OUT;
        }

        // Right Out
        if(
            $newNextTime < $oldStartTime
            or (
                $newNextTime == $oldStartTime
                and $newPrice != $oldPrice
            )
        ) {
            return self::TYPE_OUT;
        }

        // Left Join
        if(
            $newPrevTime == $oldEndTime
            and $newPrice == $oldPrice
        ) {
            return self::TYPE_JOIN;
        }

        // Right Join
        if(
            $newNextTime == $oldStartTime
            and $newPrice == $oldPrice
        ) {
            return self::TYPE_JOIN;
        }

        // Covered by old interval
        if(
            $newStartTime >= $oldStartTime
            and $newEndTime <= $oldEndTime
        ) {
            return self::TYPE_COVERED;
        }

        // Old inside the new one
        if(
            $newStartTime <= $oldStartTime
            and $newEndTime >= $oldEndTime
        ) {
            return self::TYPE_IN;
        }

        // Cross 
        if(
            $oldStartTime > $newStartTime
            and $oldStartTime <= $newEndTime
            and $oldEndTime > $newEndTime 
        ) {
            return self::TYPE_CROSS;
        }

        // Cross
        if(
            $oldStartTime < $newStartTime
            and $oldEndTime >= $newStartTime
            and $oldEndTime < $newEndTime 
        ) {
            return self::TYPE_CROSS;
        }

        throw new \LogicException();

    }

    /**
     * Get array of Interval affected by new Interval
     * @return array
     */
    private function getAffectedIntervals(): array
    {

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