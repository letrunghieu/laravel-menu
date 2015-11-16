# Laravel Menu
Help to build menus easier in Laravel applications (currently support Laravel 5 only)

[![Build Status](https://travis-ci.org/letrunghieu/laravel-menu.png?branch=master)](https://travis-ci.org/letrunghieu/laravel-menu)
[![Latest Stable Version](https://poser.pugx.org/hieu-le/laravel-menu/v/stable.svg)](https://packagist.org/packages/hieu-le/laravel-menu)
[![Code Climate](https://codeclimate.com/github/letrunghieu/laravel-menu/badges/gpa.svg)](https://codeclimate.com/github/letrunghieu/laravel-menu)
[![Test Coverage](https://codeclimate.com/github/letrunghieu/laravel-menu/badges/coverage.svg)](https://codeclimate.com/github/letrunghieu/laravel-menu/coverage)
[![Total Downloads](https://poser.pugx.org/hieu-le/laravel-menu/downloads.svg)](https://packagist.org/packages/hieu-le/laravel-menu)
[![License](https://poser.pugx.org/hieu-le/laravel-menu/license.svg)](https://packagist.org/packages/hieu-le/laravel-menu)

## Instalation

First, add this package to your project dependencies:

    $> composer require "hieu-le/laravel-menu"
    
After Composer updated your vendors code, add the package service provider to your `providers` array in the `config/app.php` file:

    HieuLe\LaravelMenu\LaravelMenuServiceProvider::class,
    
Now, you can access to the menu manager via `app('menu.manager')` object. If you want to use static methods via alias classes, register the package facade to your `aliases` array in the `config/app.php` file:

    'Menu' => HieuLe\LaravelMenu\Facades\LaravelMenu::class,
    
## Usage

You can create as many menus as you want in your application. A menu comes with a unique name which can be anything. You can get the menu instance via that name by `menu` method:

    <?php
    $menu = app('menu.manager')->menu($menuName); // the default value of $menuName is "default"
    
    # with the registered alias, you can also do this
    $menu = Menu::menu($menuName);
    ?>

If there is no menu with the name you specified, a new one will be initiated and returned to you. Some menu comes with an optional *header* (or *label*), which can be set by the `setLabel` method **on the menu instance**.

    <?php
    $menu->setLabel('Sidebar navigation');
    ?>
    
The method return the menu instance itself to enable *method chaining*. Many menus can have the same label or don't have any label. Remember that you cannot retrieve a menu from menu manager via its label, you can only use its **name**.

I suggest two places to define your menus:

* a route middleware
* a service provider

### Add links to menu

API: `$menu->addLink($text, array $url = [], $options = [])`

* `$text` is the anchor text
* `$url` is an **associative** array, which is used to generate the `href` attribute of the link. More details is explained later.
* `$option` array is described later.

If `$url` is an empty array, the `href` will be an hash character (`#`).

To assign an URL to the the link, pass a string to the `to` element of the `$url` array. Example: an item created with `$url` equals to `['to' => '/foo/bar']` has the `href` leading to `http://your-domain.com/foo/bar`, an item created with `$url` equals to `['to' => 'http://other-site.com/foo/bar']` has the `href` leading to `http://other-site.com/foo/bar`.

To assign an internal URL by your named route, pass the route name as string to the `route` element of the `$url` array. If the route has parameters, pass an array with the first element is the route name, the others are route parameters **and the appropriate parameter name as key**. For example:

    <?php
    // routes.php
    Route::get('/posts/{post_id}/comments/{comment_id}', ['as' => 'posts.comments.detail'];
    
    // your menu definitions
    $menu->addLink($text, ['route' => ['posts.comments.detail', 'post_id' => 1, 'comment_id' => 99]]);
    
    // the output link is: /posts/1/comments/99
    ?>
    
To assign an internal URL by the action name, pass the action as string to the `action` element of the `$url` array. If the action has parameters, pass an array with the first element is the action, the others are the action parameter. For example:

    <?php
    // routes.php
    Route::get('/posts/{post_id}/comments/{comment_id}', ['use' => 'PostController@viewComment'];
    
    // your menu definitions
    $menu->addLink($text, ['action' => ['App\Http\Controllers\PostController@viewComment', 1, 99]]);
    
    // the output link is: /posts/1/comments/99
    ?>
    
If your desired link contains query string, you can pass an array to the `query` element of the `$url` array. I use [`http_build_query`](http://php.net/manual/en/function.http-build-query.php) to create the query string and append it to the end of the URL created by the above options.

### Add sub menu

API `$menu->addSubMenu($menu, $options = [])`

* `$menu` is a menu instance. You can use `Menu::menu($name)` to create a reusable sub men with a name as descripted above. There is another method to create a menu instance with no name: `Menu::createMenu($label = '')`. You can create nested menu system with this package.
* `$options` array is the same as `addLink` function. This array is discussed more in the next section.

### Options when create new menu item.

When creating new menu item (a link or a sub menu), you can pass an optional array as the `$options` parameter. The array can contain these elements:

* `before`: the content before the link content
* `after`: the content after the link content
* `is_active`: the function to detect whether this link is currently active or not. If it is missing, I do it for you by using my Laravel Active package underground.
* `id`: a local id of the item.
* `next_to`: the local id of the item that you want the new menu item inserted **right after**. If the local id is not found inside the parent menu, it is ignored and the new item is append to the end of the parent menu link normal.
* `url_def`: the URL definition, it is usage with the `isActive` method of the manager to determine whether the current item is active.

## Render the menu

I use Laravel built in view system to render the menu, so that you can easily customize the output of a menu. To get the HTML of a menu, call `render` method from the **menu instance**.

API: `$menu->render($data = [], $view = '')`

* `$data`: the additional data to pass to the view
* `$view`: the view name to render to menu, it can including other view as normal Laravel view. If it is empty, the default value is use: `menu_manager::master_menu`.

If you use the default built in view (the `$view` parameter is empty), there are 3 elements used in the `$data` array, all of them are strings:

* `class`: the additional classes of the outermost UL element which wraps the whole menu
* `childClass`: the additional classes of the LI element that contains the sub menu
* `childUlClass`: the additional classes of the UL element wrap the *child menu*, this element is inside a LI element which is applied the `childClass` classes above.

## Detect whether current menu item is active

In views, you usually want to add some *active* class to the menu items that currently selected. This package provides the `isActive` method from the menu manager to do that. You give a menu item and get a boolean value which tell you whether this menu item is currently active or not.

    <?php
    foreach($menu->getItems() as $menuItem) {
        $class = Menu::isActive($menuItem) ? "active" : "";
        echo "<li class='{$class}'></li>
    }
    ?>
    
The `isActive` method check the `is_active` element of the current menu item first, if it is callable, the method return the result from that to you.

If there is no `is_active` element, the method use the `url_def` element as written above. The `url_def` element describe URLs that can make the current menu item active. Its value is an associative array:

* `route`: the route name of URL of the active menu items
* `route_param`: the route parameter of the active menu items
* `route_pattern`: the pattern of the route name of the active menu items
* `action`: the action of the active menu items
* `uri`: the URI of the active menu items
* `uri_pattern`: the pattern of the URI of the active menu items
* `query`: the query string variables of the active menu items

See the documentation of [Laravel Active](https://github.com/letrunghieu/active) package to get more detail.

## Example with the default views

    // routes.php
    Route::get('/a', ['as' => 'links.a']);
    Route::get('/a/new', ['as' => 'links.a.create']);
    Route::get('/a/list', ['as' => 'links.a.list']);
    Route::get('/a/{id}', ['as' => 'links.a.detail']);
    Route::get('/b', ['as' => 'links.b']);
    Route::get('/c', ['as' => 'links.c']);
    
    
    // AppServiceProvider.php
    public function boot() {
        $subMenu = app('menu.manager')->createMenu('Multilevel link A')
            ->addLink('Create A', ['route' => 'links.a.create'])
            ->addLink('Listing A', ['route' => ['links.a.list']]);
    
        $menu = app('menu.manager')->menu('sidebar')
            ->setLabel('Sidebar Navigation')
            ->addSubMenu($subMenu, ['id' => 'link-a', 'url_def' => ['route_pattern' => 'link.a.*']])
            ->addLink('Link C', ['route' => 'links.c']);
            
        // some thing else ...
        
        app('menu.manager')->menu('sidebar')
            // we want to insert link B before link C
            ->addLink('Link B', ['route' => 'links.b'], 'next_to' => 'link-a']);
    }
    
    // Some where in your view
    {!! app('menu.manager')->menu('sidebar')->render() !!}
    
    
If you are visiting the URI `/a/new`, the HTML output will be:

    <ul class="menu">
        <li class="header">
            Sidebar Navigation
        </li>
        <li class="menuitem active">
            <a href="#">
                Multilevel link A
            </a>
            <ul class="menu submenu">
                <li class="menuitem item">
                    <a href="/a/new">Create A</a>
                </li>
                <li class="menuitem item">
                    <a href="/a/list">Listing A</a>
                </li>
            </ul>
        </li>
        <li class="menuitem item">
            <a href="/b">Link B</a>
        </li>
        <li class="menuitem item">
            <a href="/c">Link C</a>
        </li>
    </ul>

