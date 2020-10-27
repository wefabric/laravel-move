<x-move-dropdown align="right" width="48" orientation="right">
    <x-slot name="trigger">
        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
            <img class="h-8 w-8 rounded-full object-cover" src="{{ optional(Auth::user())->profile_photo_url }}" alt="{{ optional(Auth::user())->name }}" />
        </button>
    </x-slot>

        <!-- Account Management -->
        <div class="block px-4 py-2 text-xs text-gray-400">
            {{ __('Accountbeheer') }}
        </div>

        <x-move-dropdown-link href="/user/profile">
            {{ __('Profiel') }}
        </x-move-dropdown-link>

        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
            <x-move-dropdown-link href="/user/api-tokens">
                {{ __('API Tokens') }}
            </x-move-dropdown-link>
        @endif

        <div class="border-t border-gray-100"></div>

        <!-- Team Management -->
        @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
            <div class="block px-4 py-2 text-xs text-gray-400">
                {{ __('Manage Team') }}
            </div>

            <!-- Team Settings -->
            <x-move-dropdown-link href="/teams/{{ optional(Auth::user())->currentTeam->id }}">
                {{ __('Team Settings') }}
            </x-move-dropdown-link>

            @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                <x-move-dropdown-link href="/teams/create">
                    {{ __('Create New Team') }}
                </x-move-dropdown-link>
            @endcan

            <div class="border-t border-gray-100"></div>

            <!-- Team Switcher -->
            <div class="block px-4 py-2 text-xs text-gray-400">
                {{ __('Switch Teams') }}
            </div>

            @foreach (Auth::user()->allTeams() as $team)
                <x-move-switchable-team :team="$team" />
            @endforeach

            <div class="border-t border-gray-100"></div>
        @endif

        <!-- Authentication -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <x-move-dropdown-link href="{{ route('logout') }}"
                                 onclick="event.preventDefault();
                                 this.closest('form').submit();">
                {{ __('Logout') }}
            </x-move-dropdown-link>
        </form>

</x-move-dropdown>