<?php
    /* Tidy UI: tasks, grouped by month. */
    $tidy_show_done = (isset($_GET['done'])) && ($_GET['done'] == true);
    $tidy_months = [];

    if (isset($tasks)) {
        foreach ($tasks as $tidy_task_item) {
            if ($tidy_show_done) {
                $tidy_key = 'done';
            } else {
                $tidy_key = ($tidy_task_item->get('due_date')) ? date('Y-m', strtotime($tidy_task_item->get('due_date'))) : 'none';
            }

            if (!isset($tidy_months[$tidy_key])) {
                $tidy_months[$tidy_key] = [];
            }
            $tidy_months[$tidy_key][] = $tidy_task_item;
        }
    }
?>

<header class="tidy-pagehead">
    <div class="tidy-pagehead-text">
        <h1>{{ (isset($title)) ? $title : __('app.tasks') }}</h1>
        <p>{{ __('app.tasks_hint') }}</p>
    </div>

    <button type="button" class="tidy-btn is-primary" onclick="window.vue.bShowCreateTask = true;"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('app.create_new') }}</span></button>
</header>

@include('tidy/flashmsg.php')

<div class="tidy-toolbar">
    <div class="tidy-pills">
        <a class="tidy-pill {{ (!$tidy_show_done) ? 'is-current' : '' }}" href="{{ url('/tasks') }}">{{ __('app.tasks_todo') }}</a>
        <a class="tidy-pill {{ ($tidy_show_done) ? 'is-current' : '' }}" href="{{ url('/tasks?done=1') }}">{{ __('app.tasks_done') }}</a>
    </div>

    <label class="tidy-search">
        <i class="fas fa-search" aria-hidden="true"></i>
        <input type="text" id="tasks-filter" placeholder="{{ __('app.filter_by_text') }}" aria-label="{{ __('app.filter_by_text') }}">
    </label>
</div>

@if (isset($tasks))
    <div class="tasks tidy-tasks">
        @if (count($tasks) > 0)
            @foreach ($tidy_months as $tidy_month => $tidy_month_tasks)
                <section class="tidy-section tidy-month">
                    @if ($tidy_month === 'none')
                        <h2 class="tidy-overline">{{ tt('no_date') }}</h2>
                    @elseif ($tidy_month !== 'done')
                        <h2 class="tidy-overline">{{ tidy_month_label($tidy_month . '-01') }}</h2>
                    @endif

                    <div class="tidy-card tidy-tasklist">
                        @foreach ($tidy_month_tasks as $task)
                            @include('tidy/task_row.php')
                        @endforeach
                    </div>
                </section>
            @endforeach
        @else
            @if (!$tidy_show_done)
            <div class="tidy-card tidy-empty">
                <img src="{{ asset('img/partypopper.png') }}" alt=""/>
                <p>{{ __('app.all_tasks_done') }}</p>
            </div>
            @endif
        @endif
    </div>
@endif
