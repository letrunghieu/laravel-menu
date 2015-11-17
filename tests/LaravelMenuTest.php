<?php

namespace HieuLe\LaravelMenuTest;

use HieuLe\Active\ActiveServiceProvider;
use HieuLe\LaravelMenu\Facades\LaravelMenu;
use HieuLe\LaravelMenu\LaravelMenuServiceProvider;
use HieuLe\LaravelMenu\Menu;
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

    public function testAddLinkItem()
    {
        /** @var Menu $menu */
        $menu = \Menu::menu();
        $menu->addLink('a', [], ['id' => 'first']);
        $menu->addLink('b', ['to' => '/foo/bar'], ['before' => 'be', 'after' => 'af']);
        $menu->addLink('c', ['route' => ['foo.bar.view', 'id' => 1]]);
        $menu->addLink('d', ['route' => 'foo.bar'], ['next_to' => 'first']);
        $menu->addLink('e', ['action' => 'Namespace\Controller@indexMethod']);
        $menu->addLink('f', ['action' => ['Namespace\Controller@viewMethod', 1]],
            ['url_def' => ['route' => 'foo.bar.view']]);
        $menu->addLink('g', ['query' => ['a' => 1]], ['next_to' => 'first']);

        $expected = [
            [
                'item'      =>
                    [
                        'text' => 'a',
                        'url'  => '#',
                    ],
                'before'    => '',
                'after'     => '',
                'url_def'   => false,
                'is_active' => null,
                'id'        => 'first',
            ],
            [
                'item'      =>
                    [
                        'text' => 'g',
                        'url'  => '#?a=1',
                    ],
                'before'    => '',
                'after'     => '',
                'url_def'   =>
                    [
                        'query' =>
                            [
                                'a' => 1,
                            ],
                    ],
                'is_active' => null,
                'id'        => null,
            ],
            [
                'item'      =>
                    [
                        'text' => 'd',
                        'url'  => 'http://localhost/foo/bar',
                    ],
                'before'    => '',
                'after'     => '',
                'url_def'   =>
                    [
                        'route'       => 'foo.bar',
                        'route_param' =>
                            [
                            ],
                    ],
                'is_active' => null,
                'id'        => null,
            ],

            [
                'item'      =>
                    [
                        'text' => 'b',
                        'url'  => 'http://localhost/foo/bar',
                    ],
                'before'    => 'be',
                'after'     => 'af',
                'url_def'   => false,
                'is_active' => null,
                'id'        => null,
            ],
            [
                'item'      =>
                    [
                        'text' => 'c',
                        'url'  => 'http://localhost/foo/bar/1/view',
                    ],
                'before'    => '',
                'after'     => '',
                'url_def'   =>
                    [
                        'route'       => 'foo.bar.view',
                        'route_param' =>
                            [
                                'id' => 1,
                            ],
                    ],
                'is_active' => null,
                'id'        => null,
            ],
            [
                'item'      =>
                    [
                        'text' => 'e',
                        'url'  => 'http://localhost/foo/bar',
                    ],
                'before'    => '',
                'after'     => '',
                'url_def'   =>
                    [
                        'action'      => 'Namespace\\Controller@indexMethod',
                        'route_param' =>
                            [
                            ],
                    ],
                'is_active' => null,
                'id'        => null,
            ],
            [
                'item'      =>
                    [
                        'text' => 'f',
                        'url'  => 'http://localhost/foo/bar/1/view',
                    ],
                'before'    => '',
                'after'     => '',
                'url_def'   =>
                    [
                        'route' => 'foo.bar.view',
                    ],
                'is_active' => null,
                'id'        => null,
            ],

        ];

        $this->assertEquals($expected, $menu->getItems());

    }

    public function testAddSubMenu()
    {
        /** @var Menu $menu */
        $menu = \Menu::menu();
        $menu->addSubMenu(\Menu::createMenu());

        $items = $menu->getItems();

        $this->assertInstanceOf(Menu::class, $items[0]['item']);
    }

    public function testRenderMenu()
    {
        /** @var Menu $menu */
        $menu = \Menu::menu();
        $menu->setLabel('Main Navigation')
            ->addLink('a', []);

        /** @var Menu $submenu */
        $submenu = \Menu::createMenu('Multilevel');
        $submenu->addLink('Level 1: single')
            ->addSubMenu(\Menu::createMenu('Level 1: multi')
                ->addLink('Level 2: single', ['route' => ['foo.bar.view', 'id' => 1]])
                ->addSubMenu(\Menu::createMenu('Level 2: multi')
                    ->addLink('Level 3: single', [], ['before' => '<i></i>'])
                    , ['after' => 'fa fa-angle-left']
                )
            );

        $menu->addSubMenu($submenu, ['url_def' => ['route_pattern' => 'foo.*']]);

        $request = Request::create('/foo/bar/1/view', 'GET', ['a' => 'foo', 'b' => ['bar', 'baz']]);
        app(HttpKernelContract::class)->handle($request);

        $this->assertEquals(file_get_contents(__DIR__ . '/stuff/menu.xml'), $menu->render([
            'class'        => 'sidebar-menu',
            'childClass'   => 'treeview',
            'childUlClass' => 'treeview-menu',
        ]));

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