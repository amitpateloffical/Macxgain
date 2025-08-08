<?php

namespace App\Utility;

use Illuminate\Support\Facades\Facade;

class UtilityFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'utilityclass';
    }
}
