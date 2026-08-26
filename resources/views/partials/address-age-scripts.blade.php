{{--
    Include this in any form that pairs municipality with barangay, or a birth
    date with an age. It prints the service area as JSON and loads the module
    that wires the fields up; the fields themselves opt in with
    data-barangay-for / data-age-for. See public/assets/js/address-age.js.
--}}
<script>
    window.SERVICE_AREA = @json(\App\Support\ServiceArea::all());
</script>
<script src="{{ asset('assets/js/address-age.js') }}?v={{ filemtime(public_path('assets/js/address-age.js')) }}"></script>
