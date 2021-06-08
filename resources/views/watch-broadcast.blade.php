@extends('layout')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/video-js.min.css') }}" />

@endpush

@section('content')

    <div class="video-container">
        <video id="broadcastedVideo" class="video-js vjs-fluid vjs-16-9"  controls preload="auto"
            data-setup="{}">
            <source type="application/x-mpegURL" src="stream-broadcast/{{ $folder }}.m3u8">

        </video>
    </div>

    <input type="hiiden" name="{{ $folder }}">

@endsection

@push('scripts')

    <script src="{{ asset('assets/video.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-http-streaming.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-contrib-quality-levels.min.js') }}"></script>
    <script src="{{ asset('assets/videojs-hls-quality-selector.min.js') }}"></script>

    <script>


        var player = videojs('broadcastedVideo');
        // player.play();
        player.hlsQualitySelector({
            displayCurrentQuality: true,
        });


    </script>

@endpush
