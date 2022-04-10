<?php

namespace App\Exception;

class WrongInput extends \Exception
{
    protected $code = 400;
}