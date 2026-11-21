<?php
    /* Tidy UI: overview. Uses the variables the HortusFox index controller provides. */
    $tidy_tasks_enabled = (bool)app('tasks_enable');
    $tidy_groups = [];
    $tidy_open_count = 0;
    $tidy_due_today = 0;
    $tidy_overdue = 0;

    if ($tidy_tasks_enabled) {
        $tidy_open_count = TasksModel::getOpenTaskCount();
        $tidy_task_list = [];
        foreach (TasksModel::getTasks(false, 200) as $tidy_task_item) {
            $tidy_task_list[] = $tidy_task_item;
            if ($tidy_task_item->get('due_date')) {
                $tidy_d = tidy_days_until($tidy_task_item->get('due_date'));
                if ($tidy_d === 0) { $tidy_due_today++; } else if ($tidy_d < 0) { $tidy_overdue++; }
            }
        }
        $tidy_groups = array_slice(tidy_group_tasks($tidy_task_list), 0, 6, true);
    }

    if ($tidy_overdue > 0) {
        $tidy_status = tt('overdue_count', ['count' => $tidy_overdue]);
    } else if ($tidy_due_today > 0) {
        $tidy_status = tt('due_today', ['count' => $tidy_due_today]);
    } else {
        $tidy_status = tt('nothing_due');
    }
?>

<header class="tidy-pagehead">
    <div class="tidy-pagehead-text">
        <h1>{{ app('workspace') }}</h1>
        <p>{{ tidy_intl(date('Y-m-d'), 'EEEE, d. MMMM', 'l, j F') }} &middot; <span class="{{ ($tidy_overdue > 0) ? 'tidy-text-warn' : '' }}">{{ $tidy_status }}</span></p>
    </div>

    @if (($weather) && (is_object($weather)))
    <a class="tidy-weather" href="{{ url('/weather') }}">
        <img src="{{ WeatherModule::WEATHER_ICON_ENDPOINT }}/wn/{{ $weather->weather[0]->icon }}@2x.png" alt=""/>
        <span><strong>{{ round($weather->main->temp) . '°' . WeatherModule::getUnitChar(app('owm_unittype')) }}</strong> {{ $weather->name }}</span>
    </a>
    @endif

    <button type="button" class="tidy-btn is-primary" onclick="window.addNewPlant();"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('app.add_plant') }}</span></button>
</header>

@include('tidy/flashmsg.php')

@if (count($warning_plants) > 0)
<section class="tidy-section">
    <div class="tidy-section-head"><h2>{{ tt('needs_attention') }}</h2></div>

    <div class="tidy-card tidy-list">
        @foreach ($warning_plants as $plant)
        <a class="tidy-list-row" href="{{ url('/plants/details/' . $plant->get('id')) }}">
            <img class="tidy-thumb" src="{{ abs_photo($plant->get('photo')) }}" alt=""/>
            <span class="tidy-list-main"><strong>{{ $plant->get('name') }}</strong><small>{{ (new Carbon($plant->get('last_edited_date')))->diffForHumans() }}</small></span>
            <span class="tidy-chip is-warn">{{ __('app.' . $plant->get('health_state')) }}</span>
        </a>
        @endforeach
    </div>
</section>
@endif

@if (($tidy_tasks_enabled) && (count($tidy_groups) > 0))
<section class="tidy-section">
    <div class="tidy-section-head">
        <h2>{{ tt('up_next') }}</h2>
        <a href="{{ url('/tasks') }}">{{ tt('all_tasks', ['count' => $tidy_open_count]) }}</a>
    </div>

    <div class="tidy-card tidy-tasklist">
        @foreach ($tidy_groups as $tidy_day => $tidy_day_tasks)
            @if (count($tidy_day_tasks) === 1)
                <?php $task = $tidy_day_tasks[0]; ?>
                @include('tidy/task_row.php')
            @else
                <details class="tidy-taskgroup {{ (($tidy_day !== 'none') && (tidy_days_until($tidy_day) < 0)) ? 'is-overdue' : '' }}">
                    <summary>
                        <span class="tidy-taskgroup-title"><strong>{{ tt('tasks_count', ['count' => count($tidy_day_tasks)]) }}</strong></span>
                        <span class="tidy-stack" aria-hidden="true">
                            <?php $tidy_shown = 0; ?>
                            @foreach ($tidy_day_tasks as $tidy_stack_task)
                                <?php $tidy_stack_plant = ($tidy_shown < 7) ? tidy_task_plant($tidy_stack_task) : null; ?>
                                @if ($tidy_stack_plant)
                                    <?php $tidy_shown++; ?>
                                    <img src="{{ abs_photo($tidy_stack_plant->get('photo')) }}" alt=""/>
                                @endif
                            @endforeach
                        </span>
                        <span class="tidy-task-due">
                            @if ($tidy_day !== 'none')
                                <span class="tidy-task-date">{{ tidy_weekday($tidy_day) . ' ' . tidy_date($tidy_day) }}</span>
                                <span class="tidy-task-rel">{{ tidy_relative($tidy_day) }}</span>
                            @else
                                <span class="tidy-task-date">{{ tt('no_date') }}</span>
                            @endif
                        </span>
                        <i class="fas fa-chevron-down tidy-taskgroup-chevron" aria-hidden="true"></i>
                    </summary>

                    @foreach ($tidy_day_tasks as $task)
                        @include('tidy/task_row.php')
                    @endforeach
                </details>
            @endif
        @endforeach
    </div>
</section>
@endif

@if ((app('calendar_enable')) && ($user->get('show_calendar_view')))
<section class="tidy-section">
    <div class="tidy-section-head">
        <h2>{{ __('app.calendar_overview') }}</h2>
        <a href="{{ url('/calendar') }}">{{ __('app.calendar') }}</a>
    </div>

    <div class="tidy-card tidy-card-pad calendar-view"><canvas id="calendar-small-view"></canvas></div>
</section>
@endif

<section class="tidy-section">
    <a name="locations"></a>
    <div class="tidy-section-head"><h2>{{ __('app.locations') }}</h2></div>

    <?php $tidy_empty_locations = []; ?>

    <div class="tidy-rooms">
        @foreach ($locations as $location)
            <?php $tidy_room_plants = tidy_location_plants($location->get('id')); ?>

            @if (count($tidy_room_plants) === 0)
                <?php $tidy_empty_locations[] = $location; ?>
            @else
                <?php $tidy_danger = PlantsModel::getDangerCount($location->get('id')); ?>
                <div class="tidy-card tidy-room {{ (count($tidy_room_plants) > 3) ? 'is-wide' : '' }}">
                    <div class="tidy-room-head">
                        <h3><a href="{{ url('/plants/location/' . $location->get('id')) }}">{{ $location->get('name') }}</a></h3>
                        <span class="tidy-muted">{{ (count($tidy_room_plants) === 1) ? tt('plants_one') : tt('plants_count', ['count' => count($tidy_room_plants)]) }}</span>
                        @if ($tidy_danger > 0)
                            <span class="tidy-chip is-warn">{{ __('app.danger_count', ['count' => $tidy_danger]) }}</span>
                        @endif
                        <a class="tidy-room-open" href="{{ url('/plants/location/' . $location->get('id')) }}">{{ tt('open_location') }}</a>
                    </div>

                    <div class="tidy-tiles">
                        @foreach (array_slice($tidy_room_plants, 0, 12) as $plant)
                            <a class="tidy-tile" href="{{ url('/plants/details/' . $plant->get('id')) }}">
                                <img src="{{ abs_photo($plant->get('photo')) }}" alt="" loading="lazy"/>
                                <strong>{{ $plant->get('name') }}</strong>
                                @if ($plant->get('health_state') !== 'in_good_standing')
                                    <small class="tidy-text-warn">{{ __('app.' . $plant->get('health_state')) }}</small>
                                @elseif (plant_attr('last_watered'))
                                    <small>{{ tt('last_watered_short') }}: {{ ($plant->get('last_watered')) ? tidy_relative($plant->get('last_watered')) : tt('never') }}</small>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    @if (count($tidy_empty_locations) > 0)
    <div class="tidy-chiprow">
        @foreach ($tidy_empty_locations as $location)
            <a class="tidy-chip is-plain" href="{{ url('/plants/location/' . $location->get('id')) }}">{{ $location->get('name') }}</a>
        @endforeach
    </div>
    @endif
</section>

<section class="tidy-section">
    <a name="last-added-or-authored-plants"></a>
    <div class="tidy-section-head">
        <h2>{{ ($user->get('show_plants_aoru')) ? __('app.last_added_plants') : __('app.last_authored_plants') }}</h2>
    </div>

    @if (count($last_plants_list) > 0)
    <div class="tidy-tiles is-loose">
        @foreach ($last_plants_list as $plant)
            <a class="tidy-tile" href="{{ url('/plants/details/' . $plant->get('id')) }}">
                <img src="{{ abs_photo($plant->get('photo')) }}" alt="" loading="lazy"/>
                <strong>{{ $plant->get('name') }}</strong>
                <small>{{ LocationsModel::getNameById($plant->get('location')) }}</small>
            </a>
        @endforeach
    </div>
    @else
    <div class="tidy-card tidy-empty">
        <img src="{{ asset('img/plantsempty.png') }}" alt=""/>
        <p>{{ __('app.content_empty') }}</p>
        <button type="button" class="tidy-btn is-primary" onclick="window.addNewPlant();"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('app.add_plant') }}</span></button>
    </div>
    @endif
</section>

@if (($user->get('show_log')) && (count($log) > 0))
<section class="tidy-section">
    <div class="tidy-section-head"><h2>{{ __('app.log_title') }}</h2></div>

    <div class="tidy-card tidy-log">
        @foreach ($log as $entry)
            <div class="tidy-log-row">
                <span class="tidy-muted">{{ $entry['date'] }}</span>
                <span>
                    @if ($entry['link'])
                        <a href="{{ $entry['link'] }}">{{ $entry['target'] }}</a>
                    @else
                        {{ $entry['target'] }}
                    @endif
                    &middot; {{ $entry['property'] }}: {{ $entry['value'] }}
                </span>
                <span class="tidy-muted">{{ $entry['user'] }}</span>
            </div>
        @endforeach
    </div>
</section>
@endif
