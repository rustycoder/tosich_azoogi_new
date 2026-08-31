@php
    $grouped = [];
    $singles = [];
    foreach ($section['fields'] as $field) {
        if ($field->group) {
            $grouped[$field->group][] = $field;
        } else {
            $singles[] = $field;
        }
    }
@endphp

@foreach ($singles as $field)
    @php $row = $metaByKey->get($field->key, collect())->firstWhere('sort_order', 0); @endphp
    @if ($row)
        @include('dashboard.pages._field', ['field' => $field, 'row' => $row])
    @endif
@endforeach

@foreach ($grouped as $group => $fields)
    @php
        $orders = $metaByKey->get($fields[0]->key, collect())->pluck('sort_order')->unique()->sort()->values();
    @endphp
    @foreach ($orders as $order)
        <div class="dash-repeat">
            <p class="dash-repeat-title">Item {{ $order + 1 }}</p>
            @foreach ($fields as $field)
                @php $row = $metaByKey->get($field->key, collect())->firstWhere('sort_order', $order); @endphp
                @if ($row)
                    @include('dashboard.pages._field', ['field' => $field, 'row' => $row])
                @endif
            @endforeach
        </div>
    @endforeach
@endforeach
