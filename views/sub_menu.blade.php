<ul class="menu submenu {{ $childUlClass }}">
    @foreach($menu->getItems() as $item)
        @include(\HieuLe\LaravelMenu\MenuManager::PLUGIN_NAME.'::menu_item')
    @endforeach
</ul>