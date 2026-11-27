<?php

declare(strict_types=1);

namespace Tests\Feature\Docs;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class PostmanCollectionTest extends TestCase
{
    public function test_collection_covers_every_registered_api_method_and_uri(): void
    {
        $path = base_path('../docs/postman/DirectChannel.postman_collection.json');

        if (! is_file($path)) {
            $this->markTestSkipped('Collection indisponível: monte a raiz em /repo e execute com -w /repo/backend.');
        }

        $collection = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.getpostman.com/json/collection/v2.1.0/collection.json', $collection['info']['schema']);
        $requests = $this->requestSignatures($collection['item']);
        $this->assertNotEmpty($requests);
        $missing = [];

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/')) {
                continue;
            }

            foreach ($route->methods() as $method) {
                $signature = $method.' '.$this->normalizeUri($route->uri());
                if (! in_array($signature, $requests, true)) {
                    $missing[] = $method.' '.$route->uri();
                }
            }
        }

        $this->assertSame([], $missing, 'Rotas ausentes na collection: '.implode(', ', $missing));
    }

    /** @return list<string> */
    private function requestSignatures(array $items): array
    {
        $signatures = [];

        foreach ($items as $item) {
            if (isset($item['item'])) {
                array_push($signatures, ...$this->requestSignatures($item['item']));

                continue;
            }

            $request = $item['request'];
            $url = $request['url'];
            if (is_array($url)) {
                $url = $url['raw'] ?? '{{baseUrl}}/'.implode('/', $url['path']);
            }

            $url = preg_replace('~^\{\{baseUrl\}\}/?~', 'api/', $url);
            if (preg_match('~^https?://~', $url) === 1) {
                $url = parse_url($url, PHP_URL_PATH);
            }

            $signatures[] = strtoupper($request['method']).' '.$this->normalizeUri($url);
        }

        return $signatures;
    }

    private function normalizeUri(string $uri): string
    {
        $uri = explode('?', $uri, 2)[0];
        $uri = preg_replace('/\{\{[^{}]+\}\}|\{[^{}]+\}|:[A-Za-z_][A-Za-z0-9_]*/', '{}', $uri);

        return trim($uri, '/');
    }
}
