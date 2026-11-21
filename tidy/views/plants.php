<?php
    /* Tidy UI: plants of one location. Same data and script hooks as the original view. */
    $tidy_show_list = ((isset($_GET['show'])) && ($_GET['show'] === 'list')) || ((!isset($_GET['show'])) && (isset($_COOKIE['list_show_style'])) && ($_COOKIE['list_show_style'] === 'list'));
    $tidy_sort_query = url_query('sorting', '&') . url_query('direction', '&');
    $tidy_plant_total = count($plants);
?>

<nav class="tidy-crumbs" aria-label="breadcrumb">
    <a href="{{ url('/') }}">{{ tt('overview') }}</a><span aria-hidden="true">/</span><span>{{ $location_data->get('name') }}</span>
</nav>

<header class="tidy-pagehead">
    <div class="tidy-pagehead-text">
        <h1>{{ $location_data->get('name') }}</h1>
        <p>{{ ($tidy_plant_total === 1) ? tt('plants_one') : tt('plants_count', ['count' => $tidy_plant_total]) }}</p>
    </div>

    <details class="tidy-menu">
        <summary class="tidy-btn"><span>{{ tt('more_actions') }}</span><i class="fas fa-chevron-down" aria-hidden="true"></i></summary>

        <div class="tidy-menu-list">
            @if (plant_attr('last_repotted'))
            <a href="javascript:void(0);" onclick="window.vue.showPerformBulkUpdate('last_repotted', '{{ __('app.bulk_set_repotted') }}', '{{ __('app.set_repotted') }}', '{{ $location }}');">{{ __('app.set_repotted') }}</a>
            @endif
            @if (plant_attr('last_fertilised'))
            <a href="javascript:void(0);" onclick="window.vue.showPerformBulkUpdate('last_fertilised', '{{ __('app.bulk_set_fertilised') }}', '{{ __('app.set_fertilised') }}', '{{ $location }}');">{{ __('app.set_fertilised') }}</a>
            @endif
            @foreach (CustBulkCmdModel::getCmdList() as $bulk_cmd)
            <a href="javascript:void(0);" onclick="window.vue.showPerformBulkUpdate('{{ $bulk_cmd->get('attribute') }}', '{{ $bulk_cmd->get('label') }}', '{{ $bulk_cmd->get('label') }}', '{{ $location }}', true, '{{ $bulk_cmd->get('datatype') }}');">{{ $bulk_cmd->get('label') }}</a>
            @endforeach
            <a href="javascript:void(0);" onclick="window.vue.showPerformBulkUpdate('location', '{{ __('app.bulk_move_plants') }}', '{{ __('app.move_plants') }}', '{{ $location }}', false, 'string'); window.vue.setBulkComboValues(window.locationList);">{{ __('app.move_plants') }}</a>
            <a href="javascript:void(0);" onclick="window.vue.bShowPlantBulkPrint = true;">{{ __('app.bulk_print_qr_codes') }}</a>
        </div>
    </details>

    @if (plant_attr('last_watered'))
    <button type="button" class="tidy-btn" onclick="window.vue.showPerformBulkUpdate('last_watered', '{{ __('app.bulk_set_watered') }}', '{{ __('app.set_watered') }}', '{{ $location }}');"><i class="fas fa-tint" aria-hidden="true"></i><span>{{ __('app.set_watered') }}</span></button>
    @endif

    <button type="button" class="tidy-btn is-primary" onclick="window.addNewPlant();"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('app.add_plant') }}</span></button>
</header>

@include('tidy/flashmsg.php')

<div class="tidy-toolbar">
    <div class="tidy-pills">
        <a class="tidy-pill {{ (!$tidy_show_list) ? 'is-current' : '' }}" href="{{ url('/plants/location/' . $location . '?show=cards' . $tidy_sort_query) }}"><i class="fas fa-th-large" aria-hidden="true"></i> {{ tt('view_cards') }}</a>
        <a class="tidy-pill {{ ($tidy_show_list) ? 'is-current' : '' }}" href="{{ url('/plants/location/' . $location . '?show=list' . $tidy_sort_query) }}"><i class="fas fa-list" aria-hidden="true"></i> {{ tt('view_list') }}</a>
    </div>

    <select class="tidy-select" aria-label="sorting" onchange="location.href = '{{ url('/plants/location/' . $location . '?sorting=') }}' + this.value + '{{ ((isset($list_order_style)) ? '&direction=' . $list_order_style : '') . url_query('show', '&') }}';">
        @foreach ($sorting_types as $sorting_type)
            @if (strpos($sorting_type, 'history') === false)
                <option value="{{ $sorting_type }}" {{ (($list_sorting_style) && ($list_sorting_style === $sorting_type)) ? 'selected' : '' }}>{{ __('app.sorting_type_' . $sorting_type) }}</option>
            @endif
        @endforeach
    </select>

    <select class="tidy-select" aria-label="direction" onchange="location.href = '{{ url('/plants/location/' . $location . '?sorting=' . ((isset($list_sorting_style)) ? $list_sorting_style : 'name')) . url_query('show', '&') }}&direction=' + this.value;">
        @foreach ($sorting_dirs as $sorting_dir)
            <option value="{{ $sorting_dir }}" {{ ((isset($list_order_style)) && ($list_order_style === $sorting_dir)) ? 'selected' : '' }}>{{ __('app.sorting_dir_' . $sorting_dir) }}</option>
        @endforeach
    </select>

    <label class="tidy-search">
        <i class="fas fa-search" aria-hidden="true"></i>
        <input type="text" id="sorting-control-filter-text" placeholder="{{ __('app.filter_by_text') }}" aria-label="{{ __('app.filter_by_text') }}">
    </label>
</div>

@if ($tidy_plant_total > 0)
    @if (!$tidy_show_list)
    <div class="tidy-cards">
        @foreach ($plants as $plant)
            <a class="tidy-card tidy-plantcard plant-filter-text-root" href="{{ url('/plants/details/' . $plant->get('id')) }}">
                <img src="{{ abs_photo($plant->get('photo')) }}" alt="" loading="lazy"/>

                <span class="tidy-plantcard-body">
                    <strong class="plant-filter-text-target"><?php if ($user->get('show_plant_id')) { ?><span class="tidy-task-id">#{{ $plant->get('id') }}</span> <?php } ?>{{ $plant->get('name') . (((PlantsModel::offspringCount($plant->get('id'))) || (PlantsModel::getDetails($plant->get('clone_origin')) !== null)) ? ' (' . strval($plant->get('clone_num') + 1) . ')' : '') }}</strong>
                    <em>{{ $plant->get('scientific_name') ?? '' }}</em>

                    <span class="tidy-chiprow">
                        @if ($plant->get('health_state') !== 'in_good_standing')
                            <span class="tidy-chip is-warn">{{ __('app.' . $plant->get('health_state')) }}</span>
                        @endif

                        @if ((isset($list_sorting_style)) && ($list_sorting_style) && ($list_sorting_style !== 'name') && ($plant->get($list_sorting_style)))
                            <span class="tidy-chip is-plain">{{ UtilsModule::readablePlantAttribute($plant->get($list_sorting_style), $list_sorting_style) }}</span>
                        @elseif ((plant_attr('last_watered')) && ($plant->get('last_watered')))
                            <span class="tidy-chip is-water"><i class="fas fa-tint" aria-hidden="true"></i> {{ tidy_relative($plant->get('last_watered')) }}</span>
                        @endif

                        @if ((plant_attr('light_level')) && ($plant->get('light_level')))
                            <span class="tidy-chip is-plain">{{ __('app.' . $plant->get('light_level')) }}</span>
                        @endif
                    </span>
                </span>
            </a>
        @endforeach
    </div>
    @else
    <div class="tidy-card tidy-tablewrap">
        <table class="tidy-table">
            <thead>
                <tr>
                    <th>{{ __('app.name') }}</th>
                    <?php if (plant_attr('light_level')) { ?><th>{{ __('app.light_level') }}</th><?php } ?>
                    <?php if (plant_attr('last_watered')) { ?><th>{{ __('app.last_watered') }}</th><?php } ?>
                    <th>{{ __('app.health_state') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($plants as $plant)
                <tr class="plant-filter-text-root">
                    <td>
                        <a class="tidy-table-plant" href="{{ url('/plants/details/' . $plant->get('id')) }}">
                            <img class="tidy-thumb" src="{{ abs_photo($plant->get('photo')) }}" alt="" loading="lazy"/>
                            <span><strong class="plant-filter-text-target">{{ $plant->get('name') . (((PlantsModel::offspringCount($plant->get('id'))) || (PlantsModel::getDetails($plant->get('clone_origin')) !== null)) ? ' (' . strval($plant->get('clone_num') + 1) . ')' : '') }}</strong><em>{{ $plant->get('scientific_name') ?? '' }}</em></span>
                        </a>
                    </td>
                    <?php if (plant_attr('light_level')) { ?><td class="tidy-muted">{{ ($plant->get('light_level')) ? __('app.' . $plant->get('light_level')) : '' }}</td><?php } ?>
                    <?php if (plant_attr('last_watered')) { ?><td>{{ ($plant->get('last_watered')) ? tidy_date($plant->get('last_watered')) : '' }} <small class="tidy-muted">{{ ($plant->get('last_watered')) ? tidy_relative($plant->get('last_watered')) : '' }}</small></td><?php } ?>
                    <td><span class="tidy-chip {{ ($plant->get('health_state') === 'in_good_standing') ? 'is-ok' : 'is-warn' }}">{{ __('app.' . $plant->get('health_state')) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@else
    <div class="tidy-card tidy-empty">
        <img src="{{ asset('img/plantsempty.png') }}" alt=""/>
        <p>{{ __('app.content_empty') }}</p>
        <button type="button" class="tidy-btn is-primary" onclick="window.addNewPlant();"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('app.add_plant') }}</span></button>
    </div>
@endif

<div class="tidy-two">
    <section class="tidy-card tidy-card-pad location-notes">
        <a name="location-notes-anchor" id="location-notes-anchor"></a>

        <div class="tidy-card-head">
            <h2>{{ __('app.notes') }}</h2>
            <button type="button" class="tidy-iconbtn" aria-label="{{ tt('edit') }}" onclick="window.vue.showEditLocationNotes({{ $location_data->get('id') }}, document.getElementById('location-notes-content').value, 'location-notes-anchor');"><i class="fas fa-pen" aria-hidden="true"></i></button>
        </div>

        <textarea class="is-hidden" id="location-notes-content">{{ $location_data->get('notes') ?? '' }}</textarea>

        <div class="tidy-prose">
            @if (is_string($location_data->get('notes')))
                {!! UtilsModule::markdown($location_data->get('notes')) !!}
            @else
                <span class="tidy-muted">{{ __('app.no_notes_specified') }}</span>
            @endif
        </div>
    </section>

    <section class="tidy-card tidy-card-pad location-log">
        <a name="location-log-anchor" id="location-log-anchor"></a>

        <div class="tidy-card-head">
            <h2>{{ __('app.location_log') }}</h2>
            <button type="button" class="tidy-btn" onclick="window.vue.showAddLocationLogEntry('{{ $location }}', 'location-log-anchor');"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('app.add_location_log_entry') }}</span></button>
        </div>

        @if ((is_countable($location_log_entries)) && (count($location_log_entries) > 0))
        <table id="location-log-table" class="tidy-table is-compact">
            <thead>
                <tr>
                    <td>{{ __('app.location_log_content') }}</td>
                    <td>{{ __('app.location_log_date') }}</td>
                    <td></td>
                </tr>
            </thead>

            <tbody>
                @foreach ($location_log_entries as $location_log_entry)
                <tr id="location-log-entry-table-row-{{ $location_log_entry->get('id') }}">
                    <td id="location-log-entry-item-{{ $location_log_entry->get('id') }}">{{ $location_log_entry->get('content') }}</td>
                    <td class="tidy-muted">{{ tidy_date($location_log_entry->get('created_at')) }}</td>
                    <td class="tidy-table-actions">
                        <button type="button" class="tidy-iconbtn" aria-label="{{ tt('edit') }}" onclick="window.vue.showEditLocationLogEntry('{{ $location_log_entry->get('id') }}', '{{ $location_log_entry->get('location') }}', document.getElementById('location-log-entry-item-{{ $location_log_entry->get('id') }}').innerText, 'location-log-anchor');"><i class="fas fa-pen" aria-hidden="true"></i></button>
                        <button type="button" class="tidy-iconbtn" aria-label="{{ __('app.remove') }}" onclick="if (confirm('{{ __('app.confirm_remove_location_log_entry') }}')) { window.vue.removeLocationLogEntry('{{ $location_log_entry->get('id') }}', 'location-log-entry-table-row-{{ $location_log_entry->get('id') }}'); }"><i class="far fa-trash-alt" aria-hidden="true"></i></button>
                    </td>
                </tr>
                @endforeach

                @if ($location_log_entries->asArray()[count($location_log_entries) - 1]['id'] > 1)
                    <tr id="location-log-load-more" class="location-log-paginate">
                        <td colspan="3"><a href="javascript:void(0);" onclick="window.vue.loadNextLocationLogEntries(this, '{{ $location }}', document.getElementById('location-log-table'));" data-paginate="{{ $location_log_entries->asArray()[count($location_log_entries) - 1]['id'] }}">{{ __('app.load_more') }}</a></td>
                    </tr>
                @endif
            </tbody>
        </table>
        @else
            <p class="tidy-muted">{{ __('app.no_location_log_entries_yet') }}</p>
        @endif
    </section>
</div>
