@extends('layouts.dashboard')

@section('title', 'New project')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.projects.index') }}">Projects</a>
        <span>/</span>
        <span>New</span>
    </div>
    <div class="dash-head-title">
        <h1>New project</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="dash-project-form">Create</button>
        </div>
    </div>
</div>
<form id="dash-project-form" class="dash-form" method="post" action="{{ route('dashboard.projects.store') }}" enctype="multipart/form-data">
    @csrf
    @include('dashboard.projects._form', ['project' => null])
</form>
@endsection
