(function () {
    const root = document.getElementById('license-assistant');
    if (!root) return;

    const panel = root.querySelector('#assistant-panel');
    const openButton = root.querySelector('[data-assistant-open]');
    const closeButton = root.querySelector('[data-assistant-close]');
    const resetButton = root.querySelector('[data-assistant-reset]');
    const form = root.querySelector('[data-assistant-form]');
    const input = root.querySelector('[data-assistant-input]');
    const sendButton = root.querySelector('[data-assistant-send]');
    const messages = root.querySelector('[data-assistant-messages]');
    const typing = root.querySelector('[data-assistant-typing]');
    const endpoint = root.dataset.assistantEndpoint;
    let loaded = false;
    let busy = false;

    const language = () => window.appI18n?.getLanguage?.() === 'en' ? 'en' : 'vi';
    const t = (vi, en) => language() === 'en' ? en : vi;

    function refreshIcons() {
        window.lucide?.createIcons();
    }

    function scrollToLatest() {
        requestAnimationFrame(() => {
            messages.scrollTop = messages.scrollHeight;
        });
    }

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = String(text);
        return node;
    }

    function renderUserMessage(text) {
        const row = element('div', 'flex justify-end');
        const bubble = element('div', 'max-w-[86%] rounded-2xl rounded-br-md bg-slate-950 px-3.5 py-2.5 text-sm leading-6 text-white shadow-md dark:bg-teal-600', text);
        row.appendChild(bubble);
        messages.appendChild(row);
        scrollToLatest();
    }

    function severityClasses(severity) {
        const classes = {
            success: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200',
            warning: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-400/20 dark:bg-amber-500/10 dark:text-amber-200',
            critical: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-400/20 dark:bg-rose-500/10 dark:text-rose-200',
            info: 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-400/20 dark:bg-sky-500/10 dark:text-sky-200'
        };
        return classes[severity] || classes.info;
    }

    function toneClasses(tone) {
        const classes = {
            success: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
            warning: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
            critical: 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200',
            info: 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200'
        };
        return classes[tone] || classes.info;
    }

    function renderResponse(response) {
        const row = element('div', 'flex justify-start');
        const card = element('article', 'max-w-[96%] overflow-hidden rounded-2xl rounded-bl-md border border-slate-200/80 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900');
        const header = element('div', `border-b px-3.5 py-3 ${severityClasses(response.severity)}`);
        header.appendChild(element('p', 'font-semibold', response.title));
        header.appendChild(element('p', 'mt-1 text-xs leading-5 opacity-90', response.summary));
        card.appendChild(header);

        if (Array.isArray(response.metrics) && response.metrics.length) {
            const metrics = element('div', `grid gap-2 p-3 ${response.metrics.length >= 3 ? 'grid-cols-3' : 'grid-cols-2'}`);
            response.metrics.forEach((metric) => {
                const metricCard = element('div', 'rounded-xl bg-slate-50 px-2 py-2 text-center dark:bg-white/5');
                metricCard.appendChild(element('p', 'text-lg font-semibold text-slate-950 dark:text-white', metric.value));
                metricCard.appendChild(element('p', 'mt-0.5 text-[10px] leading-4 text-slate-500 dark:text-slate-400', metric.label));
                metrics.appendChild(metricCard);
            });
            card.appendChild(metrics);
        }

        if (Array.isArray(response.items) && response.items.length) {
            const list = element('div', 'max-h-52 divide-y divide-slate-100 overflow-y-auto border-t border-slate-100 dark:divide-white/10 dark:border-white/10');
            response.items.forEach((item) => {
                const itemNode = element(item.prompt ? 'button' : 'div', 'flex w-full items-center gap-2 px-3.5 py-2.5 text-left transition hover:bg-slate-50 dark:hover:bg-white/5');
                if (item.prompt) {
                    itemNode.type = 'button';
                    itemNode.addEventListener('click', () => sendQuestion(item.prompt));
                }
                const content = element('div', 'min-w-0 flex-1');
                content.appendChild(element('p', 'truncate text-sm font-medium text-slate-900 dark:text-white', item.primary));
                if (item.secondary) content.appendChild(element('p', 'truncate text-xs text-slate-500 dark:text-slate-400', item.secondary));
                if (item.meta) content.appendChild(element('p', 'mt-0.5 truncate text-[11px] text-slate-400', item.meta));
                itemNode.appendChild(content);
                if (item.badge) itemNode.appendChild(element('span', `shrink-0 rounded-lg px-2 py-1 text-[10px] font-semibold ${toneClasses(item.tone)}`, item.badge));
                list.appendChild(itemNode);
            });
            card.appendChild(list);
        }

        if (response.action?.url) {
            const actionWrap = element('div', 'border-t border-slate-100 p-3 dark:border-white/10');
            const action = element('a', 'inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition-all duration-300 hover:-translate-y-0.5 hover:border-teal-300 hover:text-teal-700 dark:border-white/10 dark:text-white dark:hover:border-teal-400/30', response.action.label);
            action.href = response.action.url;
            const icon = document.createElement('i');
            icon.dataset.lucide = 'arrow-up-right';
            icon.className = 'h-3.5 w-3.5';
            action.appendChild(icon);
            actionWrap.appendChild(action);
            card.appendChild(actionWrap);
        }

        row.appendChild(card);
        messages.appendChild(row);

        if (Array.isArray(response.suggestions) && response.suggestions.length) {
            const suggestions = element('div', 'flex flex-wrap gap-1.5 pl-1');
            response.suggestions.slice(0, 4).forEach((prompt) => {
                const button = element('button', 'rounded-full border border-slate-200 bg-white px-2.5 py-1.5 text-left text-[11px] font-medium text-slate-600 transition hover:border-teal-300 hover:text-teal-700 dark:border-white/10 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-teal-200', prompt);
                button.type = 'button';
                button.addEventListener('click', () => sendQuestion(prompt));
                suggestions.appendChild(button);
            });
            messages.appendChild(suggestions);
        }

        refreshIcons();
        scrollToLatest();
    }

    function renderError(message) {
        renderResponse({
            title: t('Không thể hoàn tất truy vấn', 'Unable to complete query'),
            summary: message,
            severity: 'critical',
            metrics: [],
            items: [],
            suggestions: []
        });
    }

    function setBusy(value) {
        busy = value;
        typing.classList.toggle('hidden', !value);
        typing.classList.toggle('flex', value);
        input.disabled = value;
        sendButton.disabled = value;
        if (value) scrollToLatest();
    }

    async function request(action, extra = {}) {
        const body = new FormData();
        body.set('action', action);
        body.set('language', language());
        body.set('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        Object.entries(extra).forEach(([key, value]) => body.set(key, value));

        const response = await fetch(endpoint, {
            method: 'POST',
            body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const payload = await response.json();
        if (!response.ok || !payload.ok) throw new Error(payload.message || t('Trợ lý không phản hồi.', 'Assistant did not respond.'));
        return payload;
    }

    async function loadHistory() {
        if (loaded) return;
        setBusy(true);
        try {
            const payload = await request('history');
            messages.replaceChildren();
            if (!payload.messages.length) {
                renderResponse(payload.welcome);
            } else {
                payload.messages.forEach((message) => {
                    if (message.sender === 'User') renderUserMessage(message.message_text);
                    if (message.sender === 'Assistant' && message.response) renderResponse(message.response);
                });
            }
            loaded = true;
        } catch (error) {
            renderError(error.message);
        } finally {
            setBusy(false);
        }
    }

    async function sendQuestion(question) {
        const clean = String(question || '').trim();
        if (!clean || busy) return;
        renderUserMessage(clean);
        input.value = '';
        input.style.height = 'auto';
        setBusy(true);
        try {
            const payload = await request('ask', { question: clean });
            renderResponse(payload.message);
        } catch (error) {
            renderError(error.message);
        } finally {
            setBusy(false);
            input.focus();
        }
    }

    async function resetConversation() {
        if (busy) return;
        setBusy(true);
        try {
            const payload = await request('reset');
            messages.replaceChildren();
            renderResponse(payload.welcome);
            loaded = true;
        } catch (error) {
            renderError(error.message);
        } finally {
            setBusy(false);
        }
    }

    function openPanel() {
        panel.classList.remove('hidden');
        panel.classList.add('flex');
        panel.setAttribute('aria-hidden', 'false');
        openButton.setAttribute('aria-expanded', 'true');
        loadHistory().then(() => input.focus());
    }

    function closePanel() {
        panel.classList.add('hidden');
        panel.classList.remove('flex');
        panel.setAttribute('aria-hidden', 'true');
        openButton.setAttribute('aria-expanded', 'false');
    }

    openButton.addEventListener('click', () => panel.classList.contains('hidden') ? openPanel() : closePanel());
    closeButton.addEventListener('click', closePanel);
    resetButton.addEventListener('click', resetConversation);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        sendQuestion(input.value);
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 112)}px`;
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.classList.contains('hidden')) closePanel();
    });
    document.addEventListener('app:languagechange', () => {
        loaded = false;
        if (!panel.classList.contains('hidden')) {
            messages.replaceChildren();
            loadHistory();
        }
    });
})();
