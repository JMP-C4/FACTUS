<a
    href="{{ $item['href'] }}"
    data-active="{{ $item['active'] ? 'true' : 'false' }}"
    class="brutal-sidebar-item"
    :class="sidebarCollapsed ? 'justify-center px-2' : ''"
    :title="sidebarCollapsed ? @js($item['label']) : null"
    @if ($item['active']) aria-current="page" @endif
>
    <span class="flex h-7 w-7 shrink-0 items-center justify-center border-[2px] border-current">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="{{ $item['icon'] }}" />
        </svg>
    </span>

    <span x-show="!sidebarCollapsed" x-transition.opacity.duration.150ms class="truncate">{{ $item['label'] }}</span>
</a>
