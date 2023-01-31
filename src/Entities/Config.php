<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Config {
    const DEFAULT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    const DEFAULT_NAME = 'bdd-analyser-config.php';

    public function __construct(array $config, string $path) {
        $this->path = $path;
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