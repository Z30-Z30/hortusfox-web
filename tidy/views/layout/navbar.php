<?php
    /* Tidy UI: sidebar navigation (desktop), top bar + drawer + bottom bar (mobile). */
    $tidy_locations_filled = [];
    $tidy_locations_empty = [];

    foreach (LocationsModel::getAll() as $tidy_loc) {
        $tidy_count = PlantsModel::getPlantCount($tidy_loc->get('id'));
        if ($tidy_count > 0) {
            $tidy_locations_filled[] = [$tidy_loc, $tidy_count];
        } else {
            $tidy_locations_empty[] = [$tidy_loc, $tidy_count];
        }
    }

    $tidy_auth_user = UserModel::getAuthUser();
    $tidy_user_name = ($tidy_auth_user) ? (string)$tidy_auth_user->get('name') : '';
?>

<header class="tidy-topbar">
    <a class="tidy-brand" href="{{ url('/') }}">
        <img src="{{ asset('logo.png') }}" alt=""/>
        <span>{{ app('workspace') }}</span>
    </a>

    <button type="button" class="tidy-iconbtn" id="tidy-menu-toggle" aria-label="{{ tt('menu') }}" aria-expanded="false" aria-controls="tidy-side">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
</header>

<div class="tidy-scrim" id="tidy-scrim"></div>

<nav class="tidy-side" id="tidy-side" aria-label="main navigation">
    <a class="tidy-brand tidy-brand-side" href="{{ url('/') }}">
        <img src="{{ asset('logo.png') }}" alt=""/>
        <span>{{ app('workspace') }}</span>
    </a>

    <div class="tidy-nav-group">
        <a class="tidy-nav-link {{ (tidy_is_path('/', true)) ? 'is-current' : '' }}" href="{{ url('/') }}">
            <i class="fas fa-home" aria-hidden="true"></i><span class="tidy-nav-label">{{ tt('overview') }}</span>
        </a>

        @if (app('tasks_enable'))
        <a class="tidy-nav-link {{ (tidy_is_path('/tasks')) ? 'is-current' : '' }}" href="{{ url('/tasks') }}">
            <i class="fas fa-check" aria-hidden="true"></i><span class="tidy-nav-label">{{ __('app.tasks') }}</span>
            <span class="tidy-nav-count">{{ TasksModel::getOpenTaskCount() }}</span>
        </a>
        @endif

        @if (app('calendar_enable'))
        <a class="tidy-nav-link {{ (tidy_is_path('/calendar')) ? 'is-current' : '' }}" href="{{ url('/calendar') }}">
            <i class="far fa-calendar-alt" aria-hidden="true"></i><span class="tidy-nav-label">{{ __('app.calendar') }}</span>
        </a>
        @endif

        @if (app('inventory_enable'))
        <a class="tidy-nav-link {{ (tidy_is_path('/inventory')) ? 'is-current' : '' }}" href="{{ url('/inventory') }}">
            <i class="fas fa-box-open" aria-hidden="true"></i><span class="tidy-nav-label">{{ __('app.inventory') }}</span>
        </a>
        @endif

        <a class="tidy-nav-link {{ (tidy_is_path('/search')) ? 'is-current' : '' }}" href="{{ url('/search') }}">
            <i class="fas fa-search" aria-hidden="true"></i><span class="tidy-nav-label">{{ __('app.search') }}</span>
        </a>

        @if (app('owm_enable'))
        <a class="tidy-nav-link {{ (tidy_is_path('/weather')) ? 'is-current' : '' }}" href="{{ url('/weather') }}">
            <i class="fas fa-cloud-sun" aria-hidden="true"></i><span class="tidy-nav-label">{{ __('app.weather') }}</span>
        </a>
        @endif

        @if (app('chat_enable'))
        <a class="tidy-nav-link notification-badge {{ (tidy_is_path('/chat')) ? 'is-current' : '' }}" href="{{ url('/chat') }}">
            <i class="fas fa-comments" aria-hidden="true"></i><span class="tidy-nav-label">{{ __('app.chat') }}</span>
            <span class="notify-badge is-hidden" id="unread-message-count"><span class="notify-badge-count"></span></span>
        </a>
        @endif

        @if (app('history_enable'))
        <a class="tidy-nav-link {{ (tidy_is_path('/plants/history')) ? 'is-current' : '' }}" href="{{ url('/plants/history') }}">
            <i class="fas fa-history" aria-hidden="true"></i><span class="tidy-nav-label">{{ app('history_name') }}</span>
        </a>
        @endif
    </div>

    <div class="tidy-nav-group">
        <div class="tidy-nav-heading">{{ __('app.locations') }}</div>

        @foreach ($tidy_locations_filled as $tidy_entry)
        <a class="tidy-nav-link {{ (tidy_is_path('/plants/location/' . $tidy_entry[0]->get('id'), true)) ? 'is-current' : '' }}" href="{{ url('/plants/location/' . $tidy_entry[0]->get('id')) }}">
            <span class="tidy-nav-label">{{ $tidy_entry[0]->get('name') }}</span>
            <span class="tidy-nav-count">{{ $tidy_entry[1] }}</span>
        </a>
        @endforeach

        @if (count($tidy_locations_empty) > 0)
        <details class="tidy-nav-more" {{ (count($tidy_locations_filled) === 0) ? 'open' : '' }}>
            <summary class="tidy-nav-link tidy-nav-muted">{{ tt('empty_locations', ['count' => count($tidy_locations_empty)]) }}</summary>

            @foreach ($tidy_locations_empty as $tidy_entry)
            <a class="tidy-nav-link {{ (tidy_is_path('/plants/location/' . $tidy_entry[0]->get('id'), true)) ? 'is-current' : '' }}" href="{{ url('/plants/location/' . $tidy_entry[0]->get('id')) }}">
                <span class="tidy-nav-label">{{ $tidy_entry[0]->get('name') }}</span>
            </a>
            @endforeach
        </details>
        @endif
    </div>

    <div class="tidy-nav-group tidy-nav-bottom">
        @if (UserModel::isCurrentlyAdmin())
        <a class="tidy-nav-link {{ (tidy_is_path('/admin')) ? 'is-current' : '' }}" href="{{ url('/admin') }}">
            <i class="fas fa-cog" aria-hidden="true"></i><span class="tidy-nav-label">{{ __('app.admin_area') }}</span>
            @if (VersionModule::getCachedVersion() > config('version'))
                <span class="tidy-nav-dot" title="Update"></span>
            @endif
        </a>
        @endif

        <a class="tidy-nav-link {{ (tidy_is_path('/profile')) ? 'is-current' : '' }}" href="{{ url('/profile') }}">
            <span class="tidy-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($tidy_user_name, 0, 1)) }}</span><span class="tidy-nav-label">{{ ($tidy_user_name !== '') ? $tidy_user_name : __('app.profile') }}</span>
        </a>

        @if ((!app('auth_proxy_enable')) || (!app('auth_proxy_hide_logout')))
        <a class="tidy-nav-link tidy-nav-muted" href="{{ url('/logout') }}">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i><span class="tidy-nav-label">{{ __('app.logout') }}</span>
        </a>
        @endif
    </div>
</nav>

<nav class="tidy-bottombar" aria-label="quick navigation">
    <a class="{{ (tidy_is_path('/', true)) ? 'is-current' : '' }}" href="{{ url('/') }}">
        <i class="fas fa-home" aria-hidden="true"></i><span>{{ tt('overview') }}</span>
    </a>

    @if (app('tasks_enable'))
    <a class="{{ (tidy_is_path('/tasks')) ? 'is-current' : '' }}" href="{{ url('/tasks') }}">
        <i class="fas fa-check" aria-hidden="true"></i><span>{{ __('app.tasks') }}</span>
    </a>
    @endif

    <a class="tidy-bottombar-add" href="javascript:void(0);" onclick="window.addNewPlant();" aria-label="{{ __('app.add_plant') }}">
        <i class="fas fa-plus" aria-hidden="true"></i>
    </a>

    @if (app('calendar_enable'))
    <a class="{{ (tidy_is_path('/calendar')) ? 'is-current' : '' }}" href="{{ url('/calendar') }}">
        <i class="far fa-calendar-alt" aria-hidden="true"></i><span>{{ __('app.calendar') }}</span>
    </a>
    @endif

    <a href="javascript:void(0);" onclick="document.getElementById('tidy-menu-toggle').click();">
        <i class="fas fa-bars" aria-hidden="true"></i><span>{{ tt('more') }}</span>
    </a>
</nav>
