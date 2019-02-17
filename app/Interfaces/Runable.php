<?php 

namespace Interfaces;

interface Runable
{

    /**
     * Run the command with params
     * @param array $params
     * @return void
     */
    public function run(array $params);

}
