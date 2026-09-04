@extends('layouts.dashboard')

@section('title', $type->menuLabel())

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>{{ $type->menuLabel() }}</h1>
    </div>
    <p class="dash-lead">Click a card for details. Drag the handle to move it between Pending, Active, Done, and Cancelled.</p>
</div>

@include('dashboard.enquiries._kanban', [
    'columns' => $columns,
    'statuses' => $statuses,
    'kanbanId' => $type->value,
])

@include('dashboard.enquiries._dialog')
@endsection
