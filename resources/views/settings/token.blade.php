@extends('layout.index')

@section('title', 'Default Layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mt-2">My Settings</h3>
                <hr />
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('settings.extra.nav')
            </div>
            <div class="col-lg-9">
                @include('extra.alert')
                <div class="card">
                    <div class="card-header text-center">
                        My Settings
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <tr>
                                <th width=25%>
                                    Current Access Token
                                </th>
                                <td>
                                    {{ str_limit(Auth::user()->access_token, 40) ?: '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Currently Authorized Scopes
                                </th>
                                <td>
                                    @if (Auth::user()->scopes->isNotEmpty())
                                        <ul>
                                            <li>{!! Auth::user()->scopes->implode("</li><li>") !!}</li>
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Current Refresh Token
                                </th>
                                <td>
                                    {{ str_limit(Auth::user()->refresh_token, 40) ?: '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Time till Refresh Token Expires
                                </th>
                                <td>
                                    {{ Auth::user()->expires->subMinutes(10)->diffForHumans(now(), true) }} till expiration*
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    All Tokens are refreshed every 10 minutes. Unfortunately, this is a requirement for this application to carry out its purpose. As always, you have the option to nuke your data with the red button below. This will permanently delete all data associated with your character on our site.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    If time till expiration is negative, your token is either in the process of being refreshed or an error was encountered while refreshing the token
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        <form action="{{ route('settings.token') }}" method="post">
                            {{ csrf_field() }}
                            @method('DELETE')
                            <button type="submit" name="action" value="delete" class="btn btn-danger">Delete My Token</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
