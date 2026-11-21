<?php
/*
    HortusFox Tidy UI - view helpers.
    Only reads data through the models HortusFox already ships; no backend changes.
*/

if (!function_exists('tt')) {
    /** Translate a Tidy UI string (falls back to English, then to the key). */
    function tt($key, $vars = [])
    {
        static $strings = null;

        if ($strings === null) {
            $strings = require app_path('/views/tidy/lang/en.php');

            $file = app_path('/views/tidy/lang/' . basename((string)getLocale()) . '.php');
            if (file_exists($file)) {
                $strings = array_merge($strings, require $file);
            }
        }

        $text = $strings[$key] ?? $key;

        foreach ($vars as $name => $value) {
            $text = str_replace('{' . $name . '}', (string)$value, $text);
        }

        return $text;
    }

    /** Format a date with the workspace date format. */
    function tidy_date($value)
    {
        if (!$value) {
            return '';
        }

        $format = app('date_format');
        if ((!is_string($format)) || (strlen($format) === 0)) {
            $format = 'Y-m-d';
        }

        return date($format, strtotime($value));
    }

    /** Whole days from today until the given date (negative = in the past). */
    function tidy_days_until($value)
    {
        $today = new DateTime(date('Y-m-d'));
        $target = new DateTime(date('Y-m-d', strtotime($value)));

        return (int)$today->diff($target)->format('%r%a');
    }

    /** Short relative label such as "today", "in 13 days", "3 days ago". */
    function tidy_relative($value)
    {
        if (!$value) {
            return '';
        }

        $days = tidy_days_until($value);

        if ($days === 0) {
            return tt('today');
        } else if ($days === 1) {
            return tt('tomorrow');
        } else if ($days === -1) {
            return tt('yesterday');
        } else if ($days > 1) {
            return tt('in_days', ['count' => $days]);
        }

        return tt('days_ago', ['count' => abs($days)]);
    }

    /** The plant a task refers to, or null. */
    function tidy_task_plant($task)
    {
        try {
            if (PlantTasksRefModel::hasPlantReference($task->get('id'))) {
                $ref = PlantTasksRefModel::getForTask($task->get('id'));
                if ($ref) {
                    return PlantsModel::getDetails($ref->get('plant_id'));
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    /** Human readable recurrence of a task, or an empty string. */
    function tidy_task_recurrence($task)
    {
        $time = $task->get('recurring_time');
        if (!$time) {
            return '';
        }

        $hours = (int)$time;
        if ($hours % 8760 === 0) {
            $count = intdiv($hours, 8760);
            return ($count === 1) ? tt('every_year') : tt('every_years', ['count' => $count]);
        } else if ($hours % 24 === 0) {
            $count = intdiv($hours, 24);
            return ($count === 1) ? tt('every_day') : tt('every_days', ['count' => $count]);
        }

        return tt('every_hours', ['count' => $hours]);
    }

    /** Localised pattern formatting through intl, with a plain fallback. */
    function tidy_intl($value, $pattern, $fallback)
    {
        $timestamp = strtotime($value);

        if (class_exists('IntlDateFormatter')) {
            try {
                $formatter = new IntlDateFormatter((string)getLocale(), IntlDateFormatter::NONE, IntlDateFormatter::NONE, date_default_timezone_get(), null, $pattern);
                $result = $formatter->format($timestamp);
                if (is_string($result)) {
                    return $result;
                }
            } catch (\Throwable $e) {
            }
        }

        return date($fallback, $timestamp);
    }

    /** "December 2026" */
    function tidy_month_label($value)
    {
        return tidy_intl($value, 'LLLL yyyy', 'F Y');
    }

    /** "Fri" */
    function tidy_weekday($value)
    {
        return rtrim(tidy_intl($value, 'EEE', 'D'), '.');
    }

    /** Group open tasks by due day. Returns [ 'Y-m-d' | 'none' => [tasks] ] in list order. */
    function tidy_group_tasks($tasks)
    {
        $groups = [];

        foreach ($tasks as $task) {
            $key = ($task->get('due_date')) ? date('Y-m-d', strtotime($task->get('due_date'))) : 'none';
            if (!isset($groups[$key])) {
                $groups[$key] = [];
            }
            $groups[$key][] = $task;
        }

        return $groups;
    }

    /** True when the current request path starts with the given prefix. */
    function tidy_is_path($prefix, $exact = false)
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if ($exact) {
            return rtrim($path, '/') === rtrim($prefix, '/');
        }

        return strpos($path, $prefix) === 0;
    }

    /** Plants of a location as a plain list (empty on error). */
    function tidy_location_plants($locationId)
    {
        try {
            $result = PlantsModel::getAll($locationId);
            $list = [];
            foreach ($result as $plant) {
                $list[] = $plant;
            }
            return $list;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
