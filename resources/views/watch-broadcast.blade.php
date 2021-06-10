@extends('layout')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/video-js.min.css') }}" />
    <style>
        div.author-info{
            background: green;
            color: white;
        }
    </style>
@endpush

@section('content')

    <div class="video-container">
        <video id="broadcastedVideo" class="video-js vjs-fluid vjs-16-9"  controls preload="auto"
            data-setup="{}">
            <source type="application/x-mpegURL" src="/stream-broadcast/{{ $folder }}/master.m3u8">

        </video>
    </div>

    <div class="author-info">
        <p>Broadcast started on : {{ $broadcast["started_on"] }}</p>
        <h1>{{ $broadcast["user"]["name"] }}</h1>
        <p>{{ $broadcast["user"]["email"] }}</p>
    </div>
    <input type="hidden" name="folder" value="{{ $folder }}">
    {{ csrf_field() }}

@endsection

@push('scripts')

    <script src="{{ asset('assets/video.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-http-streaming.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-contrib-quality-levels.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-hls-quality-selector.min.js') }}"></script>
    <script src="{{ asset('assets/watch-broadcast.js') }}"></script>



@endpush
