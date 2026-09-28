{{--
    The "I'm not a robot" check for the Android app. The app opens this page in
    a WebView and listens on window.CctnRecaptcha for the outcome; the token it
    gets goes with its sign-in or registration request, which verifies it.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Security check</title>
    <style>
        /* Transparent, so the app's own surface shows around the widget. */
        html, body { margin: 0; background: transparent; }
        body { display: flex; justify-content: center; padding: 24px 8px; }
    </style>
</head>
<body>
    <script>
        function tell(method, value) {
            var bridge = window.CctnRecaptcha;
            if (!bridge || !bridge[method]) return;
            if (value === undefined) {
                bridge[method]();
            } else {
                bridge[method](value);
            }
        }
        function onToken(token) { tell('onToken', token); }
        function onExpired() { tell('onExpired'); }
        function onWidgetError() { tell('onError'); }
    </script>

    @if (!$required)
        {{-- Switched off on the server: there is nothing for the app to collect. --}}
        <script>tell('onNotRequired');</script>
    @elseif (!$siteKey)
        {{-- A token is required, but there is no widget to get one from. --}}
        <script>tell('onError');</script>
    @else
        <div class="g-recaptcha"
             data-sitekey="{{ $siteKey }}"
             data-theme="{{ $dark ? 'dark' : 'light' }}"
             data-callback="onToken"
             data-expired-callback="onExpired"
             data-error-callback="onWidgetError"></div>
        <script src="https://www.google.com/recaptcha/api.js" async defer onerror="onWidgetError()"></script>
    @endif
</body>
</html>
