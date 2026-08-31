@extends('layouts.dashboard')

@section('title', 'Edit staff')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.staff.index') }}">Staff</a>
        <span>/</span>
        <span>{{ $staff->name }}</span>
    </div>
    <div class="dash-head-title">
        <h1>Edit staff</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="dash-staff-form">Save</button>
        </div>
    </div>
</div>
<div class="dash-card">
    <form id="dash-staff-form" class="dash-form" method="post" action="{{ route('dashboard.staff.update', $staff) }}">
        @csrf
        @method('put')
        @include('dashboard.staff._form', ['staff' => $staff, 'assigned' => old('resources', $assigned)])
    </form>
</div>
@endsection
