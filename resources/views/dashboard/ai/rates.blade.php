@extends('layouts.dashboard')

@section('title', 'AI Rates & Token Pricing')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>AI Rates</span>
    </div>
    <div class="dash-head-title">
        <h1>AI Rates &amp; Token Pricing</h1>
        <div class="dash-head-actions">
            <a href="{{ route('dashboard.ai.models') }}" class="btn" style="display:inline-flex;align-items:center;gap:6px;background:var(--card-bg);border:1px solid var(--line);color:var(--ink);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;" aria-hidden="true"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                Configure Models
            </a>
            <a href="{{ route('dashboard.chat-sessions.index') }}" class="btn primary" style="display:inline-flex;align-items:center;gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                View Chat Logs &amp; Costs
            </a>
        </div>
    </div>
    <p class="dash-lead">Inspect token pricing schedules, cost calculation formulas, live estimation simulations, and computed infrastructure costs across all AI model engines.</p>
</div>

<!-- Quick Metrics Bar -->
<div class="dash-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px;">
    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Active Model Rate</div>
        <div style="display:flex;align-items:baseline;gap:6px;">
            <span style="font-size:20px;font-weight:700;color:var(--accent);font-family:monospace;">${{ number_format($activeInputRate, 3) }}</span>
            <span style="font-size:11px;color:var(--muted);">in</span>
            <span style="font-size:14px;color:var(--muted);font-weight:600;">/</span>
            <span style="font-size:20px;font-weight:700;color:#38bdf8;font-family:monospace;">${{ number_format($activeOutputRate, 3) }}</span>
            <span style="font-size:11px;color:var(--muted);">out</span>
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:6px;font-family:monospace;word-break:break-all;">
            {{ $activeModelIdentifier }}
        </div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Total Tokens Consumed</div>
        <div style="font-size:24px;font-weight:700;color:var(--accent);font-family:monospace;">{{ number_format($totalTokens ?? 0) }}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">Across {{ number_format($sessionsCount ?? 0) }} sessions ({{ number_format($messagesCount ?? 0) }} messages)</div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Total Estimated Spend</div>
        <div style="font-size:24px;font-weight:700;color:#10b981;font-family:monospace;">${{ number_format($totalCost ?? 0, 4) }} <span style="font-size:12px;font-weight:normal;color:var(--muted);">USD</span></div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">Real-time computed LLM cost</div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Average Cost / Session</div>
        <div style="font-size:24px;font-weight:700;color:var(--ink);font-family:monospace;">
            ${{ ($sessionsCount ?? 0) > 0 ? number_format(($totalCost ?? 0) / $sessionsCount, 5) : '0.00000' }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">Cost efficiency per visitor intake</div>
    </div>
</div>

<!-- Cost Calculation Engine Rate Schedule Card -->
<div class="dash-card" style="padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 14px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--accent);"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--dash-ink, var(--ink));">
                Cost Calculation Engine — Formula &amp; Architecture
            </h3>
        </div>
        <span class="dash-doc-badge" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-weight: 600; font-size: 11px; padding: 4px 10px; border-radius: 6px;">
            Precision: 6 Decimals ($ USD)
        </span>
    </div>

    <p style="font-size: 13px; color: var(--dash-muted, var(--muted)); margin: 0 0 16px 0; line-height: 1.55;">
        The system tracks prompt and completion tokens separately for every chat interaction and calculates exact compute costs using the mathematical formula:
        <br>
        <code style="display: inline-block; margin-top: 8px; padding: 8px 14px; background: var(--dash-bg, var(--bg)); border: 1px solid var(--line); border-radius: 6px; font-size: 13px; color: var(--dash-ink, var(--ink)); font-family: monospace;">
            Cost (USD) = ((Prompt Tokens / 1,000,000) × Input Rate) + ((Completion Tokens / 1,000,000) × Output Rate)
        </code>
    </p>
</div>

<!-- Unified Token Rates & Cost Estimator Card -->
<div class="dash-card" style="padding: 24px;">
    <!-- Card Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 14px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--accent);"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--dash-ink, var(--ink));">
                Standard Token Rate Schedule &amp; Live Cost Estimator Playground
            </h3>
        </div>
        <span class="dash-doc-badge" style="background: rgba(103, 208, 78, 0.12); color: var(--accent); border: 1px solid rgba(103, 208, 78, 0.3); font-weight: 600; font-size: 11px; padding: 4px 10px; border-radius: 6px;">
            Interactive Rate Schedule &amp; Cost Simulator (USD per 1,000,000 Tokens)
        </span>
    </div>

    <p style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); margin: 0 0 18px 0; line-height: 1.55;">
        Inspect standard token pricing schedules across all AI engines and interactively simulate compute expenses per message and monthly volume in real time.
    </p>

    <!-- Simulation Parameters Inputs Toolbar -->
    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--line); border-radius: 8px; padding: 16px 20px; margin-bottom: 22px;">
        <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <span>Live Cost Estimation Parameters</span>
            </div>
            <span style="font-size: 11px; color: var(--muted); font-weight: normal; text-transform: none;">Values dynamically recalculate in the table below</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.05em;margin-bottom:6px;">Prompt Tokens (Input)</label>
                <input type="number" id="calc-prompt-tokens" value="2500" min="0" step="500" class="dash-input" style="font-family:monospace;">
                <div style="font-size:11px;color:var(--muted);margin-top:4px;">System prompt &amp; catalog context</div>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.05em;margin-bottom:6px;">Completion Tokens (Output)</label>
                <input type="number" id="calc-comp-tokens" value="800" min="0" step="100" class="dash-input" style="font-family:monospace;">
                <div style="font-size:11px;color:var(--muted);margin-top:4px;">AI answer &amp; spec recommendations</div>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.05em;margin-bottom:6px;">Estimated Monthly Chats</label>
                <input type="number" id="calc-chats-count" value="1000" min="1" step="100" class="dash-input" style="font-family:monospace;">
                <div style="font-size:11px;color:var(--muted);margin-top:4px;">Expected visitor volume</div>
            </div>
        </div>
    </div>

    <!-- Combined Master Rates & Cost Estimator Table -->
    <div style="overflow-x: auto; border: 1px solid var(--line); border-radius: 8px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--line); background: rgba(255, 255, 255, 0.03);">
                    <th style="padding: 12px 14px; font-weight: 600; color: var(--dash-muted, var(--muted)); min-width: 180px;">Model Family &amp; Identifier</th>
                    <th style="padding: 12px 14px; font-weight: 600; color: var(--dash-muted, var(--muted));">Provider</th>
                    <th style="padding: 12px 14px; font-weight: 600; color: #38bdf8;">Prompt / 1M</th>
                    <th style="padding: 12px 14px; font-weight: 600; color: var(--accent);">Completion / 1M</th>
                    <th style="padding: 12px 14px; font-weight: 600; color: var(--dash-muted, var(--muted));">Context</th>
                    <th style="padding: 12px 14px; font-weight: 600; color: #38bdf8; min-width: 130px;">Est. Cost / Msg</th>
                    <th style="padding: 12px 14px; font-weight: 600; color: var(--accent); min-width: 150px;">Monthly Projected</th>
                    <th style="padding: 12px 14px; font-weight: 600; color: var(--dash-muted, var(--muted));">Target Use Case</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pricingCatalog as $index => $item)
                    @php
                        $defaultPrompt = 2500;
                        $defaultComp = 800;
                        $defaultMonthly = 1000;
                        $initSingleCost = (($defaultPrompt / 1000000) * $item['prompt_rate']) + (($defaultComp / 1000000) * $item['completion_rate']);
                        $initMonthlyCost = $initSingleCost * $defaultMonthly;
                        $isActive = str_contains(strtolower($activeModelIdentifier), strtolower(str_replace('*', '', explode(',', $item['pattern'])[0])));
                    @endphp
                    <tr class="rate-catalog-row" data-prompt-rate="{{ $item['prompt_rate'] }}" data-comp-rate="{{ $item['completion_rate'] }}" style="border-bottom: 1px solid var(--line); {{ $isActive ? 'background: rgba(103, 208, 78, 0.06);' : '' }}">
                        <td style="padding: 12px 14px;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <strong style="color: var(--dash-ink, var(--ink));">{{ $item['family'] }}</strong>
                                @if ($isActive)
                                    <span style="font-size: 9.5px; background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); padding: 2px 6px; border-radius: 4px; font-weight: 700; text-transform: uppercase;">Active</span>
                                @endif
                            </div>
                            <code style="font-size: 11px; color: var(--dash-muted, var(--muted)); display: block; margin-top: 2px;">({{ $item['pattern'] }})</code>
                        </td>
                        <td style="padding: 12px 14px;">
                            <span class="dash-tag is-primary" style="font-size: 10.5px;">{{ $item['provider'] }}</span>
                        </td>
                        <td style="padding: 12px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">
                            ${{ number_format($item['prompt_rate'], 3) }}
                        </td>
                        <td style="padding: 12px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">
                            ${{ number_format($item['completion_rate'], 3) }}
                        </td>
                        <td style="padding: 12px 14px; font-family: monospace; font-size: 11px; color: var(--muted);">
                            {{ $item['context_window'] }}
                        </td>
                        <td class="est-single-cell" style="padding: 12px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">
                            ${{ number_format($initSingleCost, 5) }} USD
                        </td>
                        <td class="est-monthly-cell" style="padding: 12px 14px; font-family: monospace; font-weight: 700; color: var(--accent);">
                            ${{ number_format($initMonthlyCost, 2) }} USD
                        </td>
                        <td style="padding: 12px 14px; color: var(--muted); font-size: 11.5px;">
                            {{ $item['tier'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const promptInput = document.getElementById('calc-prompt-tokens');
        const compInput = document.getElementById('calc-comp-tokens');
        const countInput = document.getElementById('calc-chats-count');
        const rows = document.querySelectorAll('.rate-catalog-row');

        function recalculateEstimator() {
            const promptTokens = Math.max(0, parseInt(promptInput.value) || 0);
            const compTokens = Math.max(0, parseInt(compInput.value) || 0);
            const chats = Math.max(1, parseInt(countInput.value) || 1);

            rows.forEach(row => {
                const promptRate = parseFloat(row.getAttribute('data-prompt-rate')) || 0;
                const compRate = parseFloat(row.getAttribute('data-comp-rate')) || 0;

                const singleCost = ((promptTokens / 1000000) * promptRate) + ((compTokens / 1000000) * compRate);
                const monthlyCost = singleCost * chats;

                const singleCell = row.querySelector('.est-single-cell');
                const monthlyCell = row.querySelector('.est-monthly-cell');

                if (singleCell) {
                    singleCell.textContent = `$${singleCost.toFixed(5)} USD`;
                }
                if (monthlyCell) {
                    monthlyCell.textContent = `$${monthlyCost.toFixed(2)} USD`;
                }
            });
        }

        if (promptInput && compInput && countInput) {
            promptInput.addEventListener('input', recalculateEstimator);
            compInput.addEventListener('input', recalculateEstimator);
            countInput.addEventListener('input', recalculateEstimator);
            recalculateEstimator();
        }
    });
</script>
@endsection
