<?php

namespace Http\Controller\API;

use View\JsonView;
use Http\Request;
use Model\Interval as Range;
use Api\Insert;
use Api\Delete;
use Pattern\Observer\Observer;
use DB\Connection;

class Interval implements Observer
{

    private $queries = [];

    public function get(Request $r)
    {
     
        Connection::get()->registerObserver($this);

        $collection = Range::query()
            ->order(['start' => 'asc'])
            ->find();
            
        $intervals = [];
        foreach($collection as $obj)
        {
            $intervals[] = [
                'id' => $obj->getId(),
                'start' => $obj->getStartDate(),
                'end' => $obj->getEndDate(),
                'price' => $obj->getPrice(),
            ];
        }   

        return new JsonView([
            'success' => true,
            'intervals' => $intervals,
            'queries' => $this->queries,
        ]);

    }

    public function post(Request $r)
    {
        
        $params = $r->getParams();
        
        if(
            count($params) != 3
            or !$price = (float) $params['price']
            or $price < 0
            or !preg_match(Range::REGEX_DATE, $params['start'])
            or !preg_match(Range::REGEX_DATE, $params['end'])
            or !$start = strtotime($params['start'])
            or !$end = strtotime($params['end'])
            or $start > $end
        ) {        
            return new JsonView([
                'success' => false,
            ]);
        }

        Connection::get()->registerObserver($this);

        new Insert($params['start'], $params['end'], $price);
        
        return new JsonView([
            'success' => true,
            'queries' => $this->queries,
        ]);
        
    }

    public function delete(Request $r)
    {
        
        $params = $r->getParams();
        
        if(
            count($params) != 2
            or !preg_match(Range::REGEX_DATE, $params['start'])
            or !preg_match(Range::REGEX_DATE, $params['end'])
            or !$start = strtotime($params['start'])
            or !$end = strtotime($params['end'])
            or $start > $end
        ) {        
            return new JsonView([
                'success' => false,
            ]);
        }

        Connection::get()->registerObserver($this);

        new Delete($params['start'], $params['end']);
        
        return new JsonView([
            'success' => true,
            'queries' => $this->queries,
        ]);
        
    }

    public function notify($message)
    {
        $this->queries[] = $message;
    }

}
