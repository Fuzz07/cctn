@if (config('services.recaptcha.enabled', true) && config('services.recaptcha.site_key'))
    <div class="recaptcha-widget-wrap" style="margin-bottom: 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
        <div class="g-recaptcha-scaler" style="display: flex; justify-content: center; width: 100%; overflow: hidden;">
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
        </div>
        @error('g-recaptcha-response')
            <div class="recaptcha-error-msg" style="color: #dc2626; font-size: 0.8rem; font-weight: 600; margin-top: 0.45rem; display: flex; align-items: center; gap: 0.35rem; text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $message }}</span>
            </div>
        @enderror
    </div>

    <style>
        @media (max-width: 360px) {
            .g-recaptcha-scaler {
                transform: scale(0.85);
                -webkit-transform: scale(0.85);
                transform-origin: center center;
                -webkit-transform-origin: center center;
            }
        }
    </style>
@endif
