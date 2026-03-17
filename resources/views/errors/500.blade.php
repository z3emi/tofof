<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطأ في الخادم</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(180deg, #f7f7f7 0%, #f1e7e6 100%);
            color: #1f2937;
            font-family: Tahoma, Arial, sans-serif;
        }

        .error-card {
            width: 100%;
            max-width: 720px;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(109, 14, 22, 0.10);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.10);
            padding: 48px 32px;
            text-align: center;
        }

        .error-code {
            margin: 0;
            font-size: clamp(72px, 12vw, 120px);
            line-height: 1;
            font-weight: 800;
            color: #6d0e16;
            opacity: 0.28;
        }

        .error-title {
            margin: 18px 0 0;
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 800;
            color: #111827;
        }

        .error-text {
            margin: 16px auto 0;
            max-width: 560px;
            font-size: 16px;
            line-height: 1.9;
            color: #4b5563;
        }

        .error-actions {
            margin-top: 28px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            padding: 14px 22px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .btn-primary {
            background: #6d0e16;
            color: #ffffff;
        }

        .btn-secondary {
            background: #ffffff;
            color: #374151;
            border-color: #d1d5db;
        }

        @media (max-width: 640px) {
            .error-card {
                padding: 36px 20px;
                border-radius: 22px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="error-card">
        <p class="error-code">500</p>
        <h1 class="error-title">عذراً، حدث خطأ داخلي</h1>
        <p class="error-text">
            لقد واجهنا مشكلة غير متوقعة في الخادم. فريقنا يعمل حالياً على حل هذه المشكلة. يرجى المحاولة مرة أخرى لاحقاً.
        </p>

        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">العودة إلى الرئيسية</a>
            <button type="button" class="btn btn-secondary" onclick="window.location.reload()">تحديث الصفحة</button>
        </div>
    </div>
</body>
</html>
