<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>تسجيل دخول الإدارة</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

<style>

:root{
--primary:#0F2A44;
--gold:#D4AF37;
--light:#F4F4F4;
--border:#E6E6E6;
--text:#333;
}

body{
font-family:'Tajawal',sans-serif;
height:100vh;
margin:0;
display:flex;
align-items:center;
justify-content:center;
background:
radial-gradient(circle at 18% 22%,rgba(212,175,55,.22) 0%,transparent 34%),
radial-gradient(circle at 83% 78%,rgba(255,255,255,.08) 0%,transparent 30%),
linear-gradient(140deg,#101820 0%,#1A2F4A 48%,#0C1A2A 100%);
overflow:hidden;
position:relative;
}

body:before{
content:"";
position:absolute;
width:500px;
height:500px;
background:radial-gradient(circle,rgba(212,175,55,.30),transparent 66%);
top:-150px;
right:-150px;
filter:blur(4px);
}

body:after{
content:"";
position:absolute;
width:450px;
height:450px;
background:radial-gradient(circle,rgba(15,42,68,.55),transparent 68%);
bottom:-150px;
left:-150px;
filter:blur(2px);
}

.login-card{
width:420px;
max-width:100%;
background:rgba(255,255,255,0.96);
backdrop-filter:blur(10px);
border-radius:18px;
box-shadow:0 25px 70px rgba(0,0,0,.45);
border:1px solid rgba(255,255,255,.2);
overflow:hidden;
}

.login-header{
background:linear-gradient(135deg,#0F2A44,#091a2b);
color:#fff;
padding:35px 30px;
text-align:center;
}

.brand{
font-size:12px;
letter-spacing:2px;
color:var(--gold);
margin-bottom:10px;
display:inline-block;
font-weight:700;
}

.login-header h1{
font-size:22px;
font-weight:700;
margin-bottom:5px;
}

.login-header p{
font-size:14px;
opacity:.8;
margin:0;
}

.login-body{
padding:35px 32px;
}

.form-label{
font-weight:600;
color:var(--primary);
margin-bottom:6px;
}

.form-control{
border-radius:10px;
padding:12px;
border:1px solid var(--border);
font-size:14px;
transition:.2s;
}

.form-control:focus{
border-color:var(--gold);
box-shadow:0 0 0 3px rgba(212,175,55,.15);
}

.form-check{
margin-bottom:18px;
}

.form-check-input{
cursor:pointer;
}

.form-check-input:checked{
background-color:var(--gold);
border-color:var(--gold);
}

.btn-login{
width:100%;
padding:12px;
border-radius:10px;
font-weight:700;
background:linear-gradient(135deg,#0F2A44,#0a1e32);
border:none;
color:#fff;
transition:.25s;
}

.btn-login:hover{
background:var(--gold);
color:#000;
transform:translateY(-2px);
box-shadow:0 10px 25px rgba(0,0,0,.3);
}

.invalid-feedback{
font-size:13px;
}

@media(max-width:500px){

.login-card{
width:100%;
margin:20px;
}

.login-body{
padding:25px;
}

}

</style>

</head>

<body>

<div class="login-card">

<div class="login-header">
<span class="brand">TOFOF ADMIN</span>
<h1>لوحة الإدارة</h1>
<p>يرجى تسجيل الدخول للوصول إلى لوحة التحكم</p>
</div>

<div class="login-body">

<form method="POST" action="{{ route('admin.login') }}">

@csrf

<div class="mb-3">

<label for="login" class="form-label">اسم المستخدم</label>

<input
type="text"
id="login"
name="login"
value="{{ old('login') }}"
class="form-control @error('login') is-invalid @enderror"
required
autofocus
>

@error('login')
<div class="invalid-feedback">{{ $message }}</div>
@enderror

</div>


<div class="mb-3">

<label for="password" class="form-label">كلمة السر</label>

<input
type="password"
id="password"
name="password"
class="form-control @error('password') is-invalid @enderror"
required
>

@error('password')
<div class="invalid-feedback">{{ $message }}</div>
@enderror

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
تذكرني
</label>

</div>


<button type="submit" class="btn-login">
تسجيل الدخول
</button>

</form>

</div>

</div>

</body>
</html>