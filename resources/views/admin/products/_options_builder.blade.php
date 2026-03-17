@php
    $initialOptions = old('product_options');

    if ($initialOptions === null && isset($product)) {
        $initialOptions = $product->options
            ->sortBy('sort_order')
            ->values()
            ->map(function ($option, $optionIndex) {
                return [
                    'name_ar' => $option->name_ar,
                    'name_en' => $option->name_en,
                    'is_required' => (bool) $option->is_required,
                    'values' => $option->values
                        ->sortBy('sort_order')
                        ->values()
                        ->map(function ($value, $valueIndex) use ($optionIndex) {
                            return [
                                'value_ar' => $value->value_ar,
                                'value_en' => $value->value_en,
                                'client_key' => 'o' . $optionIndex . '_v' . $valueIndex,
                            ];
                        })
                        ->toArray(),
                ];
            })
            ->toArray();
    }

    $initialOptions = is_array($initialOptions) ? $initialOptions : [];

    $initialCombinationDefinitions = old('combination_definitions', []);
    $valueLabelMap = [];

    if (empty($initialCombinationDefinitions) && isset($product)) {
        $product->loadMissing('optionCombinations');
        foreach ($product->options as $option) {
            foreach ($option->values as $value) {
                $valueLabelMap[(int) $value->id] = [
                    'option' => $option->name_ar,
                    'value' => $value->value_ar,
                ];
            }
        }

        foreach ($product->optionCombinations as $combination) {
            $ids = is_array($combination->option_value_ids) ? $combination->option_value_ids : [];
            sort($ids);
            $rowKey = 'existing_' . implode('_', $ids);
            $labels = [];
            foreach ($ids as $id) {
                $id = (int) $id;
                if (isset($valueLabelMap[$id])) {
                    $labels[] = $valueLabelMap[$id];
                }
            }

            $clientKeys = [];
            foreach ($labels as $idx => $labelPair) {
                $clientKeys[] = 'existing_' . $rowKey . '_' . $idx;
            }

            $initialCombinationDefinitions[$rowKey] = json_encode([
                'client_keys' => $clientKeys,
                'labels' => $labels,
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    $existingComboImages = [];
    if (isset($product)) {
        $product->loadMissing('optionCombinations.images');
        foreach ($product->optionCombinations as $combination) {
            $ids = is_array($combination->option_value_ids) ? $combination->option_value_ids : [];
            sort($ids);
            $comboImage = $combination->images->first();
            if (!$comboImage) {
                continue;
            }

            $signatureParts = [];
            foreach ($ids as $id) {
                $id = (int) $id;
                if (!isset($valueLabelMap[$id])) {
                    continue;
                }
                $signatureParts[] = mb_strtolower(trim((string) $valueLabelMap[$id]['option']))
                    . '::'
                    . mb_strtolower(trim((string) $valueLabelMap[$id]['value']));
            }

            sort($signatureParts);
            $signature = implode('||', $signatureParts);
            if ($signature === '') {
                continue;
            }

            $existingComboImages[$signature] = [
                'path' => $comboImage->image_path,
                'product_image_id' => $comboImage->product_image_id,
            ];
        }
    }

    $existingProductImages = [];
    if (isset($product)) {
        foreach ($product->images as $image) {
            $existingProductImages[] = [
                'id' => $image->id,
                'url' => asset('storage/' . $image->image_path),
            ];
        }
    }
@endphp

@push('styles')
<style>
    .product-options-card .option-block {
        background: #fafbff;
        border-color: #dbe3ff !important;
    }

    .product-options-card .value-row {
        padding: 10px;
        border: 1px dashed #d8deea;
        border-radius: 10px;
        background: #fff;
    }

    .product-options-card .inline-combo-slot {
        margin-top: 8px;
    }

    .product-options-card .inline-combo-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 10px;
    }

    .product-options-card .combination-row {
        border: 1px solid #e4e7ee;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
    }

    .product-options-card .section-muted {
        background: #f8f9fc;
        border: 1px solid #edf0f5;
        border-radius: 10px;
        padding: 10px 12px;
    }
</style>
@endpush

<div class="card shadow-sm product-options-card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-sliders text-info"></i> خيارات المنتج والتركيبات
        </span>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-option-btn">
            <i class="bi bi-plus-lg me-1"></i> إضافة خيار
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            مثال: <strong>اللون</strong> بقيم (أحمر، أزرق)، أو <strong>القياس</strong> بقيم (S، M، L).
            يمكن ترك هذا القسم فارغاً إذا كان المنتج بدون خيارات.
        </p>
        <div class="section-muted d-flex align-items-center justify-content-between small mb-3">
            <span class="text-muted">الحالة الحالية</span>
            <span id="options-summary" class="fw-semibold">0 خيارات / 0 قيم</span>
        </div>

        <div id="options-container" class="d-flex flex-column gap-3"></div>

        <div id="combinations-panel">
            <hr class="my-3">

            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold small">
                    <i class="bi bi-grid-3x3-gap text-secondary me-1"></i> صور التركيبات
                    <span class="text-muted fw-normal">(اختياري)</span>
                </span>
                <span id="combo-count" class="badge bg-light text-dark border">0 تركيبة</span>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="regen-combos-btn">
                    <i class="bi bi-arrow-repeat me-1"></i> تحديث
                </button>
            </div>
            <p class="text-muted small mb-3">
                عند اختيار تركيبة في صفحة المنتج، سيتم عرض الصورة المرتبطة بها إن وُجدت.
            </p>

            <div id="combinations-container" class="d-flex flex-column gap-2"></div>
        </div>
    </div>
</div>

<template id="option-template">
    <div class="border rounded-3 p-3 option-block">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0 fw-semibold option-title">خيار</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">حذف الخيار</button>
        </div>

        <div class="row g-2 align-items-end mb-2">
            <div class="col-md-4">
                <label class="form-label">اسم الخيار (عربي)</label>
                <input type="text" class="form-control option-name-ar" placeholder="مثال: اللون">
            </div>
            <div class="col-md-4">
                <label class="form-label">Option Name (EN)</label>
                <input type="text" class="form-control option-name-en" placeholder="Example: Color">
            </div>
            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input class="form-check-input option-required" type="checkbox" checked>
                    <label class="form-check-label">إلزامي</label>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-2 mt-3">
            <span class="small fw-semibold text-secondary">القيم التابعة للخيار</span>
        </div>
        <div class="values-container d-flex flex-column gap-2"></div>

        <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-value-btn">
            <i class="bi bi-plus-lg me-1"></i> إضافة قيمة
        </button>
    </div>
</template>

<template id="value-template">
    <div class="row g-2 align-items-end value-row">
        <div class="col-md-5">
            <label class="form-label">القيمة (عربي)</label>
            <input type="text" class="form-control value-ar" placeholder="مثال: أحمر">
        </div>
        <div class="col-md-5">
            <label class="form-label">Value (EN)</label>
            <input type="text" class="form-control value-en" placeholder="Example: Red">
        </div>
        <div class="col-md-2 text-end">
            <input type="hidden" class="value-client-key">
            <button type="button" class="btn btn-sm btn-outline-danger remove-value-btn">حذف القيمة</button>
        </div>
        <div class="col-12 inline-combo-slot"></div>
    </div>
</template>

@push('scripts')
<script>
(function () {
    const initialOptions = @json($initialOptions);
    const existingComboImages = @json($existingComboImages);
    const existingProductImages = @json($existingProductImages);

    const optionsContainer = document.getElementById('options-container');
    const combinationsPanel = document.getElementById('combinations-panel');
    const combinationsContainer = document.getElementById('combinations-container');
    const addOptionBtn = document.getElementById('add-option-btn');
    const regenBtn = document.getElementById('regen-combos-btn');
    const optionsSummary = document.getElementById('options-summary');
    const comboCount = document.getElementById('combo-count');

    if (!optionsContainer || !combinationsContainer || !addOptionBtn) {
        return;
    }

    const optionTemplate = document.getElementById('option-template');
    const valueTemplate = document.getElementById('value-template');

    let seed = 0;

    function nextClientKey(optionIdx, valueIdx) {
        seed += 1;
        return `o${optionIdx}_v${valueIdx}_${seed}`;
    }

    function debounce(fn, wait = 250) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    }

    const debouncedRenderCombinations = debounce(renderCombinations, 180);

    function addOption(optionData = {}) {
        const node = optionTemplate.content.firstElementChild.cloneNode(true);
        node.querySelector('.option-name-ar').value = optionData.name_ar || '';
        node.querySelector('.option-name-en').value = optionData.name_en || '';
        node.querySelector('.option-required').checked = optionData.is_required !== false;

        const valuesContainer = node.querySelector('.values-container');
        const values = Array.isArray(optionData.values) && optionData.values.length ? optionData.values : [{ value_ar: '', value_en: '' }];
        values.forEach((value) => addValue(node, value));

        node.querySelector('.add-value-btn').addEventListener('click', () => {
            addValue(node, { value_ar: '', value_en: '' });
            reindexOptionNames();
            renderCombinations();
        });

        node.querySelector('.remove-option-btn').addEventListener('click', () => {
            node.remove();
            reindexOptionNames();
            renderCombinations();
        });

        node.addEventListener('input', debouncedRenderCombinations);
        node.addEventListener('change', debouncedRenderCombinations);

        optionsContainer.appendChild(node);
        reindexOptionNames();
        updateOptionsSummary();
    }

    function addValue(optionNode, valueData = {}) {
        const node = valueTemplate.content.firstElementChild.cloneNode(true);
        node.querySelector('.value-ar').value = valueData.value_ar || '';
        node.querySelector('.value-en').value = valueData.value_en || '';
        node.querySelector('.value-client-key').value = valueData.client_key || '';

        node.querySelector('.remove-value-btn').addEventListener('click', () => {
            const valuesContainer = optionNode.querySelector('.values-container');
            if (valuesContainer.children.length <= 1) {
                node.querySelector('.value-ar').value = '';
                node.querySelector('.value-en').value = '';
                renderCombinations();
                return;
            }
            node.remove();
            reindexOptionNames();
            renderCombinations();
            updateOptionsSummary();
        });

        optionNode.querySelector('.values-container').appendChild(node);
    }

    function reindexOptionNames() {
        const optionBlocks = Array.from(optionsContainer.querySelectorAll('.option-block'));
        optionBlocks.forEach((optionNode, optionIndex) => {
            const title = optionNode.querySelector('.option-title');
            if (title) {
                title.textContent = `الخيار ${optionIndex + 1}`;
            }

            optionNode.querySelector('.option-name-ar').name = `product_options[${optionIndex}][name_ar]`;
            optionNode.querySelector('.option-name-en').name = `product_options[${optionIndex}][name_en]`;
            optionNode.querySelector('.option-required').name = `product_options[${optionIndex}][is_required]`;
            optionNode.querySelector('.option-required').value = '1';

            const valueRows = Array.from(optionNode.querySelectorAll('.value-row'));
            valueRows.forEach((valueNode, valueIndex) => {
                valueNode.querySelector('.value-ar').name = `product_options[${optionIndex}][values][${valueIndex}][value_ar]`;
                valueNode.querySelector('.value-en').name = `product_options[${optionIndex}][values][${valueIndex}][value_en]`;
                const keyInput = valueNode.querySelector('.value-client-key');
                if (!keyInput.value) {
                    keyInput.value = nextClientKey(optionIndex, valueIndex);
                }
                keyInput.name = `product_options[${optionIndex}][values][${valueIndex}][client_key]`;
            });
        });

        updateOptionsSummary();
    }

    function updateOptionsSummary() {
        if (!optionsSummary) {
            return;
        }

        const options = Array.from(optionsContainer.querySelectorAll('.option-block'));
        const valuesCount = options.reduce((sum, optionNode) => {
            return sum + optionNode.querySelectorAll('.value-row').length;
        }, 0);

        optionsSummary.textContent = `${options.length} خيارات / ${valuesCount} قيم`;
    }

    function collectOptionState() {
        const options = [];
        const optionBlocks = Array.from(optionsContainer.querySelectorAll('.option-block'));

        optionBlocks.forEach((optionNode) => {
            const optionName = (optionNode.querySelector('.option-name-ar').value || optionNode.querySelector('.option-name-en').value || '').trim();
            const valueRows = Array.from(optionNode.querySelectorAll('.value-row'));
            const values = [];

            valueRows.forEach((valueNode) => {
                const valueLabel = (valueNode.querySelector('.value-ar').value || valueNode.querySelector('.value-en').value || '').trim();
                if (!valueLabel) {
                    return;
                }
                values.push({
                    label: valueLabel,
                    client_key: valueNode.querySelector('.value-client-key').value,
                });
            });

            if (!optionName || values.length === 0) {
                return;
            }

            options.push({
                name: optionName,
                values,
            });
        });

        return options;
    }

    function cartesianProduct(arrays) {
        if (!arrays.length) {
            return [];
        }

        return arrays.reduce((acc, current) => {
            const result = [];
            acc.forEach((accRow) => {
                current.forEach((item) => {
                    result.push(accRow.concat([item]));
                });
            });
            return result;
        }, [[]]);
    }

    function comboRowKey(parts) {
        return parts.map((p) => p.client_key).sort().join('__');
    }

    function comboSignature(parts) {
        return parts
            .map((p) => `${(p.option || '').trim().toLowerCase()}::${(p.value || '').trim().toLowerCase()}`)
            .sort()
            .join('||');
    }

    function buildCombinationControls(rowKey, signature, display, isInline = false) {
        const wrapper = document.createElement('div');
        wrapper.className = isInline ? 'inline-combo-card' : 'combination-row';

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = `combination_definitions[${rowKey}]`;
        hidden.value = JSON.stringify({
            client_keys: display.clientKeys,
            labels: display.labels,
        });

        const label = document.createElement('div');
        label.className = isInline ? 'small fw-semibold mb-2 text-dark' : 'small fw-semibold mb-2 text-dark';
        label.textContent = isInline ? `صورة القيمة: ${display.text}` : display.text;

        const row = document.createElement('div');
        row.className = 'd-flex flex-column gap-2';

        const col1 = document.createElement('div');
        col1.className = 'w-100';
        const uploadLabel = document.createElement('label');
        uploadLabel.className = 'form-label small text-muted mb-1';
        uploadLabel.textContent = 'رفع صورة جديدة لهذه التركيبة';
        const upload = document.createElement('input');
        upload.type = 'file';
        upload.accept = 'image/*';
        upload.className = 'form-control form-control-sm';
        upload.name = `combination_images[${rowKey}]`;
        col1.appendChild(uploadLabel);
        col1.appendChild(upload);

        const col2 = document.createElement('div');
        col2.className = 'w-100';
        const selectLabel = document.createElement('label');
        selectLabel.className = 'form-label small text-muted mb-1';
        selectLabel.textContent = 'أو اختر من صور المنتج الحالية';
        const select = document.createElement('select');
        select.className = 'form-select form-select-sm';
        select.name = `combination_existing_image_ids[${rowKey}]`;

        const emptyOpt = document.createElement('option');
        emptyOpt.value = '';
        emptyOpt.textContent = 'بدون صورة من معرض المنتج';
        select.appendChild(emptyOpt);

        existingProductImages.forEach((img) => {
            const opt = document.createElement('option');
            opt.value = String(img.id);
            opt.textContent = `صورة #${img.id}`;
            select.appendChild(opt);
        });

        const existingComboImage = existingComboImages[signature] || null;
        if (existingComboImage && existingComboImage.product_image_id) {
            select.value = String(existingComboImage.product_image_id);
        }

        col2.appendChild(selectLabel);
        col2.appendChild(select);

        const col3 = document.createElement('div');
        col3.className = 'small text-muted';
        col3.textContent = 'اختر صورة جديدة أو صورة موجودة من معرض المنتج';

        if (existingComboImage && existingComboImage.path) {
            const uploadedNote = document.createElement('div');
            uploadedNote.className = 'small text-success mt-1';
            uploadedNote.textContent = 'توجد صورة مرفوعة مسبقًا لهذه التركيبة.';
            col3.appendChild(uploadedNote);
        }

        row.appendChild(col1);
        row.appendChild(col2);
        row.appendChild(col3);

        wrapper.appendChild(hidden);
        wrapper.appendChild(label);
        wrapper.appendChild(row);

        return wrapper;
    }

    function renderCombinations() {
        combinationsContainer.innerHTML = '';

        const inlineSlots = Array.from(optionsContainer.querySelectorAll('.inline-combo-slot'));
        inlineSlots.forEach((slot) => {
            slot.innerHTML = '';
        });

        const options = collectOptionState();
        if (options.length === 0) {
            if (combinationsPanel) {
                combinationsPanel.classList.add('d-none');
            }
            if (comboCount) {
                comboCount.textContent = '0 تركيبة';
            }
            return;
        }

        const valueGroups = options.map((option) => option.values.map((value) => ({
            option: option.name,
            value: value.label,
            client_key: value.client_key,
        })));

        const combinations = cartesianProduct(valueGroups);
        if (combinations.length === 0) {
            if (combinationsPanel) {
                combinationsPanel.classList.add('d-none');
            }
            if (comboCount) {
                comboCount.textContent = '0 تركيبة';
            }
            return;
        }

        if (comboCount) {
            comboCount.textContent = `${combinations.length} تركيبة`;
        }

        const isSingleOption = options.length === 1;
        if (combinationsPanel) {
            combinationsPanel.classList.toggle('d-none', isSingleOption);
        }

        combinations.forEach((parts) => {
            const rowKey = comboRowKey(parts);
            const signature = comboSignature(parts);
            const display = parts.map((p) => `${p.option}: ${p.value}`).join(' | ');

            const controlNode = buildCombinationControls(
                rowKey,
                signature,
                {
                    text: display,
                    clientKeys: parts.map((p) => p.client_key),
                    labels: parts.map((p) => ({ option: p.option, value: p.value })),
                },
                isSingleOption
            );

            if (isSingleOption) {
                const onlyPart = parts[0];
                const valueNode = optionsContainer.querySelector(`.value-client-key[value="${onlyPart.client_key}"]`);
                const slot = valueNode?.closest('.value-row')?.querySelector('.inline-combo-slot');
                if (slot) {
                    slot.appendChild(controlNode);
                } else {
                    combinationsContainer.appendChild(controlNode);
                }
            } else {
                combinationsContainer.appendChild(controlNode);
            }
        });

    }

    addOptionBtn.addEventListener('click', () => {
        addOption();
        renderCombinations();
        updateOptionsSummary();
    });

    if (regenBtn) {
        regenBtn.addEventListener('click', renderCombinations);
    }

    if (Array.isArray(initialOptions) && initialOptions.length) {
        initialOptions.forEach((option) => addOption(option));
    } else {
        addOption();
    }

    renderCombinations();
})();
</script>
@endpush
