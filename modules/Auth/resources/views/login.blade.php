<x-auth::layouts.auth>
    @section('title', __('auth::auth.login'))
    @section('subtitle', __('auth::auth.login_subheading'))

    {{-- Login Form Start Flag --}}
    <auth-login></auth-login>
    {{-- Login Form End Flag --}}
</x-auth::layouts.auth>
