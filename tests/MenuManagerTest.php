<?php

namespace HieuLe\LaravelMenuTest;

use HieuLe\LaravelMenu\MenuManager;
use Illuminate\Routing\UrlGenerator;
use Illuminate\View\Factory;

class MenuManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MenuManager
     */
    private $_manager;

    public function setUp()
    {
        parent::setUp();

        $this->_manager = new MenuManager($this->getMock(Factory::class, [], [], '', false),
            $this->getMock(UrlGenerator::class, [], [], '', false));
    }

    /**
     * @param array $item
     * @param       $result
     *
     * @dataProvider provideMenuItemCallableData
     */
    public function testIsMenuItemActiveWithCallable(array $item, $result)
    {
        $this->assertEquals($result, $this->_manager->isActive($item));
    }

    public function testIsMenuItemActiveReturnFalseByDefault()
    {
        $this->assertFalse($this->_manager->isActive([]));
    }

    public function testCreateNewMenu() {
        $this->_manager->menu()
            ->setLabel('Default menu');

        $this->assertCount(1, $this->_manager->getMenus());

        $this->assertEquals('Default menu', $this->_manager->menu('default')->getLabel());
    }

    public function provideMenuItemCallableData()
    {
        return [
            'return true'  => [
                [
                    'is_active' => function () {
                        return true;
                    },
                ],
                true,
            ],
            'return false' => [
                [
                    'is_active' => function () {
                        return false;
                    },
                ],
                false,
            ],
        ];
    }
}