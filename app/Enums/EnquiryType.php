<?php

namespace App\Enums;

use InvalidArgumentException;

enum EnquiryType: string
{
    case Quote = 'quote';
    case Product = 'product';
    case Contact = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::Quote => 'Quote',
            self::Product => 'Product',
            self::Contact => 'Contact',
        };
    }

    public function menuLabel(): string
    {
        return match ($this) {
            self::Quote => 'Quote Enquiries',
            self::Product => 'Product Enquiries',
            self::Contact => 'Contact Enquiries',
        };
    }

    public function menuSlug(): string
    {
        return match ($this) {
            self::Quote => 'quote',
            self::Product => 'products',
            self::Contact => 'contacts',
        };
    }

    public static function fromMenuSlug(string $slug): self
    {
        return match ($slug) {
            'quote' => self::Quote,
            'products' => self::Product,
            'contacts' => self::Contact,
            default => throw new InvalidArgumentException("Unknown enquiry menu [{$slug}]."),
        };
    }

    public function contentResource(): ContentResource
    {
        return match ($this) {
            self::Quote => ContentResource::QuoteEnquiries,
            self::Product => ContentResource::ProductEnquiries,
            self::Contact => ContentResource::ContactEnquiry,
        };
    }
}
