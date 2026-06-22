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
                setTimeout(() => window.renderAssetOsChart && window.renderAssetOsChart(), 80);
            });
        });
    }

    function initCopyButtons() {
        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-copy-text]');
            if (!button) return;

            const value = button.dataset.copyText || '';
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(value);
                } else {
                    const input = document.createElement('textarea');
                    input.value = value;
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    input.remove();
                }
                window.showToast('success', window.appI18n?.t('Đã sao chép link tải.') || 'Đã sao chép link tải.');
            } catch (error) {
                window.showToast('error', window.appI18n?.t('Không thể sao chép link tải.') || 'Không thể sao chép link tải.');
            }
        });
    }

    function initUserDetailModal() {
        const modal = document.getElementById('user-detail-modal');
        if (!modal) return;

        const loading = modal.querySelector('[data-user-detail-loading]');
        const errorBox = modal.querySelector('[data-user-detail-error]');
        const content = modal.querySelector('[data-user-detail-content]');
        const rows = modal.querySelector('[data-user-license-rows]');
        const translate = (value) => window.appI18n?.t(value) || value;

        const close = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        };

        const open = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        };

        const setState = (state, message = '') => {
            loading.classList.toggle('hidden', state !== 'loading');
            errorBox.classList.toggle('hidden', state !== 'error');
            content.classList.toggle('hidden', state !== 'content');
            errorBox.textContent = message;
        };

        const createCell = (text, className = 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300') => {
            const cell = document.createElement('td');
            cell.className = className;
            cell.textContent = text;
            return cell;
        };

        const renderLicenses = (licenses) => {
            rows.replaceChildren();

            if (!licenses.length) {
                const row = document.createElement('tr');
                const cell = createCell(translate('Người dùng chưa có lịch sử license.'), 'px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400');
                cell.colSpan = 4;
                row.appendChild(cell);
                rows.appendChild(row);
                return;
            }

            licenses.forEach((license) => {
                const row = document.createElement('tr');
                row.className = 'transition-colors hover:bg-slate-50/80 dark:hover:bg-white/5';

                const software = document.createElement('td');
                software.className = 'px-4 py-3';
                const name = document.createElement('p');
                name.className = 'font-medium text-slate-900 dark:text-white';
                name.textContent = license.software_name;
                const vendor = document.createElement('p');
                vendor.className = 'text-xs text-slate-500 dark:text-slate-400';
                vendor.textContent = license.vendor;
                software.append(name, vendor);
                row.appendChild(software);

                row.appendChild(createCell(license.masked_key || '********', 'px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300'));
                row.appendChild(createCell(`${license.start_date} → ${license.end_date}`));

                const statusLabels = { Active: 'Đang active', Expired: 'Hết hạn', Revoked: 'Đã thu hồi' };
                const statusCell = createCell(translate(statusLabels[license.status] || license.status), 'px-4 py-3 text-right');
                const status = document.createElement('span');
                const statusPalette = license.status === 'Active'
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200'
                    : license.status === 'Revoked'
                        ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200'
                        : 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-200';
                status.className = `rounded-lg px-2.5 py-1 text-xs font-semibold ${statusPalette}`;
                status.textContent = translate(statusLabels[license.status] || license.status);
                statusCell.replaceChildren(status);
                row.appendChild(statusCell);
                rows.appendChild(row);
            });
        };

        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-user-detail-id]');
            if (!button) return;

            open();
            setState('loading');

            try {
                const formData = new FormData();
                formData.set('action', 'user_detail');
                formData.set('id', button.dataset.userDetailId);
                formData.set('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

                const response = await fetch(button.dataset.userDetailUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await response.json();
                if (!response.ok || !payload.ok) throw new Error(payload.message || translate('Không thể tải chi tiết người dùng.'));

                modal.querySelectorAll('[data-user-field]').forEach((field) => {
                    const key = field.dataset.userField;
                    const roleLabels = { Student: 'Sinh viên', Teacher: 'Giảng viên', Admin: 'Quản trị' };
                    field.textContent = key === 'role' ? translate(roleLabels[payload.user[key]] || payload.user[key]) : (payload.user[key] || '—');
                });
                renderLicenses(payload.licenses || []);
                setState('content');
            } catch (error) {
                setState('error', error.message || translate('Không thể tải chi tiết người dùng.'));
            }
        });

        modal.querySelectorAll('[data-user-detail-close]').forEach((button) => button.addEventListener('click', close));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });
    }

    function tableRows(table) {
        return Array.from(table.querySelectorAll('tbody tr'));
    }

    function tablePageSize(table) {
        const size = parseInt(table.dataset.pageSize || '0', 10);
        return Number.isFinite(size) && size > 0 ? size : 0;
    }

    function tableMatches(row, query) {
        return !query || row.textContent.toLowerCase().includes(query);
    }

    function compactPageNumbers(currentPage, totalPages) {
        const pages = new Set([1, totalPages, currentPage, currentPage - 1, currentPage + 1]);

        if (currentPage <= 3) {
            [2, 3, 4].forEach((page) => pages.add(page));
        }

        if (currentPage >= totalPages - 2) {
            [totalPages - 3, totalPages - 2, totalPages - 1].forEach((page) => pages.add(page));
        }

        return Array.from(pages)
            .filter((page) => page >= 1 && page <= totalPages)
            .sort((a, b) => a - b);
    }

    function pagerButton(label, tableId, page, isActive, isDisabled) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = label;
        button.dataset.pageTarget = tableId;
        button.dataset.page = String(page);
        button.disabled = isDisabled;
        if (isActive) {
            button.setAttribute('aria-current', 'page');
        }
        button.className = isActive
            ? 'inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-950 bg-slate-950 px-3 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 transition-all duration-300 hover:-translate-y-1 hover:shadow-md dark:border-white/20 dark:bg-slate-950 dark:text-white'
            : 'inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200/80 bg-white/75 px-3 text-sm font-semibold text-slate-600 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-teal-300 hover:text-teal-700 hover:shadow-md disabled:pointer-events-none disabled:opacity-40 dark:border-white/20 dark:bg-slate-900/80 dark:text-white dark:hover:border-teal-300/50 dark:hover:bg-slate-800 dark:hover:text-white';

        return button;
    }

    function renderTablePager(table, meta) {
        const pager = document.querySelector(`[data-table-pager="${table.id}"]`);
        if (!pager) return;

        pager.innerHTML = '';

        const wrap = document.createElement('div');
        wrap.className = 'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between';

        const info = document.createElement('p');
        info.className = 'text-xs font-medium text-slate-500 dark:text-slate-400';
        info.textContent = meta.filteredCount
            ? `Hiển thị ${meta.from}-${meta.to} / ${meta.filteredCount} nội dung`
            : 'Không có nội dung phù hợp';
        wrap.appendChild(info);

        if (meta.totalPages > 1) {
            const controls = document.createElement('div');
            controls.className = 'flex max-w-full items-center gap-2 overflow-x-auto pb-1';

            controls.appendChild(pagerButton('Trước', table.id, meta.currentPage - 1, false, meta.currentPage === 1));

            const chips = document.createElement('div');
            chips.className = 'flex items-center gap-1';
            let previousPage = 0;
            compactPageNumbers(meta.currentPage, meta.totalPages).forEach((page) => {
                if (previousPage && page - previousPage > 1) {
                    const gap = document.createElement('span');
                    gap.className = 'px-1 text-sm font-semibold text-slate-400';
                    gap.textContent = '...';
                    chips.appendChild(gap);
                }

                chips.appendChild(pagerButton(String(page), table.id, page, page === meta.currentPage, false));
                previousPage = page;
            });
            controls.appendChild(chips);

            const select = document.createElement('select');
            select.dataset.pageTarget = table.id;
            select.className = 'h-9 rounded-xl border border-slate-200/80 bg-white/80 px-3 text-sm font-semibold text-slate-600 shadow-sm outline-none transition-all duration-300 hover:border-teal-300 focus:border-teal-400 focus:ring-4 focus:ring-teal-100 dark:border-white/20 dark:bg-slate-950 dark:text-white dark:focus:ring-teal-400/10';
            for (let page = 1; page <= meta.totalPages; page += 1) {
                const option = document.createElement('option');
                option.value = String(page);
                option.textContent = `Trang ${page}`;
                select.appendChild(option);
            }
            select.value = String(meta.currentPage);
            controls.appendChild(select);

            controls.appendChild(pagerButton('Sau', table.id, meta.currentPage + 1, false, meta.currentPage === meta.totalPages));
            wrap.appendChild(controls);
        }

        pager.appendChild(wrap);
    }

    function applyTableState(table) {
        const rows = tableRows(table);
        const pageSize = tablePageSize(table);
        const query = table.dataset.filterQuery || '';
        const matchingRows = rows.filter((row) => tableMatches(row, query));
        const totalPages = pageSize ? Math.max(1, Math.ceil(matchingRows.length / pageSize)) : 1;
        const requestedPage = parseInt(table.dataset.currentPage || '1', 10);
        const currentPage = Math.min(Math.max(Number.isFinite(requestedPage) ? requestedPage : 1, 1), totalPages);
        const firstIndex = pageSize ? (currentPage - 1) * pageSize : 0;
        const lastIndex = pageSize ? firstIndex + pageSize : matchingRows.length;
        let matchIndex = 0;

        table.dataset.currentPage = String(currentPage);

        rows.forEach((row) => {
            const isMatch = tableMatches(row, query);
            let isVisible = isMatch;

            if (isMatch && pageSize) {
                isVisible = matchIndex >= firstIndex && matchIndex < lastIndex;
                matchIndex += 1;
            }

            row.hidden = !isVisible;
            row.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            row.style.display = isVisible ? '' : 'none';
        });

        if (pageSize) {
            renderTablePager(table, {
                currentPage,
                filteredCount: matchingRows.length,
                from: matchingRows.length ? firstIndex + 1 : 0,
                to: Math.min(lastIndex, matchingRows.length),
                totalPages
            });
        }
    }

    function initTableFilters() {
        document.querySelectorAll('[data-table-filter]').forEach((input) => {
            const table = document.getElementById(input.dataset.tableFilter);
            if (!table) return;

            table.dataset.filterQuery = input.value.trim().toLowerCase();
            input.addEventListener('input', () => {
                table.dataset.filterQuery = input.value.trim().toLowerCase();
                table.dataset.currentPage = '1';
                applyTableState(table);
            });
        });
    }

    function initTablePagination() {
        document.querySelectorAll('table[data-page-size]').forEach((table) => {
            table.dataset.currentPage = table.dataset.currentPage || '1';
            applyTableState(table);
        });

        document.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-page-target]');
            if (!button) return;

            const table = document.getElementById(button.dataset.pageTarget);
            if (!table) return;

            table.dataset.currentPage = button.dataset.page || '1';
            applyTableState(table);
        });

        document.addEventListener('change', (event) => {
            const select = event.target.closest('select[data-page-target]');
            if (!select) return;

            const table = document.getElementById(select.dataset.pageTarget);
            if (!table) return;

            table.dataset.currentPage = select.value;
            applyTableState(table);
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

        const translate = window.appI18n?.t || ((value) => value);
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
                        { label: translate('Đã cấp'), data: window.dashboardCharts.inventory.used, backgroundColor: '#6366f1', borderRadius: 6 },
                        { label: translate('Còn trống'), data: window.dashboardCharts.inventory.available, backgroundColor: '#14b8a6', borderRadius: 6 }
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

    window.renderAssetOsChart = function () {
        const canvas = document.getElementById('assetOsChart');
        if (!window.Chart || !canvas || !window.assetOsStats) return;

        const dark = document.documentElement.classList.contains('dark');
        const labelColor = dark ? '#cbd5e1' : '#475569';
        if (window.__assetOsChart) window.__assetOsChart.destroy();

        window.__assetOsChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: window.assetOsStats.map((item) => item.os_type),
                datasets: [{
                    data: window.assetOsStats.map((item) => Number(item.total)),
                    backgroundColor: ['#14b8a6', '#6366f1', '#f59e0b', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                animation: { duration: 1500, easing: 'easeOutQuart' },
                plugins: {
                    legend: { position: 'bottom', labels: { color: labelColor, usePointStyle: true, boxWidth: 8 } }
                }
            }
        });
    };

    document.addEventListener('app:languagechange', () => {
        window.renderDashboardCharts();
        window.renderAssetOsChart();
    });

    document.addEventListener('DOMContentLoaded', () => {
        iconRefresh();
        initTheme();
        initTableFilters();
        initTablePagination();
        initConfirmModal();
        initCopyButtons();
        initUserDetailModal();

        if (window.__FLASH__) {
            window.showToast(window.__FLASH__.type, window.__FLASH__.message);
        }

        window.initRuleSuggestions();
        window.renderDashboardCharts();
        window.renderAssetOsChart();
    });
})();
