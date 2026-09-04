@php
    $rows = $enquiry->detailRows();
    $facts = array_values(array_filter($rows, fn (array $row): bool => ! $row['wide']));
    $notes = array_values(array_filter($rows, fn (array $row): bool => $row['wide']));
    $updatedAt = $enquiry->updated_at?->timezone(config('app.timezone'));
@endphp
<article
    class="dash-kanban-card"
    data-enquiry-id="{{ $enquiry->id }}"
    data-url="{{ route('dashboard.enquiries.status', $enquiry) }}"
    data-delete-url="{{ route('dashboard.enquiries.destroy', $enquiry) }}"
>
    @if ($draggable ?? true)
        <button type="button" class="dash-drag-handle" aria-label="Drag to change status">
            <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <circle cx="5" cy="3" r="1.2"/>
                <circle cx="11" cy="3" r="1.2"/>
                <circle cx="5" cy="8" r="1.2"/>
                <circle cx="11" cy="8" r="1.2"/>
                <circle cx="5" cy="13" r="1.2"/>
                <circle cx="11" cy="13" r="1.2"/>
            </svg>
        </button>
    @endif
    <button type="button" class="dash-kanban-card-open" data-enquiry-open aria-haspopup="dialog">
        <strong>{{ $enquiry->name }}</strong>
        <p class="dash-kanban-email">{{ $enquiry->email }}</p>
        @include('dashboard.enquiries._updated', ['enquiry' => $enquiry, 'updatedAt' => $updatedAt])
    </button>
    <template data-enquiry-detail>
        <div class="dash-enquiry-dialog-meta" data-enquiry-meta>
            <span class="dash-pill is-{{ $status->value }}" data-enquiry-status>{{ $status->label() }}</span>
            @include('dashboard.enquiries._updated', ['enquiry' => $enquiry, 'updatedAt' => $updatedAt])
        </div>
        @if ($facts !== [])
            <dl class="dash-enquiry-facts">
                @foreach ($facts as $row)
                    <div>
                        <dt>{{ $row['label'] }}</dt>
                        <dd>
                            @if ($row['href'])
                                <a href="{{ $row['href'] }}">{{ $row['value'] }}</a>
                            @else
                                {{ $row['value'] }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
        @foreach ($notes as $row)
            <section class="dash-enquiry-note">
                <h3>{{ $row['label'] }}</h3>
                <p>{{ $row['value'] }}</p>
            </section>
        @endforeach
    </template>
</article>
