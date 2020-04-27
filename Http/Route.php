<?php

namespace ludk\Http;

class Route {
    public $name;
    public $path;
    public $controller;
    public $function;

    public function generate(array $parameters = []) {
        $paramsStr = http_build_query($parameters);
        return $this->path . (empty($paramsStr) ? '' :   '?' . $paramsStr) ;
    }
} 