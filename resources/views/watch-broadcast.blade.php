@extends('layout')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/video-js.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/broadcast-viewers.css') }}" />

    <style>
        div.video-wr {
            min-height: 90vh !important;
            /* width: 90% !important; */
            margin: 1em auto 1em auto;
            overflow: hidden;
            overflow-y: scroll;

        }

        /* video{
                max-height: 600px !important;
            }

            .video-js{
                position: unset !important;

            } */

        video {
            width: 100% !important;
            height: auto !important;
        }

    </style>
@endpush

@section('content')
    <div class="" style="margin : 1em auto 1em auto;">

        <div class="">
            <ul class="list-group ">
                <li class="list-group-item">Broadcast started on : {{ $broadcast['started_on'] }} by</li>
                <li class="list-group-item">{{ $broadcast['user']['name'] }} - {{ $broadcast['user']['email'] }}</li>
                <li class="list-group-item">

                    <button class="btn btn-primary mr-1 open-broadcast-viewers" role="button"> <i class="fa fa-eye"
                            aria-hidden="true"></i> Viewers : <span class="viewers-count-indicator"></span></button>

                </li>
            </ul>
        </div>




    </div>
    <input type="hidden" name="folder" value="{{ $folder }}">
    {{ csrf_field() }}

    <div class="video-wr">
        <div class="video-container">
            <video id="broadcastedVideo" class="video-js vjs-fill vjs-16-9 vjs-4-3" controls preload="auto">
                <source  src="/stream-broadcast/{{ $folder }}/master.m3u8">

            </video>
        </div>
    </div>

    @include('broadcast-viewers')

@endsection

@push('scripts')

    <script src="{{ asset('assets/video.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-http-streaming.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-contrib-quality-levels.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-hls-quality-selector.min.js') }}"></script>
    <script src="{{ asset('assets/watch-broadcast.js') }}"></script>
    <script src="{{ asset('assets/broadcast-viewers.js') }}"></script>



@endpush
