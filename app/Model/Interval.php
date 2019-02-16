<?php

namespace Model;

use DB\Connection;
use Exceptions\DataException;
use DB\QueryBuilder;
use Exceptions\SQLException;

class Interval
{

    private static $table = 'interval';

    public static function create($columns = [])
    {

        return self::query()
            ->insert($columns);

    }

    public static function query()
    {

        return new QueryBuilder(self::$table, self::class);

    }
    
    /**
     * @var int $id
     * @var string $start
     * @var string $end
     * @var int $price
     */
    private $id;
    private $start;
    private $end;
    private $price;

    public function __construct(array $values)
    {

        if(isset($values['id']))
        {
            $this->id = (int) $values['id'];
        }

        if(isset($values['start']))
        {
            $this->start = $values['start'];
        }

        if(isset($values['end']))
        {
            $this->end = $values['end'];
        }

        if(isset($values['price']))
        {
            $this->price = (int) $values['price'];
        }

    }

    /**
     * @return self
     */
    public function setStartDate(string $date)
    {

        if($this->start != $date)
        {
            $this->start = $date;
        }
        return $this;

    }

    /**
     * @return self
     */
    public function setEndDate(string $date)
    {

        if($this->end != $date)
        {
            $this->end = $date;
        }
        return $this;

    }

    /**
     * @return self
     */
    public function setPrice(int $price)
    {

        if($this->price != $price)
        {
            $this->price = $price;
        }
        return $this;

    }

    public function __toString()
    {
        return ($this->start == $this->end) ?
            '[       '.$this->start. '       : '.$this->price.']' :
            '['.$this->start. ' - '. $this->end.' : '.$this->price.']';
    }

    public function save()
    {

        if(!$this->id)
        {
            $newInstance = self::query()
                ->insert([
                    'start' => $this->start,
                    'end' => $this->end,
                    'price' => $this->price,
                ]);
            $this->id = $newInstance->id;
        }
        else
        {
            self::query()
                ->update($this->id, [
                    'start' => $this->start,
                    'end' => $this->end,
                    'price' => $this->price,
                ]);
        }

    }

    public function delete()
    {
        if(!$this->id)
        {
            throw new \LogicException();
        }
        self::query()
            ->delete($this->id);
    }

    /**
     * Get Starting Date of the interval
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->start;
    }

    /**
     * Get Starting Timestamp of the interval
     * @return int
     */
    public function getStartTime(): int
    {
        return strtotime($this->start);
    }

    /**
     * Get Ending Date of the interval
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->end;
    }

    /**
     * Get Ending Timestamp of the interval
     * @return int 
     */
    public function getEndTime(): int
    {
        return strtotime($this->end);
    }

    /**
     * Get Price of the interval
     * @return int 
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * Get Date of the day before the interval
     * @return string
     */
    public function getBeforeDate(): string
    {
        return date('Y-m-d', $this->getBeforeTime());
    }

    /**
     * Get Timestamp of the day before the interval
     * @return int 
     */
    public function getBeforeTime(): int
    {
        return strtotime('-1 day', $this->getStartTime());
    }

    /**
     * Get Date of the day after the interval
     * @return string
     */
    public function getAfterDate(): string
    {
        return date('Y-m-d', $this->getAfterTime());
    }

    /**
     * Get Timestamp of the day after the interval
     * @return int 
     */
    public function getAfterTime(): int
    {
        return strtotime('+1 day', $this->getEndTime());
    }

}
