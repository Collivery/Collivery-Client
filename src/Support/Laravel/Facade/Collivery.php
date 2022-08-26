<?php

namespace Mds\Collivery\Support\Laravel\Facade;

use Illuminate\Support\Facades\Facade;

class Collivery extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'collivery';
    }
}
