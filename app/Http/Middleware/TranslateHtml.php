<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslateHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->getLocale() !== 'bn') {
            return $response;
        }

        if ($request->routeIs('landing') || $request->is('/')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '') {
            return $response;
        }

        $map = $this->dictionary();
        if ($map === []) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        $trimmed = ltrim($html);
        $isJson = str_contains($contentType, 'json')
            || ($contentType === '' && ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')));

        if ($isJson) {
            $response->setContent($this->translateJson($html, $map));

            return $response;
        }

        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return $response;
        }

        if (! str_contains($html, '<')) {
            return $response;
        }

        $response->setContent($this->translate($html, $map));

        return $response;
    }

    private function dictionary(): array
    {
        $path = lang_path('bn.json');
        if (! is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    private function translate(string $html, array $map): string
    {
        $parts = preg_split(
            '/(<script\b[^>]*>.*?<\/script>|<style\b[^>]*>.*?<\/style>)/is',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if ($parts === false) {
            return $html;
        }

        uksort($map, function (string $a, string $b): int {
            return strlen($b) <=> strlen($a);
        });

        foreach ($parts as $index => $part) {
            if (preg_match('/^<style\b/i', $part)) {
                continue;
            }

            if (preg_match('/^<script\b/i', $part)) {
                continue;
            }

            $parts[$index] = $this->translateFragment($part, $map);
        }

        return implode('', $parts);
    }

    private function translateFragment(string $html, array $map): string
    {
        $html = preg_replace_callback('/<title>(.*?)<\/title>/is', function (array $match) use ($map): string {
            $pieces = preg_split('/\s+-\s+/', $match[1]) ?: [$match[1]];
            $pieces = array_map(function (string $part) use ($map): string {
                $text = preg_replace('/\s+/u', ' ', trim($part)) ?? trim($part);

                return (isset($map[$text]) && is_string($map[$text]) && $map[$text] !== '' && ! str_contains($map[$text], '<')) ? $map[$text] : $part;
            }, $pieces);

            return '<title>'.implode(' - ', $pieces).'</title>';
        }, $html) ?? $html;

        $html = preg_replace_callback('/>([^<]*)</u', function (array $match) use ($map): string {
            $raw = $match[1];
            if ($raw === '' || str_contains($raw, '{') || str_contains($raw, '}') || str_contains($raw, ';')) {
                return $match[0];
            }

            $text = preg_replace('/\s+/u', ' ', trim(html_entity_decode($raw, ENT_QUOTES, 'UTF-8'))) ?? '';
            if ($text === '' || ! isset($map[$text]) || ! is_string($map[$text]) || $map[$text] === '' || str_contains($map[$text], '<')) {
                return $match[0];
            }

            $leading = preg_match('/^\s*/', $raw, $lead) ? $lead[0] : '';
            $trailing = preg_match('/\s*$/', $raw, $trail) ? $trail[0] : '';

            return '>'.$leading.$map[$text].$trailing.'<';
        }, $html) ?? $html;

        foreach (['placeholder', 'title', 'aria-label'] as $attribute) {
            $html = preg_replace_callback(
                '/\b'.preg_quote($attribute, '/').'="([^"]*)"/u',
                function (array $match) use ($map, $attribute): string {
                    $text = preg_replace('/\s+/u', ' ', trim(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8'))) ?? '';
                    if ($text === '' || ! isset($map[$text]) || ! is_string($map[$text]) || $map[$text] === '' || str_contains($map[$text], '<')) {
                        return $match[0];
                    }

                    return $attribute.'="'.$map[$text].'"';
                },
                $html
            ) ?? $html;
        }

        return $html;
    }

    private function translateJson(string $json, array $map): string
    {
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return $json;
        }

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $encoded = json_encode($this->walkJson($data, $map), $flags);

        return is_string($encoded) ? $encoded : $json;
    }

    private function walkJson(mixed $value, array $map): mixed
    {
        if (is_string($value)) {
            if (str_contains($value, '<') && str_contains($value, '>')) {
                return $this->translateFragment($value, $map);
            }

            return $value;
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            if (is_string($key) && is_string($item) && in_array($key, ['message', 'success', 'error', 'warning'], true) && isset($map[$item]) && is_string($map[$item]) && $map[$item] !== '') {
                $value[$key] = $map[$item];

                continue;
            }

            $value[$key] = $this->walkJson($item, $map);
        }

        return $value;
    }
}
