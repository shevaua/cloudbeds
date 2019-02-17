<?php

namespace Pattern\Observer;

abstract class Observable
{

    protected $observers = [];

    public function registerObserver(Observer $ob)
    {
        $this->observers[] = $ob;
    }

    public function notifyObservers($message)
    {

        foreach($this->observers as $ob)
        {
            $ob->notify($message);
        }

    }

}
