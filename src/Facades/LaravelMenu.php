<?php

namespace HieuLe\LaravelMenu\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelMenu extends  Facade
{
    protected static function getFacadeAccessor()
    {
        return 'menu.manager';
    }
}