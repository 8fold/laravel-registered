<?php

namespace Eightfold\Registered\Authentication;

use Eightfold\Registered\ControllerBase;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

class Logout extends ControllerBase
{
    use AuthenticatesUsers;
}
