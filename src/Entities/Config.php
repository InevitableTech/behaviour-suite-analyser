<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Config {
    public function __construct(array $config) {
        $this->data = $config;
    }

    public function get($key, $default = null) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    public function print() {
        print_r($this->data);
    }
}