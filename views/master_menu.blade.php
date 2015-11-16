<?php
if (!isset($class)) {
    $class = '';
}
if (!isset($childClass)) {
    $childClass = '';
}
if (!isset($childUlClass)) {
    $childUlClass = '';
}
?>
<ul class="menu {{ $class }}">
    @if ($menu->getLabel())
    <li class="header">
        {{$menu->getLabel()}}
    </li>
    @endif
    @foreach($menu->getItems() as $item)
        @include(\HieuLe\LaravelMenu\MenuManager::PLUGIN_NAME.'::menu_item')
    @endforeach
</ul>