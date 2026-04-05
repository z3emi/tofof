<!doctype html>
<html lang="ar" dir="rtl" x-data="themeSwitcher()" :data-theme="theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Token for AJAX requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة تحكم Tofof')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- START: Theme switcher script (prevents FOUC) --}}
    <script>
        function themeSwitcher() {
            return {
                theme: localStorage.getItem('admin_theme') || 'light',
                init() {
                    this.$watch('theme', value => {
                        localStorage.setItem('admin_theme', value);
                        // The :data-theme binding on <html> handles the rest
                    });
                },
                toggleTheme() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                }
            }
        }
    </script>
    {{-- END: Theme switcher script --}}

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 58px;
            --bg-light: #F5F5F5;
            --primary-dark: #6d0e16;
            --primary-medium: #8b121c;
            --primary-light: #af1824;
            --secondary-dark: #2C2C2C;
            --accent-gold: #D4AF37;
            --secondary-light: #e7e7e7;
            --white: #ffffff;
            --text-dark: #2C2C2C;
            --text-light: #5f5f5f;
            --transition: all 0.25s ease;
            --border-radius: 12px;
            --icon-size: 1.1rem;
            --nav-link-padding: 0.6rem 1rem;
            --shadow-sm: 0 2px 10px rgba(0,0,0,.05);
            --shadow-md: 0 5px 20px rgba(0,0,0,.08);
            --shadow-color-primary: rgba(109, 14, 22, .2);
        }

        /* START: Dark Mode Variables */
        [data-theme="dark"] {
            --bg-light: #0a0a0a;
            --primary-dark: #6d0e16;
            --primary-medium: #a61c20;
            --primary-light: #ea7a7e;
            --secondary-light: #2a2a2a;
            --white: #111111;
            --text-dark: #ffffff;
            --text-light: #d6d6d6;
            --shadow-sm: 0 2px 5px rgba(0,0,0,.16);
            --shadow-md: 0 5px 15px rgba(0,0,0,.28);
            --shadow-color-primary: rgba(195, 33, 38, .3);
        }
        /* END: Dark Mode Variables */

        body{font-family:'Tajawal',sans-serif;background-color:var(--bg-light);color:var(--text-dark);overflow-x:hidden;scroll-behavior:smooth; transition: background-color 0.25s ease, color 0.25s ease;}
        .main-wrapper{display:flex;min-height:100vh}
        .sidebar{width:var(--sidebar-width);background-color:var(--white);position:sticky;top:0;height:100vh;z-index:1000;border-left:1px solid var(--secondary-light);display:flex;flex-direction:column;box-shadow:2px 0 10px rgba(0,0,0,.05);overflow-y:auto; transition: var(--transition);}
        .sidebar a{scroll-behavior:auto}
        .sidebar-content{overflow-y:auto;flex:1;padding-bottom:.5rem;scrollbar-width:thin;scrollbar-color:var(--primary-light) var(--secondary-light)}
        .sidebar-content::-webkit-scrollbar{width:5px}
        .sidebar-content::-webkit-scrollbar-thumb{background-color:var(--primary-light);border-radius:3px}
        .sidebar-content::-webkit-scrollbar-track{background-color:var(--secondary-light)}
        .sidebar-brand{padding:0.6rem 1rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;color:var(--primary-dark);border-bottom:1px solid var(--secondary-light);flex-shrink:0; letter-spacing: 0.5px;}
        .sidebar-brand i{font-size:1.6rem;margin-left:.7rem;color:var(--accent-gold)}
        .nav-link{color:var(--text-dark);padding:var(--nav-link-padding);margin:.15rem .5rem;border-radius:var(--border-radius);display:flex;align-items:center;transition:var(--transition);font-weight:500;font-size:.9rem;position:relative;text-decoration:none}
        .nav-link:hover{background-color:var(--primary-light);color:#fff;transform:translateX(-5px)}
        .nav-link.active{background-color:var(--primary-medium);color:#fff;font-weight:600;box-shadow:0 4px 8px var(--shadow-color-primary)}
        .nav-link.active::before{content:'';position:absolute;right:-1px;top:0;height:100%;width:3px;background-color:var(--primary-dark);border-radius:3px 0 0 3px}
        .nav-link .bi{transition:var(--transition)}
        .nav-link:hover .bi,.nav-link.active .bi{transform:scale(1.1)}
        .badge{font-size:.6rem;padding:.28em .45em;margin-right:auto}
        .sidebar-footer{padding:.6rem;border-top:1px solid var(--secondary-light);flex-shrink:0;font-size:.72rem;color:var(--text-light);text-align:center}
        .content-wrapper{flex:1;display:flex;flex-direction:column;min-height:100vh}
        .main-content{flex:1;padding:0;overflow:auto; -webkit-overflow-scrolling: touch;}
        .main-content .container-fluid{padding-left:0;padding-right:0;max-width:100%}
        .topbar{background:var(--white);padding:0 1.1rem;height:var(--topbar-height);display:flex;align-items:center;border-bottom:1px solid var(--secondary-light);flex-shrink:0;box-shadow:var(--shadow-sm);position:sticky;top:0;z-index:999; transition: var(--transition);}
        .sidebar-toggle-btn{transition:var(--transition);border:none;background:transparent;font-size:1.35rem;color:var(--primary-dark);width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:50%}
        .sidebar-toggle-btn:hover{color:var(--primary-medium);background-color:rgba(195, 33, 38, .1)}
        .user-dropdown .dropdown-toggle{display:flex;align-items:center;color:var(--text-dark);text-decoration:none;transition:var(--transition)}
        .user-dropdown .dropdown-toggle:hover{color:var(--primary-dark)}
        .user-dropdown img{width:32px;height:32px;border-radius:50%;object-fit:cover;margin-left:.6rem;border:1px solid var(--primary-light);transition:var(--transition)}
        .user-dropdown .dropdown-toggle:hover img{border-color:var(--primary-medium)}
        .dropdown-menu{border:1px solid var(--secondary-light);box-shadow:var(--shadow-md);border-radius:var(--border-radius);padding:.35rem;margin-top:.5rem!important; background-color: var(--white);}
        .dropdown-item{border-radius:var(--border-radius);padding:.45rem .8rem;font-size:.88rem;transition:var(--transition); color: var(--text-dark);}
        .dropdown-item:hover{background-color:var(--primary-light);color:#fff}
        [data-theme="dark"] .dropdown-item.text-danger:hover { color: #fff !important; }
        [data-theme="dark"] .dropdown-item.text-warning:hover { color: #fff !important; }
        .card{border:1px solid var(--secondary-light);border-radius:var(--border-radius);box-shadow:var(--shadow-sm);background-color:var(--white);margin-bottom:1.1rem;transition:var(--transition)}
        .card:hover{box-shadow:var(--shadow-md)}
        .card-header{background-color:transparent;border-bottom:1px solid var(--secondary-light);padding:.9rem 1rem;font-weight:600;color:var(--primary-dark);display:flex;align-items:center;justify-content:space-between}
        .card-header .bi{font-size:1.2rem;color:var(--primary-medium)}
        .btn{border-radius:var(--border-radius);padding:.4rem .95rem;font-weight:500;transition:var(--transition)}
        .btn-primary{background-color:var(--primary-dark);border-color:var(--primary-dark);color:#fff}
        .btn-primary:hover{background-color:var(--primary-medium);border-color:var(--primary-medium);box-shadow:0 4px 12px rgba(195, 33, 38, .3)}
        .btn-sm{padding:.28rem .55rem;font-size:.8rem}
        .alert{border:none;border-radius:var(--border-radius);box-shadow:0 2px 5px rgba(0,0,0,.05);padding:.6rem .85rem}
        .reports-submenu{margin-right:1rem;border-right:1px solid var(--secondary-light);padding-right:.6rem;margin-left:.6rem}
        .reports-submenu .nav-link.sub-link{padding:.4rem .8rem;margin:.12rem 0;font-size:.85rem}
        .reports-submenu .nav-link.sub-link:hover{background-color:rgba(0,0,0,0.1);color:var(--primary-dark);transform:none}
        [data-theme="dark"] .reports-submenu .nav-link.sub-link:hover { background-color: rgba(255, 255, 255, 0.05); }
        .reports-submenu .nav-link.sub-link .bi{font-size:.9rem;color:var(--primary-medium)}
        .reports-submenu .nav-link.sub-link:hover .bi{color:var(--primary-dark);transform:none}
        .nav-link.reports-toggle.active,.nav-link.reports-toggle[aria-expanded="true"]{background-color:#a85a56;color:#fff}
        .table > :not(caption) > * > *{padding:.55rem .5rem}
        [data-theme="dark"] .table { color: var(--text-dark); border-color: var(--secondary-light); }
        [data-theme="dark"] .list-group-item { background-color: var(--white); border-color: var(--secondary-light); }
        [data-theme="dark"] .dropdown-menu-end { background-color: var(--white); }
        [data-theme="dark"] .notification-item.unread { background-color: rgba(255,255,255,0.05); }
        
        /* Global Fixes for Dark Mode Visibility */
        [data-theme="dark"] .form-card, 
        [data-theme="dark"] .table-container,
        [data-theme="dark"] .card { 
            background-color: var(--white) !important; 
            color: var(--text-dark) !important;
        }
        [data-theme="dark"] .bg-light { background-color: var(--bg-light) !important; }
        [data-theme="dark"] .text-dark { color: var(--text-dark) !important; }
        [data-theme="dark"] .text-muted { color: var(--text-light) !important; }
        [data-theme="dark"] .search-input, [data-theme="dark"] .form-control, [data-theme="dark"] .form-select {
            background-color: var(--bg-light) !important;
            color: var(--text-dark) !important;
            border-color: var(--secondary-light) !important;
        }

        /* Enable Horizontal Scroll for all Data Tables and Containers */
        .table-container, .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            width: 100% !important;
            margin-bottom: 1rem;
        }
        
        .main-content {
            overflow-x: auto;
            max-width: 100vw;
        }

        /* Ensure tables are readable and scrollable on mobile */
        table.table {
            min-width: 800px; /* Force minimum width to guarantee horizontal scroll for readability */
        }
        
        /* Exception for small tables if needed */
        table.table-sm-auto {
            min-width: auto;
        }

        @media (max-width: 768px) {
            .main-content {
                padding-bottom: 2rem; /* Space for scrollbar */
            }
        }
        
        /* Global Table Row Interaction (GREY THEME) */
        .table tr { transition: all 0.1s ease; }
        .table tr:hover > * { 
            background-color: #f1f5f9 !important; /* Light Grey Hover */
            cursor: pointer;
        }
        [data-theme="dark"] .table tr:hover > * { 
            background-color: rgba(255, 255, 255, 0.08) !important; 
        }
        
        /* High-Visibility Context Highlight (DARKER GREY) */
        tr.row-context-active > * { 
            background-color: #e2e8f0 !important; /* Darker Grey for Selected Row */
            color: #111111 !important; 
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
        }
        tr.row-context-active a, tr.row-context-active div, tr.row-context-active span, tr.row-context-active i {
            color: #111111 !important;
        }
        [data-theme="dark"] tr.row-context-active > * { 
            background-color: rgba(255, 255, 255, 0.15) !important; 
            color: white !important;
        }

        /* Fixed White-on-White issues in Card Headers */
        [data-theme="dark"] .card-header .text-muted { color: rgba(255,255,255,0.7) !important; }

        hr.mx-3.my-2{ margin: .35rem .75rem !important; background-color: var(--secondary-light) !important; opacity: 0.5;}
        
        /* START: Premium Global Pagination Styles */
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            gap: 5px;
            margin-bottom: 0;
        }
        .pagination .page-item {
            margin: 0;
        }
        .pagination .page-item .page-link {
            border: 1px solid var(--secondary-light);
            background-color: var(--white);
            color: var(--text-dark);
            padding: 0.5rem 0.85rem;
            min-width: 40px;
            text-align: center;
            border-radius: 8px !important;
            font-weight: 600;
            font-size: 0.85rem;
            transition: var(--transition);
            box-shadow: none;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #fff;
            box-shadow: 0 4px 10px var(--shadow-color-primary);
        }
        .pagination .page-item .page-link:hover:not(.active) {
            background-color: var(--primary-light);
            color: #fff;
            border-color: var(--primary-light);
            transform: translateY(-2px);
        }
        .pagination .page-item.disabled .page-link {
            background-color: var(--bg-light);
            color: var(--text-light);
            border-color: var(--secondary-light);
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        /* END: Premium Global Pagination Styles */

        @media (max-width:992px){
            :root{ --sidebar-width: 250px; }
            .main-wrapper{flex-direction:column}
            .sidebar{position:fixed;top:0;right:-100%;width:var(--sidebar-width);height:100vh;transition:var(--transition);z-index:1050}
            .sidebar.active{right:0}
            .content-wrapper{width:100%;margin-right:0}
            .sidebar-overlay{position:fixed;top:0;right:0;width:100%;height:100%;background-color:rgba(0,0,0,.5);z-index:1040;opacity:0;visibility:hidden;transition:var(--transition)}
            .sidebar-overlay.active{opacity:1;visibility:visible}
        }
        /* === أيقونات سوداء دائمًا (رئيسية وفرعية) === */
        .sidebar .nav-link .bi{color:var(--secondary-dark)!important;font-size:var(--icon-size);width:20px;text-align:center;margin-left:.6rem;vertical-align:middle; opacity: 0.8;}
        .sidebar .nav-link:hover .bi,.sidebar .nav-link.active .bi{color:#fff!important;transform:scale(1.1); opacity: 1;}
        .sidebar .reports-submenu .nav-link.sub-link .bi{color:var(--secondary-dark)!important;font-size:.95rem}
        .sidebar .reports-submenu .nav-link.sub-link:hover .bi{color:var(--accent-gold)!important;transform:none}
        .sidebar .nav-link.reports-toggle .bi,
        .sidebar .nav-link.reports-toggle:hover .bi,
        .sidebar .nav-link.reports-toggle.active .bi{color:inherit!important}
        [data-theme="dark"] .sidebar .nav-link:hover .bi,
        [data-theme="dark"] .sidebar .nav-link.active .bi { color: #fff !important; }

        /* START: Cleaned Desktop Sidebar Logic (THE FIX) */
        @media (min-width: 993px) {
            /* Sidebar takes fixed space when open */
            .sidebar {
                flex-shrink: 0; /* Prevent sidebar from shrinking */
                flex-basis: var(--sidebar-width);
                width: var(--sidebar-width);
                transition: flex-basis 0.25s ease, width 0.25s ease, border-left-width 0.25s ease;
                border-left-width: 1px;
            }

            /* When collapsed, show only icons */
            .sidebar.collapsed {
                flex-basis: auto;
                width: 70px;
                overflow: visible;
                border-left-width: 1px;
            }

            .sidebar.collapsed .sidebar-brand span,
            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .sidebar-footer {
                display: none;
            }

            .sidebar.collapsed .sidebar-brand {
                justify-content: center;
                padding: 1rem 0.5rem;
            }

            .sidebar.collapsed .sidebar-brand i {
                margin-left: 0;
            }

            .sidebar.collapsed .nav-link {
                justify-content: center;
                padding: 0.75rem;
            }

            .sidebar.collapsed .nav-link .bi {
                margin-left: 0;
            }

            .sidebar.collapsed .nav-link:hover {
                transform: none;
            }

            .sidebar.collapsed .badge {
                display: none;
            }

            .sidebar.collapsed .reports-submenu {
                display: none;
            }

            /* Content wrapper automatically fills the remaining space */
            .content-wrapper {
                flex: 1 1 auto;
                /* This rule makes it take up the rest of the space */
                min-width: 0; /* Important fix for flex items that might overflow */
            }
        }
        /* END: Cleaned Desktop Sidebar Logic */
        /* START: Premium Utility Classes */
        .text-brand { color: var(--primary-dark) !important; }
        .bg-brand { background-color: var(--primary-dark) !important; color: #fff !important; }
        .btn-brand {
            background-color: var(--primary-dark);
            color: #fff;
            border: none;
            transition: var(--transition);
        }
        .btn-brand:hover {
            background-color: var(--primary-medium);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* START: Custom Confirmation Modal Styles */
        #custom-confirm-modal .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            background-color: var(--white);
        }
        #custom-confirm-modal .modal-header {
            border-bottom: none;
            padding: 1.5rem 1.5rem 0.5rem;
        }
        #custom-confirm-modal .modal-footer {
            border-top: none;
            padding: 0.5rem 1.5rem 1.5rem;
            justify-content: center;
            gap: 1rem;
        }
        #custom-confirm-modal .btn-confirm {
            background-color: var(--primary-dark);
            color: #fff;
            border: none;
            padding: 0.6rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
        }
        #custom-confirm-modal .btn-confirm:hover {
            background-color: var(--primary-medium);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        #custom-confirm-modal .btn-cancel {
            background-color: var(--bg-light);
            color: var(--text-dark);
            border: 1px solid var(--secondary-light);
            padding: 0.6rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
        }
        #custom-confirm-modal .btn-cancel:hover {
            background-color: var(--secondary-light);
            transform: translateY(-2px);
        }
        #custom-confirm-modal .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(195, 33, 38, 0.1);
            color: var(--primary-dark);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        /* END: Custom Confirmation Modal Styles */

        .bg-soft-danger { background-color: rgba(220, 53, 69, 0.05) !important; }
        .bg-soft-warning { background-color: rgba(255, 193, 7, 0.05) !important; }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.05) !important; }
        .bg-soft-brand { background-color: rgba(195, 33, 38, 0.05) !important; }
        .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .avatar-circle {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
        }
        /* END: Premium Utility Classes */

        /* START: Context Menu Styles */
        #context-menu {
            position: fixed;
            z-index: 10000;
            width: 240px;
            background: #ffffff !important; /* Force white background in light */
            border: 1px solid var(--secondary-light);
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            padding: 0.6rem 0;
            display: none;
            backdrop-filter: blur(15px);
        }
        [data-theme="dark"] #context-menu {
            background-color: #111111 !important; /* Force dark background in dark */
            border-color: rgba(255, 255, 255, 0.1);
        }
        /* Context Menu Text Defaults */
        #context-menu .context-menu-item {
            color: #2C2C2C; /* Default dark grey in light */
        }
        [data-theme="dark"] #context-menu .context-menu-item {
            color: #ffffff; /* Default white in dark */
        }
        
        .context-menu-item {
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none !important;
            font-size: 0.92rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            border-right: 3px solid transparent;
        }
        .context-menu-item:hover {
            background-color: #f1f5f9 !important; /* Grey hover for menu items */
            border-right-color: var(--primary-dark);
        }
        .context-menu-item:hover, .context-menu-item:hover *, .context-menu-item:hover i {
            color: #111111 !important;
        }
        [data-theme="dark"] .context-menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }
        [data-theme="dark"] .context-menu-item:hover, [data-theme="dark"] .context-menu-item:hover * {
            color: #ffffff !important;
        }
        .context-menu-item i {
            font-size: 1.15rem;
            color: var(--primary-dark);
            width: 20px;
            text-align: center;
            transition: all 0.2s ease;
        }
        [data-theme="dark"] .context-menu-item i {
            color: var(--accent-gold);
        }
        
        /* Specific Color Classes with High Specificity */
        #context-menu .context-menu-item.text-primary, #context-menu .context-menu-item.text-primary * { color: var(--primary-dark) !important; }
        #context-menu .context-menu-item.text-danger, #context-menu .context-menu-item.text-danger * { color: #dc3545 !important; }
        #context-menu .context-menu-item.text-success, #context-menu .context-menu-item.text-success * { color: #198754 !important; }
        #context-menu .context-menu-item.text-warning, #context-menu .context-menu-item.text-warning * { color: #f59e0b !important; }
        #context-menu .context-menu-item.text-info, #context-menu .context-menu-item.text-info * { color: #0ea5e9 !important; }
        #context-menu .context-menu-item.text-brand, #context-menu .context-menu-item.text-brand * { color: var(--primary-dark) !important; }

        .context-menu-item.text-danger:hover {
            background-color: #f1f5f9 !important; /* Subtle Gray Hover for danger item */
        }
        .context-menu-item.text-danger:hover i, .context-menu-item.text-danger:hover * {
            color: #dc3545 !important;
        }
        [data-theme="dark"] .context-menu-item.text-danger:hover {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }
        [data-theme="dark"] .context-menu-item.text-danger:hover i, [data-theme="dark"] .context-menu-item.text-danger:hover * {
            color: #ea7a7e !important;
        }
        .context-menu-item:hover i {
            transform: scale(1.1);
        }

        .context-menu-divider {
            height: 1px;
            background-color: var(--secondary-light);
            margin: 0.5rem 0;
            opacity: 0.5;
        }
        /* END: Context Menu Styles */

        /* START: Global Loader Styles */
        #global-loader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(245, 245, 245, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1.5rem;
            transition: opacity 0.3s ease;
        }
        .loader-content {
            text-align: center;
            animation: fadeInScale 0.4s ease-out;
        }
        .premium-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(109, 14, 22, 0.1);
            border-top: 3px solid var(--primary-dark);
            border-right: 3px solid var(--accent-gold);
            border-radius: 50%;
            animation: spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            margin: 0 auto;
        }
        .loader-text {
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        /* END: Global Loader Styles */
        /* START: Sortable Columns Styling */
        .table thead th a {
            color: var(--text-dark) !important;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0;
        }
        .table thead th a:hover {
            color: var(--primary-dark) !important;
            opacity: 1;
            transform: translateY(-1px);
        }
        [data-theme="dark"] .table thead th a {
            color: var(--text-dark) !important;
        }
        .table thead th a i {
            font-size: 0.75rem;
            transition: var(--transition);
        }
        /* END: Sortable Columns Styling */

        /* Column Toggle Styles */
        .column-toggle-dropdown {
            min-width: 220px;
            padding: 0.75rem 0.6rem;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,.15);
            background: var(--white);
            border: 1px solid var(--secondary-light);
            z-index: 1050;
        }
        .column-list {
            max-height: 280px;
            overflow-y: auto;
            scroll-behavior: smooth;
            padding-inline-end: 10px; /* Space for scrollbar on LEFT side in RTL */
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }
        .column-list::-webkit-scrollbar {
            width: 6px;
        }
        .column-list::-webkit-scrollbar-thumb {
            background-color: #cbd5e1; /* Light Grey Scrollbar */
            border-radius: 10px;
        }
        .column-list::-webkit-scrollbar-track {
            background-color: #f1f5f9;
        }
        [data-theme="dark"] .column-toggle-dropdown {
            background: var(--white);
            border-color: var(--secondary-light);
        }
        [data-theme="dark"] .column-list::-webkit-scrollbar-thumb {
            background-color: #475569;
        }
        [data-theme="dark"] .column-list::-webkit-scrollbar-track {
            background-color: #334155;
        }
        .column-toggle-item {
            display: flex;
            align-items: center;
            padding: 0.35rem 0.5rem;
            border-radius: 6px;
            transition: 0.2s;
            cursor: pointer;
            color: var(--text-dark);
            font-size: 0.82rem;
            font-weight: 500;
        }
        .column-toggle-item:hover {
            background: var(--bg-light);
        }
        .column-toggle-item input {
            width: 14px;
            height: 14px;
            margin-inline-end: 8px;
            cursor: pointer;
        }
        .btn-column-toggle {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #475569;
            border-radius: 10px;
            width: 38px;
            height: 38px;
            transition: var(--transition);
        }
        .btn-column-toggle:hover {
            background-color: #e2e8f0;
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        [data-theme="dark"] .btn-column-toggle {
            background-color: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }
        [data-theme="dark"] .btn-column-toggle:hover {
            background-color: #334155;
            color: #fff;
        }

        /* START: Toast Notification Styles */
        .toast-container {
            position: fixed;
            bottom: 25px;
            left: 30px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 15px;
            pointer-events: none;
        }

        .custom-toast {
            position: relative;
            border-radius: 12px;
            background: var(--white);
            padding: 15px 25px 15px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transform: translateX(calc(-100% - 40px));
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.35);
            min-width: 300px;
            max-width: 450px;
            border: 1px solid var(--secondary-light);
            display: flex;
            align-items: center;
            pointer-events: auto;
        }

        .custom-toast.active {
            transform: translateX(0%);
        }

        .custom-toast .toast-content {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .toast-content .toast-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            min-width: 40px;
            background-color: var(--primary-dark);
            color: #fff;
            font-size: 20px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .toast-content .message {
            display: flex;
            flex-direction: column;
            margin: 0 15px;
            flex-grow: 1;
        }

        .message .text {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-light);
            line-height: 1.4;
        }

        .message .text.text-1 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .custom-toast .close-toast {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px;
            cursor: pointer;
            opacity: 0.5;
            transition: var(--transition);
            color: var(--text-dark);
            font-size: 1rem;
        }

        .custom-toast .close-toast:hover {
            opacity: 1;
        }

        .custom-toast .progress-bar-toast {
            position: absolute;
            bottom: 0;
            right: 0;
            height: 4px;
            width: 100%;
            background: var(--secondary-light);
        }

        .custom-toast .progress-bar-toast:before {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            height: 100%;
            width: 100%;
            background-color: var(--primary-dark);
        }

        .custom-toast.active .progress-bar-toast:before {
            animation: toast-progress 5s linear forwards;
        }

        .custom-toast:hover .progress-bar-toast:before {
            animation-play-state: paused;
        }

        @keyframes toast-progress {
            100% {
                right: 100%;
            }
        }

        /* Toast Variants */
        .toast-success .toast-icon { background-color: #198754; }
        .toast-success .progress-bar-toast:before { background-color: #198754; }
        
        .toast-error .toast-icon { background-color: #dc3545; }
        .toast-error .progress-bar-toast:before { background-color: #dc3545; }
        
        .toast-warning .toast-icon { background-color: #ffc107; color: #000; }
        .toast-warning .progress-bar-toast:before { background-color: #ffc107; }
        
        .toast-info .toast-icon { background-color: #0dcaf0; }
        .toast-info .progress-bar-toast:before { background-color: #0dcaf0; }
        /* END: Toast Notification Styles */
    </style>
    @stack('styles')
</head>
<body>
    
    <div class="main-wrapper" x-data="{ 
        sidebarOpen: window.innerWidth <= 992 ? false : (JSON.parse(localStorage.getItem('sidebarOpen')) !== false),
        init() {
            this.$watch('sidebarOpen', value => {
                if (window.innerWidth > 992) {
                    localStorage.setItem('sidebarOpen', value);
                }
            });
        }
    }" @window:resize.debounce="sidebarOpen = window.innerWidth > 992 ? JSON.parse(localStorage.getItem('sidebarOpen')) !== false : true">
        <div class="sidebar" :class="{ 'active': sidebarOpen, 'collapsed': !sidebarOpen && window.innerWidth > 992 }">
            <div class="sidebar-brand">
                <span>Tofof</span>
            </div>
            
            <div class="sidebar-content">
                <ul class="nav flex-column mt-2">
                    @can('view-admin-panel')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>لوحة التحكم</span>
                        </a>
                    </li>
                    @endcan
                    
                    <li class="nav-item" x-data="{ open: {{ request()->routeIs('admin.blog.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open"
                        class="nav-link d-flex justify-content-between reports-toggle"
                        :class="{ 'active': open }"
                        aria-expanded="false"
                        :aria-expanded="open.toString()">
                            <div>
                                <i class="bi bi-pencil-square"></i>
                                <span>المدونة</span>
                            </div>
                            <i class="bi transition-transform" :class="open ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                        </a>

                        <div x-show="open" x-collapse class="reports-submenu">
                            <ul class="nav flex-column">
                                @can('view-blog')
                                <li class="nav-item">
                                    <a href="{{ route('admin.blog.posts.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.blog.posts.*') ? 'active' : '' }}">
                                        <i class="bi bi-file-text"></i>
                                        <span>المقالات</span>
                                    </a>
                                </li>
                                @endcan
                                @can('view-blog-categories')
                                <li class="nav-item">
                                    <a href="{{ route('admin.blog.categories.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">
                                        <i class="bi bi-tags"></i>
                                        <span>الأقسام</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                    
                    @can('view-orders')
                    <li class="nav-item">
                        <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <i class="bi bi-cart-check"></i>
                            <span>الطلبات</span>
                            @if (isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                                <span class="badge bg-primary rounded-pill ms-auto">{{ $pendingOrdersCount }}</span>
                            @endif
                        </a>
                    </li>
                    @endcan
                    {{-- ===== END: الطلبات + العملاء ===== --}}

                    @can('view-gifts')
                    @if (Route::has('admin.gifts.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.gifts.index') }}" class="nav-link {{ request()->routeIs('admin.gifts.*') ? 'active' : '' }}">
                            <i class="bi bi-gift"></i>
                            <span>الهدايا</span>
                        </a>
                    </li>
                    @endif
                    @endcan
                    
                    @can('view-products')
                    <li class="nav-item">
                        <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="bi bi-basket"></i>
                            <span>المنتجات</span>
                        </a>
                    </li>
                    @endcan

                    @can('view-primary-categories')
                    <li class="nav-item">
                        <a href="{{ route('admin.primary-categories.index') }}" class="nav-link {{ request()->routeIs('admin.primary-categories.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            <span>الفئة</span>
                        </a>
                    </li>
                    @endcan
                    
                                        @can('view-categories')
                    <li class="nav-item">
                        <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <i class="bi bi-card-list"></i>
                            <span>البراند</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can('view-discount-codes')
                    <li class="nav-item">
                        <a href="{{ route('admin.discount-codes.index') }}" class="nav-link {{ request()->routeIs('admin.discount-codes.*') ? 'active' : '' }}">
                            <i class="bi bi-percent"></i>
                            <span>أكواد الخصم</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can('view-managers')
                    <li class="nav-item">
                        <a href="{{ route('admin.managers.index') }}" class="nav-link {{ request()->routeIs('admin.managers.*') ? 'active' : '' }}">
                            <i class="bi bi-person-vcard"></i>
                            <span>المدراء</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can('view-roles')
                    <li class="nav-item">
                        <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i>
                            <span>الصلاحيات</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can('view-users')
                    <hr class="mx-3 my-2">
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i>
                            <span>المستخدمين</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can('view-reports')
                    <hr class="mx-3 my-2">
                    <li class="nav-item" x-data="{ open: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open" class="nav-link d-flex justify-content-between reports-toggle" 
                        :class="{ 'active': open }" aria-expanded="false" :aria-expanded="open.toString()">
                            <div>
                                <i class="bi bi-graph-up-arrow"></i>
                                <span>التقارير</span>
                            </div>
                            <i class="bi transition-transform" :class="open ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                        </a>
                        <div x-show="open" x-collapse class="reports-submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.index') }}" class="nav-link sub-link">
                                        <i class="bi bi-bar-chart"></i>
                                        <span>التقارير الرئيسية</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.financial') }}" class="nav-link sub-link">
                                        <i class="bi bi-cart"></i>
                                        <span>تقارير المبيعات</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.inventory') }}" class="nav-link sub-link">
                                        <i class="bi bi-box"></i>
                                        <span>تقارير المخزون</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.customers') }}" class="nav-link sub-link">
                                        <i class="bi bi-people"></i>
                                        <span>تقارير العملاء</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @if(auth()->user()->can('edit-settings') || auth()->user()->can('edit-settings-frontend') || auth()->user()->can('edit-settings-seo') || auth()->user()->can('manage-whatsapp') || auth()->user()->can('manage-slides') || auth()->user()->can('manage-backups') || auth()->user()->can('manage-barcodes') || auth()->user()->can('manage-customer-tiers') || auth()->user()->can('manage-imports'))
                    <hr class="mx-3 my-2">
                    <li class="nav-item"
                        x-data="{ open: {{ (request()->routeIs('admin.settings.*')
                                         || request()->routeIs('admin.whatsapp.*')
                                         || request()->routeIs('admin.homepage-slides.*')
                                         || request()->routeIs('admin.imports.*')
                                         || request()->routeIs('admin.backups.*')
                                         || request()->routeIs('admin.barcodes.*')
                                         || request()->routeIs('admin.customer-tiers.*')
                                         || request()->routeIs('admin.products.import_quantity')) ? 'true' : 'false' }} }">

                        <a href="#"
                        @click.prevent="open = !open"
                        class="nav-link d-flex justify-content-between reports-toggle"
                        :class="{ 'active': open }"
                        aria-expanded="false"
                        :aria-expanded="open.toString()">
                            <div>
                                <i class="bi bi-gear-wide-connected"></i>
                                <span>الإعدادات</span>
                            </div>
                            <i class="bi transition-transform" :class="open ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                        </a>

                        <div x-show="open" x-collapse class="reports-submenu">
                            <ul class="nav flex-column">
                                @if(auth()->user()->can('edit-settings') || auth()->user()->can('edit-settings-frontend') || auth()->user()->can('edit-settings-seo'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.settings.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                        <i class="bi bi-globe2"></i>
                                        <span>الموقع</span>
                                    </a>
                                </li>
                                @endif
                                @can('manage-whatsapp')
                                <li class="nav-item">
                                    <a href="{{ route('admin.whatsapp.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>واتساب</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.settings.index', ['tab' => 'integrations']) }}"
                                    class="nav-link sub-link {{ request()->getQueryString() == 'tab=integrations' ? 'active' : '' }}">
                                        <i class="bi bi-telegram"></i>
                                        <span>تليجرام</span>
                                    </a>
                                </li>
                                @endcan

                                @can('manage-imports')
                                <li class="nav-item">
                                    <a href="{{ route('admin.products.import_quantity') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.products.import_quantity') ? 'active' : '' }}">
                                        <i class="bi bi-upload"></i>
                                        <span>تحديث كميات المنتجات (Excel)</span>
                                    </a>
                                </li>
                                @endcan
                                @can('manage-slides')
                                <li class="nav-item">
                                    <a href="{{ route('admin.homepage-slides.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.homepage-slides.*') ? 'active' : '' }}">
                                        <i class="bi bi-images"></i>
                                        <span>سلايدرات الصفحة</span>
                                    </a>
                                </li>
                                @endcan
                                @can('manage-imports')
                                <li class="nav-item">
                                    <a href="{{ route('admin.imports.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.imports.*') ? 'active' : '' }}">
                                        <i class="bi bi-cloud-arrow-down"></i>
                                        <span>استرداد</span>
                                    </a>
                                </li>
                                @endcan
                                @can('manage-backups')
                                <li class="nav-item">
                                    <a href="{{ route('admin.backups.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                                        <i class="bi bi-hdd-stack"></i>
                                        <span>النسخ الاحتياطي</span>
                                    </a>
                                </li>
                                @endcan
                                @can('manage-barcodes')
                                <li class="nav-item">
                                    <a href="{{ route('admin.barcodes.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.barcodes.*') ? 'active' : '' }}">
                                        <i class="bi bi-qr-code"></i>
                                        <span>أكواد QR / باركود</span>
                                    </a>
                                </li>
                                @endcan
                                @can('manage-customer-tiers')
                                <li class="nav-item">
                                    <a href="{{ route('admin.customer-tiers.index') }}"
                                    class="nav-link sub-link {{ request()->routeIs('admin.customer-tiers.*') ? 'active' : '' }}">
                                        <i class="bi bi-sliders2"></i>
                                        <span>إعدادات الفئات (العملاء)</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                    @endif
                    
                    @can('view-activity-log')
                    <li class="nav-item">
                        <a href="{{ route('admin.activity-log.index') }}" class="nav-link {{ request()->routeIs('admin.activity-log.*') ? 'active' : '' }}">
                            <i class="bi bi-list-check"></i>
                            <span>سجل الأنشطة</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            
            
            <div class="sidebar-footer">
                الإصدار 1.0.0
            </div>
        </div>
        
        <div class="sidebar-overlay" :class="{ 'active': sidebarOpen }" @click="sidebarOpen = false"></div>

        <div class="content-wrapper" :class="{ 'expanded': !sidebarOpen }">
            <nav class="topbar">
                <button class="sidebar-toggle-btn" @click="sidebarOpen = !sidebarOpen">
                    <i class="bi bi-list"></i>
                </button>
                
                <div class="ms-auto d-flex align-items-center">
                    
                    {{-- START: Theme Switcher Button --}}
                    <button @click="toggleTheme()" class="sidebar-toggle-btn me-2" title="تبديل المظهر">
                        <i class="bi" :class="theme === 'light' ? 'bi-moon-stars-fill' : 'bi-sun-fill'"></i>
                    </button>
                    {{-- END: Theme Switcher Button --}}

                    <div class="dropdown me-3" 
                        x-data="notificationsComponent(
                            '{{ route('admin.notifications.index') }}', 
                            '{{ route('admin.notifications.markAsRead') }}', 
                            '{{ route('admin.notifications.markAllRead') }}',
                            '{{ route('admin.notifications.clearAll') }}'
                        )" 
                        x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 30000)">

                        <button class="btn btn-light rounded-circle position-relative d-flex align-items-center justify-content-center"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                style="width:40px;height:40px;border:1px solid var(--secondary-light);">
                            <i class="bi bi-bell fs-5" style="color: var(--text-light);"></i>
                            <span x-show="unreadCount > 0" x-text="unreadCount"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light"
                                style="display:none;"></span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow mt-2" style="width: 350px;">
                            <li class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--secondary-light) !important;">
                                <h6 class="mb-0 fw-bold">الإشعارات</h6>
                                <div class="btn-group">
                                    <button x-show="unreadCount > 0" @click.prevent="markAllAsRead()" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" title="تعليم الكل كمقروء">
                                        <i class="bi bi-check-all"></i>
                                    </button>
                                    <button x-show="notifications.length > 0" @click.prevent="clearAll()" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" title="حذف الكل">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </li>
                            <li>
                                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                    <template x-if="loading">
                                        <div class="d-flex justify-content-center p-4">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                        </div>
                                    </template>
                                    <template x-if="!loading && notifications.length === 0">
                                        <div class="list-group-item text-center text-muted py-4">
                                           <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                           لا توجد إشعارات
                                        </div>
                                    </template>
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <a @click.prevent="markAsRead(notification)" :href="getNotificationUrl(notification)" 
                                           class="list-group-item list-group-item-action notification-item"
                                           :class="{ 'unread fw-bold': !notification.read_at }">
                                            <div class="d-flex align-items-start">
                                                <i class="bi" :class="notification.data.icon || 'bi-info-circle'" style="font-size: 1.5rem; margin-left: 1rem;"></i>
                                                <div>
                                                    <p class="mb-1" style="font-size: 0.9rem;" x-text="notification.data.message"></p>
                                                    <small class="text-muted" x-text="timeAgo(notification.created_at)"></small>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="user-dropdown dropdown">
                        <a class="dropdown-toggle d-flex align-items-center text-decoration-none" href="#" role="button" data-bs-toggle="dropdown">
                            <span class="d-none d-sm-inline mx-2">{{ Auth::user()->name }}</span>
                            <img
                              src="{{ Auth::user()->avatar_url }}"
                              alt="User"
                              class="user-dropdown img"
                              onerror="this.onerror=null;this.src='{{ asset('storage/avatars/default.jpg') }}';"
                            />
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="{{ route('homepage') }}" target="_blank">
                                    <i class="bi bi-box-arrow-up-right me-2"></i> عرض الموقع
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                    <i class="bi bi-person me-2"></i> الملف الشخصي
                                </a>
                            </li>
                            <div x-data="pushNotifications">
                                <button @click="toggleSubscription()" 
                                        :disabled="!isSupported || isEnabling || isDisabling"
                                        class="dropdown-item" 
                                        x-text="buttonText">
                                </button>
                            </div>
                            @if(session()->has('impersonator_id'))
                            @php
                                $stopImpersonateRoute = session('impersonator_guard') === 'admin'
                                    ? 'admin.managers.stopImpersonate'
                                    : 'admin.users.stopImpersonate';
                            @endphp
                            <li>
                                <form action="{{ route($stopImpersonateRoute) }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-warning" type="submit">
                                        <i class="bi bi-person-x me-2"></i> إيقاف الانتحال
                                    </button>
                                </form>
                            </li>
                            @endif
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('admin.logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-top').submit();">
                                    <i class="bi bi-box-arrow-left me-2"></i> تسجيل الخروج
                                </a>
                                <form id="logout-form-top" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <main class="main-content">
                <div class="container-fluid">
                    {{-- Toast Container for dynamic and flash messages --}}
                    <div id="toast-container" class="toast-container"></div>
                    
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- START: Context Menu HTML -->
    <div id="context-menu"></div>
    <!-- END: Context Menu HTML -->

    <div id="global-loader">
        <div class="loader-content">
            <div class="premium-spinner"></div>
            <div class="loader-text mt-3">جاري التحميل...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('global-loader');
        
        // Show loader on link clicks
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && href !== '#' && !href.startsWith('javascript:') && !this.hasAttribute('data-bs-toggle') && !this.hasAttribute('target')) {
                    loader.style.display = 'flex';
                }
            });
        });

        // Show loader on form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                if (this.classList.contains('js-move-form') || this.dataset.noLoader === 'true') {
                    return;
                }
                loader.style.display = 'flex';
            });
        });

        // Hide loader when page is shown (useful for back/forward cache)
        window.addEventListener('pageshow', function() {
            loader.style.display = 'none';
        });

        // This script is no longer strictly necessary with pure Alpine control, but can be a good fallback.
        const mainWrapper = document.querySelector('.main-wrapper');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggleBtn = document.querySelector('.sidebar-toggle-btn');
        function toggleSidebar(){ 
            if(mainWrapper && mainWrapper.__x) {
                mainWrapper.__x.$data.sidebarOpen = !mainWrapper.__x.$data.sidebarOpen;
            }
        }
        if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarContent = document.querySelector('.sidebar-content');
        const savedScroll = sessionStorage.getItem('sidebarScroll');
        if (savedScroll !== null && sidebarContent) sidebarContent.scrollTop = parseInt(savedScroll);
        if (sidebarContent) {
            sidebarContent.addEventListener('scroll', function () {
                sessionStorage.setItem('sidebarScroll', sidebarContent.scrollTop);
            });
        }
    });
    </script>
    
    
    <script>
    function notificationsComponent(fetchUrl, markUrl, markAllUrl, clearAllUrl) {
        return {
            notifications: [],
            unreadCount: 0,
            loading: true,
            fetchNotifications() {
                if (this.notifications.length === 0) this.loading = true;
                
                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(data => {
                        this.notifications = data.notifications;
                        this.unreadCount = data.unread_count;
                    })
                    .catch(error => console.error('Error fetching notifications:', error))
                    .finally(() => this.loading = false);
            },
            markAsRead(notification) {
                const targetUrl = this.getNotificationUrl(notification);
                if (targetUrl === '#') return;

                window.location.href = targetUrl;
                
                if (!notification.read_at) {
                    fetch(markUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ id: notification.id })
                    }).catch(err => console.error('Failed to mark notification as read:', err));
                }
            },
            markAllAsRead() {
                fetch(markAllUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.notifications.forEach(n => n.read_at = new Date().toISOString());
                        this.unreadCount = 0;
                    }
                }).catch(err => console.error('Failed to mark all as read:', err));
            },
            clearAll() {
                if (!confirm('هل أنت متأكد من رغبتك في حذف جميع الإشعارات؟ لا يمكن التراجع عن هذا الإجراء.')) {
                    return;
                }
                
                fetch(clearAllUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.notifications = [];
                        this.unreadCount = 0;
                    }
                }).catch(err => console.error('Failed to clear all notifications:', err));
            },
            getNotificationUrl(notification) {
                if (notification.data.order_id) {
                    return `{{ url('/') }}/admin/orders/${notification.data.order_id}`;
                }

                return '#';
            },
            timeAgo(dateString) {
                const date = new Date(dateString);
                const seconds = Math.floor((new Date() - date) / 1000);
                let interval = seconds / 31536000;
                if (interval > 1) return `منذ ${Math.floor(interval)} سنة`;
                interval = seconds / 2592000;
                if (interval > 1) return `منذ ${Math.floor(interval)} شهر`;
                interval = seconds / 86400;
                if (interval > 1) return `منذ ${Math.floor(interval)} يوم`;
                interval = seconds / 3600;
                if (interval > 1) return `منذ ${Math.floor(interval)} ساعة`;
                interval = seconds / 60;
                if (interval > 1) return `منذ ${Math.floor(interval)} دقيقة`;
                return `الآن`;
            }
        }
    }
    </script>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pushNotifications', () => ({
            isSupported: 'serviceWorker' in navigator && 'PushManager' in window,
            isSubscribed: false,
            isEnabling: false,
            isDisabling: false,
            buttonText: 'تفعيل إشعارات الطلبات',

            init() {
                if (!this.isSupported) {
                    this.buttonText = 'الإشعارات غير مدعومة';
                    return;
                }
                navigator.serviceWorker.register('/sw.js').then(sw => {
                    sw.pushManager.getSubscription().then(sub => {
                        if (sub) {
                            this.isSubscribed = true;
                            this.buttonText = 'إلغاء إشعارات الطلبات';
                        }
                    });
                });
            },

            toggleSubscription() {
                if (this.isSubscribed) {
                    this.unsubscribe();
                } else {
                    this.subscribe();
                }
            },

            subscribe() {
                this.isEnabling = true;
                this.buttonText = 'جاري التفعيل...';
                navigator.serviceWorker.ready.then(sw => {
                    const vapidPublicKey = '{{ config('webpush.vapid.public_key') }}';
                    sw.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey)
                    }).then(sub => {
                        this.saveSubscription(sub);
                        this.isSubscribed = true;
                        this.buttonText = 'إلغاء إشعارات الطلبات';
                    }).catch(e => {
                        console.error('Subscription failed:', e);
                        this.buttonText = 'فشل التفعيل';
                    }).finally(() => {
                        this.isEnabling = false;
                    });
                });
            },

            unsubscribe() {
                this.isDisabling = true;
                this.buttonText = 'جاري الإلغاء...';
                navigator.serviceWorker.ready.then(sw => {
                    sw.pushManager.getSubscription().then(sub => {
                        if(sub) {
                            sub.unsubscribe().then(() => {
                                this.deleteSubscription(sub);
                                this.isSubscribed = false;
                                this.buttonText = 'تفعيل إشعارات الطلبات';
                            });
                        }
                    }).catch(e => {
                         console.error('Unsubscription failed:', e);
                         this.buttonText = 'فشل الإلغاء';
                    }).finally(() => {
                        this.isDisabling = false;
                    });
                });
            },

            saveSubscription(sub) {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('{{ route("admin.push_subscriptions.update") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(sub.toJSON())
                });
            },

            deleteSubscription(sub) {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('{{ route("admin.push_subscriptions.destroy") }}', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ endpoint: sub.endpoint })
                });
            },

            urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }
        }));
    });
</script>
    <!-- START: Custom Confirmation Modal HTML -->
    <div class="modal fade" id="custom-confirm-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="icon-wrapper">
                        <i class="bi bi-question-circle"></i>
                    </div>
                    <h5 class="fw-bold mb-3" id="confirm-title">تأكيد الإجراء</h5>
                    <p class="text-muted mb-0" id="confirm-message">هل أنت متأكد من رغبتك في الاستمرار؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn-confirm" id="confirm-proceed-btn">تأكيد</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Custom Confirmation Modal HTML -->

    <!-- START: Context Menu Logic -->
    <script>
    // Global Confirmation Helper
    window.confirmAction = function(message, callback) {
        const modalEl = document.getElementById('custom-confirm-modal');
        if (!modalEl) {
            if (confirm(message)) callback();
            return;
        }
        
        const messageEl = document.getElementById('confirm-message');
        const proceedBtn = document.getElementById('confirm-proceed-btn');
        
        messageEl.textContent = message;
        
        const modal = new bootstrap.Modal(modalEl);
        
        const handleConfirm = () => {
            modal.hide();
            callback();
            proceedBtn.removeEventListener('click', handleConfirm);
        };
        
        proceedBtn.addEventListener('click', handleConfirm);
        
        modalEl.addEventListener('hidden.bs.modal', () => {
            proceedBtn.removeEventListener('click', handleConfirm);
        }, { once: true });
        
        modal.show();
    };

    // Global Interceptor for onclick="confirm(...)"
    document.addEventListener('click', function(e) {
        let target = e.target.closest('[onclick*="confirm("]');
        if (!target) return;
        
        const onclick = target.getAttribute('onclick');
        if (onclick && onclick.includes('return confirm(')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            const match = onclick.match(/confirm\(['"](.+?)['"]\)/);
            const msg = match ? match[1] : 'هل أنت متأكد؟';
            
            window.confirmAction(msg, () => {
                // Remove the onclick temporarily to prevent recursion
                const originalOnclick = target.onclick;
                target.onclick = null;
                
                if (target.type === 'submit' && target.form) {
                    target.form.submit();
                } else {
                    target.click();
                }
                
                // Restore after a delay
                setTimeout(() => { target.onclick = originalOnclick; }, 100);
            });
        }
    }, true);

    // Global Interceptor for <form onsubmit="return confirm(...)">
    document.addEventListener('submit', function(e) {
        let form = e.target;
        const onsubmit = form.getAttribute('onsubmit');
        
        if (onsubmit && onsubmit.includes('confirm(')) {
            // Check if we are already proceeding after custom confirm
            if (form.dataset.customConfirmed === 'true') {
                delete form.dataset.customConfirmed;
                return;
            }
            
            e.preventDefault();
            e.stopImmediatePropagation();
            
            const match = onsubmit.match(/confirm\(['"](.+?)['"]\)/);
            const msg = match ? match[1] : 'هل أنت متأكد؟';
            
            window.confirmAction(msg, () => {
                form.dataset.customConfirmed = 'true';
                form.submit();
            });
        }
    }, true);

    document.addEventListener('DOMContentLoaded', function() {
        const menu = document.getElementById('context-menu');
        if (!menu) return;
        
        document.addEventListener('contextmenu', function(e) {
            const row = e.target.closest('table tbody tr');
            
            // Clear existing highlighting
            document.querySelectorAll('table tbody tr').forEach(r => r.classList.remove('row-context-active'));

            if (row) {
                // Highlight current row
                row.classList.add('row-context-active');

                // Look for action buttons/links in this row
                const actions = row.querySelectorAll('a, button, .context-menu-action');
                
                let itemsFound = 0;
                menu.innerHTML = '';
                
                const addedLabels = new Set();
                actions.forEach((action) => {
                    if (action.classList.contains('form-check-input') || action.dataset.noContext === 'true') return;
                    if (action.tagName === 'A' && !action.classList.contains('btn') && !action.classList.contains('context-menu-action')) {
                        if (!action.querySelector('i')) return;
                    }

                    let text = action.innerText.trim() || action.getAttribute('title') || action.getAttribute('aria-label');
                    const icon = action.querySelector('i');
                    
                    if (!text && !icon) return;
                    if (text && addedLabels.has(text.toLowerCase())) return;
                    if (text) addedLabels.add(text.toLowerCase());

                    itemsFound++;
                    const item = document.createElement('div');
                    item.className = 'context-menu-item';

                    // Smart Detection & Coloring
                    if (icon) {
                        const classes = icon.className.toLowerCase();
                        if (classes.includes('bi-pencil') || classes.includes('bi-edit') || classes.includes('bi-pencil-square')) {
                            if (!text) text = 'تعديل';
                            item.classList.add('text-primary');
                        }
                        else if (classes.includes('bi-plus-slash-minus')) {
                            if (!text) text = 'تعديل الكمية';
                            item.classList.add('text-info');
                        }
                        else if (classes.includes('bi-trash')) {
                            if (!text) text = 'حذف';
                            item.classList.add('text-danger');
                        }
                        else if (classes.includes('bi-eye')) {
                            text = 'عرض التفاصيل';
                            item.classList.add('text-info');
                        }
                        else if (classes.includes('bi-pause')) {
                            text = text || 'إيقاف التفعيل';
                            item.classList.add('text-warning');
                        }
                        else if (classes.includes('bi-play')) {
                            text = text || 'تفعيل';
                            item.classList.add('text-success');
                        }
                        else if (classes.includes('bi-arrow-up')) {
                            text = text || 'تصعيد';
                            item.classList.add('text-info');
                        }
                        else if (classes.includes('bi-arrow-down')) {
                            text = text || 'تنزيل';
                            item.classList.add('text-info');
                        }
                        else if (classes.includes('bi-truck')) {
                            text = 'شحن الطلب';
                            item.classList.add('text-success');
                        }
                        else if (classes.includes('bi-check')) {
                            text = 'توصيل / تأكيد';
                            item.classList.add('text-success');
                        }
                        else if (classes.includes('bi-x-circle')) {
                            text = 'إلغاء';
                            item.classList.add('text-danger');
                        }
                        else if (classes.includes('bi-printer')) {
                            text = 'طباعة';
                            item.classList.add('text-brand');
                        }
                    }
                    
                    if (icon) {
                        const iconClone = icon.cloneNode(true);
                        // Clean up bootstrap text classes that might conflict with our menu styling
                        iconClone.classList.remove('text-white', 'text-light', 'text-dark', 'text-primary', 'text-secondary', 'text-success', 'text-warning', 'text-info');
                        item.appendChild(iconClone);
                    }
                    if (text) {
                        const span = document.createElement('span');
                        span.textContent = text;
                        span.style.color = 'inherit';
                        item.appendChild(span);
                    }
                    
                    if (action.classList.contains('text-danger') || action.classList.contains('btn-outline-danger') || action.classList.contains('btn-danger') || action.closest('.text-danger')) {
                        item.classList.add('text-danger');
                    }
                    else if (action.classList.contains('text-success') || action.classList.contains('btn-outline-success') || action.classList.contains('btn-success')) {
                        item.classList.add('text-success');
                    }
                    
                    item.onclick = () => {
                        menu.style.display = 'none';
                        
                        // Check for confirm link or onclick confirm
                        const onclick = action.getAttribute('onclick');
                        if (onclick && onclick.includes('confirm(')) {
                            // Extract the message from confirm('...')
                            const match = onclick.match(/confirm\(['"](.+?)['"]\)/);
                            const msg = match ? match[1] : 'هل أنت متأكد؟';
                            
                            window.confirmAction(msg, () => {
                                // If it's a form button, submit the form
                                if (action.type === 'submit' && action.form) {
                                    action.form.submit();
                                } else {
                                    action.click();
                                }
                            });
                        } else {
                            action.click();
                        }
                    };
                    
                    menu.appendChild(item);
                });
                
                if (itemsFound > 0) {
                    e.preventDefault();
                    menu.style.display = 'block';
                    
                    let posX = e.clientX;
                    let posY = e.clientY;
                    
                    if (posX + menu.offsetWidth > window.innerWidth) posX -= menu.offsetWidth;
                    if (posY + menu.offsetHeight > window.innerHeight) posY -= menu.offsetHeight;
                    
                    menu.style.left = posX + 'px';
                    menu.style.top = posY + 'px';
                }
            } else {
                menu.style.display = 'none';
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target)) {
                menu.style.display = 'none';
                document.querySelectorAll('table tbody tr').forEach(r => r.classList.remove('row-context-active'));
            }
        });
        
        window.addEventListener('resize', () => {
            menu.style.display = 'none';
            document.querySelectorAll('table tbody tr').forEach(r => r.classList.remove('row-context-active'));
        });
        
        window.addEventListener('scroll', () => {
            menu.style.display = 'none';
            document.querySelectorAll('table tbody tr').forEach(r => r.classList.remove('row-context-active'));
        }, true);
    });
    </script>
    <!-- END: Context Menu Logic -->

    <!-- START: Column Toggle Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tables = document.querySelectorAll('table:not(.no-col-toggle)');
        
        tables.forEach((table, tableIndex) => {
            const tableId = table.getAttribute('id') || 'table_' + window.location.pathname.replace(/\//g, '_') + '_' + tableIndex;
            const storageKey = 'col_visibility_' + tableId;
            let visibilityState = JSON.parse(localStorage.getItem(storageKey) || '{}');
            
            const thead = table.querySelector('thead');
            if (!thead) return;
            
            const headers = thead.querySelectorAll('th');
            const toggleContainer = document.querySelector('.col-toggle-place');
            
            if (!toggleContainer) return;

            // Create Dropdown
            const wrapper = document.createElement('div');
            wrapper.className = 'dropdown d-inline-block me-1';
            wrapper.innerHTML = `
                <button class="btn btn-column-toggle d-inline-flex align-items-center justify-content-center shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="تخصيص عرض الأعمدة">
                    <i class="bi bi-layout-three-columns fs-5"></i>
                </button>
                <div class="dropdown-menu column-toggle-dropdown shadow-lg border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="mb-0 fw-bold small text-brand"><i class="bi bi-eye-fill me-1"></i> إظهار/إخفاء الأعمدة</h6>
                        <span class="badge bg-soft-brand text-brand fw-normal" style="font-size:10px">تخصيص الجدول</span>
                    </div>
                    <div class="column-list d-grid gap-1"></div>
                </div>
            `;
            
            const listContainer = wrapper.querySelector('.column-list');
            
            headers.forEach((th, index) => {
                const colName = (th.textContent || '').trim();
                // Skip if no text and no ID
                if (!colName && !th.dataset.columnId) return;
                
                const colId = th.dataset.columnId || 'col_' + index;
                const isDefaultHidden = th.dataset.hide === 'true';
                
                // Set initial state if not in storage
                if (visibilityState[colId] === undefined) {
                    visibilityState[colId] = !isDefaultHidden;
                }
                
                // Apply initial visibility
                toggleColumn(table, index, visibilityState[colId]);
                
                // Create label
                const item = document.createElement('label');
                item.className = 'column-toggle-item';
                item.innerHTML = `
                    <input type="checkbox" ${visibilityState[colId] ? 'checked' : ''} data-index="${index}" data-col-id="${colId}">
                    <span>${colName || 'عمود ' + (index + 1)}</span>
                `;
                
                item.querySelector('input').addEventListener('change', function() {
                    const isChecked = this.checked;
                    visibilityState[colId] = isChecked;
                    localStorage.setItem(storageKey, JSON.stringify(visibilityState));
                    toggleColumn(table, index, isChecked);
                });
                
                listContainer.appendChild(item);
            });
            
            toggleContainer.appendChild(wrapper);
        });
        
        function toggleColumn(table, index, show) {
            const display = show ? '' : 'none';
            
            // Header
            const th = table.querySelector(`thead tr th:nth-child(${index + 1})`);
            if (th) th.style.display = display;
            
            // Body cells
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const td = row.querySelector(`td:nth-child(${index + 1})`);
                if (td) td.style.display = display;
            });

            // Footer cells if any
            const tfootRows = table.querySelectorAll('tfoot tr');
            tfootRows.forEach(row => {
                const td = row.querySelector(`td:nth-child(${index + 1})`);
                if (td) td.style.display = display;
            });
        }
    });
    </script>
    <!-- END: Column Toggle Logic -->

    <!-- START: Global Table Actions Hider & Double Click Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            // Find the index of the "العمليات" or "الإجراءات" column
            const headers = table.querySelectorAll('thead th');
            let actionColIndex = -1;
            headers.forEach((th, index) => {
                const text = (th.textContent || '').trim();
                if (text.includes('العمليات') || text.includes('الإجراءات') || text.toLowerCase().includes('actions') || th.classList.contains('col-actions')) {
                    actionColIndex = index;
                    th.style.display = 'none';
                }
            });

            if (actionColIndex > -1) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.children;
                    if (cells.length > actionColIndex) {
                        // Hide the action cell itself
                        cells[actionColIndex].style.display = 'none';
                    }

                    // Handle Double Click safely
                    row.addEventListener('dblclick', function(e) {
                        // Ignore if we clicked on an input, link, button, select directly
                        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input') || e.target.closest('select') || e.target.closest('textarea') || e.target.closest('.form-check')) {
                            return;
                        }

                        // We already have the elements hidden in DOM, meaning we can still query them
                        if (cells.length > actionColIndex) {
                            const actionCell = cells[actionColIndex];
                            
                            // Let's find an edit or view action
                            let primaryAction = actionCell.querySelector('a i.bi-eye, a i.bi-pencil, a[title="عرض التفاصيل"], a[title="تعديل"], a[title="View"], a[title="Edit"]');
                            
                            // If found inside the action cell (which is standard)
                            if (primaryAction) {
                                const link = primaryAction.closest('a');
                                if (link && link.href) {
                                    window.location.href = link.href;
                                    return;
                                }
                            }
                            
                            // Fallback: If no edit/view icon found, just click the first regular anchor tag
                            const firstLink = actionCell.querySelector('a:not(.btn-danger):not(.text-danger):not([onclick*="confirm"])');
                            if (firstLink && firstLink.href) {
                                window.location.href = firstLink.href;
                                return;
                            }
                        }
                    });
                });
            }
        });
    });
    </script>
    <!-- END: Global Table Actions Hider & Double Click Logic -->

    @stack('scripts')

    <!-- START: Toast Notification Logic -->
    <script>
    window.showToast = function(title, message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `custom-toast toast-${type}`;
        
        // Define icons based on type
        let icon = 'bi-check-circle-fill';
        if (type === 'error') icon = 'bi-exclamation-triangle-fill';
        if (type === 'warning') icon = 'bi-exclamation-circle-fill';
        if (type === 'info') icon = 'bi-info-circle-fill';

        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">
                    <i class="bi ${icon}"></i>
                </div>
                <div class="message">
                    <span class="text text-1">${title}</span>
                    <span class="text text-2">${message}</span>
                </div>
            </div>
            <i class="bi bi-x close-toast"></i>
            <div class="progress-bar-toast"></div>
        `;

        container.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('active');
        }, 10);

        // Close logic
        const closeBtn = toast.querySelector('.close-toast');
        const closeToast = () => {
            toast.classList.remove('active');
            setTimeout(() => {
                toast.remove();
            }, 500);
        };

        closeBtn.onclick = closeToast;

        // Auto remove with Pause/Resume on Hover
        let remaining = 5000;
        let start = Date.now();
        let timer;

        const pause = () => {
            clearTimeout(timer);
            remaining -= Date.now() - start;
        };

        const resume = () => {
            start = Date.now();
            timer = setTimeout(closeToast, remaining);
        };

        toast.addEventListener('mouseenter', pause);
        toast.addEventListener('mouseleave', resume);

        // Initial start
        timer = setTimeout(closeToast, remaining);
        
        // Cleanup timers on manual close
        closeBtn.addEventListener('click', () => {
            clearTimeout(timer);
            toast.removeEventListener('mouseenter', pause);
            toast.removeEventListener('mouseleave', resume);
        });
    };

    // Handle Laravel Session Flash Messages
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            window.showToast('تم بنجاح', "{{ session('success') }}", 'success');
        @endif

        @if(session('error'))
            window.showToast('خطأ', "{{ session('error') }}", 'error');
        @endif

        @if(session('status'))
            window.showToast('تنبيه', "{{ session('status') }}", 'info');
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                window.showToast('خطأ في البيانات', "{{ $error }}", 'error');
            @endforeach
        @endif
    });
    </script>
    <!-- END: Toast Notification Logic -->
</body>
</html>
