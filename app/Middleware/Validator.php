<?php

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;
use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;

class Validator
{
    protected array $validators = [];

    /**
     * @param $id
     * @return mixed|SchemaContract
     * @throws InvalidValue
     * @throws Exception
     */
    protected function getValidator(string $id)
    {
        if (isset($this->validators[$id]))
            return $this->validators[$id];

        $pth = realpath(__DIR__ . "/../../schema/$id.json");
        if (!file_exists($pth))
            return null;

        $this->validators[$id] = Schema::import("file://$pth");

        return $this->validators[$id];
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $id = $request->getAttribute('route')->getName();
        $validator = $this->getValidator($id);

        if ($validator !== null) {
            $body = $request->getBody()->getContents();
            try {
                $this->getValidator($id)->in(json_decode($body));
            } catch (InvalidValue $exception) {
                return $response->withStatus(400);
            }
        }

        return $next($request, $response);
    }
}