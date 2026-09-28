{{--
    The BCTVI AI Assistant, with OpenAI Fallback, Interactive Widgets & Voice Input.

    It holds no rules of its own: it POSTs to /chat (or /api/v1/chat) and renders
    whatever the Assistant sends back (database matches, OpenAI response, plan cards,
    or live coverage checker).
--}}
<div id="bctvi-chat" class="bctvi-chat">

    {{-- Launcher bubble --}}
    <button type="button" class="bctvi-chat-launcher" id="bctviChatLauncher"
            aria-expanded="false" aria-controls="bctviChatPanel" aria-label="Open the BCTVI assistant">
        <span class="launcher-icon-chat"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
        <span class="launcher-icon-close" style="display:none;"><i class="bi bi-x-lg" aria-hidden="true"></i></span>
        <span class="launcher-badge" id="bctviLauncherBadge" style="display:none;"></span>
    </button>

    {{-- Chat panel --}}
    <section class="bctvi-chat-panel" id="bctviChatPanel" role="dialog" aria-modal="false"
             aria-label="BCTVI AI assistant" hidden>

        {{-- Header --}}
        <header class="bctvi-chat-header">
            <div class="bctvi-header-avatar">
                <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI">
                <span class="bctvi-online-dot"></span>
            </div>
            <div class="bctvi-chat-titles">
                <strong>BCTVI AI Assistant</strong>
                <span>
                    <span class="bctvi-online-dot-inline"></span>
                    Online · Smart 24/7 ISP Support
                </span>
            </div>
            <div class="bctvi-header-actions">
                <button type="button" class="bctvi-chat-hdr-btn" id="bctviChatReset" title="Restart conversation" aria-label="Restart chat">
                    <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                </button>
                <button type="button" class="bctvi-chat-hdr-btn" id="bctviChatClose" title="Close chat" aria-label="Close assistant">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        {{-- Message log --}}
        <div class="bctvi-chat-log" id="bctviChatLog" role="log" aria-live="polite"></div>

        {{-- Quick-reply suggestion chips --}}
        <div class="bctvi-chat-chips" id="bctviChatChips"></div>

        {{-- Composer --}}
        <form class="bctvi-chat-composer" id="bctviChatForm" autocomplete="off">
            <input type="text" id="bctviChatInput" maxlength="500"
                   placeholder="Ask about plans, coverage, balance…" aria-label="Your message">
            
            {{-- Speech to text mic --}}
            <button type="button" class="bctvi-voice-btn" id="bctviVoiceBtn" title="Voice typing (Speak)" aria-label="Voice input">
                <i class="bi bi-mic-fill" aria-hidden="true"></i>
            </button>

            {{-- Send button --}}
            <button type="submit" aria-label="Send" id="bctviSendBtn" title="Send message">
                <i class="bi bi-send-fill" aria-hidden="true"></i>
            </button>
        </form>

    </section>
</div>

<style>
/* ── Root wrapper ──────────────────────────────────────── */
.bctvi-chat {
    position: fixed;
    right: 20px;
    bottom: 24px;
    z-index: 1200;
    font-family: var(--font-body, 'Inter', sans-serif);
}

/* ── Launcher button ───────────────────────────────────── */
.bctvi-chat-launcher {
    width: 58px; height: 58px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: #dc2626;
    color: #fff;
    font-size: 1.45rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 28px rgba(220,38,38,0.45), 0 0 0 0 rgba(220,38,38,0.4);
    transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
    position: relative;
    animation: launcherPulse 3s ease-in-out infinite;
}
@keyframes launcherPulse {
    0%,100% { box-shadow: 0 8px 28px rgba(220,38,38,0.45), 0 0 0 0 rgba(220,38,38,0.3); }
    50%      { box-shadow: 0 8px 28px rgba(220,38,38,0.45), 0 0 0 10px rgba(220,38,38,0); }
}
.bctvi-chat-launcher:hover { background: #b91c1c; transform: translateY(-2px); }
.bctvi-chat.is-open .bctvi-chat-launcher { animation: none; background: #1e1e1e; }

/* Unread badge on launcher */
.launcher-badge {
    position: absolute;
    top: -3px; right: -3px;
    background: #f59e0b;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 800;
    min-width: 18px; height: 18px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #fff;
    padding: 0 2px;
}

/* ── Panel ─────────────────────────────────────────────── */
.bctvi-chat-panel {
    position: absolute;
    right: 0;
    bottom: 72px;
    width: 380px;
    max-width: calc(100vw - 32px);
    height: 560px;
    max-height: calc(100vh - 120px);
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: 20px;
    box-shadow: 0 30px 70px rgba(15,23,42,0.22), 0 0 0 1px rgba(220,38,38,0.06);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform-origin: bottom right;
    animation: panelIn 0.22s cubic-bezier(0.22,1,0.36,1) both;
}
@keyframes panelIn {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to   { opacity: 1; transform: scale(1)    translateY(0); }
}
.bctvi-chat-panel[hidden] { display: none; }

/* ── Header ────────────────────────────────────────────── */
.bctvi-chat-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: #fff;
    flex-shrink: 0;
    position: relative;
}
.bctvi-chat-header::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at top right, rgba(255,255,255,0.08) 0%, transparent 65%);
    pointer-events: none;
}

.bctvi-header-avatar {
    position: relative;
    flex-shrink: 0;
}
.bctvi-header-avatar img {
    width: 38px; height: 38px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--bg-card);
    border: 2px solid rgba(255,255,255,0.3);
}
.bctvi-online-dot {
    position: absolute;
    bottom: 0; right: 0;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: #22c55e;
    border: 2px solid #dc2626;
    box-shadow: 0 0 6px rgba(34,197,94,0.6);
}

.bctvi-chat-titles {
    flex: 1;
    min-width: 0;
    line-height: 1.25;
}
.bctvi-chat-titles strong {
    display: block;
    font-size: 0.92rem;
    font-weight: 800;
}
.bctvi-chat-titles span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.8);
}
.bctvi-online-dot-inline {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 5px rgba(74,222,128,0.8);
    display: inline-block;
    flex-shrink: 0;
}

.bctvi-header-actions {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    position: relative;
    z-index: 2;
}
.bctvi-chat-hdr-btn {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    cursor: pointer;
    font-size: 0.85rem;
    padding: 0.3rem 0.45rem;
    border-radius: 8px;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bctvi-chat-hdr-btn:hover { background: rgba(255,255,255,0.28); }

/* ── Log ───────────────────────────────────────────────── */
.bctvi-chat-log {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    background: var(--bg-page);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    scroll-behavior: smooth;
}
.bctvi-chat-log::-webkit-scrollbar { width: 4px; }
.bctvi-chat-log::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* ── Messages ──────────────────────────────────────────── */
.bctvi-row {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
}
.bctvi-row.bot { align-self: flex-start; }
.bctvi-row.me  { align-self: flex-end; flex-direction: row-reverse; }

.bctvi-avatar {
    width: 28px; height: 28px;
    border-radius: 50%;
    object-fit: cover;
    border: 1.5px solid var(--border-light);
    flex-shrink: 0;
}

.bctvi-msg {
    max-width: 84%;
    padding: 0.65rem 0.9rem;
    border-radius: 16px;
    font-size: 0.855rem;
    line-height: 1.55;
    white-space: pre-line;
}
.bctvi-msg.bot {
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    color: var(--text-body);
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.bctvi-msg.me {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #ffffff;
    border-bottom-right-radius: 4px;
    box-shadow: 0 2px 8px rgba(220,38,38,0.3);
}

.bctvi-msg-link {
    align-self: flex-start;
    margin-top: -0.1rem;
    margin-left: 36px;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #dc2626;
    text-decoration: none;
    border: 1px solid #fca5a5;
    background: #fef2f2;
    padding: 0.35rem 0.75rem;
    border-radius: 8px;
    transition: all 0.15s;
}
.bctvi-msg-link:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
.bctvi-msg-link i { font-size: 0.85rem; }

/* ── Interactive Plan Cards Inside Chat ─────────────────── */
.bctvi-plans-grid {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    margin-top: 0.4rem;
    margin-left: 36px;
    max-width: calc(100% - 36px);
}
.bctvi-plan-card {
    background: var(--bg-card);
    border: 1.5px solid var(--bg-subtle);
    border-left: 3.5px solid #dc2626;
    border-radius: 10px;
    padding: 0.6rem 0.75rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}
.bctvi-plan-info { flex: 1; min-width: 0; }
.bctvi-plan-name { font-size: 0.82rem; font-weight: 800; color: var(--text-dark); line-height: 1.2; }
.bctvi-plan-sub { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }
.bctvi-plan-price { font-size: 0.84rem; font-weight: 800; color: #dc2626; white-space: nowrap; }
.bctvi-plan-btn {
    background: #dc2626;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s;
}
.bctvi-plan-btn:hover { background: #b91c1c; color: #fff; }

/* ── Interactive Live Coverage Checker ──────────────────── */
.bctvi-coverage-box {
    margin-top: 0.4rem;
    margin-left: 36px;
    background: var(--bg-card);
    border: 1px solid #fed7aa;
    background: #fff7ed;
    border-radius: 12px;
    padding: 0.75rem;
    max-width: calc(100% - 36px);
    box-shadow: 0 2px 6px rgba(249,115,22,0.08);
}
.bctvi-coverage-box h5 {
    font-size: 0.8rem;
    font-weight: 800;
    color: #c2410c;
    margin: 0 0 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.bctvi-coverage-selects {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.bctvi-cov-select {
    width: 100%;
    font-size: 0.76rem;
    padding: 0.4rem 0.5rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    color: #1e293b;
}
.bctvi-cov-res {
    margin-top: 0.45rem;
    padding: 0.45rem 0.6rem;
    border-radius: 6px;
    font-size: 0.74rem;
    font-weight: 700;
    line-height: 1.35;
}
.bctvi-cov-res.available { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }

/* Typing indicator */
.bctvi-typing {
    display: flex;
    gap: 4px;
    padding: 0.7rem 0.9rem;
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: 16px;
    border-bottom-left-radius: 4px;
    width: fit-content;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.bctvi-typing span {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #dc2626;
    opacity: 0.35;
    animation: bctviDot 1.2s infinite;
}
.bctvi-typing span:nth-child(2) { animation-delay: 0.18s; }
.bctvi-typing span:nth-child(3) { animation-delay: 0.36s; }
@keyframes bctviDot {
    0%,60%,100% { opacity: 0.25; transform: scale(0.85); }
    30%          { opacity: 1;    transform: scale(1); }
}

/* ── Chips ─────────────────────────────────────────────── */
.bctvi-chat-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    padding: 0.6rem 0.85rem;
    background: var(--bg-page);
    border-top: 1px solid var(--bg-subtle);
    flex-shrink: 0;
}
.bctvi-chat-chips:empty { display: none; }

.bctvi-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1px solid var(--border-light);
    background: var(--bg-card);
    color: var(--text-body);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.38rem 0.75rem;
    border-radius: 9999px;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.bctvi-chip:hover {
    border-color: #dc2626;
    color: #dc2626;
    background: #fef2f2;
    box-shadow: 0 2px 6px rgba(220,38,38,0.12);
}
.bctvi-chip i { font-size: 0.82rem; }

/* Welcome chips grid */
.bctvi-welcome-chips {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.4rem;
    padding: 0.75rem;
    background: var(--bg-page);
    border-top: 1px solid var(--bg-subtle);
    flex-shrink: 0;
}
.bctvi-welcome-chip {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.2rem;
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: 12px;
    padding: 0.65rem 0.75rem;
    cursor: pointer;
    font-family: inherit;
    text-align: left;
    transition: all 0.15s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.bctvi-welcome-chip:hover {
    border-color: #dc2626;
    background: #fef2f2;
    box-shadow: 0 3px 10px rgba(220,38,38,0.1);
}
.bctvi-welcome-chip .wc-icon {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    display: flex; align-items: center; justify-content: center;
    color: #dc2626;
    font-size: 0.9rem;
    margin-bottom: 0.15rem;
}
.bctvi-welcome-chip .wc-label {
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
}
.bctvi-welcome-chip .wc-sub {
    font-size: 0.67rem;
    color: var(--text-faint);
    line-height: 1.2;
}

/* ── Composer ───────────────────────────────────────────── */
.bctvi-chat-composer {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.65rem 0.75rem;
    border-top: 1px solid var(--border-light);
    background: var(--bg-card);
    flex-shrink: 0;
}
.bctvi-chat-composer input {
    flex: 1;
    min-width: 0;
    border: 1.5px solid var(--border-light);
    border-radius: 10px;
    padding: 0.6rem 0.8rem;
    font-size: 0.85rem;
    font-family: inherit;
    color: var(--text-dark);
    background: var(--bg-page);
    transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}
.bctvi-chat-composer input:focus {
    outline: none;
    border-color: #dc2626;
    background: var(--bg-card);
    box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
}

/* Voice mic button */
.bctvi-voice-btn {
    border: 1px solid var(--border-light);
    background: var(--bg-page);
    color: var(--text-muted);
    border-radius: 10px;
    width: 38px; height: 38px;
    cursor: pointer;
    font-size: 0.95rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}
.bctvi-voice-btn:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }
.bctvi-voice-btn.listening {
    background: #dc2626;
    color: #fff;
    border-color: #dc2626;
    animation: micPulse 1.2s infinite;
}
@keyframes micPulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.5); }
    50%      { box-shadow: 0 0 0 8px rgba(220,38,38,0); }
}

/* Send button */
.bctvi-chat-composer button[type="submit"] {
    border: none;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff;
    border-radius: 10px;
    width: 38px; height: 38px;
    cursor: pointer;
    font-size: 0.88rem;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(220,38,38,0.3);
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bctvi-chat-composer button[type="submit"]:hover { background: linear-gradient(135deg, #b91c1c, #991b1b); transform: translateY(-1px); }
.bctvi-chat-composer button[type="submit"]:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed; transform: none; }

/* ── Date divider ───────────────────────────────────────── */
.bctvi-divider {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.68rem;
    color: var(--text-faint);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin: 0.25rem 0;
}
.bctvi-divider::before, .bctvi-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-light);
}

/* ── Mobile ─────────────────────────────────────────────── */
@media (max-width: 992px) {
    .bctvi-chat { right: 16px; bottom: calc(78px + env(safe-area-inset-bottom)); }
}
@media (max-width: 480px) {
    .bctvi-chat-panel { width: calc(100vw - 32px); height: min(75vh, 560px); }
    .bctvi-welcome-chips { grid-template-columns: 1fr 1fr; }
}
</style>

<script>
(function () {

    /* ── DOM ── */
    var root      = document.getElementById('bctvi-chat');
    var launcher  = document.getElementById('bctviChatLauncher');
    var panel     = document.getElementById('bctviChatPanel');
    var closeBtn  = document.getElementById('bctviChatClose');
    var resetBtn  = document.getElementById('bctviChatReset');
    var log       = document.getElementById('bctviChatLog');
    var chipsWrap = document.getElementById('bctviChatChips');
    var form      = document.getElementById('bctviChatForm');
    var input     = document.getElementById('bctviChatInput');
    var sendBtn   = document.getElementById('bctviSendBtn');
    var voiceBtn  = document.getElementById('bctviVoiceBtn');
    var badge     = document.getElementById('bctviLauncherBadge');
    var launchIconChat  = launcher.querySelector('.launcher-icon-chat');
    var launchIconClose = launcher.querySelector('.launcher-icon-close');

    var LOGO      = {!! json_encode(asset('assets/images/cctn-logo.png')) !!};
    var CSRF      = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var CHAT_URL  = {!! json_encode(route('chat.reply')) !!};

    var greeted = false;
    var busy    = false;
    var unread  = 0;
    var welcomeChipsEl = null;

    /* ── Welcome chips (shown on initial open) ── */
    var WELCOME_CHIPS = [
        { icon: 'bi-wifi',           label: 'View Plans',       sub: 'Fiber packages & rates',       msg: 'What plans do you offer?' },
        { icon: 'bi-geo-alt',        label: 'Coverage Area',    sub: 'Check your barangay',          msg: 'What is your service coverage area?' },
        { icon: 'bi-calendar-check', label: 'How to Apply',     sub: 'Book an installation',         msg: 'How do I apply for internet?' },
        { icon: 'bi-telephone',      label: 'Contact Us',       sub: 'Office number & hours',        msg: 'What is your contact number?' },
    ];

    /* ── Helpers ── */
    function scrollDown() { log.scrollTop = log.scrollHeight; }

    function nowTime() {
        var d = new Date();
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function addDivider(text) {
        var el = document.createElement('div');
        el.className = 'bctvi-divider';
        el.textContent = text;
        log.appendChild(el);
    }

    /* ── Render a message row ── */
    function bubble(text, who) {
        var row = document.createElement('div');
        row.className = 'bctvi-row ' + who;

        if (who === 'bot') {
            var av = document.createElement('img');
            av.className = 'bctvi-avatar';
            av.src = LOGO;
            av.alt = 'BCTVI';
            row.appendChild(av);
        }

        var msg = document.createElement('div');
        msg.className = 'bctvi-msg ' + who;
        msg.textContent = text;
        row.appendChild(msg);

        log.appendChild(row);
        scrollDown();
        return row;
    }

    function linkButton(link) {
        if (!link || !link.url) return;
        var a = document.createElement('a');
        a.className = 'bctvi-msg-link';
        a.href = link.url;
        a.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i>' + link.label;
        log.appendChild(a);
        scrollDown();
    }

    /* ── Render Interactive Plan Cards ── */
    function renderPlanCards(plans) {
        if (!plans || !plans.length) return;
        var wrap = document.createElement('div');
        wrap.className = 'bctvi-plans-grid';

        plans.forEach(function (plan) {
            var card = document.createElement('div');
            card.className = 'bctvi-plan-card';
            card.innerHTML =
                '<div class="bctvi-plan-info">' +
                    '<div class="bctvi-plan-name">' + plan.name + ' (' + plan.speed + ')</div>' +
                    '<div class="bctvi-plan-sub">' + plan.price + '/mo · Install: ' + plan.fee + '</div>' +
                '</div>' +
                '<a href="' + plan.book_url + '" class="bctvi-plan-btn">Apply Now</a>';
            wrap.appendChild(card);
        });

        log.appendChild(wrap);
        scrollDown();
    }

    /* ── Render Interactive Coverage Checker ── */
    function renderCoverageChecker(areas, municipalities) {
        if (!areas) return;
        var box = document.createElement('div');
        box.className = 'bctvi-coverage-box';
        box.innerHTML =
            '<h5><i class="bi bi-geo-alt-fill"></i> Check Your Barangay Coverage</h5>' +
            '<div class="bctvi-coverage-selects">' +
                '<select class="bctvi-cov-select" id="bctviMunSelect">' +
                    '<option value="">-- Select Municipality --</option>' +
                '</select>' +
                '<select class="bctvi-cov-select" id="bctviBrgySelect" disabled>' +
                    '<option value="">-- Select Barangay --</option>' +
                '</select>' +
                '<div class="bctvi-cov-res" id="bctviCovRes" style="display:none;"></div>' +
            '</div>';

        log.appendChild(box);

        var munSel  = box.querySelector('#bctviMunSelect');
        var brgySel = box.querySelector('#bctviBrgySelect');
        var resDiv  = box.querySelector('#bctviCovRes');

        (municipalities || Object.keys(areas)).forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m;
            munSel.appendChild(opt);
        });

        munSel.addEventListener('change', function () {
            brgySel.innerHTML = '<option value="">-- Select Barangay --</option>';
            resDiv.style.display = 'none';
            if (!this.value || !areas[this.value]) {
                brgySel.disabled = true;
                return;
            }
            brgySel.disabled = false;
            areas[this.value].forEach(function (b) {
                var opt = document.createElement('option');
                opt.value = b;
                opt.textContent = b;
                brgySel.appendChild(opt);
            });
        });

        brgySel.addEventListener('change', function () {
            if (!this.value) { resDiv.style.display = 'none'; return; }
            resDiv.className = 'bctvi-cov-res available';
            resDiv.innerHTML = '✅ <strong>Fiber is Active in ' + this.value + ', ' + munSel.value + '!</strong> Fast installation slots available.';
            resDiv.style.display = 'block';
            scrollDown();
        });

        scrollDown();
    }

    /* ── Quick-reply suggestion chips ── */
    function renderChips(list) {
        chipsWrap.innerHTML = '';
        chipsWrap.style.display = '';
        (list || []).forEach(function (text) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'bctvi-chip';
            b.innerHTML = '<i class="bi bi-chat-right-text" aria-hidden="true"></i>' + text;
            b.addEventListener('click', function () { send(text); });
            chipsWrap.appendChild(b);
        });
    }

    /* ── Welcome 2×2 chip grid ── */
    function showWelcomeChips() {
        if (welcomeChipsEl) return;
        welcomeChipsEl = document.createElement('div');
        welcomeChipsEl.className = 'bctvi-welcome-chips';
        welcomeChipsEl.id = 'bctviWelcomeChips';

        WELCOME_CHIPS.forEach(function (chip) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'bctvi-welcome-chip';
            btn.innerHTML =
                '<div class="wc-icon"><i class="bi ' + chip.icon + '"></i></div>' +
                '<div class="wc-label">' + chip.label + '</div>' +
                '<div class="wc-sub">' + chip.sub + '</div>';
            btn.addEventListener('click', function () {
                removeWelcomeChips();
                send(chip.msg);
            });
            welcomeChipsEl.appendChild(btn);
        });

        panel.insertBefore(welcomeChipsEl, chipsWrap);
    }

    function removeWelcomeChips() {
        if (welcomeChipsEl) {
            welcomeChipsEl.remove();
            welcomeChipsEl = null;
        }
    }

    /* ── Typing indicator ── */
    function showTyping() {
        var row = document.createElement('div');
        row.className = 'bctvi-row bot';
        row.id = 'bctviTypingRow';

        var av = document.createElement('img');
        av.className = 'bctvi-avatar';
        av.src = LOGO;
        av.alt = '';
        row.appendChild(av);

        var el = document.createElement('div');
        el.className = 'bctvi-typing';
        el.innerHTML = '<span></span><span></span><span></span>';
        row.appendChild(el);

        log.appendChild(row);
        scrollDown();
    }

    function hideTyping() {
        var el = document.getElementById('bctviTypingRow');
        if (el) el.remove();
    }

    function setBusy(state) {
        busy = state;
        sendBtn.disabled = state;
    }

    /* ── Unread badge ── */
    function incUnread() {
        if (!panel.hidden) return;
        unread++;
        badge.textContent = unread > 9 ? '9+' : unread;
        badge.style.display = 'flex';
    }

    function clearUnread() {
        unread = 0;
        badge.style.display = 'none';
    }

    /* ── Send Message ── */
    function send(message) {
        if (busy) return;

        removeWelcomeChips();

        if (message) bubble(message, 'me');
        renderChips([]);
        setBusy(true);
        showTyping();

        fetch(CHAT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: message || '' })
        })
        .then(function (res) { if (!res.ok) throw new Error(res.status); return res.json(); })
        .then(function (data) {
            hideTyping();
            bubble(data.reply, 'bot');
            if (data.plan_cards) renderPlanCards(data.plan_cards);
            if (data.coverage_checker) renderCoverageChecker(data.areas, data.municipalities);
            linkButton(data.link);
            renderChips(data.suggestions);
            incUnread();
        })
        .catch(function () {
            hideTyping();
            bubble("I had trouble reaching the server. Please try again or call 0999 998 8209.", 'bot');
            renderChips(['What plans do you offer?', 'Where is your office?']);
        })
        .finally(function () {
            setBusy(false);
        });
    }

    /* ── Voice Input (Speech to Text) ── */
    var recognition = null;
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        var SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRec();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-PH';

        recognition.onstart = function () {
            voiceBtn.classList.add('listening');
        };
        recognition.onresult = function (event) {
            var transcript = event.results[0][0].transcript;
            input.value = transcript;
            send(transcript);
        };
        recognition.onerror = function () {
            voiceBtn.classList.remove('listening');
        };
        recognition.onend = function () {
            voiceBtn.classList.remove('listening');
        };

        voiceBtn.addEventListener('click', function () {
            if (voiceBtn.classList.contains('listening')) {
                recognition.stop();
            } else {
                try { recognition.start(); } catch (e) {}
            }
        });
    } else {
        voiceBtn.style.display = 'none';
    }

    /* ── Reset / Clear Conversation ── */
    function resetChat() {
        log.innerHTML = '';
        renderChips([]);
        removeWelcomeChips();
        addDivider('Today · ' + nowTime());
        showWelcomeChips();
        send('');
    }
    resetBtn.addEventListener('click', resetChat);

    /* ── Open / Close ── */
    function openChat() {
        panel.hidden = false;
        root.classList.add('is-open');
        launcher.setAttribute('aria-expanded', 'true');
        launchIconChat.style.display  = 'none';
        launchIconClose.style.display = '';
        clearUnread();

        if (!greeted) {
            greeted = true;
            addDivider('Today · ' + nowTime());
            showWelcomeChips();
            send('');
        }
        input.focus();
    }

    function closeChat() {
        panel.hidden = true;
        root.classList.remove('is-open');
        launcher.setAttribute('aria-expanded', 'false');
        launchIconChat.style.display  = '';
        launchIconClose.style.display = 'none';
    }

    /* ── Events ── */
    launcher.addEventListener('click', function () { panel.hidden ? openChat() : closeChat(); });
    closeBtn.addEventListener('click', closeChat);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) closeChat();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = (input.value || '').trim();
        if (!text) return;
        input.value = '';
        send(text);
    });

})();
</script>
