<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل دخول الإدارة | Tofof</title>
    
    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Google Fonts: Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #6d0e16;
            --primary-light: #8b121c;
            --primary-soft: rgba(109, 14, 22, 0.1);
            --accent: #D4AF37;
            --white: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.5);
            --shadow-premium: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background Blobs */
        .bg-blobs {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .blob {
            position: absolute;
            filter: blur(80px);
            border-radius: 50%;
            opacity: 0.4;
            animation: move 20s infinite alternate;
        }

        .blob-1 {
            width: 500px;
            height: 500px;
            background: var(--primary);
            top: -100px;
            right: -100px;
            animation-duration: 25s;
        }

        .blob-2 {
            width: 400px;
            height: 400px;
            background: var(--accent);
            bottom: -50px;
            left: -50px;
            animation-duration: 30s;
            animation-delay: -5s;
        }

        .blob-3 {
            width: 300px;
            height: 300px;
            background: #475569;
            top: 40%;
            left: 10%;
            animation-duration: 20s;
            animation-delay: -10s;
        }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(100px, 50px) scale(1.1); }
        }

        /* Login Card */
        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            perspective: 1000px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--shadow-premium);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            border-radius: 18px;
            margin: 0 auto 15px;
            box-shadow: 0 10px 20px rgba(109, 14, 22, 0.2);
            transform: rotate(-5deg);
        }

        .brand-name {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Form Controls */
        .form-label {
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
            font-size: 0.9rem;
            display: block;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 24px;
        }

        .input-group-custom i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .form-control {
            height: 54px;
            padding: 0 45px 0 15px;
            border-radius: 14px;
            border: 2px solid #eef2f6;
            background: #f8fafc;
            color: var(--text-main);
            font-weight: 500;
            transition: var(--transition);
        }

        .form-control:focus {
            background: var(--white);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-soft);
            color: var(--text-main);
        }

        .input-group-custom:focus-within i {
            color: var(--primary);
        }

        /* Error States */
        .form-control.is-invalid {
            border-color: #ef4444;
            background-image: none;
        }

        .invalid-feedback {
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 5px;
            color: #ef4444;
        }

        /* Checkbox */
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-right: 0;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-left: 10px;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid #cbd5e1;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.9rem;
            cursor: pointer;
        }

        /* Login Button */
        .btn-login {
            width: 100%;
            height: 54px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(109, 14, 22, 0.3);
        }

        .btn-login:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -10px rgba(109, 14, 22, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer Links */
        .login-footer {
            text-align: center;
            margin-top: 25px;
        }

        .back-link {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-link:hover {
            color: var(--primary);
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            .brand-name {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            
            <div class="brand-section">
                <div class="brand-logo">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1 class="brand-name">Tofof <span style="color:var(--accent)">Admin</span></h1>
                <p class="subtitle">مرحباً بك مجدداً، يرجى تسجيل الدخول</p>
            </div>

            <form method="POST" action="{{ route('admin.login') }}" id="loginForm">
                @csrf

                <div class="mb-3">
                    <label for="login" class="form-label">اسم المستخدم</label>
                    <div class="input-group-custom">
                        <i class="bi bi-person-fill"></i>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            placeholder="أدخل اسم المستخدم"
                            value="{{ old('login') }}"
                            class="form-control @error('login') is-invalid @enderror"
                            required
                            autofocus
                        >
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">كلمة السر</label>
                    <div class="input-group-custom">
                        <i class="bi bi-key-fill"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            class="form-control @error('password') is-invalid @enderror"
                            required
                        >
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="bi bi-eye-slash" id="toggleIcon" style="position: static; transform: none; font-size: 1rem;"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="remember"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="remember">
                        تذكر تسجيل دخولي
                    </label>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span>تسجيل الدخول</span>
                    <i class="bi bi-arrow-left-circle-fill"></i>
                </button>

            </form>

            <div class="login-footer">
                <a href="{{ url('/') }}" class="back-link">
                    <i class="bi bi-house-door-fill"></i>
                    العودة للمتجر
                </a>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const toggleIcon = document.querySelector('#toggleIcon');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
        });

        // Simple button loading effect
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        loginForm.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>جاري التحميل...</span>
            `;
        });
    </script>
</body>
</html>