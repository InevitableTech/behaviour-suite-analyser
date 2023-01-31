<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Config {
    const DEFAULT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    const DEFAULT_NAME = 'bdd-analyser-config.php';

    public function __construct(string $path) {
        $this->path = $this->getValidConfigPath($path);

        $this->data = include $this->path;
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

    private function getValidConfigPath(string $path) {
        if (is_dir($path)) {
            $path .= DIRECTORY_SEPARATOR . self::DEFAULT_NAME;
        }

        if (! is_file($path)) {
            throw new \Exception("No config found at '$path'");
        }

        return $path;
    }
}
