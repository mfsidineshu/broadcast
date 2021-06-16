@extends('layout')


@section('content')

<div style="margin-top:1em;" class="container broadcast-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-header">{{ __('On Going Broadcasts') }}</div>

            @foreach ($broadcasts as $broadcast)
            <div class="card" style="margin-top:1em;">

                <div class="">
                    <ul class="list-group ">
                        <li class="list-group-item">{{ $broadcast['user']['name'] }} - {{ $broadcast['user']['email'] }}</li>

                        <li class="list-group-item">Broadcast started on : {{ $broadcast['started_on'] }} </li>
                        <li class="list-group-item">

                            <a href="{{ url("/watch-broadcast").'/'.\Crypt::encrypt($broadcast['broadcast_id']) }}" class="btn btn-primary mr-1 open-broadcast-viewers" role="button"> <i class="fa fa-television" aria-hidden="true"></i> Watch Broadcast</a>

                        </li>
                    </ul>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>


@endsection


