@extends('layouts.dashboard')

@section('title', $definition->navLabel())

@section('content')
@php
    $singles = [];
    $groups = [];

    foreach ($sections as $section) {
        foreach ($section['fields'] as $field) {
            if ($field->group) {
                $groups[$field->group][] = $field;
            } else {
                $singles[] = $field;
            }
        }
    }
@endphp
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.sections.index') }}">Sections</a>
        <span>/</span>
        <span>{{ $definition->navLabel() }}</span>
    </div>
    <div class="dash-head-title">
        <h1>Edit {{ strtolower($definition->navLabel()) }}</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="dash-section-form">Save</button>
        </div>
    </div>
</div>

<form id="dash-section-form" class="dash-form" method="post" action="{{ route('dashboard.sections.update', $page) }}">
    @csrf
    @method('put')
    <input type="hidden" name="title" value="{{ $page->title }}">
    <input type="hidden" name="meta_description" value="{{ $page->meta_description }}">
    <input type="hidden" name="status" value="{{ $page->status->value }}">

    <div class="dash-card">
        <h2>Details</h2>
        <div class="dash-form-grid">
            @foreach ($singles as $field)
                @php $row = $metaByKey->get($field->key, collect())->firstWhere('sort_order', 0); @endphp
                @if ($row)
                    @include('dashboard.pages._field', ['field' => $field, 'row' => $row])
                @endif
            @endforeach
        </div>
    </div>

    @if ($groups !== [])
        <div class="dash-card">
            <h2>Rotating text</h2>
            <div class="dash-stack">
                @foreach ($groups as $fields)
                    @php
                        $orders = $metaByKey->get($fields[0]->key, collect())->pluck('sort_order')->unique()->sort()->values();
                    @endphp
                    @foreach ($orders as $order)
                        @foreach ($fields as $field)
                            @php $row = $metaByKey->get($field->key, collect())->firstWhere('sort_order', $order); @endphp
                            @if ($row)
                                @include('dashboard.pages._field', ['field' => $field, 'row' => $row, 'hideLabel' => true])
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif
</form>
@endsection
