<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <img src="/logo.svg" alt="{{ config('app.name') }}" class="h-8" />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <img src="/logo.svg" alt="{{ config('app.name') }}" class=" px-5 pt-4" />

            {{-- MENU --}}
            <x-menu activate-by-route>

                {{-- User --}}
                @if($user = auth()->user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <livewire:auth.logout />
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                @if(auth()->check())
                    <x-menu-item title="Part Search" icon="o-magnifying-glass" link="{{ route('parts.search') }}" />
                    <x-menu-item title="Search History" icon="o-clock" link="{{ route('parts.search-history') }}" />
                    
                    <x-menu-separator />
                    
                    <x-menu-item title="Organisations" icon="o-user-group" link="{{ route('teams.index') }}" />
                    
                    <div class="px-4 py-2">
                        <div class="text-xs font-semibold text-base-content/50 mb-2">CURRENT ORGANISATION</div>
                        <livewire:teams.switcher />
                    </div>
                @endif
                
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>
</html>
