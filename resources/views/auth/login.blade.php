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
                {{-- <h4 class="mt-2">Patch Notes</h4>
                <hr class="mt-0" />
                <div id="accordion">
                    <div class="card">
                        <div class="card-header" id="headingOne" data-toggle="collapse" data-target="#v020_beta">
                            <span>
                                2018-06-23 - v0.21-beta
                            </span>
                        </div>
                        <div id="v020_beta" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>
                                    The following addresses the bugs and feature requests that were released in the <a href="{{ config('services.bitbucket.urls.commit') }}/tag/v0.21-beta">release</a>. If you have any question, please reference the #TalkWithTheDeveloper section of the about page.
                                </p>
                                <ul>
                                    <li>
                                        Updated this page with these patch notes
                                    </li>
                                    <li>
                                        Added Link to URL management page on the Setting Navigation Menu
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
@endsection
