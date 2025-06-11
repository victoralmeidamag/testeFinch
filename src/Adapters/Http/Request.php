<?php

namespace App\Adapters\Http;

class Request
{
    public string $method;
    public string $uri;
    public array $query;
    public array $body;
    public array $server;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->query = $_GET;
        $this->server = $_SERVER;

        $this->body = $_POST;

        if (empty($this->body) && $this->isJsonRequest()) {
            $input = file_get_contents('php://input');
            $json = json_decode($input, true);
            if (is_array($json)) {
                $this->body = $json;
            }
        }
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    private function isJsonRequest(): bool
    {
        return isset($_SERVER['CONTENT_TYPE']) &&
               str_contains($_SERVER['CONTENT_TYPE'], 'application/json');
    }
}
