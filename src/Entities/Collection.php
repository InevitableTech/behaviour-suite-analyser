<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Collection {
    protected $items = [];

    public function add($item) {
        $this->items[] = $item;
    }

    public function remove($item) {
        if (($key = array_search($item, $this->items)) !== false) {
            unset($this->items[$key]);
        }
    }

    public function getItems() {
        return $this->items;
    }
}