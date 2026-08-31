@extends('layouts.dashboard')

@section('title', $definition->navLabel())

@section('content')
@php
    $bag = \App\Support\PageMetaBag::for($page);
    $detailKeys = [
        'header.description', 'header.phone', 'header.email',
        'footer.description', 'footer.message',
    ];
    $detailFields = [];

    foreach ($sections as $section) {
        foreach ($section['fields'] as $field) {
            if (in_array($field->key, $detailKeys, true)) {
                $detailFields[] = $field;
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
            @foreach ($detailFields as $field)
                @php $row = $metaByKey->get($field->key, collect())->firstWhere('sort_order', 0); @endphp
                @if ($row)
                    @include('dashboard.pages._field', ['field' => $field, 'row' => $row])
                @endif
            @endforeach
        </div>
    </div>

    @if ($page->slug === 'header')
        <div class="dash-card">
            <h2>Menu</h2>
            <p class="dash-lead">Products, Trade Login, and LED Calculator stay fixed. Other links can be added or edited.</p>
            @include('dashboard.sections._links', [
                'alias' => 'nav',
                'links' => $bag->group('header.nav'),
            ])
        </div>
        <div class="dash-card">
            <h2>Rotating text</h2>
            @include('dashboard.sections._words', [
                'alias' => 'words',
                'words' => $bag->group('header.word'),
            ])
        </div>
    @endif

    @if ($page->slug === 'footer')
        @php
            $productHeading = $metaByKey->get('footer.products.heading', collect())->firstWhere('sort_order', 0);
            $companyHeading = $metaByKey->get('footer.company.heading', collect())->firstWhere('sort_order', 0);
            $contactHeading = $metaByKey->get('footer.contact.heading', collect())->firstWhere('sort_order', 0);
            $contactPhone = $metaByKey->get('footer.phone', collect())->firstWhere('sort_order', 0);
            $contactEmail = $metaByKey->get('footer.email', collect())->firstWhere('sort_order', 0);
        @endphp
        <div class="dash-card">
            <h2>Menu 1</h2>
            @if ($productHeading)
                @include('dashboard.pages._field', ['field' => \App\PageMeta\Field::text('footer.products.heading', 'Column title'), 'row' => $productHeading, 'wide' => true])
            @endif
            @include('dashboard.sections._links', ['alias' => 'products', 'links' => $bag->group('footer.products.link')])
        </div>
        <div class="dash-card">
            <h2>Menu 2</h2>
            @if ($companyHeading)
                @include('dashboard.pages._field', ['field' => \App\PageMeta\Field::text('footer.company.heading', 'Column title'), 'row' => $companyHeading, 'wide' => true])
            @endif
            @include('dashboard.sections._links', ['alias' => 'company', 'links' => $bag->group('footer.company.link')])
        </div>
        <div class="dash-card">
            <h2>Menu 3</h2>
            @if ($contactHeading)
                @include('dashboard.pages._field', ['field' => \App\PageMeta\Field::text('footer.contact.heading', 'Column title'), 'row' => $contactHeading, 'wide' => true])
            @endif
            <div class="dash-form-grid">
                @if ($contactPhone)
                    @include('dashboard.pages._field', ['field' => \App\PageMeta\Field::text('footer.phone', 'Phone'), 'row' => $contactPhone])
                @endif
                @if ($contactEmail)
                    @include('dashboard.pages._field', ['field' => \App\PageMeta\Field::text('footer.email', 'Email'), 'row' => $contactEmail])
                @endif
            </div>
            @include('dashboard.sections._links', ['alias' => 'contact', 'links' => $bag->group('footer.contact.link')])
        </div>
    @endif
</form>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/dashboard-section.js') }}?v={{ config('app.asset_version') }}"></script>
@endpush
