@extends('layout')


@section('content')

<div style="margin-top:1em;" class="container broadcast-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-header">{{ __('My Previous Broadcast Videos') }}</div>

            @foreach ($broadcasts as $broadcast)
            <div class="card" style="margin-top:1em;">

                <div class="">
                    <ul class="list-group ">
                        <li class="list-group-item">Started on - {{ $broadcast['started_on'] }}</li>

                        <li class="list-group-item">Ended on - {{ $broadcast['ended_on'] }}</li>
                        <li class="list-group-item">

                            <a href="{{ url("/download-broadcast-video").'/'.\Crypt::encrypt($broadcast['broadcast_id']) }}" class="btn btn-primary mr-1 open-broadcast-viewers" role="button"> <i class="fa fa-cloud-download" aria-hidden="true"></i> Download Video</a>

                        </li>
                    </ul>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>


@endsection


