<?php

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Auth
{
    protected string $apikey;

    public function __construct(ContainerInterface $container)
    {
        $this->apikey = $container->get('config')['app']['apikey'];
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $apikey = $request->getHeaderLine('X-Apikey');
        return ($apikey === $this->apikey) ? $next($request, $response) : $response->withStatus(403);
    }
}