<?php

use App\Controller\Contacts;
use App\Middleware\Validator;
use Slim\App;

return function (App $app) {
    $app->group('/contacts', function (App $app) {

        // Поиск контактов
        // GET /contacts?phone=xxxxx{10,}
        $app->get('', Contacts::class . ':search');

        // Создать контакты
        // POST /contacts
        $app->post('', Contacts::class . ':create')
            ->add(new Validator)
            ->setName('contact.create');
    });
};