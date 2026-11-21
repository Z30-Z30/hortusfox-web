<?php
    /*
        Tidy UI: plant details.
        A compact summary with quick actions on top; below it the original HortusFox details
        template is included unchanged, so every attribute, modal and script hook stays upstream.
    */
    $tidy_next_task = null;
    if ((isset($plant_tasks)) && (is_countable($plant_tasks))) {
        foreach ($plant_tasks as $tidy_plant_task) {
            if ((!$tidy_plant_task->get('done')) && ($tidy_plant_task->get('due_date'))) {
                if (($tidy_next_task === null) || (strtotime($tidy_plant_task->get('due_date')) < strtotime($tidy_next_task->get('due_date')))) {
                    $tidy_next_task = $tidy_plant_task;
                }
            }
        }
    }
    $tidy_is_history = (bool)$plant->get('history');
?>

<nav class="tidy-crumbs" aria-label="breadcrumb">
    <a href="{{ url('/') }}">{{ tt('overview') }}</a><span aria-hidden="true">/</span>
    @if (!$tidy_is_history)
        <a href="{{ url('/plants/location/' . $plant->get('location')) }}">{{ LocationsModel::getNameById($plant->get('location')) }}</a><span aria-hidden="true">/</span>
    @endif
    <span>{{ $plant->get('name') }}</span>
</nav>

<header class="tidy-plant">
    <a class="tidy-plant-photo" href="javascript:void(0);" onclick="window.vue.showImagePreview('{{ str_replace('_thumb', '', abs_photo($plant->get('photo'))) }}');">
        <img src="{{ abs_photo($plant->get('photo')) }}" alt="{{ $plant->get('name') }}"/>
    </a>

    <div class="tidy-plant-main">
        <div>
            <h1>{{ $plant->get('name') }}</h1>
            <p class="tidy-muted"><em>{{ $plant->get('scientific_name') ?? '' }}</em>{{ ($plant->get('scientific_name')) ? ' · ' : '' }}{{ (!$tidy_is_history) ? LocationsModel::getNameById($plant->get('location')) : app('history_name') }} <span class="tidy-task-id">{{ $plant_ident }}</span></p>
        </div>

        <div class="tidy-chiprow">
            <span class="tidy-chip {{ ($plant->get('health_state') === 'in_good_standing') ? 'is-ok' : 'is-warn' }}">{{ __('app.' . $plant->get('health_state')) }}</span>

            @if ((plant_attr('light_level')) && ($plant->get('light_level')))
                <span class="tidy-chip is-plain">{{ __('app.' . $plant->get('light_level')) }}</span>
            @endif

            @if (strlen((string)$plant->get('tags')) > 1)
                @foreach ($tags as $tag)
                    @if (strlen($tag) > 0)
                        <a class="tidy-chip is-plain" href="{{ url('/search?query=' . $tag) }}">{{ $tag }}</a>
                    @endif
                @endforeach
            @endif
        </div>

        @if (!$tidy_is_history)
        <div class="tidy-plant-actions">
            @if (plant_attr('last_watered'))
            <form method="POST" action="{{ url('/plants/details/edit') }}">
                @csrf
                <input type="hidden" name="plant" value="{{ $plant->get('id') }}"/>
                <input type="hidden" name="attribute" value="last_watered"/>
                <input type="hidden" name="value" value="{{ date('Y-m-d') }}"/>
                <button type="submit" class="tidy-btn is-primary"><i class="fas fa-tint" aria-hidden="true"></i><span>{{ tt('watered_today') }}</span></button>
            </form>
            @endif

            @if (plant_attr('last_fertilised'))
            <form method="POST" action="{{ url('/plants/details/edit') }}">
                @csrf
                <input type="hidden" name="plant" value="{{ $plant->get('id') }}"/>
                <input type="hidden" name="attribute" value="last_fertilised"/>
                <input type="hidden" name="value" value="{{ date('Y-m-d') }}"/>
                <button type="submit" class="tidy-btn"><i class="fas fa-seedling" aria-hidden="true"></i><span>{{ tt('fertilised_today') }}</span></button>
            </form>
            @endif

            <button type="button" class="tidy-btn" onclick="window.vue.showPhotoUpload({{ $plant->get('id') }});"><i class="fas fa-camera" aria-hidden="true"></i><span>{{ __('app.upload') }}</span></button>
        </div>
        @endif

        <div class="tidy-stats">
            @if (plant_attr('last_watered'))
            <div class="tidy-card tidy-stat">
                <span>{{ __('app.last_watered') }}</span>
                <strong>{{ ($plant->get('last_watered')) ? tidy_date($plant->get('last_watered')) : '–' }}</strong>
                <small>{{ ($plant->get('last_watered')) ? tidy_relative($plant->get('last_watered')) : tt('never') }}</small>
            </div>
            @endif

            @if (plant_attr('last_fertilised'))
            <div class="tidy-card tidy-stat">
                <span>{{ __('app.last_fertilised') }}</span>
                <strong>{{ ($plant->get('last_fertilised')) ? tidy_date($plant->get('last_fertilised')) : '–' }}</strong>
                <small>{{ ($plant->get('last_fertilised')) ? tidy_relative($plant->get('last_fertilised')) : tt('never') }}</small>
            </div>
            @endif

            @if (app('tasks_enable'))
            <a class="tidy-card tidy-stat" href="#plant-tasks-anchor" onclick="var t = document.querySelector('a[name=plant-tasks-anchor]'); if (t) { t.scrollIntoView({behavior: 'smooth'}); } return false;">
                <span>{{ tt('next_task') }}</span>
                @if ($tidy_next_task)
                    <strong>{{ tidy_date($tidy_next_task->get('due_date')) }}</strong>
                    <small>{{ $tidy_next_task->get('title') }} · {{ tidy_relative($tidy_next_task->get('due_date')) }}</small>
                @else
                    <strong>–</strong>
                    <small>{{ tt('no_open_tasks') }}</small>
                @endif
            </a>
            @endif
        </div>
    </div>
</header>

<div class="tidy-upstream tidy-details">
@include('upstream/details.php')
</div>
