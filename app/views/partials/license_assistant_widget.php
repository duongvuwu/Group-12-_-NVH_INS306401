<?php
$assistantEmoji = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Shield.png';
?>
<div
    id="license-assistant"
    class="fixed bottom-20 right-4 z-[70] flex flex-col items-end lg:bottom-6 lg:right-6"
    data-assistant-endpoint="<?= e(app_url('assistant')) ?>"
>
    <section
        id="assistant-panel"
        class="mb-3 hidden max-h-[min(72vh,640px)] w-[min(400px,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border border-white/60 bg-white/95 shadow-2xl shadow-slate-950/20 backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/95"
        role="dialog"
        aria-modal="false"
        aria-labelledby="assistant-title"
        aria-hidden="true"
    >
        <header class="relative overflow-hidden border-b border-slate-200/80 px-4 py-3 dark:border-white/10">
            <div class="pointer-events-none absolute -right-8 -top-10 h-28 w-28 rounded-full bg-teal-300/25 blur-2xl dark:bg-teal-500/15"></div>
            <div class="relative flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-white/70 bg-white/80 shadow-md dark:border-white/10 dark:bg-white/10">
                    <img src="<?= e($assistantEmoji) ?>" alt="" class="h-8 w-8 object-contain drop-shadow-lg">
                </span>
                <div class="min-w-0 flex-1">
                    <h2 id="assistant-title" class="truncate font-semibold text-slate-950 dark:text-white">LicenseOS Assistant</h2>
                    <p class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,.12)]"></span>
                        Dữ liệu trực tiếp
                    </p>
                </div>
                <button type="button" data-assistant-reset class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-500 transition-all duration-300 hover:-translate-y-0.5 hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white" title="Cuộc trò chuyện mới">
                    <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                </button>
                <button type="button" data-assistant-close class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-500 transition-all duration-300 hover:-translate-y-0.5 hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white" title="Đóng trợ lý">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
        </header>

        <div data-assistant-messages class="min-h-64 flex-1 space-y-4 overflow-y-auto bg-slate-50/70 px-4 py-4 dark:bg-slate-950/40" aria-live="polite"></div>

        <div data-assistant-typing class="hidden items-center gap-2 border-t border-slate-200/70 px-4 py-2 text-xs text-slate-500 dark:border-white/10 dark:text-slate-400">
            <span class="flex gap-1">
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-teal-500 [animation-delay:-.3s]"></span>
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-teal-500 [animation-delay:-.15s]"></span>
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-teal-500"></span>
            </span>
            Đang phân tích dữ liệu...
        </div>

        <form data-assistant-form class="border-t border-slate-200/80 bg-white/90 p-3 dark:border-white/10 dark:bg-slate-950/90">
            <div class="flex items-end gap-2 rounded-2xl border border-slate-300/80 bg-white p-2 shadow-sm transition focus-within:border-teal-500 focus-within:ring-4 focus-within:ring-teal-500/10 dark:border-white/15 dark:bg-slate-900">
                <textarea
                    data-assistant-input
                    rows="1"
                    maxlength="500"
                    class="max-h-28 min-h-10 flex-1 resize-none bg-transparent px-2 py-2 text-sm text-slate-900 outline-none placeholder:text-slate-400 dark:text-white"
                    placeholder="Hỏi về license, hết hạn, tồn kho..."
                    aria-label="Câu hỏi cho LicenseOS Assistant"
                ></textarea>
                <button type="submit" data-assistant-send class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-teal-600 text-white shadow-lg shadow-teal-600/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50" title="Gửi câu hỏi">
                    <i data-lucide="send" class="h-4 w-4"></i>
                </button>
            </div>
            <p class="mt-2 px-1 text-[11px] text-slate-400">Truy vấn chỉ đọc, không hiển thị license key.</p>
        </form>
    </section>

    <button
        type="button"
        data-assistant-open
        class="group relative flex h-14 items-center gap-2 rounded-2xl border border-white/60 bg-slate-950 px-3 text-white shadow-2xl shadow-slate-950/25 transition-all duration-300 hover:-translate-y-1 hover:shadow-glow dark:border-white/15 dark:bg-teal-600"
        aria-controls="assistant-panel"
        aria-expanded="false"
        title="Mở LicenseOS Assistant"
    >
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 transition-transform duration-300 group-hover:scale-105">
            <img src="<?= e($assistantEmoji) ?>" alt="" class="h-8 w-8 object-contain drop-shadow-lg">
        </span>
        <span class="hidden pr-2 text-sm font-semibold sm:block">License Copilot</span>
        <span data-assistant-alert class="absolute -right-1 -top-1 hidden h-3 w-3 rounded-full border-2 border-white bg-rose-500 dark:border-slate-950"></span>
    </button>
</div>
