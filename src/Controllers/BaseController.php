<?php

namespace App\Controllers;

use App\Core\Response;
use App\Core\Helpers;

abstract class BaseController
{
    protected function success(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function error(string $message, int $status = 400): Response
    {
        return Response::error($message, $status);
    }

    protected function getSlug(string $value): string
    {
        return Helpers::getSlug($value);
    }

    protected function sanitizeText(string $value): string
    {
        $sanitized = trim($value);
        $sanitized = strip_tags($sanitized);

        return $sanitized;
    }
}
