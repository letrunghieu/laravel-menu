<?php

namespace HieuLe\LaravelMenuTest;

use HieuLe\LaravelMenu\Facades\LaravelMenu;
use HieuLe\LaravelMenu\LaravelMenuServiceProvider;
use Orchestra\Testbench\TestCase;

class LaravelMenuTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        app('router')->group(['middleware' => ['dump']], function () {
            app('router')->get('/foo/bar', ['as' => 'foo.bar', 'uses' => 'Namespace\Controller@indexMethod']);
            app('router')->get('/foo/bar/{id}/view',
                ['as' => 'foo.bar.view', 'uses' => 'Namespace\Controller@viewMethod']);
            app('router')->get('/home', [
                'as'   => 'home',
                'uses' => function () {
                },
            ]);
            app('router')->get('/', function () {
            });
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelMenuServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Menu' => LaravelMenu::class,
        ];
    }

}