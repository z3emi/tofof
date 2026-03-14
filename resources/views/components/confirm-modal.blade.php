@once
    <div id="global-confirm" class="global-confirm d-none" dir="{{ app()->getLocale() === 'en' ? 'ltr' : 'rtl' }}">
        <div class="global-confirm__backdrop" data-confirm-cancel></div>
        <div class="global-confirm__dialog">
            <div class="global-confirm__header">
                <h5 class="global-confirm__title" data-confirm-title>تأكيد الإجراء</h5>
                <button type="button" class="global-confirm__close" data-confirm-cancel aria-label="إغلاق">&times;</button>
            </div>
            <div class="global-confirm__body">
                <p class="global-confirm__message" data-confirm-message></p>
                <div class="global-confirm__extra d-none" data-confirm-extra></div>
            </div>
            <div class="global-confirm__footer">
                <button type="button" class="btn btn-light" data-confirm-cancel data-confirm-set-text>إلغاء</button>
                <button type="button" class="btn btn-danger" data-confirm-accept>تأكيد</button>
            </div>
        </div>
    </div>

    <style>
        #global-confirm.global-confirm {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        #global-confirm.global-confirm.d-none {
            display: none !important;
        }

        .global-confirm__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
        }

        .global-confirm__dialog {
            position: relative;
            width: min(480px, 92vw);
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 24px 64px rgba(15, 23, 42, 0.25);
            overflow: hidden;
            transform: translateY(12px);
            transition: transform 0.2s ease, opacity 0.2s ease;
            opacity: 0;
        }

        .global-confirm__dialog.global-confirm__dialog--visible {
            transform: translateY(0);
            opacity: 1;
        }

        .global-confirm__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        .global-confirm__title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
            color: #0f172a;
        }

        .global-confirm__close {
            border: none;
            background: transparent;
            font-size: 1.5rem;
            line-height: 1;
            color: rgba(15, 23, 42, 0.55);
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .global-confirm__close:hover {
            color: rgba(15, 23, 42, 0.85);
        }

        .global-confirm__body {
            padding: 1.25rem 1.25rem 0.5rem 1.25rem;
            color: #1e293b;
        }

        .global-confirm__message {
            margin-bottom: 0.75rem;
            line-height: 1.6;
        }

        .global-confirm__extra {
            margin-top: 0.5rem;
        }

        #global-confirm .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.45rem 1.2rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        #global-confirm .btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(129, 212, 250, 0.35);
        }

        #global-confirm .btn.btn-light {
            background: #f1f5f9;
            color: #0f172a;
        }

        #global-confirm .btn.btn-light:hover {
            background: #e2e8f0;
        }

        #global-confirm .btn.btn-danger {
            background: #dc2626;
            color: #fff;
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.28);
        }

        #global-confirm .btn.btn-danger:hover {
            background: #b91c1c;
        }

        .global-confirm__footer {
            display: flex;
            justify-content: flex-start;
            gap: 0.5rem;
            padding: 0.85rem 1.25rem 1.25rem 1.25rem;
        }

        @media (prefers-color-scheme: dark) {
            .global-confirm__dialog {
                background: #1f2937;
                color: #f8fafc;
            }

            .global-confirm__header {
                border-bottom-color: rgba(148, 163, 184, 0.2);
            }

            .global-confirm__title {
                color: #f8fafc;
            }

            .global-confirm__message {
                color: #e2e8f0;
            }

            #global-confirm .btn.btn-light {
                background: #334155;
                color: #e2e8f0;
            }

            #global-confirm .btn.btn-light:hover {
                background: #475569;
            }

            #global-confirm .btn.btn-danger {
                background: #f87171;
                box-shadow: 0 8px 16px rgba(248, 113, 113, 0.28);
            }

            #global-confirm .btn.btn-danger:hover {
                background: #ef4444;
            }
        }
    </style>

    <script>
        (function () {
            if (window.CustomConfirm) {
                return;
            }

            const modal = document.getElementById('global-confirm');
            const dialog = modal ? modal.querySelector('.global-confirm__dialog') : null;
            const messageEl = modal ? modal.querySelector('[data-confirm-message]') : null;
            const titleEl = modal ? modal.querySelector('[data-confirm-title]') : null;
            const extraEl = modal ? modal.querySelector('[data-confirm-extra]') : null;
            const cancelButtons = modal ? modal.querySelectorAll('[data-confirm-cancel]') : [];
            const acceptButton = modal ? modal.querySelector('[data-confirm-accept]') : null;

            if (!modal || !dialog || !messageEl || !titleEl || !extraEl || !acceptButton) {
                return;
            }

            let resolver = null;
            let currentOptions = {};

            function close(result) {
                modal.classList.add('d-none');
                dialog.classList.remove('global-confirm__dialog--visible');
                resolver && resolver(result || { confirmed: false });
                resolver = null;
                messageEl.textContent = '';
                titleEl.textContent = 'تأكيد الإجراء';
                extraEl.innerHTML = '';
                extraEl.classList.add('d-none');
                currentOptions = {};
            }

            function open(options) {
                return new Promise((resolve) => {
                    resolver = resolve;
                    currentOptions = Object.assign({
                        title: 'تأكيد الإجراء',
                        message: '',
                        confirmText: 'تأكيد',
                        cancelText: 'إلغاء',
                        templateId: null,
                        extraHtml: null,
                        focusAccept: true,
                    }, options || {});

                    messageEl.textContent = currentOptions.message || '';
                    titleEl.textContent = currentOptions.title || 'تأكيد الإجراء';
                    acceptButton.textContent = currentOptions.confirmText || 'تأكيد';

                    cancelButtons.forEach(btn => {
                        if (btn.hasAttribute('data-confirm-set-text')) {
                            btn.textContent = currentOptions.cancelText || 'إلغاء';
                        }
                    });

                    extraEl.innerHTML = '';
                    if (currentOptions.templateId) {
                        const tpl = document.querySelector(currentOptions.templateId);
                        if (tpl && 'content' in tpl) {
                            extraEl.appendChild(tpl.content.cloneNode(true));
                        } else if (tpl) {
                            extraEl.innerHTML = tpl.innerHTML;
                        }
                    } else if (currentOptions.extraHtml) {
                        extraEl.innerHTML = currentOptions.extraHtml;
                    }

                    if (extraEl.children.length) {
                        extraEl.classList.remove('d-none');
                    } else {
                        extraEl.classList.add('d-none');
                    }

                    modal.classList.remove('d-none');
                    requestAnimationFrame(() => {
                        dialog.classList.add('global-confirm__dialog--visible');
                        if (currentOptions.focusAccept !== false) {
                            acceptButton.focus({ preventScroll: true });
                        }
                    });
                });
            }

            cancelButtons.forEach(btn => {
                btn.addEventListener('click', () => close({ confirmed: false }));
            });

            acceptButton.addEventListener('click', () => {
                if (typeof currentOptions.onBeforeConfirm === 'function') {
                    const result = currentOptions.onBeforeConfirm(extraEl, acceptButton);
                    if (result === false) {
                        return;
                    }
                }
                close({ confirmed: true, extra: extraEl });
            });

            window.CustomConfirm = {
                open,
                close,
                handlers: {},
                confirm(message, options) {
                    const confirmOptions = Object.assign({}, options || {}, { message });

                    if (!message) {
                        return Promise.resolve(true);
                    }

                    return open(confirmOptions).then(result => !!(result && result.confirmed));
                }
            };

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('d-none')) {
                    event.preventDefault();
                    close({ confirmed: false });
                }
            });

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                const message = form.dataset.confirmMessage;
                if (!message || form.dataset.confirmState === 'confirmed') {
                    return;
                }

                event.preventDefault();

                CustomConfirm.open({
                    message,
                    confirmText: form.dataset.confirmConfirmText || 'تأكيد',
                    cancelText: form.dataset.confirmCancelText || 'إلغاء',
                    title: form.dataset.confirmTitle || 'تأكيد الإجراء'
                }).then(result => {
                    if (!result.confirmed) {
                        return;
                    }

                    form.dataset.confirmState = 'confirmed';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                    setTimeout(() => {
                        delete form.dataset.confirmState;
                    }, 100);
                });
            }, true);

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('[data-confirm-trigger]');
                if (!trigger) {
                    return;
                }

                const message = trigger.dataset.confirmMessage;
                if (!message) {
                    return;
                }

                event.preventDefault();

                const handlerKey = trigger.dataset.confirmHandler || null;
                const templateId = trigger.dataset.confirmTemplate || null;

                CustomConfirm.open({
                    message,
                    confirmText: trigger.dataset.confirmConfirmText || 'تأكيد',
                    cancelText: trigger.dataset.confirmCancelText || 'إلغاء',
                    title: trigger.dataset.confirmTitle || 'تأكيد الإجراء',
                    templateId,
                    onBeforeConfirm(extraEl) {
                        if (!handlerKey) {
                            return true;
                        }

                        const handler = CustomConfirm.handlers[handlerKey];
                        if (typeof handler === 'function') {
                            return handler(trigger, extraEl) !== false;
                        }

                        return true;
                    }
                }).then(result => {
                    if (!result.confirmed) {
                        return;
                    }

                    const explicitFormId = trigger.dataset.confirmForm || trigger.getAttribute('form');
                    if (explicitFormId) {
                        const targetForm = document.getElementById(explicitFormId);
                        if (targetForm) {
                            targetForm.dataset.confirmState = 'confirmed';
                            if (typeof targetForm.requestSubmit === 'function') {
                                targetForm.requestSubmit(trigger);
                            } else {
                                targetForm.submit();
                            }
                            setTimeout(() => {
                                delete targetForm.dataset.confirmState;
                            }, 100);
                            return;
                        }
                    }

                    const href = trigger.getAttribute('href');
                    if (href && href !== '#') {
                        window.location.href = href;
                        return;
                    }

                    const closestForm = trigger.closest('form');
                    if (closestForm) {
                        closestForm.dataset.confirmState = 'confirmed';
                        if (typeof closestForm.requestSubmit === 'function') {
                            closestForm.requestSubmit(trigger);
                        } else {
                            closestForm.submit();
                        }
                        setTimeout(() => {
                            delete closestForm.dataset.confirmState;
                        }, 100);
                    }
                });
            });
        })();
    </script>
@endonce
