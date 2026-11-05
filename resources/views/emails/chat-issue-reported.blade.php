<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Issue Report</title>
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
                                            AI Chat Widget Alert
                                        </div>
                                        <h1 style="color: #ffffff; font-size: 22px; font-weight: 700; margin: 0; line-height: 1.3;">
                                            Chat Issue Reported
                                        </h1>
                                    </td>
                                    @if ($session)
                                        <td align="right" style="vertical-align: middle;">
                                            <span style="background: rgba(103, 208, 78, 0.15); color: #67d04e; border: 1px solid rgba(103, 208, 78, 0.4); font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                                                Session #{{ $session->id }}
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 28px 32px;">

                            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 6px; padding: 16px 20px; margin-bottom: 24px;">
                                <div style="font-weight: 700; font-size: 13px; color: #991b1b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">Reported Issue Description</div>
                                <div style="font-size: 14.5px; color: #111827; line-height: 1.6; white-space: pre-line;">{{ $issueDescription }}</div>
                            </div>

                            <!-- Metadata Card -->
                            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;">
                                    Reporter &amp; Session Context
                                </div>
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13.5px;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #6b7280; width: 140px;">Reporter Name:</td>
                                        <td style="padding: 5px 0; color: #111827; font-weight: 600;">{{ $reporterName ?: ($session?->lead_name ?: 'Anonymous Visitor') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #6b7280;">Reporter Email:</td>
                                        <td style="padding: 5px 0; color: #111827; font-weight: 600;">
                                            @if ($reporterEmail || $session?->lead_email)
                                                <a href="mailto:{{ $reporterEmail ?: $session?->lead_email }}" style="color: #2d7a1e; text-decoration: none;">
                                                    {{ $reporterEmail ?: $session?->lead_email }}
                                                </a>
                                            @else
                                                <span style="color: #9ca3af;">Not provided</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($pageUrl || $session?->referrer_url)
                                        <tr>
                                            <td style="padding: 5px 0; color: #6b7280;">Page URL:</td>
                                            <td style="padding: 5px 0; color: #4b5563;">{{ $pageUrl ?: $session?->referrer_url }}</td>
                                        </tr>
                                    @endif
                                    @if ($session)
                                        <tr>
                                            <td style="padding: 5px 0; color: #6b7280;">Session UUID:</td>
                                            <td style="padding: 5px 0; color: #4b5563; font-family: monospace; font-size: 12px;">{{ $session->uuid }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 5px 0; color: #6b7280;">Location / IP:</td>
                                            <td style="padding: 5px 0; color: #4b5563;">{{ $session->country ?: 'Australia' }} &bull; {{ $session->ip_address ?: 'Unknown IP' }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="padding: 5px 0; color: #6b7280;">Reported At:</td>
                                        <td style="padding: 5px 0; color: #4b5563;">{{ now()->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>

                            @if (!empty($messages) && count($messages) > 0)
                                <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #374151; margin-bottom: 12px;">
                                    Session Transcript ({{ count($messages) }} messages)
                                </div>

                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; background-color: #fafafa;">
                                    @foreach ($messages as $msg)
                                        @php
                                            $isUser = is_object($msg) ? ($msg->sender === 'user') : (($msg['sender'] ?? '') === 'user');
                                            $content = is_object($msg) ? $msg->content : ($msg['content'] ?? '');
                                            $time = is_object($msg) && $msg->created_at ? $msg->created_at->format('h:i A') : '';
                                        @endphp
                                        <div style="margin-bottom: 12px; {{ $isUser ? 'text-align: right;' : 'text-align: left;' }}">
                                            <div style="font-size: 11px; font-weight: 600; color: {{ $isUser ? '#059669' : '#4b5563' }}; margin-bottom: 3px;">
                                                {{ $isUser ? ($reporterName ?: ($session?->lead_name ?: 'Visitor')) : 'Azoogi AI Assistant' }}
                                                @if ($time)
                                                    <span style="font-weight: 400; color: #9ca3af; font-size: 10px;"> &bull; {{ $time }}</span>
                                                @endif
                                            </div>
                                            <div style="display: inline-block; max-width: 88%; padding: 10px 14px; border-radius: 8px; font-size: 13.5px; line-height: 1.5; text-align: left; {{ $isUser ? 'background-color: #0b0b0b; color: #ffffff;' : 'background-color: #ffffff; color: #1f2937; border: 1px solid #e5e7eb;' }}">
                                                {!! nl2br(e($content)) !!}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div style="text-align: center; margin-top: 28px;">
                                <a href="{{ url('/dashboard/ai/chat-logs') }}" style="display: inline-block; background-color: #0b0b0b; color: #ffffff; text-decoration: none; font-size: 13.5px; font-weight: 600; padding: 12px 24px; border-radius: 6px; letter-spacing: 0.3px;">
                                    Open Chat Logs in Dashboard &rarr;
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 32px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-align: center;">
                            Automated AI Chat Issue Notification &bull; Azoogi Architectural Lighting Sydney NSW
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
