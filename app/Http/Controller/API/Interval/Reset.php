<?php

namespace Http\Controller\API\Interval;

use View\JsonView;
use Http\Request;
use Model\Interval as Range;
use Pattern\Observer\Observer;
use DB\Connection;

class Reset implements Observer
{

    private $queries;

    public function delete(Request $r)
    {
     
        Connection::get()->registerObserver($this);

        Range::query()
            ->truncate();  

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
