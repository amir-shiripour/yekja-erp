<script id="prevent-double-submit" data-prevent-double-submit>
    (function () {
        if (window.__resellerDoubleSubmitProtected) return;
        window.__resellerDoubleSubmitProtected = true;

        // 1. Intercept Form Submit Events Globally
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            // Allow bypass if explicitly tagged or managed by Alpine AJAX
            if (form.hasAttribute('data-allow-double-submit') ||
                form.hasAttribute('data-no-double-submit') ||
                form.hasAttribute('@submit.prevent') ||
                form.hasAttribute('x-on:submit.prevent')) {
                return;
            }

            // If form is already submitting, block duplicate submission completely
            if (form.dataset.isSubmitting === 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            // Find submit buttons
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])');
            if (submitButtons && submitButtons.length > 0) {
                form.dataset.isSubmitting = 'true';

                submitButtons.forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    btn.setAttribute('aria-disabled', 'true');

                    // Prepend spinner ONLY if button doesn't already have its own spinner or animation
                    const hasExistingSpinner = btn.querySelector('.animate-spin, .reseller-submit-spinner') !== null ||
                        btn.innerHTML.includes('animate-spin') ||
                        btn.hasAttribute('data-no-spinner') ||
                        form.hasAttribute('data-no-spinner');

                    if (!hasExistingSpinner) {
                        const spinner = document.createElement('span');
                        spinner.className = 'reseller-submit-spinner inline-flex items-center justify-center shrink-0 me-2';
                        spinner.innerHTML = `<svg class="animate-spin w-4 h-4 text-current" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>`;
                        btn.prepend(spinner);
                    }
                });

                // Safety fallback: re-enable after 8 seconds if page was not unloaded (e.g. client validation error)
                setTimeout(() => {
                    form.dataset.isSubmitting = 'false';
                    submitButtons.forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                        btn.removeAttribute('aria-disabled');
                        const sp = btn.querySelector('.reseller-submit-spinner');
                        if (sp) sp.remove();
                    });
                }, 8000);
            }
        }, true);

        // 2. Click Interceptor for Non-Form Action Buttons with [data-prevent-double-click]
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-prevent-double-click]');
            if (!btn) return;

            if (btn.dataset.isClickLocked === 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            btn.dataset.isClickLocked = 'true';
            btn.disabled = true;
            btn.classList.add('opacity-60', 'cursor-not-allowed', 'pointer-events-none');

            const delay = parseInt(btn.getAttribute('data-unlock-delay') || '3000', 10);
            setTimeout(() => {
                btn.dataset.isClickLocked = 'false';
                btn.disabled = false;
                btn.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
            }, delay);
        }, true);
    })();
</script>
