<?php

namespace Http\Controller\API;

use View\JsonView;
use Http\Request;
use Model\Interval as Range;
use Api\Insert;

class Interval
{

    public function get(Request $r)
    {
     
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

        new Insert($params['start'], $params['end'], $price);
        
        return new JsonView([
            'success' => true,
        ]);
        
    }

}
