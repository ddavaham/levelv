@extends('layout.index')

@section('title', 'LevelV Login')

@section('content')
    <!-- Page Content -->
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-md-3 mt-3">
                <div class="card">
                    <div class="card-header text-center">
                        Welcome to {{ config('app.name') }}
                    </div>
                    <div class="card-body">
                        @include('extra.alert')
                        <p>Welcome to {{ config('app.name') }}. To get started, use the button below to login using CCP's SSO. From there, we will get you account setup.</p>
                        <a href="{{ $ssoUrl }}" class="text-center">
                            <img src="https://web.ccpgamescdn.com/eveonlineassets/developers/eve-sso-login-white-large.png" class="rounded mx-auto d-block"/>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
