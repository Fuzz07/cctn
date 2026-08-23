/*
 * BCTVI — field-level input restrictions
 * ---------------------------------------------------------------------------
 * Declarative, real-time input filtering. Mark a field with data-restrict and
 * the matching rule is enforced as the user types, on paste, and on drop:
 *
 *   <input type="text" name="firstname" data-restrict="name">
 *   <input type="text" name="age"       data-restrict="number" data-max="120">
 *
 * Invalid characters never make it into the field, and a red inline message is
 * shown underneath it — never a browser alert. Submitting a form with an
 * invalid field is blocked and focus jumps to the first offender.
 *
 * This is a convenience layer only. Every rule here is mirrored server-side in
 * App\Support\InputRules, because client-side checks can be bypassed.
 */
(function () {
    'use strict';

    // ── Rule table ──────────────────────────────────────────────────────────
    // strip:    removes every character the rule disallows (real-time filter)
    // test:     validates the whole value once typing has settled
    // message:  the inline text shown when the value is rejected
    var RULES = {
        // Letters, spaces, hyphens, apostrophes. \p{L} keeps accented and
        // non-Latin letters working (José, Ñoño) instead of stripping them.
        // A literal space rather than \s, so a pasted newline or tab is
        // stripped too — matching InputRules::NAME_REGEX on the server.
        name: {
            strip: function (s) { return s.replace(/[^\p{L} '-]/gu, ''); },
            test: function (s) { return /^[\p{L} '-]+$/u.test(s); },
            message: 'Please enter letters only.'
        },

        // Digits and nothing else.
        number: {
            strip: function (s) { return s.replace(/\D/g, ''); },
            test: function (s) { return /^\d+$/.test(s); },
            message: 'Please enter numbers only.'
        },

        // Philippine mobile: exactly 11 digits starting 09.
        mobile: {
            strip: function (s) { return s.replace(/\D/g, '').slice(0, 11); },
            test: function (s) { return /^09\d{9}$/.test(s); },
            message: 'Enter 11 digits starting with 09 (e.g. 09123456789).',
            // Normalises +639..., 639... and 9... to a leading 09 on paste.
            paste: function (text) {
                var d = text.replace(/\D/g, '');
                if (d.indexOf('639') === 0) d = '0' + d.slice(2);
                else if (d.indexOf('9') === 0) d = '0' + d;
                return d.slice(0, 11);
            }
        },

        // Street addresses need house and block numbers, so digits are allowed
        // here along with the punctuation real addresses use. Everything else
        // (@ # $ % < > and friends) is stripped.
        address: {
            strip: function (s) { return s.replace(/[^\p{L}\d .,'\-\/]/gu, ''); },
            test: function (s) { return /^[\p{L}\d .,'\-\/]+$/u.test(s); },
            message: 'Please enter a valid address.'
        },

        // PH government IDs mix letters, digits and hyphens (N01-12-345678,
        // 07-1234567-8), so all three are kept and the value is uppercased.
        idnumber: {
            strip: function (s) { return s.replace(/[^A-Za-z0-9-]/g, '').toUpperCase(); },
            test: function (s) { return /^[A-Z0-9-]+$/.test(s); },
            message: 'Please enter a valid ID number.'
        }
    };

    // ── Styling ─────────────────────────────────────────────────────────────
    // Injected once so the module works in any view without a CSS dependency.
    // Scoped to its own class names to avoid colliding with page styles.
    function injectStyles() {
        if (document.getElementById('field-restrict-styles')) return;
        var style = document.createElement('style');
        style.id = 'field-restrict-styles';
        style.textContent = [
            '.fr-invalid{border-color:#dc2626!important;box-shadow:0 0 0 3px rgba(220,38,38,.12)!important;}',
            '.fr-error{display:none;font-size:.78rem;color:#dc2626;font-weight:600;',
            'margin-top:.35rem;align-items:center;gap:.3rem;line-height:1.3;}',
            '.fr-error.visible{display:flex;}',
            '.fr-error svg{flex:0 0 auto;}'
        ].join('');
        document.head.appendChild(style);
    }

    // ── Error message element ───────────────────────────────────────────────
    // Reuses an existing <span id="<field-id>-error"> if the view already has
    // one (the register form ships its own), otherwise builds one on the fly.
    function errorElementFor(input, message) {
        var existing = input.id && document.getElementById(input.id + '-error');
        if (existing) {
            existing.classList.add('fr-error');
            return existing;
        }

        var el = document.createElement('span');
        el.className = 'fr-error';
        el.setAttribute('role', 'alert');
        if (input.id) el.id = input.id + '-error';

        el.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" ' +
            'fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">' +
            '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>' +
            '<line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
            '<span class="fr-error-text"></span>';
        el.querySelector('.fr-error-text').textContent = message;

        input.parentNode.insertBefore(el, input.nextSibling);
        return el;
    }

    function showError(input, errorEl, message) {
        if (message) {
            var textNode = errorEl.querySelector('.fr-error-text');
            if (textNode) textNode.textContent = message;
        }
        errorEl.classList.add('visible');
        input.classList.add('fr-invalid');
        input.setAttribute('aria-invalid', 'true');
    }

    function clearError(input, errorEl) {
        errorEl.classList.remove('visible');
        input.classList.remove('fr-invalid');
        input.removeAttribute('aria-invalid');
    }

    // Replaces the value and drops the caret at `pos`. Callers work out `pos`
    // by stripping the text that sat before the caret, so the caret ends up
    // after however much of it survived filtering.
    function setValue(input, value, pos) {
        input.value = value;
        try { input.setSelectionRange(pos, pos); } catch (e) { /* non-text input */ }
    }

    // ── Wiring ──────────────────────────────────────────────────────────────
    function attach(input) {
        if (input.__frBound) return;

        var rule = RULES[(input.getAttribute('data-restrict') || '').toLowerCase()];
        if (!rule) return;
        input.__frBound = true;

        var message = input.getAttribute('data-restrict-msg') || rule.message;
        var errorEl = errorElementFor(input, message);
        var min = input.getAttribute('data-min');
        var max = input.getAttribute('data-max');
        var hideTimer = null;

        // Range check for numeric fields (data-min / data-max), e.g. Age 1–120.
        function rangeMessage(value) {
            if (!/^\d+$/.test(value)) return null;
            var n = parseInt(value, 10);
            if (min !== null && n < parseInt(min, 10)) return 'Please enter a number of ' + min + ' or more.';
            if (max !== null && n > parseInt(max, 10)) return 'Please enter a number of ' + max + ' or less.';
            return null;
        }

        // Full-value check. An empty field stays silent so the browser's own
        // "required" prompt handles it rather than two errors firing at once.
        function validate() {
            var value = input.value;
            if (value === '') { clearError(input, errorEl); return true; }

            if (!rule.test(value)) { showError(input, errorEl, message); return false; }

            var rangeError = rangeMessage(value);
            if (rangeError) { showError(input, errorEl, rangeError); return false; }

            clearError(input, errorEl);
            return true;
        }
        input.__frValidate = validate;

        // Flashes the message when a keystroke is rejected. Without this the
        // character would just vanish and the user would not know why.
        function flashRejection() {
            showError(input, errorEl, message);
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () {
                if (input.value === '' || rule.test(input.value)) clearError(input, errorEl);
            }, 2000);
        }

        input.addEventListener('input', function () {
            var original = input.value;
            var caret = input.selectionStart;
            var head = original.slice(0, caret === null ? original.length : caret);
            var cleaned = rule.strip(original);

            if (cleaned !== original) {
                setValue(input, cleaned, rule.strip(head).length);
                flashRejection();
                return;
            }
            clearTimeout(hideTimer);
            validate();
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            var text = ((e.clipboardData || window.clipboardData).getData('text') || '');
            var insert = rule.paste ? rule.paste(text) : rule.strip(text);

            var start = input.selectionStart || 0;
            var end = input.selectionEnd || 0;
            var head = input.value.slice(0, start) + insert;
            var merged = rule.strip(head + input.value.slice(end));

            // mobile re-applies its 11-digit cap after the merge
            if (rule.paste) merged = rule.paste(merged);

            setValue(input, merged, Math.min(rule.strip(head).length, merged.length));

            // A rule with its own paste() is a normaliser: turning
            // +639171234567 into 09171234567 is a fix, not a rejection, so it
            // only complains when the result is still unusable. Plain filtering
            // rules complain whenever characters had to be dropped.
            var rejected = rule.paste
                ? (merged !== '' && !rule.test(merged))
                : rule.strip(text) !== text;

            if (rejected) flashRejection();
            else validate();
        });

        // Text dragged into the field bypasses paste, so filter it too.
        input.addEventListener('drop', function (e) {
            e.preventDefault();
            var text = e.dataTransfer ? e.dataTransfer.getData('text') || '' : '';
            input.value = rule.strip(input.value + text);
            validate();
        });

        input.addEventListener('blur', validate);

        // old() values replayed after a failed submit are checked immediately.
        if (input.value) validate();
    }

    // Blocks submission and focuses the first invalid field.
    function guardForm(form) {
        if (form.__frBound) return;
        form.__frBound = true;

        form.addEventListener('submit', function (e) {
            var firstInvalid = null;

            form.querySelectorAll('[data-restrict]').forEach(function (input) {
                if (input.disabled || input.__frValidate === undefined) return;
                if (!input.__frValidate() && !firstInvalid) firstInvalid = input;
            });

            if (firstInvalid) {
                e.preventDefault();
                e.stopPropagation();
                firstInvalid.focus();
                if (firstInvalid.scrollIntoView) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    function init(root) {
        injectStyles();
        (root || document).querySelectorAll('[data-restrict]').forEach(attach);
        (root || document).querySelectorAll('form').forEach(function (form) {
            if (form.querySelector('[data-restrict]')) guardForm(form);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }

    // Exposed so views that add fields dynamically, or run their own
    // multi-step validation, can reuse the same rules.
    window.FieldRestrict = {
        init: init,
        rules: RULES,
        // True when every restricted field inside `scope` passes.
        validateWithin: function (scope) {
            var valid = true;
            scope.querySelectorAll('[data-restrict]').forEach(function (input) {
                if (input.__frValidate && !input.__frValidate()) valid = false;
            });
            return valid;
        }
    };
})();
