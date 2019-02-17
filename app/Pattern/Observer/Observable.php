<?php

namespace Pattern\Observer;

abstract class Observable
{

    /**
     * List of observers
     * @var array 
     */
    protected $observers = [];

    /**
     * Register new observer
     * @return void
     */
    public function registerObserver(Observer $ob)
    {
        $this->observers[] = $ob;
    }

    /**
     * Notify all registered observers with message
     * @return void
     */
    public function notifyObservers($message)
    {

        foreach($this->observers as $ob)
        {
            $ob->notify($message);
        }

    }

}
