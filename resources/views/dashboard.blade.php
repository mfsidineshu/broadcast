@extends('layout')

@section('content')
    <div style="margin-top:1em;" class="container broadcast-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body">
                        <a class="btn btn-primary" href="/start-broadcast" role="button">Start Broadcast</a>
                    </div>
                    @if (session('success'))
                        <div class="card-body">
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        </div>

                    @endif


                    @if (session('danger'))

                        <div class="card-body">
                            <div class="alert alert-danger" role="alert">
                                {{ session('danger') }}
                            </div>
                        </div>
                    @endif


                </div>
            </div>
        </div>
    </div>
@endsection
