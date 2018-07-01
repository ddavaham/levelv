@extends('layout.index')

@section('title', 'Default Layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mt-2">My Settings</h1>
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
                                <th>
                                    Current Token In Use
                                </th>
                                <td>
                                    {{ str_limit(Auth::user()->access_token, 40) ?: '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
