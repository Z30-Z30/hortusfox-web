<?php require_once app_path('/views/tidy/helpers.php'); ?>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('css/bulma.css') }}"/>

@if (env('APP_DEBUG'))
<script src="{{ asset('js/vue.js') }}"></script>
@else
<script src="{{ asset('js/vue.min.js') }}"></script>
@endif

<script src="{{ asset('js/app.js', true) }}"></script>

<script>document.documentElement.classList.add('tidy');</script>
<link rel="stylesheet" type="text/css" href="{{ asset('css/tidy.css', true) }}"/>
<script src="{{ asset('js/tidy.js', true) }}" defer></script>

@if (is_string(app('custom_head_code')))
{!! app('custom_head_code') !!}
@endif
