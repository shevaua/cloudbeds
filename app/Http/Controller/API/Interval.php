<?php

namespace Http\Controller\API;

use View\JsonView;
use Http\Request;
use Model\Interval as Range;

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

}
