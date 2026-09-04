<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class QuoteRequestDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'request-a-quote';
    }

    public function title(): string
    {
        return 'Request a Quote — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Review the products in your quote list and send a request to the Azoogi trade team.';
    }

    public function navLabel(): string
    {
        return 'Request a Quote';
    }

    public function fields(): array
    {
        return [
            Field::text('intro.kicker', 'Kicker'),
            Field::text('intro.title', 'Title'),
            Field::textarea('intro.body', 'Intro'),
            Field::text('drawer.trigger_label', 'Header button label'),
            Field::text('drawer.title', 'Drawer title'),
            Field::text('drawer.close', 'Drawer close'),
            Field::text('drawer.submit', 'Drawer submit'),
            Field::text('list.title', 'Product list title'),
            Field::text('form.title', 'Form title'),
            Field::text('form.contact_tag', 'Contact section'),
            Field::text('form.name_label', 'First name label'),
            Field::text('form.email_label', 'Email label'),
            Field::text('form.phone_label', 'Mobile label'),
            Field::text('form.project_tag', 'Project section'),
            Field::text('form.description_label', 'Description label'),
            Field::text('form.products_label', 'Products label'),
            Field::text('form.role_tag', 'Role section'),
            Field::text('form.role.label', 'Role option', true, 'form.role'),
            Field::text('form.method_tag', 'Contact method section'),
            Field::text('form.method.label', 'Contact method', true, 'form.method'),
            Field::text('form.supplier_tag', 'Supplier section'),
            Field::text('form.suburb_label', 'Suburb label'),
            Field::text('form.submit', 'Submit label'),
        ];
    }

    public function seed(): array
    {
        $rows = [
            ['key' => 'intro.kicker', 'sort_order' => 0, 'value' => 'Trade quote'],
            ['key' => 'intro.title', 'sort_order' => 0, 'value' => 'Get A Quote For Your Project'],
            ['key' => 'intro.body', 'sort_order' => 0, 'value' => 'Looking for tailored lighting solutions for your next project? Whether you\'re an architect, builder, designer or wholesaler, our team is here to help. Simply tell us what you need — and we\'ll provide a fast, accurate quote with expert support every step of the way.'],
            ['key' => 'drawer.trigger_label', 'sort_order' => 0, 'value' => 'Quote list'],
            ['key' => 'drawer.title', 'sort_order' => 0, 'value' => 'Quote List'],
            ['key' => 'drawer.close', 'sort_order' => 0, 'value' => 'Close quote list'],
            ['key' => 'drawer.submit', 'sort_order' => 0, 'value' => 'Request a Quote'],
            ['key' => 'list.title', 'sort_order' => 0, 'value' => 'Products in this quote'],
            ['key' => 'form.title', 'sort_order' => 0, 'value' => 'Request details'],
            ['key' => 'form.contact_tag', 'sort_order' => 0, 'value' => '1. Contact information'],
            ['key' => 'form.name_label', 'sort_order' => 0, 'value' => 'First Name*'],
            ['key' => 'form.email_label', 'sort_order' => 0, 'value' => 'Email*'],
            ['key' => 'form.phone_label', 'sort_order' => 0, 'value' => 'Mobile*'],
            ['key' => 'form.project_tag', 'sort_order' => 0, 'value' => '2. Project information'],
            ['key' => 'form.description_label', 'sort_order' => 0, 'value' => 'Short Description'],
            ['key' => 'form.products_label', 'sort_order' => 0, 'value' => 'Products Needed + Quantities'],
            ['key' => 'form.role_tag', 'sort_order' => 0, 'value' => '3. Which describes you best'],
            ['key' => 'form.method_tag', 'sort_order' => 0, 'value' => '4. Preferred contact method'],
            ['key' => 'form.supplier_tag', 'sort_order' => 0, 'value' => '5. Preferred retail supplier or suburb'],
            ['key' => 'form.suburb_label', 'sort_order' => 0, 'value' => 'Suburb or Retailer'],
            ['key' => 'form.submit', 'sort_order' => 0, 'value' => 'Get a Custom Quote'],
        ];

        foreach ([
            'I’m a Builder',
            'I’m an Architect',
            'I’m Renovating my Home',
            'I own a Lighting Store',
            'I’m a Consultant',
            'I’m a Distributor',
            'I’m a Contractor',
        ] as $order => $role) {
            $rows[] = ['key' => 'form.role.label', 'sort_order' => $order, 'value' => $role];
        }

        foreach (['Phone', 'Email'] as $order => $method) {
            $rows[] = ['key' => 'form.method.label', 'sort_order' => $order, 'value' => $method];
        }

        return $rows;
    }
}
