@php
    $kanbanId ??= 'enquiry';
@endphp
<div
    class="dash-kanban"
    data-enquiry-kanban
    data-status-labels='@json(collect($statuses)->mapWithKeys(fn ($status) => [$status->value => $status->label()]))'
>
    @foreach ($statuses as $status)
        @php
            $enquiries = $columns[$status->value] ?? collect();
        @endphp
        <section class="dash-kanban-col is-{{ $status->value }}" data-kanban-col aria-labelledby="kanban-{{ $kanbanId }}-{{ $status->value }}">
            <header class="dash-kanban-col-head">
                <h2 id="kanban-{{ $kanbanId }}-{{ $status->value }}">{{ $status->label() }}</h2>
                <span data-kanban-count>{{ $enquiries->count() }}</span>
            </header>
            <div class="dash-kanban-col-body" data-kanban-drop data-status="{{ $status->value }}">
                @foreach ($enquiries as $enquiry)
                    @include('dashboard.enquiries._card', ['enquiry' => $enquiry, 'status' => $status])
                @endforeach
                <p class="dash-kanban-empty">No {{ strtolower($status->label()) }} cards.</p>
            </div>
        </section>
    @endforeach
</div>
