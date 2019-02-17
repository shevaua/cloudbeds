<?php

namespace Action;

use Model\Interval;

use DB\Where;
use DB\Clause;
use DB\Combined;
use DB\Connection;

class Apply
{

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

                case Interval::TYPE_LEFT_JOIN:

                    $oldInterval->setEndDate($endDate);
                    $this->forSave[] = $oldInterval;
                    $leftJoin = $oldInterval;
                    $newShouldBeSaved = false;

                    break;

                case Interval::TYPE_LEFT_CROSS:

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

                case Interval::TYPE_IN:
                    $this->forDelete[] = $oldInterval;
                    break;

                case Interval::TYPE_RIGHT_CROSS:

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

                case Interval::TYPE_RIGHT_JOIN:

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
