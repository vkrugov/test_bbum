<?php

declare(strict_types=1);

namespace App\Router;

use App\Container;
use App\Http\Request;
use App\Http\Response;

class Router
{
    /** @var array<string, array<string, array{class-string, string}>> */
    private array $routes = [];

    public function __construct(private readonly Container $container) {}

    /**
     * @param array{class-string, string} $handler
     */
    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * @param array{class-string, string} $handler
     */
    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method;
        $uri = $request->uri;

        if (isset($this->routes[$method][$uri])) {
            return $this->call($this->routes[$method][$uri], $request);
        }

        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $params = $this->matchPattern($pattern, $uri);
            if ($params !== null) {
                return $this->call($handler, $request, $params);
            }
        }

        return (new Response())->json(['error' => 'Not Found'], 404);
    }

    /**
     * @param array{class-string, string} $handler
     * @param array<string, string>       $params
     */
    private function call(array $handler, Request $request, array $params = []): Response
    {
        if ($params !== []) {
            $request = $request->withRouteParams($params);
        }

        $controller = $this->container->make($handler[0]);
        $method = $handler[1];

        return $controller->$method($request);
    }

    /**
     * @return array<string, string>|null
     */
    private function matchPattern(string $pattern, string $uri): ?array
    {
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $uri, $matches)) {
            return null;
        }

        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }
}
