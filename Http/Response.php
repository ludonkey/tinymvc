<?php

namespace ludk\Http;

class Response
{

    protected $headers;

    protected $content;

    protected $statusCode;

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->setHeaders($headers);
        $this->setContent($content);
        $this->setStatusCode($statusCode);
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
        $this->sendStatusCode();
    }

    private function sendHeaders()
    {
        foreach ($this->headers as $oneHeaderName => $oneHeaderValue) {
            header("$oneHeaderName: $oneHeaderValue");
        }
    }

    private function sendContent()
    {
        echo $this->content;
    }

    private function sendStatusCode()
    {
        http_response_code($this->statusCode);
    }
}
