@extends('layout')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/start-broadcast.css') }}" />

@endpush

@section('content')
{{ csrf_field() }}

<video autoplay playsinline muted id="broadcastVideo"></video>
    <div class="content">

        <div>
            <!-- <h1>BROADCAST APPLICATION</h1> -->
            <div class="buttons-container">
                <button id="broadcastToggle" class="togglers play">START BROADCAST</button>
            </div>
            <div class="broadcastInfo" style="display: none;">
                <ul>
                    <!-- <li>Process ID : <span id="processId"></span> </li> -->
                    <!-- <li>Directory : <span id="directoryName"></span> </li> -->
                    <li>
                        <a href="#" id="broadcastLink" target="_blanc">Share Broadcast Link</a>
                    </li>

                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- <script src="{{ asset('assets/RecordRTC.js') }}"></script> --}}
    <script src="{{ asset('assets/start-broadcast.js') }}"></script>

@endpush
