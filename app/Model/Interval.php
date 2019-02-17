<?php

namespace Model;

use DB\QueryBuilder;

class Interval
{

    const REGEX_DATE = '#^(\d{4})-(\d{2})-(\d{2})$#';

    const TYPE_OUT = 'outside';
    const TYPE_IN = 'inside';
    const TYPE_LEFT_CROSS = 'left_cross';
    const TYPE_LEFT_JOIN = 'left_join';
    const TYPE_RIGHT_CROSS = 'right_cross';
    const TYPE_RIGHT_JOIN = 'right_join';
    const TYPE_COVERED = 'covered';

    private static $table = 'interval';

    /**
     * Insert new record into DB
     * @param array $data
     * @return self
     */
    public static function create($data = []): self
    {

        return self::query()
            ->insert($data);

    }

    /**
     * Prepare and get query builde
     * @return QueryBuilder
     */
    public static function query(): QueryBuilder
    {

        return new QueryBuilder(self::$table, self::class);

    }
    
    /**
     * @var int $id
     * @var string $start
     * @var string $end
     * @var float $price
     */
    private $id;
    private $start;
    private $end;
    private $price = 0.0;


    /**
     * @param array $values
     */
    public function __construct(array $values)
    {

        if(isset($values['id']))
        {
            $this->id = (int) $values['id'];
        }

        if(isset($values['start']))
        {
            $this->setStartDate($values['start']);
        }

        if(isset($values['end']))
        {
            $this->setEndDate($values['end']);
        }

        if(isset($values['price']))
        {
            $this->price = (float) $values['price'];
        }

    }

    /**
     * Set new start date
     * @param string $date
     * @return self
     */
    public function setStartDate(string $date)
    {

        if(!$this->isDate($date))
        {
            throw new \LogicException('Wrong Date: '.$date);
        }
        if($this->start != $date)
        {
            $this->start = $date;
        }
        return $this;

    }

    /**
     * Set new end date
     * @param string $date
     * @return self
     */
    public function setEndDate(string $date)
    {
        
        if(!$this->isDate($date))
        {
            throw new \LogicException('Wrong Date: '.$date);
        }
        if($this->end != $date)
        {
            $this->end = $date;
        }
        return $this;

    }

    /**
     * Set new price
     * @param float $price
     * @return self
     */
    public function setPrice(float $price)
    {

        if($this->price != $price)
        {
            $this->price = $price;
        }
        return $this;

    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return '['
            . (($this->start == $this->end) ? 
                '       '.$this->start. '      ' :
                $this->start. ' - '. $this->end )
            . ' : ' 
            . number_format($this->price, 2)
            . ']';
    }

    /**
     * Save|Update instance to DB
     */
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

    /**
     * Delete instance from DB
     */
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
     * @return float 
     */
    public function getPrice(): float
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

    /**
     * Get id of the interval
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Compare with another interval
     * @return string One of the Type
     */
    public function compareTo(Interval $interval): string
    {

        $newStartTime = $this->getStartTime();
        $newEndTime = $this->getEndTime();
        $newPrice = $this->getPrice();
        
        $newPrevTime = $this->getBeforeTime();
        $newNextTime = $this->getAfterTime();

        $oldStartTime = $interval->getStartTime();
        $oldEndTime = $interval->getEndTime();
        $oldPrice = $interval->getPrice();

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
     * Checks whether string is correct date or not
     * @return bool
     */
    private function isDate(string $date): bool
    {
        return (
            preg_match(self::REGEX_DATE, $date, $matches)
            and checkdate(
                (int) $matches[2], 
                (int) $matches[3], 
                (int) $matches[1]
            )
        );
    }

}
