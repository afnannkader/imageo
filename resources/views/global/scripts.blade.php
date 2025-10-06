@stack('top_scripts')
<script src="{{ asset('assets/vendor/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/vironeer/toastr/js/vironeer-toastr.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/aos/aos.min.js') }}"></script>
@stack('scripts_libs')
<script src="{{ assetWithVersion('assets/extra/js/extra.js') }}"></script>
@stack('scripts')
@toastrRender
