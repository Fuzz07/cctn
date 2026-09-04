{{--
    The BCTVI assistant, as a bubble on every page.

    It holds no rules of its own: it POSTs to /chat and renders whatever the
    Assistant sends back, which is the same thing the mobile app renders. That
    is why the two platforms cannot answer the same question differently.

    It works signed out — the landing page carries it too — and the server
    decides which answers need an account.
--}}
<div id="bctvi-chat" class="bctvi-chat">
    <button type="button" class="bctvi-chat-launcher" id="bctviChatLauncher"
            aria-expanded="false" aria-controls="bctviChatPanel" aria-label="Open the BCTVI assistant">
        <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
    </button>

    <section class="bctvi-chat-panel" id="bctviChatPanel" role="dialog" aria-modal="false"
             aria-label="BCTVI assistant" hidden>
        <header class="bctvi-chat-header">
            <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="" class="bctvi-chat-mark">
            <div class="bctvi-chat-titles">
                <strong>BCTVI Assistant</strong>
                <span>Answers about your account and our service</span>
            </div>
            <button type="button" class="bctvi-chat-close" id="bctviChatClose" aria-label="Close the assistant">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </header>

        <div class="bctvi-chat-log" id="bctviChatLog" role="log" aria-live="polite"></div>

        <div class="bctvi-chat-chips" id="bctviChatChips"></div>

        <form class="bctvi-chat-composer" id="bctviChatForm" autocomplete="off">
            <input type="text" id="bctviChatInput" maxlength="500" placeholder="Ask a question…"
                   aria-label="Your message">
            <button type="submit" aria-label="Send">
                <i class="bi bi-send-fill" aria-hidden="true"></i>
            </button>
        </form>
    </section>
</div>

<style>
    .bctvi-chat { position: fixed; right: 20px; bottom: 24px; z-index: 1200; font-family: var(--font-body, 'Inter', sans-serif); }

    .bctvi-chat-launcher {
        width: 56px; height: 56px; border-radius: 50%; border: none; cursor: pointer;
        background: #dc2626; color: #fff; font-size: 1.5rem; line-height: 1;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 22px rgba(220, 38, 38, 0.4);
        transition: transform 0.2s, background 0.2s;
    }
    .bctvi-chat-launcher:hover { background: #b91c1c; transform: translateY(-2px); }
    .bctvi-chat.is-open .bctvi-chat-launcher { transform: scale(0.9); opacity: 0.9; }

    .bctvi-chat-panel {
        position: absolute; right: 0; bottom: 72px;
        width: 360px; max-width: calc(100vw - 32px);
        height: 520px; max-height: calc(100vh - 140px);
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.28);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .bctvi-chat-panel[hidden] { display: none; }

    .bctvi-chat-header {
        display: flex; align-items: center; gap: 0.7rem;
        padding: 0.9rem 1rem; background: #dc2626; color: #ffffff; flex-shrink: 0;
    }
    .bctvi-chat-mark { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; background: #fff; flex-shrink: 0; }
    .bctvi-chat-titles { flex: 1; min-width: 0; line-height: 1.25; }
    .bctvi-chat-titles strong { display: block; font-size: 0.95rem; font-weight: 800; }
    .bctvi-chat-titles span { display: block; font-size: 0.72rem; color: #fee2e2; }
    .bctvi-chat-close { background: transparent; border: none; color: #fff; cursor: pointer; font-size: 1rem; padding: 0.25rem; }

    .bctvi-chat-log {
        flex: 1; overflow-y: auto; padding: 1rem; background: #f8fafc;
        display: flex; flex-direction: column; gap: 0.65rem;
    }

    .bctvi-msg { max-width: 85%; padding: 0.65rem 0.85rem; border-radius: 14px; font-size: 0.86rem; line-height: 1.5; white-space: pre-line; }
    .bctvi-msg.bot { align-self: flex-start; background: #ffffff; border: 1px solid #e2e8f0; color: #334155; border-bottom-left-radius: 4px; }
    .bctvi-msg.me { align-self: flex-end; background: #dc2626; color: #ffffff; border-bottom-right-radius: 4px; }

    .bctvi-msg-link {
        align-self: flex-start; margin-top: -0.25rem;
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-size: 0.8rem; font-weight: 700; color: #dc2626; text-decoration: none;
        border: 1px solid #fca5a5; background: #fef2f2; padding: 0.4rem 0.7rem; border-radius: 8px;
    }

    .bctvi-typing { align-self: flex-start; display: flex; gap: 4px; padding: 0.7rem 0.85rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; }
    .bctvi-typing span { width: 6px; height: 6px; border-radius: 50%; background: #cbd5e1; animation: bctviBlink 1.2s infinite; }
    .bctvi-typing span:nth-child(2) { animation-delay: 0.2s; }
    .bctvi-typing span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes bctviBlink { 0%, 60%, 100% { opacity: 0.35; } 30% { opacity: 1; } }

    .bctvi-chat-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0 0.85rem 0.6rem; background: #f8fafc; flex-shrink: 0; }
    .bctvi-chat-chips:empty { display: none; }
    .bctvi-chip {
        border: 1px solid #cbd5e1; background: #ffffff; color: #334155;
        font-size: 0.76rem; font-weight: 600; padding: 0.35rem 0.7rem;
        border-radius: 9999px; cursor: pointer; font-family: inherit;
    }
    .bctvi-chip:hover { border-color: #dc2626; color: #dc2626; }

    .bctvi-chat-composer { display: flex; gap: 0.5rem; padding: 0.7rem; border-top: 1px solid #e2e8f0; background: #ffffff; flex-shrink: 0; }
    .bctvi-chat-composer input {
        flex: 1; min-width: 0; border: 1px solid #cbd5e1; border-radius: 8px;
        padding: 0.6rem 0.75rem; font-size: 0.86rem; font-family: inherit; color: #0f172a;
    }
    .bctvi-chat-composer input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }
    .bctvi-chat-composer button {
        border: none; background: #dc2626; color: #fff; border-radius: 8px;
        width: 42px; cursor: pointer; font-size: 0.9rem; flex-shrink: 0;
    }
    .bctvi-chat-composer button:disabled { background: #cbd5e1; cursor: not-allowed; }

    /* Below 992px the layout shows its own bottom bar; sit above it. */
    @media (max-width: 992px) {
        .bctvi-chat { right: 16px; bottom: calc(78px + env(safe-area-inset-bottom)); }
    }
    @media (max-width: 480px) {
        .bctvi-chat-panel { width: calc(100vw - 32px); height: min(70vh, 520px); }
    }
</style>

<script>
(function () {
    var root      = document.getElementById('bctvi-chat');
    var launcher  = document.getElementById('bctviChatLauncher');
    var panel     = document.getElementById('bctviChatPanel');
    var closeBtn  = document.getElementById('bctviChatClose');
    var log       = document.getElementById('bctviChatLog');
    var chips     = document.getElementById('bctviChatChips');
    var form      = document.getElementById('bctviChatForm');
    var input     = document.getElementById('bctviChatInput');
    var sendBtn   = form.querySelector('button');

    var greeted = false;
    var busy    = false;

    var tokenTag = document.querySelector('meta[name="csrf-token"]');
    var csrf     = tokenTag ? tokenTag.getAttribute('content') : '';

    function scrollDown() {
        log.scrollTop = log.scrollHeight;
    }

    function bubble(text, who) {
        var el = document.createElement('div');
        el.className = 'bctvi-msg ' + who;
        el.textContent = text;
        log.appendChild(el);
        scrollDown();
    }

    function linkButton(link) {
        if (!link || !link.url) return;
        var a = document.createElement('a');
        a.className = 'bctvi-msg-link';
        a.href = link.url;
        a.textContent = link.label;
        log.appendChild(a);
        scrollDown();
    }

    function renderChips(list) {
        chips.innerHTML = '';
        (list || []).forEach(function (text) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'bctvi-chip';
            b.textContent = text;
            b.addEventListener('click', function () { send(text); });
            chips.appendChild(b);
        });
    }

    function showTyping() {
        var el = document.createElement('div');
        el.className = 'bctvi-typing';
        el.id = 'bctviTyping';
        el.innerHTML = '<span></span><span></span><span></span>';
        log.appendChild(el);
        scrollDown();
    }

    function hideTyping() {
        var el = document.getElementById('bctviTyping');
        if (el) el.remove();
    }

    function setBusy(state) {
        busy = state;
        sendBtn.disabled = state;
    }

    // An empty message asks the server for its opening greeting, so the words
    // the assistant opens with live with the rest of its answers.
    function send(message) {
        if (busy) return;
        if (message) bubble(message, 'me');

        renderChips([]);
        setBusy(true);
        showTyping();

        fetch({!! json_encode(route('chat.reply')) !!}, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: message || '' })
        })
        .then(function (res) {
            if (!res.ok) throw new Error('http ' + res.status);
            return res.json();
        })
        .then(function (data) {
            hideTyping();
            bubble(data.reply, 'bot');
            linkButton(data.link);
            renderChips(data.suggestions);
        })
        .catch(function () {
            hideTyping();
            bubble("I couldn't reach the server just then. Please check your connection and try again.", 'bot');
        })
        .finally(function () {
            setBusy(false);
            input.focus();
        });
    }

    function open() {
        panel.hidden = false;
        root.classList.add('is-open');
        launcher.setAttribute('aria-expanded', 'true');
        if (!greeted) {
            greeted = true;
            send('');
        }
        input.focus();
    }

    function close() {
        panel.hidden = true;
        root.classList.remove('is-open');
        launcher.setAttribute('aria-expanded', 'false');
    }

    launcher.addEventListener('click', function () {
        panel.hidden ? open() : close();
    });
    closeBtn.addEventListener('click', close);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) close();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = input.value.trim();
        if (!text) return;
        input.value = '';
        send(text);
    });
})();
</script>
