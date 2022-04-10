<?php

namespace App;

use PDO;
use Slim\Container;

abstract class Repository
{
    protected PDO $db;

    protected const FMT_MYSQLDT = 'Y-m-d H:i:s';
    
    public function __construct(Container $container)
    {
        $this->db = $container['db'];
    }
}