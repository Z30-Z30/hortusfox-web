<?php /* Tidy UI: month calendar. The grid is rendered by public/js/tidy.js from /calendar/query. */ ?>

<header class="tidy-pagehead">
    <div class="tidy-pagehead-text">
        <h1 id="tidy-calendar-title">{{ (isset($title)) ? $title : __('app.calendar') }}</h1>
        <p id="tidy-calendar-sub">{{ __('app.calendar_hint') }}</p>
    </div>

    <div class="tidy-btngroup">
        <button type="button" class="tidy-iconbtn is-bordered" id="tidy-calendar-prev" aria-label="{{ tt('previous_month') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
        <button type="button" class="tidy-btn" id="tidy-calendar-today">{{ tt('this_month') }}</button>
        <button type="button" class="tidy-iconbtn is-bordered" id="tidy-calendar-next" aria-label="{{ tt('next_month') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
    </div>

    <button type="button" class="tidy-btn is-primary" onclick="window.vue.bShowAddCalendarItem = true;"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('app.add') }}</span></button>
</header>

@include('tidy/flashmsg.php')

<div class="tidy-calendar-layout">
    <div class="tidy-card tidy-calendar-wrap">
        <div class="tidy-calendar" id="tidy-calendar" data-locale="{{ getLocale() }}" data-count-label="{{ tt('items_this_month', ['count' => '{count}']) }}"></div>
    </div>

    <aside class="tidy-card tidy-card-pad">
        <div class="tidy-card-head"><h2>{{ tt('next_entries') }}</h2></div>
        <div class="tidy-calendar-list" id="tidy-calendar-list" data-empty="{{ tt('no_entries') }}"></div>
    </aside>
</div>
