<?php
    /* Tidy UI: one task row. Expects $task. Keeps the ids and node order the HortusFox scripts rely on. */
    $tidy_task_id = $task->get('id');
    $tidy_task_plant = tidy_task_plant($task);
    $tidy_task_due = $task->get('due_date');
    $tidy_task_overdue = ($tidy_task_due) && (!$task->get('done')) && (tidy_days_until($tidy_task_due) < 0);
    $tidy_task_recurrence = tidy_task_recurrence($task);

    $tidy_task_due_html = '';
    if ($tidy_task_due) {
        $tidy_task_due_html = '<span class="tidy-raw">' . date('Y-m-d', strtotime($tidy_task_due)) . '</span> '
            . '<span class="is-task-recurring tidy-raw" data-time="' . (($task->get('recurring_time') !== null) ? htmlspecialchars((string)TasksModel::translateScope($task->get('recurring_time'), $task->get('recurring_scope'))) : '') . '" data-scope="' . htmlspecialchars((string)($task->get('recurring_scope') ?? '')) . '"></span>'
            . '<span class="tidy-task-date">' . htmlspecialchars(tidy_weekday($tidy_task_due) . ' ' . tidy_date($tidy_task_due)) . '</span>'
            . '<span class="tidy-task-rel">' . htmlspecialchars(tidy_relative($tidy_task_due)) . '</span>';
    }
?>
<div class="task tidy-task {{ ($tidy_task_overdue) ? 'is-overdue' : '' }}" id="task-item-{{ $tidy_task_id }}">
    <a name="task-anchor-{{ $tidy_task_id }}"></a>

    <div class="tidy-task-main">
        <div class="tidy-task-title" id="task-item-title-{{ $tidy_task_id }}"><span class="tidy-task-id">#{{ sprintf('%03d', $tidy_task_id) }}</span> {{ $task->get('title') }}</div>

        <div class="tidy-task-meta">
            @if ($tidy_task_plant)
                <a href="{{ url('/plants/details/' . $tidy_task_plant->get('id')) }}"><img src="{{ abs_photo($tidy_task_plant->get('photo')) }}" alt=""/>{{ $tidy_task_plant->get('name') }}</a>
            @endif

            @if ($tidy_task_recurrence !== '')
                <span><i class="fas fa-redo-alt" aria-hidden="true"></i> {{ $tidy_task_recurrence }}</span>
            @endif
        </div>
    </div>

    <div class="tidy-task-desc" id="task-item-description-{{ $tidy_task_id }}"><pre>{!! ($task->get('description')) ? UtilsModule::purify(UtilsModule::translateURLs($task->get('description'))) : '' !!}</pre></div>

    <div class="tidy-task-due" id="task-item-due-{{ $tidy_task_id }}"><?php echo $tidy_task_due_html; ?></div>

    <div class="tidy-task-actions">
        @if (!$task->get('done'))
            <button type="button" class="tidy-btn" onclick="window.vue.toggleTaskStatus({{ $tidy_task_id }});"><i class="fas fa-check" aria-hidden="true"></i><span>{{ __('app.done') }}</span></button>
        @else
            <button type="button" class="tidy-btn" onclick="window.vue.toggleTaskStatus({{ $tidy_task_id }});"><i class="fas fa-undo" aria-hidden="true"></i><span>{{ tt('open') }}</span></button>
        @endif
        <button type="button" class="tidy-iconbtn" aria-label="{{ tt('edit') }}" onclick="window.vue.editTask({{ $tidy_task_id }});"><i class="fas fa-pen" aria-hidden="true"></i></button>
        <button type="button" class="tidy-iconbtn" aria-label="{{ __('app.remove') }}" onclick="if (confirm('{{ __('app.confirm_remove_task') }}')) { window.vue.removeTask({{ $tidy_task_id }}); }"><i class="far fa-trash-alt" aria-hidden="true"></i></button>
    </div>
</div>
