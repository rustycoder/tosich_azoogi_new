@extends('layouts.dashboard')

@section('title', 'Edit ' . $template->name)

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.email-templates.index') }}">Email Notifications</a>
        <span>/</span>
        <span>{{ $template->name }}</span>
    </div>
    <div class="dash-head-title">
        <h1>{{ $template->name }}</h1>
        <div class="dash-head-actions">
            <form id="email-template-reset-form" method="post" action="{{ route('dashboard.email-templates.reset', $template) }}">
                @csrf
                <button type="button" class="btn danger" data-reset-open>Reset</button>
            </form>
            <button class="btn primary" type="submit" form="email-template-form">Save</button>
        </div>
    </div>
    <p class="dash-lead">{{ $template->description }}</p>
</div>

@if (session('status'))
    <div class="dash-status" style="background:#e6f4ea;color:#137333;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #ceead6;">
        {{ session('status') }}
    </div>
@endif

<div class="email-template-layout">
    <!-- Left Column: Settings & HTML Editor -->
    <div style="display:flex;flex-direction:column;gap:20px;">
        <form id="email-template-form" class="dash-form" method="post" action="{{ route('dashboard.email-templates.update', $template) }}">
            @csrf
            @method('put')

            <div class="dash-card">
                <h2>Notification Routing</h2>
                <div class="dash-field">
                    <label for="recipient_emails">Recipient Email(s)</label>
                    <input id="recipient_emails" name="recipient_emails" value="{{ old('recipient_emails', $template->recipient_emails) }}" required placeholder="e.g. adrian@azoogi.com, sales@azoogi.com">
                    <small style="color:var(--muted);font-size:12px;margin-top:4px;display:block;">Separate multiple recipients with commas or spaces. Sent from <code>{{ config('mail.from.address') }}</code>.</small>
                    @error('recipient_emails')<p class="login-error">{{ $message }}</p>@enderror
                </div>

                <div class="dash-field">
                    <label for="subject">Email Subject</label>
                    <input id="subject" name="subject" value="{{ old('subject', $template->subject) }}" required>
                    <small style="color:var(--muted);font-size:12px;margin-top:4px;display:block;">You can use dynamic tokens such as <code>@{{name}}</code> or <code>@{{project}}</code> in the subject.</small>
                    @error('subject')<p class="login-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="dash-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h2 style="margin:0;">HTML Template Body</h2>
                    <button type="button" class="btn" id="btn-refresh-preview" style="font-size:12px;padding:6px 12px;">Refresh Preview</button>
                </div>

                @if (!empty($template->available_tokens))
                    <div style="margin-bottom:14px;">
                        <span style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px;">Click token to insert:</span>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;" id="token-chips">
                            @foreach ($template->available_tokens as $token)
                                @php $tokenTag = '{{' . $token . '}}'; @endphp
                                <button type="button" class="token-chip" data-token="{{ $tokenTag }}" style="background:var(--rgba-hover);border:1px solid var(--rgba-line);color:var(--ink);padding:3px 8px;border-radius:4px;font-size:11px;font-family:monospace;cursor:pointer;transition:all 0.15s;" title="Click to insert {{ $tokenTag }}">
                                    + {{ $tokenTag }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="dash-field" style="margin-bottom:0;">
                    <textarea id="body_html" name="body_html" rows="18" style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:13px;line-height:1.5;white-space:pre;tab-size:2;" required>{{ old('body_html', $template->body_html) }}</textarea>
                    @error('body_html')<p class="login-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </form>

        <!-- Test Email Sender -->
        <div class="dash-card">
            <h2>Send Test Delivery</h2>
            <p style="font-size:13px;color:var(--muted);margin-top:-4px;margin-bottom:12px;">Dispatch a real rendered sample email via configured SMTP to verify client formatting and inbox delivery.</p>
            <div class="dash-form">
                <div class="dash-field" style="margin-bottom:0;">
                    <label for="test_email">Destination email</label>
                    <div class="email-test-row">
                        <input type="email" id="test_email" value="{{ auth()->user()->email }}" placeholder="Enter destination email address">
                        <button type="button" class="btn primary" id="btn-send-test">Send Test</button>
                    </div>
                </div>
            </div>
            <div id="test-result" style="display:none;margin-top:12px;padding:10px 14px;border-radius:6px;font-size:13px;"></div>
        </div>
    </div>

    <!-- Right Column: Live Email Preview -->
    <div style="position:sticky;top:20px;display:flex;flex-direction:column;gap:12px;">
        <div class="dash-card" style="padding:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--rgba-line);padding-bottom:10px;margin-bottom:12px;">
                <div style="font-size:13px;font-weight:600;color:var(--ink);">
                    Subject Preview: <span id="preview-subject-text" style="font-weight:normal;color:var(--muted);">{{ $preview['subject'] }}</span>
                </div>
                <span style="font-size:11px;background:var(--rgba-hover);padding:3px 8px;border-radius:12px;color:var(--muted);">Sample Data</span>
            </div>

            <div style="border:1px solid var(--rgba-line);border-radius:8px;overflow:hidden;background:#f4f4f5;">
                <iframe id="preview-frame" style="width:100%;height:680px;border:none;display:block;" title="Email Live Preview"></iframe>
            </div>
        </div>
    </div>
</div>

<dialog class="dash-enquiry-dialog dash-confirm-dialog" data-reset-dialog aria-labelledby="dash-reset-dialog-title">
    <div class="dash-enquiry-dialog-panel">
        <header class="dash-enquiry-dialog-head">
            <div class="dash-enquiry-dialog-heading">
                <h2 id="dash-reset-dialog-title">Reset this template?</h2>
            </div>
            <button type="button" class="dash-drawer-close" data-reset-dialog-close aria-label="Close">×</button>
        </header>
        <div class="dash-enquiry-dialog-body">
            <p>Custom modifications will be replaced with the default template.</p>
        </div>
        <footer class="dash-enquiry-dialog-foot">
            <button type="button" class="btn" data-reset-dialog-close>Cancel</button>
            <button type="submit" class="btn danger" form="email-template-reset-form">Reset</button>
        </footer>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var previewFrame = document.getElementById('preview-frame');
    var bodyHtmlInput = document.getElementById('body_html');
    var subjectInput = document.getElementById('subject');
    var previewSubjectText = document.getElementById('preview-subject-text');
    var btnRefresh = document.getElementById('btn-refresh-preview');
    var btnSendTest = document.getElementById('btn-send-test');
    var testEmailInput = document.getElementById('test_email');
    var testResult = document.getElementById('test-result');
    var tokenChips = document.querySelectorAll('.token-chip');
    var resetDialog = document.querySelector('[data-reset-dialog]');
    var resetOpen = document.querySelector('[data-reset-open]');
    var debounceTimer = null;

    function closeResetDialog() {
        if (resetDialog && resetDialog.open) {
            resetDialog.close();
        }
    }

    if (resetOpen && resetDialog) {
        resetOpen.addEventListener('click', function () {
            resetDialog.showModal();
        });

        resetDialog.addEventListener('click', function (event) {
            if (event.target === resetDialog) {
                closeResetDialog();
            }
        });

        resetDialog.querySelectorAll('[data-reset-dialog-close]').forEach(function (button) {
            button.addEventListener('click', closeResetDialog);
        });
    }

    updatePreviewFrame(@json($preview['body_html']));

    function updatePreviewFrame(html) {
        if (!previewFrame) {
            return;
        }
        var doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
        doc.open();
        doc.write(html);
        doc.close();
    }

    function refreshPreviewAjax() {
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route('dashboard.email-templates.preview', $template) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                body_html: bodyHtmlInput ? bodyHtmlInput.value : '',
                subject: subjectInput ? subjectInput.value : ''
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.body_html) {
                updatePreviewFrame(data.body_html);
            }
            if (data.subject && previewSubjectText) {
                previewSubjectText.textContent = data.subject;
            }
        })
        .catch(function (err) {
            console.error('Preview update error:', err);
        });
    }

    function schedulePreviewRefresh() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(refreshPreviewAjax, 600);
    }

    if (btnRefresh) {
        btnRefresh.addEventListener('click', refreshPreviewAjax);
    }

    if (bodyHtmlInput) {
        bodyHtmlInput.addEventListener('input', schedulePreviewRefresh);
    }

    if (subjectInput) {
        subjectInput.addEventListener('input', schedulePreviewRefresh);
    }

    tokenChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var token = this.getAttribute('data-token');
            if (!bodyHtmlInput) {
                return;
            }

            var start = bodyHtmlInput.selectionStart;
            var end = bodyHtmlInput.selectionEnd;
            var text = bodyHtmlInput.value;

            bodyHtmlInput.value = text.substring(0, start) + token + text.substring(end);
            bodyHtmlInput.focus();
            bodyHtmlInput.selectionStart = bodyHtmlInput.selectionEnd = start + token.length;

            refreshPreviewAjax();
        });
    });

    if (btnSendTest) {
        btnSendTest.addEventListener('click', function () {
            var email = testEmailInput.value.trim();
            if (!email) {
                alert('Please enter an email address for test delivery.');
                return;
            }

            btnSendTest.disabled = true;
            btnSendTest.textContent = 'Sending...';
            testResult.style.display = 'none';

            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('{{ route('dashboard.email-templates.test', $template) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    test_email: email
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btnSendTest.disabled = false;
                btnSendTest.textContent = 'Send Test';
                testResult.style.display = 'block';

                if (data.success) {
                    testResult.style.background = '#e6f4ea';
                    testResult.style.color = '#137333';
                    testResult.style.border = '1px solid #ceead6';
                    testResult.textContent = data.message;
                } else {
                    testResult.style.background = '#fce8e6';
                    testResult.style.color = '#c5221f';
                    testResult.style.border = '1px solid #fad2cf';
                    testResult.textContent = data.message || 'Failed to send test email.';
                }
            })
            .catch(function () {
                btnSendTest.disabled = false;
                btnSendTest.textContent = 'Send Test';
                testResult.style.display = 'block';
                testResult.style.background = '#fce8e6';
                testResult.style.color = '#c5221f';
                testResult.style.border = '1px solid #fad2cf';
                testResult.textContent = 'Network or server error sending test email.';
            });
        });
    }
});
</script>
@endpush

