<?php


class bla {
    protected $date;
    
    
    public function getDateAsString(){
        return $this->date->format('Y-m-d H:i:s');
    }
}


$blub = new bla();
