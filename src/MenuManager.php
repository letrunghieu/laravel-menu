<?php

namespace HieuLe\LaravelMenu;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory;

class MenuManager
{
    const PLUGIN_NAME = 'menu_manager';
    /**
     * @var Factory
     */
    protected $view;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var array
     */
    protected $menus = [];

    /**
     * MenuManager constructor.
     *
     * @param Factory      $view
     * @param UrlGenerator $url
     */
    public function __construct(Factory $view, UrlGenerator $url)
    {
        $this->view = $view;
        $this->url = $url;
    }

    /**
     * Get menu with a specific name
     *
     * @param string $name
     *
     * @return Menu
     */
    public function menu($name = 'default')
    {
        if (!isset($this->menus[$name])) {
            $this->menus[$name] = new Menu($this);
        }

        return $this->menus[$name];
    }

    /**
     * Create new menu from this manager
     *
     * @param string $label
     *
     * @return Menu
     */
    public function createMenu($label = '')
    {
        return new Menu($this, $label);
    }

    /**
     * Get the view factory
     *
     * @return Factory
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return UrlGenerator
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getMenus()
    {
        return $this->menus;
    }

    /**
     * Check if a menu item is currently active or not
     *
     * @param $menuItem
     *
     * @return bool
     */
    public function isActive($menuItem)
    {
        // If the `is_active` option is a callable object, return the result of this callable object with the current
        // item passed as the only argument
        if (is_callable(array_get($menuItem, 'is_active'))) {
            return (bool)call_user_func($menuItem['is_active'], $menuItem);
        }

        // If the `url_def` option is defined, use the `hieu-le/active` helper functions to calculate the result from
        // the keys and values of this array.
        if ($urlDef = array_get($menuItem, 'url_def')) {
            $result = true;
            foreach ($urlDef as $method => $value) {
                if (!is_array($value)) {
                    $result = $result && call_user_func("if_" . $method, [$value]);
                } else {
                    foreach ($value as $k => $v) {
                        $result = $result && call_user_func("if_" . $method, $k, $v);
                    }
                }
            }

            return $result;
        }

        // Default: not active
        return false;
    }

}