@if ($item['item'] instanceof \HieuLe\LaravelMenu\Menu)
    <li class="menuitem {{$childClass}} {{ app('menu.manager')->isActive($item) ? 'active' : '' }}">
        <a href="#">
            {!! $item['before'] !!}
            {{ $item['item']->getLabel()  }}
            {!! $item['after'] !!}
        </a>
        @include(\HieuLe\LaravelMenu\MenuManager::PLUGIN_NAME.'::sub_menu', ['menu' => $item['item']])
    </li>
@else
    <li class="menuitem item {{ app('menu.manager')->isActive($item) ? 'active' : '' }}">
        <a href="{{ $item['item']['url']  }}">
            {!! $item['before'] !!}
            {{ $item['item']['text']  }}
            {!! $item['after'] !!}
        </a>
    </li>
@endif

