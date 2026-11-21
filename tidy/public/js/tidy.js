/*
    HortusFox Tidy UI - small progressive enhancements.
    No build step, no dependencies beyond what HortusFox already loads (axios, Vue instance).
*/
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') { fn(); } else { document.addEventListener('DOMContentLoaded', fn); }
    }

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function ymd(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
    function parseDay(value) {
        var parts = String(value).split(' ')[0].split('-');
        return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    }
    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) { node.className = className; }
        if (text !== undefined) { node.textContent = text; }
        return node;
    }

    /* ---- Navigation drawer (mobile) ---- */
    function initMenu() {
        var toggle = document.getElementById('tidy-menu-toggle');
        var side = document.getElementById('tidy-side');
        var scrim = document.getElementById('tidy-scrim');
        if (!toggle || !side) { return; }

        function set(open) {
            side.classList.toggle('is-open', open);
            if (scrim) { scrim.classList.toggle('is-open', open); }
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () { set(!side.classList.contains('is-open')); });
        if (scrim) { scrim.addEventListener('click', function () { set(false); }); }
        document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') { set(false); } });
    }

    /* ---- Dropdown menus built on <details> close when clicking elsewhere ---- */
    function initMenus() {
        document.addEventListener('click', function (ev) {
            var menus = document.querySelectorAll('details.tidy-menu[open]');
            for (var i = 0; i < menus.length; i++) {
                if (!menus[i].contains(ev.target) || (ev.target.closest && ev.target.closest('.tidy-menu-list a'))) {
                    menus[i].removeAttribute('open');
                }
            }
        });
    }

    /* ---- Month calendar ---- */
    function initCalendar() {
        var grid = document.getElementById('tidy-calendar');
        if (!grid || !window.axios) { return; }

        var list = document.getElementById('tidy-calendar-list');
        var title = document.getElementById('tidy-calendar-title');
        var sub = document.getElementById('tidy-calendar-sub');
        var locale = grid.dataset.locale || undefined;
        var today = new Date();
        var view = new Date(today.getFullYear(), today.getMonth(), 1);
        var monthFmt, dayFmt, dateFmt;

        try {
            monthFmt = new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric' });
            dayFmt = new Intl.DateTimeFormat(locale, { weekday: 'short' });
            dateFmt = new Intl.DateTimeFormat(locale, { weekday: 'short', day: 'numeric', month: 'numeric' });
        } catch (e) {
            monthFmt = new Intl.DateTimeFormat(undefined, { month: 'long', year: 'numeric' });
            dayFmt = new Intl.DateTimeFormat(undefined, { weekday: 'short' });
            dateFmt = new Intl.DateTimeFormat(undefined, { weekday: 'short', day: 'numeric', month: 'numeric' });
        }

        function openEdit(item) {
            var set = function (id, prop, value) { var n = document.getElementById(id); if (n) { n[prop] = value; } };
            set('inpEditCalendarItemIdent', 'innerText', '#' + item.id + ' ' + item.name);
            set('inpEditCalendarItemId', 'value', item.id);
            set('inpEditCalendarItemName', 'value', item.name);
            set('inpEditCalendarItemDateFrom', 'value', String(item.date_from).split(' ')[0]);
            set('inpEditCalendarItemDateTill', 'value', String(item.date_till).split(' ')[0]);
            set('inpEditCalendarItemClass', 'value', item.class_descriptor);
            if (window.vue) { window.vue.bShowEditCalendarItem = true; }
        }

        function chip(item, continued) {
            var node = el('button', 'tidy-cal-item' + (continued ? ' is-continued' : ''), item.name);
            node.type = 'button';
            node.title = item.name + (item.class_name ? ' (' + item.class_name + ')' : '');
            if (item.color_background) { node.style.setProperty('--tidy-cal-bg', item.color_background); }
            if (item.color_border) { node.style.setProperty('--tidy-cal-fg', item.color_border); }
            node.addEventListener('click', function () { openEdit(item); });
            return node;
        }

        function render(items) {
            var year = view.getFullYear(), month = view.getMonth();
            var first = new Date(year, month, 1);
            var offset = (first.getDay() + 6) % 7;
            var start = new Date(year, month, 1 - offset);
            var byDay = {};
            var inMonth = 0;

            items.forEach(function (item) {
                var from = parseDay(item.date_from);
                var till = parseDay(item.date_till);
                var span = Math.round((till - from) / 86400000);
                if (span <= 1) { till = from; }
                if (from.getFullYear() === year && from.getMonth() === month) { inMonth++; }
                for (var d = new Date(from), n = 0; d <= till && n < 62; d.setDate(d.getDate() + 1), n++) {
                    var key = ymd(d);
                    (byDay[key] = byDay[key] || []).push({ item: item, continued: n > 0 });
                }
            });

            grid.textContent = '';
            for (var w = 0; w < 7; w++) {
                var ref = new Date(start); ref.setDate(start.getDate() + w);
                grid.appendChild(el('div', 'tidy-cal-head', dayFmt.format(ref)));
            }

            var weeks = Math.ceil((offset + new Date(year, month + 1, 0).getDate()) / 7);
            for (var i = 0; i < weeks * 7; i++) {
                var day = new Date(start); day.setDate(start.getDate() + i);
                var cell = el('div', 'tidy-cal-cell' + (day.getMonth() !== month ? ' is-outside' : '') + (ymd(day) === ymd(today) ? ' is-today' : ''));
                cell.appendChild(el('span', 'tidy-cal-day', String(day.getDate())));
                (byDay[ymd(day)] || []).forEach(function (entry) { cell.appendChild(chip(entry.item, entry.continued)); });
                grid.appendChild(cell);
            }

            if (title) { title.textContent = monthFmt.format(first); }
            if (sub) { sub.textContent = (grid.dataset.countLabel || '').replace('{count}', inMonth); }

            if (list) {
                list.textContent = '';
                var upcoming = items.filter(function (item) { return parseDay(item.date_till) >= new Date(today.getFullYear(), today.getMonth(), today.getDate()); })
                    .sort(function (a, b) { return parseDay(a.date_from) - parseDay(b.date_from); }).slice(0, 8);

                if (upcoming.length === 0) {
                    list.appendChild(el('p', 'tidy-muted', list.dataset.empty || ''));
                }

                upcoming.forEach(function (item) {
                    var row = el('button', 'tidy-cal-row');
                    row.type = 'button';
                    row.appendChild(el('span', 'tidy-cal-row-date', dateFmt.format(parseDay(item.date_from))));
                    var main = el('span', 'tidy-cal-row-main');
                    main.appendChild(el('strong', '', item.name));
                    if (item.class_name) { main.appendChild(el('small', '', item.class_name)); }
                    row.appendChild(main);
                    row.addEventListener('click', function () { openEdit(item); });
                    list.appendChild(row);
                });
            }
        }

        function load() {
            var year = view.getFullYear(), month = view.getMonth();
            var from = new Date(year, month, 1 - 7);
            var till = new Date(year, month + 1, 7);

            window.axios.post(window.location.origin + '/calendar/query', { date_from: ymd(from), date_till: ymd(till) })
                .then(function (response) {
                    if (response.data && response.data.code == 200) { render(response.data.data || []); } else { render([]); }
                })
                .catch(function () { render([]); });
        }

        function bind(id, fn) { var n = document.getElementById(id); if (n) { n.addEventListener('click', fn); } }
        bind('tidy-calendar-prev', function () { view = new Date(view.getFullYear(), view.getMonth() - 1, 1); load(); });
        bind('tidy-calendar-next', function () { view = new Date(view.getFullYear(), view.getMonth() + 1, 1); load(); });
        bind('tidy-calendar-today', function () { view = new Date(today.getFullYear(), today.getMonth(), 1); load(); });

        load();
    }

    /*
        HortusFox mounts a Vue instance on #app, which re-creates the DOM inside it.
        Wait for that before touching elements, otherwise the changes would be thrown away.
    */
    function whenAppMounted(fn) {
        var tries = 0;
        (function check() {
            if ((window.vue && window.vue._isMounted) || tries > 150) { fn(); return; }
            tries++;
            window.setTimeout(check, 20);
        })();
    }

    ready(function () {
        whenAppMounted(function () {
            initMenu();
            initMenus();
            initCalendar();
        });
    });
})();
