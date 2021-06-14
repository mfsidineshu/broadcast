@extends('layout')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/start-broadcast.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/share-overlay.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/broadcast-viewers.css') }}" />

@endpush

@section('content')
{{ csrf_field() }}

<video autoplay playsinline muted id="broadcastVideo"></video>
    <div class="content">

        <div>
            <!-- <h1>BROADCAST APPLICATION</h1> -->
            <div class="buttons-container">


                <button id="start" class="togglers play"><i class="fa fa-television" aria-hidden="true"></i> BROADCAST</button>
                <input type="hidden" name="folder" value="">

                <section class="broadcastInfo" style="display: none">
                  <button id="stop" class="togglers stop"><i class="fa fa-stop-circle" aria-hidden="true"></i> STOP</button>
                  <button id="shareBroadcast" class="togglers"><i class="fa fa-share-alt" aria-hidden="true"></i> SHARE</button>
                  <button class="btn btn-primary mr-1 open-broadcast-viewers"  role="button"> <i class="fa fa-eye" aria-hidden="true"></i> Viewers : <span class="viewers-count-indicator"></span></button>

                </section>

            </div>

        </div>
    </div>

    @include('share-overlay')
    @include('broadcast-viewers')


@endsection

@push('scripts')
    {{-- <script src="{{ asset('assets/RecordRTC.js') }}"></script> --}}
    <script src="{{ asset('assets/start-broadcast.js') }}"></script>
    <script src="{{ asset('assets/broadcast-viewers.js') }}"></script>

@endpush
