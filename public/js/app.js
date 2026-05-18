/* Habit Tracker — UI helpers */
(function () {
    'use strict';

    const html = document.documentElement;

    /* ---------- Theme ---------- */
    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        try { localStorage.setItem('ht-theme', theme); } catch (e) {}
        document.querySelectorAll('.theme-toggle button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });
    }
    function initTheme() {
        let theme = html.getAttribute('data-theme');
        try {
            const saved = localStorage.getItem('ht-theme');
            if (saved) theme = saved;
        } catch (e) {}
        if (!theme) theme = 'auto';
        applyTheme(theme);
        document.querySelectorAll('.theme-toggle button').forEach(btn => {
            btn.addEventListener('click', () => {
                const t = btn.dataset.theme;
                applyTheme(t);
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                if (csrf) {
                    fetch('/theme', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ theme: t }),
                    }).catch(() => {});
                }
            });
        });
    }

    /* ---------- Toasts ---------- */
    window.toast = function (msg, type = 'info', ttl = 3500) {
        const c = document.getElementById('toasts');
        if (!c) { console.log('[toast]', msg); return; }
        const t = document.createElement('div');
        t.className = 'toast toast-' + type;
        t.innerHTML = (type === 'success' ? '✓ ' : type === 'error' ? '⚠️ ' : 'ℹ️ ') + msg;
        c.appendChild(t);
        requestAnimationFrame(() => t.classList.add('shown'));
        setTimeout(() => {
            t.classList.remove('shown');
            setTimeout(() => t.remove(), 400);
        }, ttl);
    };

    /* ---------- Flash → toast ---------- */
    function initFlash() {
        document.querySelectorAll('.flash').forEach(el => {
            window.toast(el.textContent.trim(), 'success', 4000);
            el.remove();
        });
    }

    /* ---------- AI suggestion fetch ---------- */
    function initAi() {
        const btn = document.querySelector('[data-ai-generate]');
        if (!btn) return;

        const url     = btn.dataset.aiGenerate;
        const out     = document.querySelector('[data-ai-output]');
        const spinner = document.querySelector('[data-ai-spinner]');
        const stamp   = document.querySelector('[data-ai-stamp]');

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            out.textContent = 'Claude думает над вашими данными…';

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const res  = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                const json = await res.json();
                if (!json.ok) {
                    out.textContent = '⚠️ ' + (json.error || 'Не удалось получить ответ.');
                    window.toast(json.error || 'Не удалось получить ответ', 'error');
                } else {
                    out.textContent = json.response;
                    if (stamp) stamp.textContent = json.created_at;
                    window.toast('Совет от Claude готов', 'success');
                }
            } catch (e) {
                out.textContent = '⚠️ Ошибка сети: ' + e.message;
                window.toast('Ошибка сети: ' + e.message, 'error');
            } finally {
                btn.disabled = false;
                if (spinner) spinner.style.display = 'none';
            }
        });
    }

    /* ---------- Calendar click → fill form + scroll ---------- */
    function initCalendar() {
        document.querySelectorAll('[data-day]').forEach(cell => {
            cell.addEventListener('click', () => {
                const date = cell.dataset.day;
                const input = document.querySelector('input[name="log_date"]');
                if (input) {
                    input.value = date;
                    input.dispatchEvent(new Event('change'));
                    // плавный скролл
                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // подсветить форму
                    const card = input.closest('.card');
                    if (card) {
                        card.classList.add('highlight');
                        setTimeout(() => card.classList.remove('highlight'), 1400);
                    }
                    window.toast('Выбрана дата ' + date.split('-').reverse().join('.'), 'info', 1800);
                }
            });
        });
    }

    /* ---------- Keyboard shortcuts on day form ---------- */
    function initShortcuts() {
        const statusSelect = document.querySelector('select[name="status"]');
        if (!statusSelect) return;
        document.addEventListener('keydown', (e) => {
            // не реагируем, если фокус в textarea/input (кроме самого select)
            if (['TEXTAREA', 'INPUT'].includes(document.activeElement.tagName)) return;
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            const k = e.key.toLowerCase();
            const map = { '+': 'success', '=': 'success', '-': 'fail', '*': 'skipped' };
            if (map[k]) {
                statusSelect.value = map[k];
                statusSelect.dispatchEvent(new Event('change'));
                window.toast('Статус: ' + statusSelect.options[statusSelect.selectedIndex].text.trim(), 'info', 1500);
                e.preventDefault();
            }
        });
    }

    /* ---------- Tooltip helpers for heatmap (mobile) ---------- */
    function initHeatmapTooltips() {
        const cells = document.querySelectorAll('.hm-cell[title]');
        let tip;
        function ensureTip() {
            if (!tip) {
                tip = document.createElement('div');
                tip.className = 'hm-tooltip';
                document.body.appendChild(tip);
            }
            return tip;
        }
        cells.forEach(c => {
            c.addEventListener('mouseenter', e => {
                const t = ensureTip();
                t.textContent = c.getAttribute('title');
                t.style.display = 'block';
                const r = c.getBoundingClientRect();
                t.style.left = (r.left + window.scrollX + r.width / 2) + 'px';
                t.style.top  = (r.top + window.scrollY - 30) + 'px';
            });
            c.addEventListener('mouseleave', () => { if (tip) tip.style.display = 'none'; });
            // снимаем нативный title чтобы не дублировался
            c.dataset.tip = c.getAttribute('title');
            c.removeAttribute('title');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initTheme();
        initFlash();
        initAi();
        initCalendar();
        initShortcuts();
        initHeatmapTooltips();
    });
})();
