<?php

namespace HieuLe\LaravelMenu;

class Menu
{

    /**
     * @var MenuManager
     */
    protected $manager;

    protected $items = [];

    protected $label;

    protected $id;

    /**
     * Menu constructor.
     *
     * @param MenuManager $manager
     * @param string      $label
     * @param string      $id
     */
    public function __construct(MenuManager $manager, $label = '', $id = '')
    {
        $this->manager = $manager;
        $this->label = $label;
        $this->id = $id;
    }

    /**
     * Add new sub menu
     *
     * @param Menu  $menu
     * @param array $options
     *
     * @return Menu
     */
    public function addSubMenu(Menu $menu, $options = [])
    {
        $this->addItem($menu, $options);

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add new link
     *
     * @param string $text
     * @param array  $url
     * @param array  $options
     *
     * @return Menu
     */
    public function addLink($text, array $url, $options = [])
    {
        $linkUrl = '#';
        $defaultIsActive = false;
        if ($to = array_get($url, 'to')) {
            $linkUrl = $this->manager->getUrl()->to($url);
        } elseif ($route = array_get($url, 'route')) {
            $routeName = array_shift($route);
            $routeParams = $route;

            $linkUrl = $this->manager->getUrl()->route($routeName, $routeParams);
            $defaultIsActive = [
                'route'       => $routeName,
                'route_param' => $routeParams,
            ];
        } elseif ($action = array_get($url, 'action')) {
            $actionName = array_shift($action);

            $linkUrl = $this->manager->getUrl()->action($actionName, $action);
            $defaultIsActive = [
                'action'      => $actionName,
                'route_param' => $action,
            ];
        }
        if ($query = array_get($url, 'query')) {
            $linkUrl .= '?' . http_build_query($query);
            $defaultIsActive['query'] = $query;
        }

        if (!isset($options['url_def'])) {
            $options['url_def'] = $defaultIsActive;
        }

        $this->addItem([
            'text' => $text,
            'url'  => $linkUrl,
        ], $options);

        return $this;
    }

    public function render(array $options = [])
    {
        $view = array_get($options, 'view', MenuManager::PLUGIN_NAME . '::master_menu');
        $data = array_get($options, 'data', []);

        return $this->manager->getView()->make($view, ['menu' => $this], $data)
            ->render();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Add new item to this menu
     *
     * @param mixed $item
     * @param array $options
     *
     * @return Menu
     */
    protected function addItem($item, $options = [])
    {
        $nextTo = array_get($options, 'next_to');
        $position = false;
        foreach ($this->items as $index => $m) {
            if ($m instanceof Menu && $nextTo == $m->getId()) {
                $position = $index;
            }
        }
        $newMenu = [
            'item'      => $item,
            'before'    => array_get($options, 'before', ''),
            'after'     => array_get($options, 'after', ''),
            'url_def'   => array_get($options, 'url_def'),
            'is_active' => array_get($options, 'is_active'),
        ];
        if ($position) {
            array_splice($this->items, $position, 0, [$newMenu]);
        } else {
            $this->items[] = $newMenu;
        }

        return $this;
    }

}