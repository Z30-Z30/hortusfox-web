<?php /* Tidy UI: flash messages. Same ids as the original so existing scripts keep working. */ ?>
@if (FlashMessage::hasMsg('success'))
    <div id="success-message" class="tidy-flash is-success" role="status">
        <i class="fas fa-check-circle" aria-hidden="true"></i>
        <div class="tidy-flash-body">{!! UtilsModule::purify(FlashMessage::getMsg('success')) !!}</div>
        <button type="button" class="tidy-iconbtn" aria-label="{{ __('app.close') }}" onclick="document.getElementById('success-message').style.display = 'none';"><i class="fas fa-times" aria-hidden="true"></i></button>
    </div>
@endif

@if (FlashMessage::hasMsg('error'))
    <div id="error-message" class="tidy-flash is-error" role="alert">
        <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
        <div class="tidy-flash-body">{!! UtilsModule::purify(FlashMessage::getMsg('error')) !!}</div>
        <button type="button" class="tidy-iconbtn" aria-label="{{ __('app.close') }}" onclick="document.getElementById('error-message').style.display = 'none';"><i class="fas fa-times" aria-hidden="true"></i></button>
    </div>
@endif
