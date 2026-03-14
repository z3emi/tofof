@once
    <style>
        :root {
            --manual-primary-dark: var(--primary-dark, #1F3C88);
            --manual-primary-medium: var(--primary-medium, #0FB5D3);
            --manual-primary-light: var(--primary-light, #CDE7FF);
            --manual-surface: var(--glass-surface, #ffffff);
            --manual-surface-alt: rgba(15, 181, 211, 0.05);
            --manual-border: var(--glass-border, rgba(31, 60, 136, 0.22));
            --manual-text-dark: var(--text-dark, #102542);
            --manual-text-muted: var(--text-light, #5B708B);
        }

        .manual-order-form .card {
            background: var(--manual-surface);
            border: 1px solid var(--manual-border);
            border-radius: 18px;
            box-shadow: var(--shadow-sm, 0 20px 40px rgba(31, 60, 136, 0.16));
            overflow: hidden;
        }

        .manual-order-form .card-header {
            background: linear-gradient(135deg, var(--manual-primary-dark), var(--manual-primary-medium));
            color: #fff;
            border: 0;
            padding: 1.25rem 1.5rem;
        }

        .manual-order-form .card-body {
            padding: 1.75rem;
            background: var(--manual-surface);
        }

        .manual-order-form .card-footer {
            background: rgba(255, 255, 255, 0.72);
            border-top: 1px solid var(--manual-border);
            padding: 1.25rem 1.5rem;
        }

        .manual-order-form .section-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--manual-primary-dark);
            margin-bottom: 1rem;
            position: relative;
            padding-right: 0.75rem;
        }

        .manual-order-form .section-title::before {
            content: '';
            position: absolute;
            inset-inline-start: 0;
            inset-block-start: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--manual-primary-dark), var(--manual-primary-medium));
        }

        .manual-order-form .form-label {
            font-weight: 600;
            color: var(--manual-text-dark);
        }

        .manual-order-form .form-control,
        .manual-order-form .form-select {
            border-radius: 10px;
            border: 1px solid rgba(31, 60, 136, 0.2);
            background: rgba(255, 255, 255, 0.9);
            color: var(--manual-text-dark);
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .manual-order-form .form-control:focus,
        .manual-order-form .form-select:focus {
            border-color: var(--manual-primary-medium);
            box-shadow: 0 0 0 0.2rem rgba(15, 181, 211, 0.25);
        }

        .manual-order-form .form-control-plaintext {
            color: var(--manual-text-dark);
        }

        .manual-order-form .input-group-text {
            background: rgba(15, 181, 211, 0.12);
            border-color: rgba(15, 181, 211, 0.25);
            color: var(--manual-text-dark);
        }

        .manual-order-form .btn {
            border-radius: 999px;
            font-weight: 600;
            padding: 0.45rem 1.25rem;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }

        .manual-order-form .btn-primary {
            background: linear-gradient(135deg, var(--manual-primary-dark), var(--manual-primary-medium));
            border-color: transparent;
            box-shadow: 0 12px 25px rgba(31, 60, 136, 0.28);
            color: #fff;
        }

        .manual-order-form .btn-secondary {
            background: rgba(31, 60, 136, 0.08);
            border-color: transparent;
            color: var(--manual-primary-dark);
        }

        .manual-order-form .btn-success {
            background: linear-gradient(135deg, #16d9a6, var(--manual-primary-dark));
            border-color: transparent;
            color: #fff;
            box-shadow: 0 12px 25px rgba(22, 217, 166, 0.28);
        }

        .manual-order-form .btn-danger {
            background: #e63946;
            border-color: transparent;
            color: #fff;
            box-shadow: 0 10px 22px rgba(230, 57, 70, 0.28);
        }

        .manual-order-form .btn-outline-secondary {
            color: var(--manual-primary-dark);
            border-color: rgba(31, 60, 136, 0.45);
        }

        .manual-order-form .btn:hover {
            transform: translateY(-1px);
        }

        .manual-order-form .btn:active {
            transform: translateY(0);
        }

        .manual-order-form .table {
            border-radius: 14px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.94);
        }

        .manual-order-form .table thead th {
            background: linear-gradient(135deg, var(--manual-primary-dark), var(--manual-primary-medium));
            border: 0;
            color: #fff;
            font-weight: 600;
        }

        .manual-order-form .table tbody tr:nth-child(even) {
            background: rgba(15, 181, 211, 0.06);
        }

        .manual-order-form .table-bordered > :not(caption) > * {
            border-color: rgba(31, 60, 136, 0.18);
        }

        .manual-order-form .badge.bg-light {
            background: rgba(31, 60, 136, 0.12) !important;
            color: var(--manual-primary-dark) !important;
        }

        .manual-order-form .alert-info {
            background: rgba(15, 181, 211, 0.12);
            border-color: rgba(15, 181, 211, 0.2);
            color: var(--manual-text-dark);
        }

        .manual-order-form .alert-danger {
            background: rgba(230, 57, 70, 0.1);
            border-color: rgba(230, 57, 70, 0.3);
            color: #941c1f;
        }

        .manual-order-form #customer-location-card,
        .manual-order-form .customer-location-card,
        .manual-order-form .map-picker-card {
            border-radius: 16px;
            border: 1px solid var(--manual-border);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 16px 32px rgba(31, 60, 136, 0.12);
        }

        .manual-order-form .map-picker-card .card-body,
        .manual-order-form .customer-location-card .card-body {
            padding: 1.25rem 1.5rem;
        }

        .manual-order-form .map-picker-canvas {
            border-radius: 14px;
            border: 1px solid rgba(31, 60, 136, 0.16);
            background: linear-gradient(135deg, rgba(15, 181, 211, 0.08), rgba(31, 60, 136, 0.1));
            min-height: 260px;
        }

        .manual-order-form #order-customer-map {
            width: 100%;
            height: 100%;
            min-height: 260px;
            cursor: grab;
        }

        .manual-order-form #order-customer-map.leaflet-dragging {
            cursor: grabbing;
        }

        .manual-order-form .order-map-marker-wrapper {
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .manual-order-form .order-map-marker-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid #fff;
            background: linear-gradient(135deg, var(--manual-primary-dark), var(--manual-primary-medium));
            box-shadow: 0 6px 16px rgba(31, 60, 136, 0.28);
            display: block;
        }

        .manual-order-form .bg-light {
            background: rgba(15, 181, 211, 0.08) !important;
        }

        .manual-order-form #partial-summary-card .border {
            border-radius: 14px !important;
            border-color: rgba(31, 60, 136, 0.2) !important;
            background: rgba(15, 181, 211, 0.08) !important;
        }

        .manual-order-form #partial-summary-card strong {
            color: var(--manual-text-dark);
        }

        .manual-order-form small.text-muted,
        .manual-order-form .text-muted {
            color: var(--manual-text-muted) !important;
        }

        .manual-order-form .form-check-input:checked {
            background-color: var(--manual-primary-dark);
            border-color: var(--manual-primary-dark);
        }

        .manual-order-form .form-switch .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(15, 181, 211, 0.25);
        }

        @media (max-width: 576px) {
            .manual-order-form .map-picker-canvas,
            .manual-order-form #order-customer-map {
                min-height: 220px;
            }
        }
    </style>
@endonce
