<?php

namespace App\Services\Contracts;

use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\ProductDatasheetExport;
use Illuminate\Database\Eloquent\Collection;

interface IEmailTemplateService
{
    /**
     * @return Collection<int, EmailTemplate>
     */
    public function all(): Collection;

    public function findByKey(string $key): ?EmailTemplate;

    public function getOrCreate(string $key): EmailTemplate;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(EmailTemplate $template, array $data): EmailTemplate;

    public function resetToDefault(EmailTemplate $template): EmailTemplate;

    /**
     * @param  array<string, mixed>  $data
     * @return array{subject: string, body_html: string, recipients: list<string>}
     */
    public function preview(EmailTemplate $template, array $data = []): array;

    public function sendTest(EmailTemplate $template, string $recipientEmail): void;

    public function sendEnquiryNotification(Enquiry $enquiry): bool;

    public function sendDatasheetNotification(ProductDatasheetExport $export): bool;
}
