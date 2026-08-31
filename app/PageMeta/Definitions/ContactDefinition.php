<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class ContactDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'contact';
    }

    public function title(): string
    {
        return 'Contact Us — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Get in touch with Azoogi. Office hours, address, and contact form for lighting projects across Australia.';
    }

    public function navLabel(): string
    {
        return 'Contact';
    }

    public function fields(): array
    {
        return [
            Field::text('hours.label', 'Hours label'),
            Field::textarea('hours.value', 'Hours'),
            Field::text('address.label', 'Address label'),
            Field::textarea('address.value', 'Address'),
            Field::url('address.maps_url', 'Maps URL'),
            Field::text('phone.label', 'Phone label'),
            Field::text('phone.value', 'Phone'),
            Field::text('abn.label', 'ABN label'),
            Field::text('abn.value', 'ABN'),
            Field::text('acn.label', 'ACN label'),
            Field::text('acn.value', 'ACN'),
            Field::text('intl.heading', 'International heading'),
            Field::textarea('intl.body', 'International body'),
            Field::text('intl.email', 'International email'),
            Field::text('intl.phone', 'International phone'),
            Field::text('form.kicker', 'Form kicker'),
            Field::text('form.title', 'Form title'),
            Field::textarea('form.lead', 'Form lead'),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'hours.label', 'sort_order' => 0, 'value' => 'Office Hours'],
            ['key' => 'hours.value', 'sort_order' => 0, 'value' => "08:00AM – 04:00PM\nMonday To Friday"],
            ['key' => 'address.label', 'sort_order' => 0, 'value' => 'Address'],
            ['key' => 'address.value', 'sort_order' => 0, 'value' => "Unit 47/10-12 Girawah Pl\nMatraville NSW 2036"],
            ['key' => 'address.maps_url', 'sort_order' => 0, 'value' => 'https://www.google.com/maps/place/Azoogi+LED+Lighting/@-33.9654395,151.2254676,17z'],
            ['key' => 'phone.label', 'sort_order' => 0, 'value' => 'Office Number'],
            ['key' => 'phone.value', 'sort_order' => 0, 'value' => '1300 641 261'],
            ['key' => 'abn.label', 'sort_order' => 0, 'value' => 'ABN'],
            ['key' => 'abn.value', 'sort_order' => 0, 'value' => '72 600 241 209'],
            ['key' => 'acn.label', 'sort_order' => 0, 'value' => 'ACN'],
            ['key' => 'acn.value', 'sort_order' => 0, 'value' => '600 241 209'],
            ['key' => 'intl.heading', 'sort_order' => 0, 'value' => 'Inquiring from outside Australia?'],
            ['key' => 'intl.body', 'sort_order' => 0, 'value' => 'We regularly partner with architects, designers, developers, and trade contractors across the Asia-Pacific, Indian Ocean, and beyond. Our team is fully experienced in managing international logistics, cross-border time zones, and ensuring all products comply with local electrical, safety, and governance standards.'],
            ['key' => 'intl.email', 'sort_order' => 0, 'value' => 'exports@azoogi.com'],
            ['key' => 'intl.phone', 'sort_order' => 0, 'value' => '+61 2 7912 3524'],
            ['key' => 'form.kicker', 'sort_order' => 0, 'value' => 'Contact'],
            ['key' => 'form.title', 'sort_order' => 0, 'value' => 'We’d love to hear from you!'],
            ['key' => 'form.lead', 'sort_order' => 0, 'value' => 'Use the form below.'],
        ];
    }
}
