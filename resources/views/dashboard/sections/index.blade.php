@extends('layouts.dashboard')

@section('title', 'Sections')

@section('content')
<div class="dash-head">
    <div>
        <h1>Sections</h1>
        <p class="dash-lead">Edit header and footer copy shown on every public page.</p>
    </div>
</div>

<div class="dash-table-wrap">
    <table class="dash-table">
        <thead>
            <tr>
                <th>Section</th>
                <th>Last updated</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pages as $page)
                <tr>
                    <td>
                        <a class="dash-row-link" href="{{ route('dashboard.sections.edit', $page) }}">{{ \App\PageMeta\Catalog::for($page->slug)->navLabel() }}</a>
                    </td>
                    <td>
                        @include('dashboard.partials.updated', ['record' => $page])
                    </td>
                    <td>
                        @include('dashboard.partials.icon-actions', [
                            'edit' => route('dashboard.sections.edit', $page),
                        ])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="dash-empty">No sections assigned to this account.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
