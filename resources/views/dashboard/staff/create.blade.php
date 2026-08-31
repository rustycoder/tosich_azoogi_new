@extends('layouts.dashboard')

@section('title', 'New staff')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.staff.index') }}">Staff</a>
        <span>/</span>
        <span>New</span>
    </div>
    <div class="dash-head-title">
        <h1>New staff</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="dash-staff-form">Create</button>
        </div>
    </div>
</div>
<div class="dash-card">
    <form id="dash-staff-form" class="dash-form" method="post" action="{{ route('dashboard.staff.store') }}">
        @csrf
        @include('dashboard.staff._form', ['staff' => null, 'assigned' => old('resources', [])])
    </form>
</div>
@endsection
