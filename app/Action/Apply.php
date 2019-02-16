<?php

namespace Action;

use Model\Interval;

use DB\Where;
use DB\Clause;
use DB\Combined;
use DB\Connection;

class Apply
{

    /*
     * See notes/cases.php 
     */
    const TYPE_OUT = 'outside';
    const TYPE_IN = 'inside';
    const TYPE_LEFT_CROSS = 'left_cross';
    const TYPE_LEFT_JOIN = 'left_join';
    const TYPE_RIGHT_CROSS = 'right_cross';
    const TYPE_RIGHT_JOIN = 'right_join';
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

    /**
     * List of Intervals for removal
     * @var array $forDelete
     */
    private $forDelete = [];

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
    protected function identifyChanges()
    {

        $newInterval = $this->interval;

        if(!$affectedIntervals = $this->getAffectedIntervals())
        {
            $this->forSave[] = $newInterval;
            return;
        }
   
        $newShouldBeSaved = true;

        $startDate = $newInterval->getStartDate();
        $endDate = $newInterval->getEndDate();

        $leftJoin = null;

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
                    
                    $oldInterval
                        ->setEndDate($newInterval->getBeforeDate());
                    $this->forSave[] = $oldInterval;
                    $this->forSave[] = new Interval([
                        'price' => $oldInterval->getPrice(),
                        'start' => $newInterval->getAfterDate(),
                        'end' => $coveredEndDate,
                    ]);
                    $this->forSave[] = $newInterval;

                    return;

                case self::TYPE_LEFT_JOIN:

                    $oldInterval->setEndDate($endDate);
                    $this->forSave[] = $oldInterval;
                    $leftJoin = $oldInterval;
                    $newShouldBeSaved = false;

                    break;

                case self::TYPE_LEFT_CROSS:

                    if($newInterval->getPrice() == $oldInterval->getPrice())
                    {
                        $oldInterval->setEndDate($newInterval->getEndDate());
                        $newShouldBeSaved = false;
                        $leftJoin = $oldInterval;
                    }
                    else
                    {
                        $oldInterval->setEndDate($newInterval->getBeforeDate());
                    }
                    $this->forSave[] = $oldInterval;
                    break;

                case self::TYPE_IN:
                    $this->forDelete[] = $oldInterval;
                    break;

                case self::TYPE_RIGHT_CROSS:

                    if($newInterval->getPrice() == $oldInterval->getPrice())
                    {
                        if($leftJoin)
                        {
                            $leftJoin->setEndDate($oldInterval->getEndDate());
                            $this->forDelete[] = $oldInterval;
                        }
                        else
                        {
                            $oldInterval->setStartDate($newInterval->getStartDate());
                            $newShouldBeSaved = false;
                            $this->forSave[] = $oldInterval;
                        }
                    }
                    else
                    {
                        $oldInterval->setStartDate($newInterval->getAfterDate());
                        $this->forSave[] = $oldInterval;
                    }
                    break;

                case self::TYPE_RIGHT_JOIN:

                    if($leftJoin)
                    {
                        $leftJoin->setEndDate($oldInterval->getEndDate());
                        $this->forDelete[] = $oldInterval;
                    }
                    else
                    {
                        $oldInterval->setStartDate($newInterval->getStartDate());
                        $this->forSave[] = $oldInterval;
                        $newShouldBeSaved = false;
                    }
                    break;

                default:
                    throw new \LogicException();
            }
        }

        if($newShouldBeSaved)
        {
            if($this->forDelete)
            {
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
            return self::TYPE_LEFT_JOIN;
        }

        // Right Join
        if(
            $newNextTime == $oldStartTime
            and $newPrice == $oldPrice
        ) {
            return self::TYPE_RIGHT_JOIN;
        }

        // Old inside the new one
        if(
            $newStartTime <= $oldStartTime
            and $newEndTime >= $oldEndTime
        ) {
            return self::TYPE_IN;
        }

        // Covered by old interval
        if(
            $newStartTime > $oldStartTime
            and $newEndTime < $oldEndTime
        ) {
            return self::TYPE_COVERED;
        }

        // Cross right
        if(
            $oldStartTime >= $newStartTime
            and $oldEndTime > $newEndTime 
        ) {
            return self::TYPE_RIGHT_CROSS;
        }

        // Cross left
        if(
            $oldStartTime < $newStartTime
            and $oldEndTime <= $newEndTime 
        ) {
            return self::TYPE_LEFT_CROSS;
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
