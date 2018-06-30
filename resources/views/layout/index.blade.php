<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="../../favicon.ico">

        <title>@yield('title') || Level V || Eve Online</title>

        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">

        <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.1.1/cyborg/bootstrap.min.css" rel="stylesheet" integrity="sha384-iMvB1cubutqnCw/Xlf3A1lEXHojPMC7dETFR9CYfYENKn8yw6QlyL+BaYmPXEjmo" crossorigin="anonymous">

        <!-- Custom styles for this template -->
        <link href="{{ url('css/app.css') }}" rel="stylesheet">

        @yield('css')
    </head>

    <body>

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container">
                <a class="navbar-brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav mr-auto ml-3">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Homepage</a>
                        </li>
                        {{-- <li class="nav-item">
                            <a href="{{ route('about') }}" class="nav-link">About</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('donate') }}" class="nav-link">Donate</a>
                        </li> --}}
                        <li class="nav-item">
                            <a href="{{ config('services.bitbucket.urls.issues') }}" class="nav-link" target="_blank">Report An Issue</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ml-auto mr-3">


                        @if(Auth::check())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="nav-item dropdown ml-3">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Hello {{ collect(explode(' ', Auth::user()->info->name))->first() }} <b class="caret"></b>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                  <a class="dropdown-item" href="{{ route('dashboard') }}"> My Dashboard</a>
                                  <a class="dropdown-item" href="{{ route('settings.index') }}"> My Settings</a>
                                  {{-- <a class="dropdown-item" href="#"> Alt Hotswap</a> --}}
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="{{ route('auth.logout') }}"> Logout</a>
                                </div>
                            </li>
                        @else
                            <a href="{{ route('auth.login') }}" class="nav-link">Login</a>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')


        <div class="container mt-3">
            <footer>
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <hr>
                        <!-- Footer -->
                        <p>
                            Brought to you by <a href="https://evewho.com/pilot/David+Davaham">David Davaham</a><br />
                            <a href="{{ config('services.bitbucket.urls.overview') }}" target="_blank">Bitbucket</a>
                        </p>
                    </div>
                </div>
            </footer>
        </div>


        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

        @yield('js')

    </body>
</html>
