<!doctype html>
<html lang="{{ getLocale() }}">
    <head>
        @include('layout/head.php')
        <title>{{ app('workspace') }}</title>
    </head>

    <body>
        <div id="app" class="tidy-auth">
            <div class="tidy-auth-art" style="background-image: url('{{ asset('img/background.jpg') }}');"></div>

            <main class="tidy-auth-panel">
                <div class="tidy-auth-box">
                    <div class="tidy-brand">
                        <img src="{{ asset('logo.png') }}" alt=""/>
                        <span>HortusFox</span>
                    </div>

                    <div>
                        <h1>{{ app('workspace') }}</h1>
                        <p class="tidy-muted">{{ tt('sign_in_hint') }}</p>
                    </div>

                    @if (FlashMessage::hasMsg('error'))
                    <div class="tidy-flash is-error" role="alert"><div class="tidy-flash-body">{{ FlashMessage::getMsg('error') }}</div></div>
                    @elseif (FlashMessage::hasMsg('success'))
                    <div class="tidy-flash is-success" role="status"><div class="tidy-flash-body">{{ FlashMessage::getMsg('success') }}</div></div>
                    @endif

                    <form method="POST" action="{{ url('/login' . (isset($_GET['redirect']) ? '?redirect=' . $_GET['redirect'] : '')) }}">
                        @csrf

                        <div class="field">
                            <label class="label" for="tidy-auth-email">{{ __('app.email') }}</label>
                            <div class="control">
                                <input type="email" class="input" id="tidy-auth-email" name="email" autocomplete="username" placeholder="{{ __('app.enter_email') }}" required/>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="tidy-auth-password">{{ __('app.password') }}</label>
                            <div class="control">
                                <input type="password" class="input" id="tidy-auth-password" name="password" autocomplete="current-password" placeholder="{{ __('app.enter_password') }}" required/>
                            </div>
                        </div>

                        <button type="submit" class="tidy-btn is-primary tidy-auth-submit">{{ __('app.login') }}</button>
                    </form>

                    <a class="tidy-auth-help" href="javascript:void(0);" onclick="window.vue.bShowRestorePassword = true;">{{ __('app.restore_password') }}</a>
                </div>
            </main>

            <div class="modal" :class="{'is-active': bShowRestorePassword}">
                <div class="modal-background"></div>
                <div class="modal-card">
                    <header class="modal-card-head is-stretched">
                        <p class="modal-card-title">{{ __('app.restore_password') }}</p>
                        <button class="delete" aria-label="close" onclick="window.vue.bShowRestorePassword = false;"></button>
                    </header>
                    <section class="modal-card-body is-stretched">
                        <form id="frmRestorePassword" method="POST" action="{{ url('/password/restore') }}">
                            @csrf

                            <div class="field">
                                <div class="control">
                                    <input type="email" class="input" name="email" placeholder="{{ __('app.restore_password_email_placeholder') }}" required>
                                </div>
                            </div>
                        </form>
                    </section>
                    <footer class="modal-card-foot is-stretched">
                        <button class="button is-success" onclick="this.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i>&nbsp;{{ __('app.loading_please_wait') }}'; document.getElementById('frmRestorePassword').submit();">{{ __('app.restore_password') }}</button>
                        <button class="button" onclick="window.vue.bShowRestorePassword = false;">{{ __('app.cancel') }}</button>
                    </footer>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
            });
        </script>
    </body>
</html>
