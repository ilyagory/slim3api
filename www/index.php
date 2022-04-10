<?php

use App\Middleware\Auth;
use App\Repository\Contacts as ContactsRepository;
use Slim\App;
use Slim\Container;

require __DIR__ . '/../vendor/autoload.php';
/**
 * Конфиг
 */
$configFile = __DIR__ . '/../app/config.ini';
if (!file_exists($configFile)) throw new Exception('No config');
$cfg = parse_ini_file($configFile, true, INI_SCANNER_TYPED);

// новый экземпляр Slim PHP 3
$app = new App($cfg);

// DI
// далее в контейнер добавляются
// значения и создатели экземпляров
// зависиомостей - при запросе из контейнера,
// новый экэемпляр не будет создаваться
$container = $app->getContainer();

$container['config'] = $cfg;
// Библиотека для работы с БД
// будет бросать исключение при ошибке
$container['db'] = function (Container $container) {
    $conf = $container->get('config')['db'];
    return new PDO(
        sprintf(
            'mysql:host=%s;port=%d;dbname=%s',
            $conf['host'], $conf['port'], $conf['dbname']
        ),
        $conf['user'],
        $conf['password'],
        [PDO::ERRMODE_EXCEPTION]
    );
};

$container[ContactsRepository::class] = fn(Container $c) => new ContactsRepository($c);
$app->add(new Auth($container));

// Навесить обработчики на URL
$routes = require_once __DIR__ . '/../app/routes.php';
$routes($app);

$app->run();