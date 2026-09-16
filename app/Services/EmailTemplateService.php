<?php

namespace App\Services;

use App\Enums\EnquiryType;
use App\Mail\DynamicNotificationMail;
use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\ProductDatasheetExport;
use App\Services\Contracts\IEmailTemplateService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailTemplateService implements IEmailTemplateService
{
    public const KEY_CONTACT = 'contact_enquiry';

    public const KEY_PRODUCT = 'product_enquiry';

    public const KEY_QUOTE = 'quote_request';

    public const KEY_DATASHEET = 'datasheet_download';

    /**
     * @return Collection<int, EmailTemplate>
     */
    public function all(): Collection
    {
        $this->ensureDefaultTemplatesExist();

        return EmailTemplate::query()->orderBy('id')->get();
    }

    public function findByKey(string $key): ?EmailTemplate
    {
        return EmailTemplate::query()->where('key', $key)->first();
    }

    public function getOrCreate(string $key): EmailTemplate
    {
        $template = $this->findByKey($key);

        if ($template !== null) {
            return $template;
        }

        $defaults = $this->defaultTemplates();

        if (! isset($defaults[$key])) {
            throw new \InvalidArgumentException("Unknown email template key [{$key}].");
        }

        return EmailTemplate::query()->create($defaults[$key]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(EmailTemplate $template, array $data): EmailTemplate
    {
        $template->update([
            'recipient_emails' => trim((string) ($data['recipient_emails'] ?? $template->recipient_emails)),
            'subject' => trim((string) ($data['subject'] ?? $template->subject)),
            'body_html' => (string) ($data['body_html'] ?? $template->body_html),
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $template->is_active,
        ]);

        return $template;
    }

    public function resetToDefault(EmailTemplate $template): EmailTemplate
    {
        $defaults = $this->defaultTemplates();

        if (isset($defaults[$template->key])) {
            $default = $defaults[$template->key];
            $template->update([
                'name' => $default['name'],
                'description' => $default['description'],
                'subject' => $default['subject'],
                'body_html' => $default['body_html'],
                'available_tokens' => $default['available_tokens'],
            ]);
        }

        return $template;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{subject: string, body_html: string, recipients: list<string>}
     */
    public function preview(EmailTemplate $template, array $data = []): array
    {
        $sampleData = array_merge($this->sampleDataFor($template->key), $data);

        return [
            'subject' => $template->renderSubject($sampleData),
            'body_html' => $template->renderBody($sampleData),
            'recipients' => $template->recipientList(),
        ];
    }

    public function sendTest(EmailTemplate $template, string $recipientEmail): void
    {
        $preview = $this->preview($template);

        Mail::to($recipientEmail)->send(new DynamicNotificationMail(
            renderedSubject: '[TEST] '.$preview['subject'],
            renderedHtml: $preview['body_html'],
            replyToEmail: null,
            replyToName: 'Azoogi Test Mailer',
        ));
    }

    public function sendEnquiryNotification(Enquiry $enquiry): bool
    {
        $key = match ($enquiry->type) {
            EnquiryType::Contact => self::KEY_CONTACT,
            EnquiryType::Product => self::KEY_PRODUCT,
            EnquiryType::Quote => self::KEY_QUOTE,
        };

        try {
            $template = $this->getOrCreate($key);

            if (! $template->is_active) {
                return false;
            }

            $recipients = $template->recipientList();

            if (empty($recipients)) {
                return false;
            }

            $data = $this->buildEnquiryTokens($enquiry);
            $subject = $template->renderSubject($data);
            $body = $template->renderBody($data);

            Mail::to($recipients)->send(new DynamicNotificationMail(
                renderedSubject: $subject,
                renderedHtml: $body,
                replyToEmail: $enquiry->email,
                replyToName: $enquiry->name,
            ));

            return true;
        } catch (Throwable $e) {
            Log::error("Failed to send enquiry notification [{$enquiry->id}]: ".$e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }

    public function sendDatasheetNotification(ProductDatasheetExport $export): bool
    {
        try {
            $template = $this->getOrCreate(self::KEY_DATASHEET);

            if (! $template->is_active) {
                return false;
            }

            $recipients = $template->recipientList();

            if (empty($recipients)) {
                return false;
            }

            $data = $this->buildDatasheetTokens($export);
            $subject = $template->renderSubject($data);
            $body = $template->renderBody($data);

            Mail::to($recipients)->send(new DynamicNotificationMail(
                renderedSubject: $subject,
                renderedHtml: $body,
                replyToEmail: null,
                replyToName: $export->person_name ?: 'Azoogi Visitor',
            ));

            return true;
        } catch (Throwable $e) {
            Log::error("Failed to send datasheet notification [{$export->id}]: ".$e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEnquiryTokens(Enquiry $enquiry): array
    {
        $payload = is_array($enquiry->payload) ? $enquiry->payload : [];
        $tz = config('app.timezone', 'UTC');
        $date = $enquiry->created_at ? $enquiry->created_at->timezone($tz)->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A');

        $productsTable = $this->formatProductsTable($payload['products'] ?? []);

        return [
            'name' => (string) ($enquiry->name ?: 'N/A'),
            'email' => (string) ($enquiry->email ?: 'N/A'),
            'phone' => (string) ($enquiry->phone ?: 'N/A'),
            'company' => (string) ($enquiry->company ?: 'N/A'),
            'message' => nl2br(e((string) ($enquiry->message ?: 'None provided'))),
            'project' => (string) ($payload['project'] ?? 'N/A'),
            'specification' => nl2br(e((string) ($payload['specification'] ?? 'None specified'))),
            'role' => (string) ($payload['role'] ?? 'N/A'),
            'contact_method' => (string) ($payload['method'] ?? 'Email'),
            'suburb' => (string) ($payload['suburb'] ?? 'N/A'),
            'description' => nl2br(e((string) ($payload['description'] ?? 'None provided'))),
            'products_table' => $productsTable,
            'ip_address' => (string) ($enquiry->ip_address ?: 'Unknown'),
            'country' => (string) ($enquiry->country ?: 'Unknown'),
            'device' => device_name($enquiry->user_agent) ?: 'Unknown',
            'submitted_at' => $date,
            'dashboard_url' => url('/dashboard/enquiries/'.$enquiry->type->menuSlug()),
            'site_url' => config('app.url'),
            'app_name' => config('app.name', 'Azoogi'),
            'logo_url' => $this->emailLogoUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDatasheetTokens(ProductDatasheetExport $export): array
    {
        $export->loadMissing('product');
        $tz = config('app.timezone', 'UTC');
        $date = $export->created_at ? $export->created_at->timezone($tz)->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A');

        $configuration = is_array($export->configuration) ? $export->configuration : [];
        $specsTable = $this->formatSpecsTable($configuration['specifications'] ?? []);

        return [
            'person_name' => (string) ($export->person_name ?: 'Anonymous Visitor'),
            'project_name' => (string) ($export->project_name ?: 'General Enquiry'),
            'product_name' => (string) ($export->product_name ?: 'Custom Product'),
            'product_code' => (string) ($export->product_code ?: 'N/A'),
            'specifications_table' => $specsTable,
            'datasheet_url' => route('products.datasheet.show', ['export' => $export, 'print' => 1]),
            'ip_address' => (string) ($export->ip_address ?: 'Unknown'),
            'country' => (string) ($export->country ?: 'Unknown'),
            'device' => device_name($export->user_agent) ?: 'Unknown',
            'downloaded_at' => $date,
            'dashboard_url' => url('/dashboard/datasheets/exports'),
            'site_url' => config('app.url'),
            'app_name' => config('app.name', 'Azoogi'),
            'logo_url' => $this->emailLogoUrl(),
        ];
    }

    private function formatProductsTable(mixed $products): string
    {
        if (is_string($products)) {
            $trimmed = trim($products);

            if ($trimmed === '') {
                return '<p style="color:#666;font-style:italic;margin:8px 0;">No specific products selected.</p>';
            }

            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                $products = $decoded;
            } else {
                return '<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:12px 16px;margin:12px 0;font-size:14px;color:#111;line-height:1.6;">'.nl2br(e($trimmed)).'</div>';
            }
        }

        if (! is_array($products) || empty($products)) {
            return '<p style="color:#666;font-style:italic;margin:8px 0;">No specific products selected.</p>';
        }

        $html = '<table style="width:100%;border-collapse:collapse;margin:12px 0;font-size:14px;border:1px solid #e5e7eb;">';
        $html .= '<thead><tr style="background:#f3f4f6;text-align:left;">';
        $html .= '<th style="padding:10px 14px;border-bottom:1px solid #e5e7eb;color:#111;">Product</th>';
        $html .= '<th style="padding:10px 14px;border-bottom:1px solid #e5e7eb;color:#111;">Code / SKU</th>';
        $html .= '<th style="padding:10px 14px;border-bottom:1px solid #e5e7eb;color:#111;text-align:center;">Qty</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($products as $item) {
            if (is_string($item)) {
                $html .= '<tr style="border-bottom:1px solid #f0f0f0;">';
                $html .= '<td colspan="3" style="padding:10px 14px;color:#111;">'.e($item).'</td>';
                $html .= '</tr>';

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $name = e((string) ($item['name'] ?? $item['title'] ?? 'Product'));
            $code = e((string) ($item['code'] ?? $item['sku'] ?? $item['model'] ?? '-'));
            $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);

            $html .= '<tr style="border-bottom:1px solid #f0f0f0;">';
            $html .= '<td style="padding:10px 14px;color:#111;font-weight:500;">'.$name.'</td>';
            $html .= '<td style="padding:10px 14px;color:#666;">'.$code.'</td>';
            $html .= '<td style="padding:10px 14px;color:#111;text-align:center;font-weight:600;">'.$qty.'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function formatSpecsTable(mixed $specs): string
    {
        if (! is_array($specs) || empty($specs)) {
            return '<p style="color:#666;font-style:italic;margin:8px 0;">No custom specifications configured.</p>';
        }

        $html = '<table style="width:100%;border-collapse:collapse;margin:12px 0;font-size:13px;border:1px solid #e5e7eb;">';
        $html .= '<tbody>';

        foreach ($specs as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = e((string) ($row['label'] ?? ''));
            $value = e((string) ($row['value'] ?? ''));

            if ($label === '' || $value === '') {
                continue;
            }

            $html .= '<tr style="border-bottom:1px solid #f0f0f0;">';
            $html .= '<td style="padding:8px 12px;background:#f9fafb;color:#4b5563;font-weight:600;width:35%;">'.$label.'</td>';
            $html .= '<td style="padding:8px 12px;color:#111827;">'.$value.'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function ensureDefaultTemplatesExist(): void
    {
        foreach ($this->defaultTemplates() as $key => $attributes) {
            EmailTemplate::query()->firstOrCreate(['key' => $key], $attributes);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function defaultTemplates(): array
    {
        return [
            self::KEY_CONTACT => [
                'key' => self::KEY_CONTACT,
                'name' => 'Contact Enquiry Notification',
                'description' => 'Sent to administration when a visitor submits the website Contact form.',
                'recipient_emails' => 'adrian@azoogi.com',
                'subject' => 'New Contact Enquiry from {{name}}',
                'available_tokens' => [
                    'name', 'email', 'company', 'message',
                    'ip_address', 'country', 'device', 'submitted_at',
                    'dashboard_url', 'site_url', 'app_name', 'logo_url',
                ],
                'body_html' => $this->wrapLayout(
                    title: 'New Contact Enquiry',
                    badge: 'Contact Form',
                    content: '
                    <p style="font-size:15px;color:#333;margin-top:0;margin-bottom:20px;">You have received a new contact inquiry submitted via the Azoogi website.</p>
                    
                    <table style="width:100%;border-collapse:collapse;margin-bottom:24px;font-size:14px;">
                        <tr><td style="padding:9px 0;color:#777;width:120px;border-bottom:1px solid #eee;">Name</td><td style="padding:9px 0;color:#111;font-weight:600;border-bottom:1px solid #eee;">{{name}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Email</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;"><a href="mailto:{{email}}" style="color:#2d7a1e;text-decoration:none;">{{email}}</a></td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Company</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;">{{company}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;vertical-align:top;border-bottom:1px solid #eee;">Message</td><td style="padding:9px 0;color:#111;line-height:1.6;border-bottom:1px solid #eee;">{{message}}</td></tr>
                    </table>

                    <div style="background:#f8f9fa;border-radius:6px;padding:14px 18px;margin-bottom:24px;font-size:12px;color:#666;">
                        <strong>Visitor Info:</strong> {{ip_address}} ({{country}}) &bull; {{device}} &bull; {{submitted_at}}
                    </div>

                    <div style="text-align:center;margin-top:28px;">
                        <a href="{{dashboard_url}}" style="display:inline-block;background:#0b0b0b;color:#ffffff;padding:12px 28px;font-size:14px;font-weight:600;border-radius:6px;text-decoration:none;letter-spacing:0.5px;">View in Dashboard &rarr;</a>
                    </div>
                    '
                ),
                'is_active' => true,
            ],

            self::KEY_PRODUCT => [
                'key' => self::KEY_PRODUCT,
                'name' => 'Product Enquiry Notification',
                'description' => 'Sent to sales team when a visitor requests information on a specific product.',
                'recipient_emails' => 'sales@azoogi.com',
                'subject' => 'New Product Enquiry from {{name}} - {{project}}',
                'available_tokens' => [
                    'name', 'email', 'company', 'project', 'specification', 'message',
                    'ip_address', 'country', 'device', 'submitted_at',
                    'dashboard_url', 'site_url', 'app_name', 'logo_url',
                ],
                'body_html' => $this->wrapLayout(
                    title: 'New Product Enquiry',
                    badge: 'Product Specification',
                    content: '
                    <p style="font-size:15px;color:#333;margin-top:0;margin-bottom:20px;">A new product inquiry was submitted via the product detail page.</p>
                    
                    <table style="width:100%;border-collapse:collapse;margin-bottom:24px;font-size:14px;">
                        <tr><td style="padding:9px 0;color:#777;width:120px;border-bottom:1px solid #eee;">Name</td><td style="padding:9px 0;color:#111;font-weight:600;border-bottom:1px solid #eee;">{{name}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Email</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;"><a href="mailto:{{email}}" style="color:#2d7a1e;text-decoration:none;">{{email}}</a></td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Company</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;">{{company}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Project</td><td style="padding:9px 0;color:#111;font-weight:600;border-bottom:1px solid #eee;">{{project}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;vertical-align:top;border-bottom:1px solid #eee;">Specification</td><td style="padding:9px 0;color:#111;line-height:1.6;border-bottom:1px solid #eee;">{{specification}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;vertical-align:top;border-bottom:1px solid #eee;">Message</td><td style="padding:9px 0;color:#111;line-height:1.6;border-bottom:1px solid #eee;">{{message}}</td></tr>
                    </table>

                    <div style="background:#f8f9fa;border-radius:6px;padding:14px 18px;margin-bottom:24px;font-size:12px;color:#666;">
                        <strong>Visitor Info:</strong> {{ip_address}} ({{country}}) &bull; {{device}} &bull; {{submitted_at}}
                    </div>

                    <div style="text-align:center;margin-top:28px;">
                        <a href="{{dashboard_url}}" style="display:inline-block;background:#0b0b0b;color:#ffffff;padding:12px 28px;font-size:14px;font-weight:600;border-radius:6px;text-decoration:none;letter-spacing:0.5px;">View in Dashboard &rarr;</a>
                    </div>
                    '
                ),
                'is_active' => true,
            ],

            self::KEY_QUOTE => [
                'key' => self::KEY_QUOTE,
                'name' => 'Quote Request Notification',
                'description' => 'Sent to sales team when a client requests a quote with their bill of materials.',
                'recipient_emails' => 'sales@azoogi.com',
                'subject' => 'New Quote Request from {{name}}',
                'available_tokens' => [
                    'name', 'email', 'phone', 'role', 'contact_method', 'suburb',
                    'products_table', 'description',
                    'ip_address', 'country', 'device', 'submitted_at',
                    'dashboard_url', 'site_url', 'app_name', 'logo_url',
                ],
                'body_html' => $this->wrapLayout(
                    title: 'New Quote Request',
                    badge: 'Quote Bill of Materials',
                    content: '
                    <p style="font-size:15px;color:#333;margin-top:0;margin-bottom:20px;">A customer has submitted a request for quote on the Azoogi website.</p>
                    
                    <table style="width:100%;border-collapse:collapse;margin-bottom:20px;font-size:14px;">
                        <tr><td style="padding:9px 0;color:#777;width:130px;border-bottom:1px solid #eee;">Name</td><td style="padding:9px 0;color:#111;font-weight:600;border-bottom:1px solid #eee;">{{name}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Email</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;"><a href="mailto:{{email}}" style="color:#2d7a1e;text-decoration:none;">{{email}}</a></td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Phone</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;"><a href="tel:{{phone}}" style="color:#2d7a1e;text-decoration:none;">{{phone}}</a></td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Role / Industry</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;">{{role}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Contact Method</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;">{{contact_method}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Suburb / Retailer</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;">{{suburb}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;vertical-align:top;border-bottom:1px solid #eee;">Notes / Description</td><td style="padding:9px 0;color:#111;line-height:1.6;border-bottom:1px solid #eee;">{{description}}</td></tr>
                    </table>

                    <h3 style="font-size:15px;color:#111;margin:24px 0 8px 0;padding-bottom:6px;border-bottom:2px solid #67d04e;display:inline-block;">Requested Products</h3>
                    {{products_table}}

                    <div style="background:#f8f9fa;border-radius:6px;padding:14px 18px;margin:24px 0;font-size:12px;color:#666;">
                        <strong>Visitor Info:</strong> {{ip_address}} ({{country}}) &bull; {{device}} &bull; {{submitted_at}}
                    </div>

                    <div style="text-align:center;margin-top:28px;">
                        <a href="{{dashboard_url}}" style="display:inline-block;background:#0b0b0b;color:#ffffff;padding:12px 28px;font-size:14px;font-weight:600;border-radius:6px;text-decoration:none;letter-spacing:0.5px;">Manage Enquiries &rarr;</a>
                    </div>
                    '
                ),
                'is_active' => true,
            ],

            self::KEY_DATASHEET => [
                'key' => self::KEY_DATASHEET,
                'name' => 'Custom Datasheet Download Notification',
                'description' => 'Sent to sales team when an architect or engineer customizes and exports a product datasheet.',
                'recipient_emails' => 'sales@azoogi.com',
                'subject' => 'Custom Datasheet Downloaded: {{product_name}}',
                'available_tokens' => [
                    'person_name', 'project_name', 'product_name', 'product_code',
                    'specifications_table', 'datasheet_url',
                    'ip_address', 'country', 'device', 'downloaded_at',
                    'dashboard_url', 'site_url', 'app_name', 'logo_url',
                ],
                'body_html' => $this->wrapLayout(
                    title: 'Custom Datasheet Generated',
                    badge: 'Datasheet Export',
                    content: '
                    <p style="font-size:15px;color:#333;margin-top:0;margin-bottom:20px;">A visitor has generated and downloaded a customized PDF datasheet for a project specification.</p>
                    
                    <table style="width:100%;border-collapse:collapse;margin-bottom:20px;font-size:14px;">
                        <tr><td style="padding:9px 0;color:#777;width:130px;border-bottom:1px solid #eee;">Prepared For</td><td style="padding:9px 0;color:#111;font-weight:600;border-bottom:1px solid #eee;">{{person_name}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Project Name</td><td style="padding:9px 0;color:#111;font-weight:600;border-bottom:1px solid #eee;">{{project_name}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Product Name</td><td style="padding:9px 0;color:#111;border-bottom:1px solid #eee;">{{product_name}}</td></tr>
                        <tr><td style="padding:9px 0;color:#777;border-bottom:1px solid #eee;">Product Code</td><td style="padding:9px 0;color:#111;font-family:monospace;font-weight:600;border-bottom:1px solid #eee;">{{product_code}}</td></tr>
                    </table>

                    <h3 style="font-size:15px;color:#111;margin:24px 0 8px 0;padding-bottom:6px;border-bottom:2px solid #67d04e;display:inline-block;">Configured Specifications</h3>
                    {{specifications_table}}

                    <div style="background:#f8f9fa;border-radius:6px;padding:14px 18px;margin:24px 0;font-size:12px;color:#666;">
                        <strong>Visitor Info:</strong> {{ip_address}} ({{country}}) &bull; {{device}} &bull; {{downloaded_at}}
                    </div>

                    <div style="text-align:center;margin-top:28px;">
                        <a href="{{datasheet_url}}" target="_blank" style="display:inline-block;background:#2d7a1e;color:#ffffff;padding:12px 28px;font-size:14px;font-weight:600;border-radius:6px;text-decoration:none;margin-right:10px;">View Exported Datasheet &rarr;</a>
                        <a href="{{dashboard_url}}" style="display:inline-block;background:#0b0b0b;color:#ffffff;padding:12px 28px;font-size:14px;font-weight:600;border-radius:6px;text-decoration:none;">Dashboard Exports</a>
                    </div>
                    '
                ),
                'is_active' => true,
            ],
        ];
    }

    private function wrapLayout(string $title, string $badge, string $content): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>'.htmlspecialchars($title).'</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;">
        <tr>
            <td align="center" style="padding:30px 15px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);border:1px solid #e4e4e7;">
                    <!-- Brand Header -->
                    <tr>
                        <td style="background-color:#0b0b0b;padding:22px 32px;text-align:left;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td valign="middle">
                                        <a href="{{site_url}}" target="_blank" style="text-decoration:none;display:inline-block;">
                                            <img src="{{logo_url}}" alt="Azoogi" height="26" style="height:26px;max-height:30px;width:auto;display:block;border:0;" border="0">
                                        </a>
                                    </td>
                                    <td align="right" valign="middle">
                                        <span style="display:inline-block;background:rgba(255,255,255,0.12);color:#67d04e;padding:5px 12px;border-radius:20px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">'.htmlspecialchars($badge).'</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Main Body -->
                    <tr>
                        <td style="padding:32px 32px 28px 32px;background:#ffffff;">
                            <h1 style="font-size:20px;color:#111111;margin-top:0;margin-bottom:16px;font-weight:700;">'.htmlspecialchars($title).'</h1>
                            '.trim($content).'
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb;padding:20px 32px;border-top:1px solid #f0f0f0;text-align:center;font-size:12px;color:#9ca3af;">
                            <p style="margin:0 0 6px 0;">This is an automated notification from the <strong>{{app_name}}</strong> website system.</p>
                            <p style="margin:0;">&copy; '.date('Y').' Azoogi Pty Ltd. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleDataFor(string $key): array
    {
        return [
            'name' => 'Adrian Vance',
            'person_name' => 'Adrian Vance',
            'email' => 'client@example.com',
            'phone' => '0412 345 678',
            'company' => 'Vance Architectural Studio',
            'project' => 'Bondi Waterfront Residence',
            'project_name' => 'Bondi Waterfront Residence',
            'specification' => "Product: Linear Recessed Profile\nFinish: Matt Black (RAL 9005)\nLength: 3200mm\nCCT: 3000K Warm White",
            'message' => 'Hi team, please advise lead time for 45 units delivered to Sydney site by end of month.',
            'role' => 'Architect / Lighting Designer',
            'contact_method' => 'Email',
            'suburb' => 'Bondi, NSW',
            'description' => 'Looking for quotes on linear strip profiles for a high-end residential build.',
            'product_name' => 'Slim 7 Surface Profile',
            'product_code' => 'AZG-SL7-BLK-30K',
            'products_table' => $this->formatProductsTable([
                ['name' => 'Slim 7 Surface Profile', 'code' => 'AZG-SL7-BLK', 'qty' => 12],
                ['name' => 'High Output COB Strip 24V 3000K', 'code' => 'AZG-COB-3K-24V', 'qty' => 30],
                ['name' => 'Casambi DALI Dimmer Driver 100W', 'code' => 'AZG-DRV-CAS-100', 'qty' => 6],
            ]),
            'specifications_table' => $this->formatSpecsTable([
                ['label' => 'Brand', 'value' => 'Azoogi'],
                ['label' => 'Dimensions', 'value' => '7mm x 15mm x 2000mm'],
                ['label' => 'Color Temperature', 'value' => '3000K Warm White'],
                ['label' => 'CRI', 'value' => 'CRI 90+'],
                ['label' => 'IP Rating', 'value' => 'IP65 Wet Rated'],
                ['label' => 'Control', 'value' => 'Casambi Bluetooth Mesh'],
            ]),
            'datasheet_url' => url('/products'),
            'ip_address' => '203.0.113.45',
            'country' => 'AU',
            'device' => 'Chrome on macOS',
            'submitted_at' => now()->format('d/m/Y h:i A'),
            'downloaded_at' => now()->format('d/m/Y h:i A'),
            'dashboard_url' => url('/dashboard/enquiries'),
            'site_url' => config('app.url'),
            'app_name' => config('app.name', 'Azoogi'),
            'logo_url' => $this->emailLogoUrl(),
        ];
    }

    private function emailLogoUrl(): string
    {
        $custom = trim((string) env('EMAIL_LOGO_URL', ''));

        if ($custom !== '') {
            return $custom;
        }

        return asset('assets/logo_white.png');
    }
}
