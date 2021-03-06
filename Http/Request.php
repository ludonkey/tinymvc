<?php

namespace ludk\Http;

use ludk\Http\Session;
use ludk\Http\ParameterBag;

class Request
{
    // GET or POST or HEAD ...
    public $method;

    // http://localhost/web/index.php?var=1 contains '/web'
    public $basePath;

    // incoming headers (User-Agent, etc)
    public ParameterBag $headers;

    // $_POST parameters
    public ParameterBag $request;

    // $_GET parameters
    public ParameterBag $query;

    // $_SERVER infos
    public ParameterBag $server;

    // $_COOKIE
    public ParameterBag $cookies;

    private Session $session;

    public function __construct(array $query = [], array $request = [], array $server = [], array $cookies = [])
    {
        $this->query = new ParameterBag($query);
        $this->request = new ParameterBag($request);
        $this->server = new ParameterBag($server);
        $this->cookies = new ParameterBag($cookies);
        $this->headers = new ParameterBag(getallheaders());
        $this->basePath = parse_url($server['REQUEST_URI'], PHP_URL_PATH);
        $this->method = $server['REQUEST_METHOD'];
    }

    public function getSession()
    {
        if (!isset($this->session)) {
            $this->session = new Session();
        }
        if (!$this->session->isStarted()) {
            $this->session->start();
        }
        return $this->session;
    }

    public function hasSession()
    {
        $localSession = new Session();
        return $localSession->isStarted();
    }
}
