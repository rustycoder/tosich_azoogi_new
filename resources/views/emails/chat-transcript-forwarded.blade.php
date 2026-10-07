<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Consultation Transcript</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #111827; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 30px 15px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 680px; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0b0b0b; padding: 28px 32px; border-bottom: 3px solid #67d04e;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <div style="color: #67d04e; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 6px;">
                                            AI Consultation Handoff
                                        </div>
                                        <h1 style="color: #ffffff; font-size: 22px; font-weight: 700; margin: 0; line-height: 1.3;">
                                            Project Chat Transcript
                                        </h1>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <span style="background: rgba(103, 208, 78, 0.15); color: #67d04e; border: 1px solid rgba(103, 208, 78, 0.4); font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                                            Session #{{ $session->id }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 28px 32px;">

                            @if (!empty($notes))
                                <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; border-radius: 6px; padding: 14px 18px; margin-bottom: 24px;">
                                    <div style="font-weight: 700; font-size: 13px; color: #166534; margin-bottom: 4px;">Client Notes & Requests</div>
                                    <div style="font-size: 14px; color: #15803d; line-height: 1.5;">{{ $notes }}</div>
                                </div>
                            @endif

                            <!-- Client & Project Metadata Card -->
                            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;">
                                    Consultation Details
                                </div>
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13.5px;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #6b7280; width: 140px;">Contact Name:</td>
                                        <td style="padding: 5px 0; color: #111827; font-weight: 600;">{{ $session->lead_name ?: ($senderName ?: 'Not provided') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #6b7280;">Email Address:</td>
                                        <td style="padding: 5px 0; color: #111827; font-weight: 600;">
                                            @if ($session->lead_email || $senderEmail)
                                                <a href="mailto:{{ $session->lead_email ?: $senderEmail }}" style="color: #2d7a1e; text-decoration: none;">
                                                    {{ $session->lead_email ?: $senderEmail }}
                                                </a>
                                            @else
                                                <span style="color: #9ca3af;">Not provided</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($session->lead_phone)
                                        <tr>
                                            <td style="padding: 5px 0; color: #6b7280;">Phone:</td>
                                            <td style="padding: 5px 0; color: #111827; font-weight: 600;">{{ $session->lead_phone }}</td>
                                        </tr>
                                    @endif
                                    @if ($session->lead_company)
                                        <tr>
                                            <td style="padding: 5px 0; color: #6b7280;">Company / Firm:</td>
                                            <td style="padding: 5px 0; color: #111827; font-weight: 600;">{{ $session->lead_company }}</td>
                                        </tr>
                                    @endif
                                    @if ($session->project_name)
                                        <tr>
                                            <td style="padding: 5px 0; color: #6b7280;">Project Reference:</td>
                                            <td style="padding: 5px 0; color: #111827; font-weight: 600;">{{ $session->project_name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="padding: 5px 0; color: #6b7280;">Location / IP:</td>
                                        <td style="padding: 5px 0; color: #4b5563;">{{ $session->country ?: 'Australia' }} &bull; {{ $session->ip_address ?: 'Unknown IP' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #6b7280;">Session Date:</td>
                                        <td style="padding: 5px 0; color: #4b5563;">{{ $session->created_at ? $session->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>

                            @if (!empty($quoteItems) && count($quoteItems) > 0)
                                <!-- Shortlisted Fixtures Table -->
                                <div style="margin-bottom: 24px;">
                                    <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #374151; margin-bottom: 10px;">
                                        Shortlisted Fixtures / Quote Drawer Items ({{ count($quoteItems) }})
                                    </div>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #e5e7eb; font-size: 13px;">
                                        <thead>
                                            <tr style="background-color: #f3f4f6;">
                                                <th style="padding: 8px 12px; text-align: left; border-bottom: 1px solid #e5e7eb; color: #4b5563;">Product Name</th>
                                                <th style="padding: 8px 12px; text-align: left; border-bottom: 1px solid #e5e7eb; color: #4b5563;">Code / SKU</th>
                                                <th style="padding: 8px 12px; text-align: center; border-bottom: 1px solid #e5e7eb; color: #4b5563;">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($quoteItems as $item)
                                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                                    <td style="padding: 8px 12px; color: #111827; font-weight: 500;">{{ $item['name'] ?? 'Product' }}</td>
                                                    <td style="padding: 8px 12px; color: #6b7280; font-family: monospace;">{{ $item['sku'] ?? ($item['code'] ?? '-') }}</td>
                                                    <td style="padding: 8px 12px; text-align: center; color: #111827; font-weight: 600;">{{ $item['quantity'] ?? ($item['qty'] ?? 1) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- Transcript Stream -->
                            <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #374151; margin-bottom: 12px;">
                                Full Conversation Transcript
                            </div>

                            <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; background-color: #fafafa;">
                                @forelse ($messages as $msg)
                                    @php
                                        $isUser = is_object($msg) ? ($msg->sender === 'user') : (($msg['sender'] ?? '') === 'user');
                                        $content = is_object($msg) ? $msg->content : ($msg['content'] ?? '');
                                        $time = is_object($msg) && $msg->created_at ? $msg->created_at->format('h:i A') : '';
                                    @endphp
                                    <div style="margin-bottom: 14px; {{ $isUser ? 'text-align: right;' : 'text-align: left;' }}">
                                        <div style="font-size: 11px; font-weight: 600; color: {{ $isUser ? '#059669' : '#4b5563' }}; margin-bottom: 3px;">
                                            {{ $isUser ? ($session->lead_name ?: 'Visitor / Client') : 'Azoogi AI Assistant' }}
                                            @if ($time)
                                                <span style="font-weight: 400; color: #9ca3af; font-size: 10px;"> &bull; {{ $time }}</span>
                                            @endif
                                        </div>
                                        <div style="display: inline-block; max-width: 88%; padding: 10px 14px; border-radius: 8px; font-size: 13.5px; line-height: 1.5; text-align: left; {{ $isUser ? 'background-color: #0b0b0b; color: #ffffff;' : 'background-color: #ffffff; color: #1f2937; border: 1px solid #e5e7eb;' }}">
                                            {!! nl2br(e($content)) !!}
                                        </div>
                                    </div>
                                @empty
                                    <div style="text-align: center; color: #9ca3af; font-size: 13px; padding: 20px 0;">
                                        No messages recorded in this session.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Dashboard Link -->
                            <div style="text-align: center; margin-top: 28px;">
                                <a href="{{ url('/dashboard/ai/sessions') }}" style="display: inline-block; background-color: #0b0b0b; color: #ffffff; text-decoration: none; font-size: 13.5px; font-weight: 600; padding: 12px 24px; border-radius: 6px; letter-spacing: 0.3px;">
                                    View Session in Admin Dashboard &rarr;
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 32px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-align: center;">
                            This automated transcript was generated by the Azoogi AI Assistant &bull; Azoogi Architectural Lighting Sydney NSW &bull; <a href="{{ config('app.url') }}" style="color: #2d7a1e; text-decoration: none;">azoogi.com.au</a>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
