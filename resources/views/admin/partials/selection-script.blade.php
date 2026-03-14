<script>
(function () {
    const group = @json($group);
    if (!group) {
        return;
    }

    const highlightClass = @json($highlightClass ?? 'table-row-selected');
    const placeholderToken = @json($placeholder ?? '__ID__');
    const emptyMessage = @json($emptyMessage ?? 'يرجى تحديد عنصر أولاً.');
    const fallbackSingleOnlyMessage = 'يرجى اختيار عنصر واحد فقط لهذا الإجراء.';
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

    function askConfirm(message, options) {
        if (!message) {
            return Promise.resolve(true);
        }

        if (window.CustomConfirm && typeof window.CustomConfirm.confirm === 'function') {
            return window.CustomConfirm.confirm(message, options);
        }

        return Promise.resolve(window.confirm(message));
    }

    function getCheckboxes() {
        return Array.from(document.querySelectorAll('.selection-checkbox[data-selection-group="' + group + '"]'));
    }

    function getActions() {
        return Array.from(document.querySelectorAll('.selection-action[data-selection-group="' + group + '"]'));
    }

    function getSelectedCheckboxes() {
        return getCheckboxes().filter(cb => cb.checked);
    }

    function actionAllowed(action, datasets) {
        if (!datasets.length) {
            return false;
        }

        return datasets.every(dataset => {
            const requiresField = action.dataset.requiresField;
            if (requiresField && !dataset[requiresField]) {
                return false;
            }

            const requiresStateField = action.dataset.requiresStateField;
            const requiresStateValue = action.dataset.requiresStateValue;
            if (requiresStateField && typeof requiresStateValue !== 'undefined') {
                if ((dataset[requiresStateField] ?? null) !== requiresStateValue) {
                    return false;
                }
            }

            const disallowStateField = action.dataset.disallowStateField;
            const disallowStateValue = action.dataset.disallowStateValue;
            if (disallowStateField && typeof disallowStateValue !== 'undefined') {
                if ((dataset[disallowStateField] ?? null) === disallowStateValue) {
                    return false;
                }
            }

            return true;
        });
    }

    function formatMessage(message, count) {
        if (!message) {
            return '';
        }

        return message.replace(/__COUNT__/g, count);
    }

    function updateSelections() {
        const checkboxes = getCheckboxes();
        const selectedCheckboxes = checkboxes.filter(cb => cb.checked);
        const selectedDatasets = selectedCheckboxes.map(cb => cb.dataset);
        const actions = getActions();

        checkboxes.forEach(cb => {
            const row = cb.closest('tr');
            if (!row) {
                return;
            }
            if (cb.checked) {
                row.classList.add(highlightClass);
            } else {
                row.classList.remove(highlightClass);
            }
        });

        const hasSelection = selectedCheckboxes.length > 0;

        actions.forEach(action => {
            if (action.dataset.requiresSelection === 'false') {
                return;
            }

            const allowMultiple = action.dataset.allowMultiple === 'true';
            let allowed = false;

            if (hasSelection) {
                if (allowMultiple) {
                    allowed = actionAllowed(action, selectedDatasets);
                } else if (selectedDatasets.length === 1) {
                    allowed = actionAllowed(action, [selectedDatasets[0]]);
                }
            }

            if (allowed) {
                action.disabled = false;
                action.classList.remove('disabled');
                action.removeAttribute('aria-disabled');
            } else {
                action.disabled = true;
                action.classList.add('disabled');
                action.setAttribute('aria-disabled', 'true');
            }
        });
    }

    async function performBulkSubmission(action, form, selectedCheckboxes, template) {
        const baseMethod = (form.getAttribute('method') || 'POST').toUpperCase();
        const submissionMethod = baseMethod === 'GET' ? 'GET' : 'POST';

        const templateData = new FormData(form);
        const storedEntries = [];
        templateData.forEach((value, key) => {
            storedEntries.push([key, value]);
        });

        const errors = [];

        for (const checkbox of selectedCheckboxes) {
            const targetUrl = template.replace(placeholderToken, checkbox.value);
            try {
                let response;
                if (submissionMethod === 'GET') {
                    const urlObject = new URL(targetUrl, window.location.origin);
                    storedEntries.forEach(([key, value]) => {
                        urlObject.searchParams.set(key, value);
                    });
                    response = await fetch(urlObject.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                } else {
                    const formData = new FormData();
                    storedEntries.forEach(([key, value]) => {
                        formData.append(key, value);
                    });
                    response = await fetch(targetUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                        },
                        body: formData,
                    });
                }

                if (!response || !response.ok) {
                    errors.push(checkbox.value);
                }
            } catch (error) {
                errors.push(checkbox.value);
            }
        }

        if (errors.length) {
            alert(action.dataset.errorMultiple || 'تعذر تنفيذ العملية لكل العناصر المحددة. يرجى المحاولة مرة أخرى.');
        }

        window.location.reload();
    }

    async function handleSubmitForm(action, selectedCheckboxes, allowMultiple) {
        const formId = action.dataset.formTarget;
        if (!formId) {
            return;
        }

        const form = document.getElementById(formId);
        if (!form) {
            return;
        }

        const template = action.dataset.urlTemplate || '';
        if (!template) {
            return;
        }

        const confirmMessage = allowMultiple && selectedCheckboxes.length > 1
            ? (action.dataset.confirmMultiple || action.dataset.confirm)
            : action.dataset.confirm;

        if (confirmMessage) {
            const formatted = formatMessage(confirmMessage, selectedCheckboxes.length);
            const confirmed = await askConfirm(formatted, {
                title: action.dataset.confirmTitle || 'تأكيد الإجراء'
            });
            if (!confirmed) {
                return;
            }
        }

        if (allowMultiple && selectedCheckboxes.length > 1) {
            await performBulkSubmission(action, form, selectedCheckboxes, template);
            return;
        }

        const recordId = selectedCheckboxes[0].value;
        form.action = template.replace(placeholderToken, recordId);
        form.submit();
    }

    async function handleAction(action) {
        const selectedCheckboxes = getSelectedCheckboxes();
        if (!selectedCheckboxes.length) {
            alert(emptyMessage);
            return;
        }

        const allowMultiple = action.dataset.allowMultiple === 'true';
        const selectedDatasets = selectedCheckboxes.map(cb => cb.dataset);

        if (!allowMultiple && selectedCheckboxes.length > 1) {
            const multiMessage = action.dataset.multiMessage || fallbackSingleOnlyMessage;
            alert(multiMessage);
            return;
        }

        const datasetsToValidate = allowMultiple ? selectedDatasets : [selectedDatasets[0]];
        if (!actionAllowed(action, datasetsToValidate)) {
            alert(emptyMessage);
            return;
        }

        const actionType = action.dataset.actionType || 'navigate';
        const template = action.dataset.urlTemplate || '';

        if (action.dataset.requiresField) {
            const requiredField = action.dataset.requiresField;
            const allHaveField = datasetsToValidate.every(dataset => !!dataset[requiredField]);
            if (!allHaveField) {
                alert(emptyMessage);
                return;
            }
        }

        if (actionType === 'navigate') {
            const recordId = selectedCheckboxes[0].value;
            const url = template.replace(placeholderToken, recordId);
            window.location.href = url;
            return;
        }

        if (actionType === 'submit-form') {
            await handleSubmitForm(action, selectedCheckboxes, allowMultiple);
            return;
        }

        if (actionType === 'custom') {
            const primaryCheckbox = selectedCheckboxes[0];
            const eventName = action.dataset.customEvent || 'selection-action';
            const customEvent = new CustomEvent(eventName, {
                detail: {
                    id: primaryCheckbox.value,
                    ids: selectedCheckboxes.map(cb => cb.value),
                    dataset: primaryCheckbox.dataset,
                    datasets: selectedDatasets,
                    action: action
                },
                bubbles: true
            });
            action.dispatchEvent(customEvent);
        }
    }

    function bindCheckbox(checkbox) {
        if (checkbox.dataset.selectionBound === 'true') {
            return;
        }

        checkbox.addEventListener('change', updateSelections);

        const row = checkbox.closest('tr');
        if (row) {
            row.addEventListener('click', function (event) {
                if (event.target.closest('[data-ignore-row-select]') || event.target.closest('a') || event.target.closest('button') || event.target.closest('form') || event.target.closest('input') || event.target.closest('label') || event.target.closest('select')) {
                    return;
                }

                const checkboxes = getCheckboxes();
                const multipleSelected = checkboxes.filter(function (input) { return input.checked; }).length > 1;

                if (multipleSelected) {
                    if (!checkbox.checked) {
                        return;
                    }
                    updateSelections();
                    return;
                }

                checkboxes.forEach(function (input) {
                    input.checked = false;
                });
                checkbox.checked = true;
                updateSelections();
            });
        }

        checkbox.dataset.selectionBound = 'true';
    }

    function bindAction(action) {
        if (action.dataset.selectionActionBound === 'true') {
            return;
        }

        if (action.dataset.requiresSelection !== 'false') {
            action.disabled = true;
            action.classList.add('disabled');
            action.setAttribute('aria-disabled', 'true');
        }

        action.addEventListener('click', async function (event) {
            if (action.dataset.requiresSelection === 'false') {
                return;
            }

            event.preventDefault();

            if (action.disabled || action.classList.contains('disabled')) {
                alert(emptyMessage);
                return;
            }

            await handleAction(action);
        });

        action.dataset.selectionActionBound = 'true';
    }

    function initializeSelections() {
        const checkboxes = getCheckboxes();
        if (!checkboxes.length) {
            return;
        }

        checkboxes.forEach(bindCheckbox);
        getActions().forEach(bindAction);
        updateSelections();
    }

    document.addEventListener('DOMContentLoaded', initializeSelections);
    document.addEventListener('admin:content-refreshed', initializeSelections);
})();
</script>
