@extends('layouts.dashboard')

@section('title', 'Edit project')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.projects.index') }}">Projects</a>
        <span>/</span>
        <span>{{ $project->title }}</span>
    </div>
    <div class="dash-head-title">
        <h1>Edit project</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="dash-project-form">Save</button>
        </div>
    </div>
</div>
<form id="dash-project-form" class="dash-form" method="post" action="{{ route('dashboard.projects.update', $project) }}" enctype="multipart/form-data">
    @csrf
    @method('put')
    @include('dashboard.projects._form', ['project' => $project])
</form>
@endsection
