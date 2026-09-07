@if ($rows !== [])
    <dl class="dash-enquiry-facts">
        @foreach ($rows as $row)
            <div>
                <dt>{{ $row['label'] }}</dt>
                <dd>
                    @if ($row['href'] ?? null)
                        <a href="{{ $row['href'] }}">{{ $row['value'] }}</a>
                    @else
                        {{ $row['value'] }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
@endif
