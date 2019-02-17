<?php

namespace Http\Controller\API\Interval;

use View\JsonView;
use Http\Request;
use Model\Interval as Range;

class Reset
{

    public function delete(Request $r)
    {
     
        Range::query()
            ->truncate();  

        return new JsonView([
            'success' => true,
        ]);

    }

}
