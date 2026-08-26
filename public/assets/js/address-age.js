/**
 * Two linked-field behaviours shared by every form that collects an address or
 * a birth date.
 *
 *   1. Municipality -> barangay. Choosing a municipality fills the barangay
 *      dropdown with that municipality's barangays and nothing else, so a
 *      Madridejos barangay can never be saved against Bantayan.
 *   2. Birth date -> age. Age is read off the chosen date and never typed, so
 *      the two cannot disagree.
 *
 * Both are wired by data attributes rather than by hard-coded element ids, so
 * a form opts in from its markup:
 *
 *   <select id="municipality" name="address_municipality">...</select>
 *   <select data-barangay-for="municipality" data-old="{{ old(...) }}"></select>
 *
 *   <input type="date" id="birthdate" name="birthdate">
 *   <input data-age-for="birthdate" readonly>
 *
 * The barangay list itself comes from window.SERVICE_AREA, printed by the
 * server from App\Support\ServiceArea so there is only one copy of it.
 *
 * Age here is display only. The server recomputes it from the submitted birth
 * date, so a tampered age field changes nothing.
 */
(function (window, document) {
    'use strict';

    var AREA = window.SERVICE_AREA || {};

    function barangaysOf(municipality) {
        var list = AREA[municipality];
        return Object.prototype.toString.call(list) === '[object Array]' ? list : [];
    }

    /**
     * Refills a barangay select from its municipality.
     *
     * The value to re-select is whichever is still valid: what the user has
     * already picked, or the `data-old` value the server sent back after a
     * failed submit. Anything that does not belong to the new municipality is
     * dropped rather than silently kept.
     */
    function populate(barangaySelect, municipalitySelect) {
        var municipality = municipalitySelect.value;
        var list = barangaysOf(municipality);
        var wanted = barangaySelect.value || barangaySelect.getAttribute('data-old') || '';
        var placeholder = barangaySelect.getAttribute('data-placeholder') || 'Select Barangay';

        barangaySelect.innerHTML = '';

        var blank = document.createElement('option');
        blank.value = '';
        blank.textContent = placeholder;
        barangaySelect.appendChild(blank);

        var matched = false;
        for (var i = 0; i < list.length; i++) {
            var option = document.createElement('option');
            option.value = list[i];
            option.textContent = list[i];
            if (list[i] === wanted) {
                option.selected = true;
                matched = true;
            }
            barangaySelect.appendChild(option);
        }

        if (!matched) {
            barangaySelect.value = '';
        }

        // Once consumed, the server's old value must not override a later
        // choice by the user.
        barangaySelect.removeAttribute('data-old');
    }

    function linkAddress(barangaySelect) {
        var id = barangaySelect.getAttribute('data-barangay-for');
        var municipalitySelect = document.getElementById(id);
        if (!municipalitySelect) return;

        municipalitySelect.addEventListener('change', function () {
            populate(barangaySelect, municipalitySelect);
        });

        populate(barangaySelect, municipalitySelect);
    }

    /** Whole years elapsed, matching Carbon's ->age on the server. */
    function ageOn(birthdate, today) {
        var years = today.getFullYear() - birthdate.getFullYear();
        var monthDelta = today.getMonth() - birthdate.getMonth();
        if (monthDelta < 0 || (monthDelta === 0 && today.getDate() < birthdate.getDate())) {
            years--;
        }
        return years;
    }

    function linkAge(ageField) {
        var id = ageField.getAttribute('data-age-for');
        var birthdateField = document.getElementById(id);
        if (!birthdateField) return;

        // Age is derived, so it must not be typeable even if the markup forgot.
        ageField.readOnly = true;

        function refresh() {
            var raw = birthdateField.value;
            if (!raw) {
                ageField.value = '';
                return;
            }

            // A date input is always YYYY-MM-DD. Building the date from parts
            // keeps it local; Date.parse would read it as UTC and shift the day
            // backwards for anyone west of Greenwich.
            var parts = raw.split('-');
            if (parts.length !== 3) {
                ageField.value = '';
                return;
            }

            var birthdate = new Date(+parts[0], +parts[1] - 1, +parts[2]);
            if (isNaN(birthdate.getTime())) {
                ageField.value = '';
                return;
            }

            var years = ageOn(birthdate, new Date());
            // A future birth date only yields a negative age; show nothing and
            // let the field's own validation report the problem.
            ageField.value = years < 0 ? '' : String(years);
        }

        birthdateField.addEventListener('change', refresh);
        birthdateField.addEventListener('input', refresh);
        refresh();
    }

    function init(root) {
        var scope = root || document;

        var barangays = scope.querySelectorAll('[data-barangay-for]');
        for (var i = 0; i < barangays.length; i++) {
            linkAddress(barangays[i]);
        }

        var ages = scope.querySelectorAll('[data-age-for]');
        for (var j = 0; j < ages.length; j++) {
            linkAge(ages[j]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(document); });
    } else {
        init(document);
    }

    // Exposed so a form that builds fields after load can wire them too.
    window.AddressAge = {
        init: init,
        barangaysOf: barangaysOf,
        ageOn: ageOn
    };
})(window, document);
