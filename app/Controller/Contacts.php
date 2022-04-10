<?php

namespace App\Controller;

use App\Exception\NotFound;
use App\Exception\WrongInput;
use App\Repository\Contacts as ContactsRepository;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Contacts
{
    private ContactsRepository $r;

    public function __construct(Container $container)
    {
        $this->r = $container[ContactsRepository::class];
    }

    function create(Request $request, Response $response)
    {
        $inserted = $this->r->createForSource(
            $request->getParsedBodyParam('source_id'),
            $request->getParsedBodyParam('items'),
        );

        return $response->withJson(['created' => $inserted]);
    }

    function search(Request $request, Response $response)
    {
        try {
            $contacts = $this->r->getByPhone(
                $request->getQueryParam('phone')
            );
        } catch (WrongInput | NotFound $exception) {
            return $response->withStatus($exception->getCode());
        }
        return $response->withJson($contacts, null, JSON_UNESCAPED_UNICODE);
    }
}