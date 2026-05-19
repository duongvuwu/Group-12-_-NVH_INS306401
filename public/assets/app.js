(function () {
    const toastRoot = () => document.getElementById('toast-root');
    let pendingForm = null;

    function iconRefresh() {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    window.showToast = function (type, message) {
        const root = toastRoot();
        if (!root || !message) return;

        const palette = type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-500/15 dark:text-emerald-100'
            : 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-400/20 dark:bg-rose-500/15 dark:text-rose-100';

        const icon = type === 'success' ? 'check-circle-2' : 'circle-alert';
        const item = document.createElement('div');
        item.className = `translate-x-5 rounded-lg border px-4 py-3 opacity-0 shadow-xl backdrop-blur transition duration-300 ${palette}`;
        item.innerHTML = `
            <div class="flex items-start gap-3">
                <i data-lucide="${icon}" class="mt-0.5 h-5 w-5 shrink-0"></i>
                <p class="max-w-sm text-sm font-medium">${escapeHtml(message)}</p>
                <button type="button" class="ml-2 text-current/60 transition hover:text-current" aria-label="Đóng">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
        `;
        root.appendChild(item);
        iconRefresh();

        requestAnimationFrame(() => {
            item.classList.remove('translate-x-5', 'opacity-0');
        });

        const close = () => {
            item.classList.add('translate-x-5', 'opacity-0');
            setTimeout(() => item.remove(), 280);
        };

        item.querySelector('button')?.addEventListener('click', close);
        setTimeout(close, 5200);
    };

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function initTheme() {
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('license-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                setTimeout(() => window.renderDashboardCharts && window.renderDashboardCharts(), 80);
            });
        });
    }

    function initTableFilters() {
        document.querySelectorAll('[data-table-filter]').forEach((input) => {
            input.addEventListener('input', () => {
                const table = document.getElementById(input.dataset.tableFilter);
                if (!table) return;

                const query = input.value.trim().toLowerCase();
                table.querySelectorAll('tbody tr').forEach((row) => {
                    row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
                });
            });
        });
    }

    function openConfirm(form) {
        const modal = document.getElementById('confirm-modal');
        const body = document.getElementById('confirm-body');
        const reasonWrap = document.getElementById('confirm-reason-wrap');
        const reasonInput = document.getElementById('confirm-reason');

        if (!modal || !body || !reasonWrap || !reasonInput) {
            form.submit();
            return;
        }

        pendingForm = form;
        body.textContent = form.dataset.confirmMessage || 'Xác nhận thực hiện thao tác này?';
        reasonInput.value = '';
        reasonWrap.classList.toggle('hidden', !form.hasAttribute('data-confirm-reason'));
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        iconRefresh();

        if (form.hasAttribute('data-confirm-reason')) {
            setTimeout(() => reasonInput.focus(), 80);
        }
    }

    function closeConfirm() {
        const modal = document.getElementById('confirm-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingForm = null;
    }

    function initConfirmModal() {
        document.querySelectorAll('form[data-confirm-submit]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                openConfirm(form);
            });
        });

        document.querySelector('[data-confirm-cancel]')?.addEventListener('click', closeConfirm);
        document.getElementById('confirm-modal')?.addEventListener('click', (event) => {
            if (event.target.id === 'confirm-modal') {
                closeConfirm();
            }
        });

        document.querySelector('[data-confirm-accept]')?.addEventListener('click', () => {
            if (!pendingForm) return;

            if (pendingForm.hasAttribute('data-confirm-reason')) {
                const reasonInput = document.getElementById('confirm-reason');
                const reason = reasonInput?.value.trim() || '';

                if (!reason) {
                    window.showToast('error', 'Cần nhập lý do thu hồi.');
                    reasonInput?.focus();
                    return;
                }

                let hidden = pendingForm.querySelector('input[name="reason"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'reason';
                    pendingForm.appendChild(hidden);
                }
                hidden.value = reason;
            }

            const form = pendingForm;
            closeConfirm();
            HTMLFormElement.prototype.submit.call(form);
        });
    }

    window.initRuleSuggestions = function () {
        const departmentSelect = document.querySelector('[data-rule-department]');
        const softwareSelect = document.querySelector('[data-rule-software]');
        const root = document.querySelector('[data-rule-suggestions]');
        const suggestions = window.ruleSuggestions || {};

        if (!departmentSelect || !softwareSelect || !root) return;

        const render = () => {
            const items = suggestions[departmentSelect.value] || [];
            root.innerHTML = '';

            if (!items.length) {
                root.innerHTML = '<span class="text-sm text-slate-500">Chưa có phần mềm gợi ý trong danh mục.</span>';
                return;
            }

            items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'rounded-lg border border-teal-200 bg-white/75 px-3 py-1.5 text-sm font-semibold text-teal-700 transition hover:bg-teal-600 hover:text-white dark:border-teal-400/20 dark:bg-slate-900/60 dark:text-teal-200';
                button.textContent = `${item.name} · ${item.vendor}`;
                button.addEventListener('click', () => {
                    softwareSelect.value = String(item.id);
                    window.showToast('success', `Đã chọn gợi ý ${item.name}.`);
                });
                root.appendChild(button);
            });
        };

        departmentSelect.addEventListener('change', render);
        render();
    };

    window.renderDashboardCharts = function () {
        if (!window.Chart || !window.dashboardCharts) return;

        const dark = document.documentElement.classList.contains('dark');
        const gridColor = dark ? 'rgba(148, 163, 184, .18)' : 'rgba(148, 163, 184, .28)';
        const labelColor = dark ? '#cbd5e1' : '#475569';
        const inventoryCanvas = document.getElementById('inventoryChart');
        const departmentCanvas = document.getElementById('departmentChart');

        if (window.__inventoryChart) window.__inventoryChart.destroy();
        if (window.__departmentChart) window.__departmentChart.destroy();

        if (inventoryCanvas) {
            window.__inventoryChart = new Chart(inventoryCanvas, {
                type: 'bar',
                data: {
                    labels: window.dashboardCharts.inventory.labels,
                    datasets: [
                        { label: 'Đã cấp', data: window.dashboardCharts.inventory.used, backgroundColor: '#6366f1', borderRadius: 6 },
                        { label: 'Còn trống', data: window.dashboardCharts.inventory.available, backgroundColor: '#14b8a6', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        x: { stacked: true, ticks: { color: labelColor }, grid: { display: false } },
                        y: { stacked: true, ticks: { color: labelColor, precision: 0 }, grid: { color: gridColor } }
                    },
                    plugins: {
                        legend: { labels: { color: labelColor, usePointStyle: true, boxWidth: 8 } }
                    }
                }
            });
        }

        if (departmentCanvas) {
            window.__departmentChart = new Chart(departmentCanvas, {
                type: 'doughnut',
                data: {
                    labels: window.dashboardCharts.departments.labels,
                    datasets: [{
                        data: window.dashboardCharts.departments.values,
                        backgroundColor: ['#14b8a6', '#6366f1', '#f59e0b', '#06b6d4', '#ef4444', '#84cc16'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    cutout: '64%',
                    plugins: {
                        legend: { position: 'bottom', labels: { color: labelColor, usePointStyle: true, boxWidth: 8 } }
                    }
                }
            });
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        iconRefresh();
        initTheme();
        initTableFilters();
        initConfirmModal();

        if (window.__FLASH__) {
            window.showToast(window.__FLASH__.type, window.__FLASH__.message);
        }

        window.initRuleSuggestions();
        window.renderDashboardCharts();
    });
})();
