@extends('layouts.dashboard')

@section('title', 'Pages')

@section('content')
<div class="dash-head">
    <div>
        <h1>Pages</h1>
        <p class="dash-lead">Open a page to edit its seeded fields. New pages cannot be created here.</p>
    </div>
</div>

<div class="dash-table-wrap">
    <table class="dash-table">
        <thead>
            <tr>
                <th>Page</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Last updated</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pages as $page)
                <tr>
                    <td>
                        <a class="dash-row-link" href="{{ route('dashboard.pages.edit', $page) }}">{{ \App\PageMeta\Catalog::for($page->slug)->navLabel() }}</a>
                        <div class="dash-lead">{{ $page->title }}</div>
                    </td>
                    <td><code>{{ $page->slug }}</code></td>
                    <td>
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.pages.toggle-status', $page),
                            'on' => $page->isActive(),
                            'label' => $page->status->label(),
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>
                    <td>
                        @include('dashboard.partials.updated', ['record' => $page])
                    </td>
                    <td>
                        @include('dashboard.partials.icon-actions', [
                            'edit' => route('dashboard.pages.edit', $page),
                            'view' => $page->publicPath(),
                        ])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="dash-empty">No pages assigned to this account.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
