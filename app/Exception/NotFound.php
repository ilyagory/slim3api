<?php

namespace App\Exception;

use Exception;

class NotFound extends Exception
{
    protected $code = 404;
}