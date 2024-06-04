<?php

namespace Hexlet\Code;

class UrlValidator
{
    public function __construct()
    {
    }

    public function validate(string $url): array
    {
        $errors = [];

        if (empty($url)) {
            $errors[] = 'URL не должен быть пустым';
        }

        if (!$url) {
            $errors[] = 'Некорректный URL';
        }

        if (strlen($url) > 255) {
            $errors[] = 'Слишком длинный URL';
        }

        return $errors;
    }

    public function normalize(string $url): string|false
    {
        $parsedUrl = parse_url($url);
        if ($parsedUrl === false || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return false;
        }
        $normalizedUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        return $normalizedUrl;
    }
}
