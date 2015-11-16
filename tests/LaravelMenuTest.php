<?php

namespace HieuLe\LaravelMenuTest;

use HieuLe\Active\ActiveServiceProvider;
use HieuLe\LaravelMenu\Facades\LaravelMenu;
use HieuLe\LaravelMenu\LaravelMenuServiceProvider;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Http\Request;
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

    /**
     * @param array $item
     * @param       $result
     *
     * @dataProvider provideMenuItemUrlDefData
     */
    public function testIsMenuItemActiveWithUrlDef(array $item, $result)
    {
        $request = Request::create('/foo/bar/1/view', 'GET', ['a' => 'foo', 'b' => ['bar', 'baz']]);
        app(HttpKernelContract::class)->handle($request);
        $this->assertEquals($result, \Menu::isActive($item));
    }

    public function provideMenuItemUrlDefData()
    {
        return [
            'one single value - return true'       => [
                [
                    'url_def' => [
                        'action' => 'Namespace\Controller@viewMethod',
                    ],
                ],
                true,
            ],
            'one single value - return false'      => [
                [
                    'url_def' => [
                        'route' => 'foo.bar',
                    ],
                ],
                false,
            ],
            'multiple single value - return true'  => [
                [
                    'url_def' => [
                        'action' => 'Namespace\Controller@viewMethod',
                        'route'  => 'foo.bar.view',
                    ],
                ],
                true,
            ],
            'multiple single value - return false' => [
                [
                    'url_def' => [
                        'action' => 'Namespace\Controller@viewMethod',
                        'route'  => 'foo.bar',
                    ],
                ],
                false,
            ],
            'single tuple value - return true'     => [
                [
                    'url_def' => [
                        'route_param' => [
                            'id' => 1,
                        ],
                    ],
                ],
                true,
            ],
            'multiple tuple value - return true'   => [
                [
                    'url_def' => [
                        'action' => 'Namespace\Controller@viewMethod',
                        'query'  => ['a' => 'foo', 'b' => ['bar', 'baz']],
                    ],
                ],
                true,
            ],
        ];
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelMenuServiceProvider::class,
            ActiveServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Menu' => LaravelMenu::class,
        ];
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', Http\Kernel::class);
    }

}