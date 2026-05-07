<?php

namespace App\Core;

class Response
{
    private int $status;
    private array $headers;
    private array $body;

    public function __construct(int $status = 200, array $body = [], array $headers = [])
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        header('Content-Type: application/json');
        echo json_encode($this->body);
        exit;
    }

    static function error(string $message, int $code = 404): self
    {
        return new self($code, ['error' => $message]);
    }

    static function json(array $body, int $code = 200): self
    {
        return new self($code, $body);
    }
}
