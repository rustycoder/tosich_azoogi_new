<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\Contracts\IEmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EmailTemplateController extends Controller
{
    public function __construct(private IEmailTemplateService $templates) {}

    public function index(): View
    {
        return view('dashboard.email-templates.index', [
            'templates' => $this->templates->all(),
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        $preview = $this->templates->preview($emailTemplate);

        return view('dashboard.email-templates.edit', [
            'template' => $emailTemplate,
            'preview' => $preview,
        ]);
    }

    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->templates->update($emailTemplate, $request->validated());

        return redirect()
            ->route('dashboard.email-templates.edit', $emailTemplate)
            ->with('status', 'Email template updated successfully.');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $html = (string) $request->input('body_html', $emailTemplate->body_html);
        $subject = (string) $request->input('subject', $emailTemplate->subject);

        // Temporarily set for preview calculation
        $cloned = clone $emailTemplate;
        $cloned->body_html = $html;
        $cloned->subject = $subject;

        $preview = $this->templates->preview($cloned);

        return response()->json([
            'subject' => $preview['subject'],
            'body_html' => $preview['body_html'],
        ]);
    }

    public function test(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $targetEmail = trim((string) $request->input('test_email', $request->user()?->email));

        if (! filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address provided for test delivery.',
            ], 422);
        }

        try {
            $this->templates->sendTest($emailTemplate, $targetEmail);

            return response()->json([
                'success' => true,
                'message' => "Test email successfully dispatched to {$targetEmail}.",
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: '.$e->getMessage(),
            ], 500);
        }
    }

    public function reset(EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->templates->resetToDefault($emailTemplate);

        return redirect()
            ->route('dashboard.email-templates.edit', $emailTemplate)
            ->with('status', 'Email template reset to default branded layout.');
    }

    public function toggleStatus(EmailTemplate $emailTemplate): JsonResponse
    {
        $emailTemplate->is_active = ! $emailTemplate->is_active;
        $emailTemplate->save();

        return response()->json([
            'on' => $emailTemplate->is_active,
            'label' => $emailTemplate->is_active ? 'Active' : 'Disabled',
            'message' => $emailTemplate->is_active ? 'Notification template activated.' : 'Notification template disabled.',
        ]);
    }
}
