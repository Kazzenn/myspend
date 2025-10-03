<?php
session_start();
include('server.php');
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./favicon.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="description" content="เข้าสู่ระบบ MySpend เพื่อจัดการบัญชีรายรับรายจ่ายส่วนตัวของคุณอย่างปลอดภัย">
<meta name="keywords" content="เข้าสู่ระบบ MySpend, login การเงิน, บันทึกรายรับรายจ่าย">
    <!-- hCaptcha Script -->
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <title>MySpend - ระบบบันทึกรายรับรายจ่าย</title>
    <style>

.kanit-regular {
  font-family: "Kanit", sans-serif;
  font-weight: 400;
  font-style: normal;
}

body {
    font-family: "Kanit", sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%);
    pointer-events: none;
}

.container-box {
    font-family: "Kanit", sans-serif;
    max-width: 440px;
    width: 100%;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    padding: 45px 40px;
    border-radius: 24px;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.2),
        0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
}

.container-box:hover {
    transform: translateY(-8px);
    box-shadow: 
        0 30px 80px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.6) inset;
}

/* Decorative elements */
.container-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
    background-size: 200% 100%;
    border-radius: 24px 24px 0 0;
    animation: shimmer 3s ease infinite;
}

@keyframes shimmer {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* หัวข้อ */
.container-box h1 {
    font-family: "Kanit", sans-serif;
    font-size: 2.2rem;
    margin-bottom: 35px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-align: center;
    font-weight: 700;
    letter-spacing: -0.5px;
    animation: fadeInDown 0.6s ease-out;
}

.input-group {
    font-family: "Kanit", sans-serif;
    margin-bottom: 24px;
    animation: fadeIn 0.8s ease-out forwards;
    opacity: 0;
    position: relative;
}

/* ลำดับการแสดงผล */
.input-group:nth-child(2) { animation-delay: 0.1s; }
.input-group:nth-child(3) { animation-delay: 0.2s; }
.input-group:nth-child(4) { animation-delay: 0.3s; }
.input-group:nth-child(5) { animation-delay: 0.4s; }
.input-group:nth-child(6) { animation-delay: 0.5s; }
.input-group:nth-child(7) { animation-delay: 0.6s; }
.text-center { animation: fadeIn 0.8s ease-out forwards; opacity: 0; animation-delay: 0.7s; }

.input-group label {
    font-family: "Kanit", sans-serif;
    margin-bottom: 10px;
    font-weight: 600;
    color: #4c51bf;
    font-size: 0.95rem;
    display: block;
    letter-spacing: 0.3px;
}

.input-group input {
    font-family: "Kanit", sans-serif;
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    outline: none;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: #ffffff;
    color: #1f2937;
}

.input-group input:focus {
font-family: "Kanit", sans-serif;
    border-color: #667eea;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.input-group input::placeholder {
    color: #9ca3af;
}

/* hCaptcha Container */
.hcaptcha-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px;
    background: #f9fafb;
    border-radius: 12px;
    border: 2px dashed #e5e7eb;
    transition: all 0.3s ease;
}

.hcaptcha-container:hover {
    border-color: #667eea;
    background: #f3f4f6;
}

/* ปุ่ม */
button, .btn.btn-success {
    font-family: "Kanit", sans-serif;
    width: 100%;
    padding: 16px;
    font-size: 1.1rem;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 4px 15px rgba(102, 126, 234, 0.4),
        0 0 0 0 rgba(102, 126, 234, 0.5);
    position: relative;
    overflow: hidden;
    letter-spacing: 0.3px;
}

button::before, .btn.btn-success::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

button:hover::before, .btn.btn-success:hover::before {
    width: 300px;
    height: 300px;
}

button:hover, .btn.btn-success:hover {
    background: linear-gradient(135deg, #5568d3 0%, #6b3fa0 100%);
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(102, 126, 234, 0.5),
        0 0 0 4px rgba(102, 126, 234, 0.1);
}

button:active, .btn.btn-success:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

/* ปุ่มที่ถูกปิดใช้งาน */
button:disabled, .btn.btn-success:disabled {
    background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
    opacity: 0.6;
}

button:disabled:hover, .btn.btn-success:disabled:hover {
    background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
    transform: none;
    box-shadow: none;
}

button:disabled::before, .btn.btn-success:disabled::before {
    display: none;
}

/* ลิงก์ */
.container-box a {
font-family: "Kanit", sans-serif;
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
}

.container-box a::after {
font-family: "Kanit", sans-serif;
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
}

.container-box a:hover {
    color: #764ba2;
    text-decoration: none;
}

.container-box a:hover::after {
    width: 100%;
}

.text-center {
font-family: "Kanit", sans-serif;
    text-align: center;
    color: #6b7280;
    font-size: 0.95rem;
    line-height: 1.8;
}

/* ข้อความแสดงข้อผิดพลาด */
.error-message-box {
font-family: "Kanit", sans-serif;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border: 2px solid #f87171;
    border-radius: 12px;
    color: #dc2626;
    margin-bottom: 28px;
    padding: 16px 20px;
    text-align: center;
    animation: shake 0.5s ease-in-out, fadeIn 0.4s ease-out;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

.error-message-box h3 {
font-family: "Kanit", sans-serif;
    font-size: 0.95rem;
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.error-message-box h3::before {
    content: '⚠';
    font-size: 1.2rem;
}

/* Keyframes */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
    20%, 40%, 60%, 80% { transform: translateX(10px); }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 500px) {
    body {
        padding: 15px;
    }

    .container-box {
        padding: 35px 28px;
        border-radius: 20px;
        box-shadow: 
            0 15px 45px rgba(0, 0, 0, 0.2),
            0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    }

    .container-box h1 {
        font-size: 1.85rem;
    }

    button, .btn.btn-success {
        padding: 14px;
        font-size: 1.05rem;
    }

    .input-group input {
        padding: 12px 16px;
        font-size: 0.95rem;
    }

    .input-group {
        margin-bottom: 20px;
    }

    /* ปรับ hCaptcha */
    .h-captcha {
        transform: scale(0.9);
        transform-origin: center;
    }

    .hcaptcha-container {
        padding: 6px;
    }
}

@media (max-width: 380px) {
    .container-box {
        padding: 30px 22px;
    }

    .container-box h1 {
        font-size: 1.65rem;
        margin-bottom: 28px;
    }

    .h-captcha {
        transform: scale(0.8);
    }
}
 .custom-hr {
    border: none;              /* ลบเส้น default */
    height: 2px;               /* ความหนา */
    width: 150px;               /* ความยาว */
    background-color: #7e7f81ff; /* สีเส้น (เช่นน้ำเงิน) */
    margin: 20px auto;         /* จัดให้อยู่กลาง */
    border-radius: 5px;        /* มน ๆ หน่อย */
    margin: 7px auto;
  }
    </style>
</head>

<body>
    <div class="container-box">
        <h1>MySpend Login</h1>
        <form action="login_db.php" method="post" id="loginForm">
            <?php if (isset($_SESSION['error'])) : ?>
                <div class="error-message-box">
                    <h3>
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </h3>
                </div>
            <?php endif ?>
            
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="รหัสผ่าน" required>
            </div>

            <div class="input-group">
                <div class="hcaptcha-container">
                   
                    <div class="h-captcha" data-sitekey="0c2ebc06-bb48-44aa-8024-e2ece00cd31e" data-callback="enableLoginButton"></div>
                </div>
            </div>

            <div class="input-group">
                <button type="submit" name="login_user" class="btn btn-success" id="loginButton" disabled>เข้าสู่ระบบ</button>
            </div>

            <div class="text-center mt-3">
                <a href="./forgot_password.php">ลืมรหัสผ่าน?</a><br>
                <hr class="custom-hr">
                หรือยังไม่มีบัญชี? <a href="./register.php">สมัครสมาชิกเลย</a>
            </div>
        </form>
    </div>

    <script>
        // Function ที่จะถูกเรียกเมื่อ hCaptcha ถูกแก้ไขสำเร็จ
        function enableLoginButton(token) {
            document.getElementById('loginButton').disabled = false;
            document.getElementById('loginButton').style.opacity = '1';
        }

        // Function ที่จะถูกเรียกเมื่อ hCaptcha หมดอายุ
        function disableLoginButton() {
            document.getElementById('loginButton').disabled = true;
            document.getElementById('loginButton').style.opacity = '0.6';
        }

        // ตรวจสอบก่อน submit form
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const hcaptchaResponse = hcaptcha.getResponse();
            if (!hcaptchaResponse) {
                e.preventDefault();
                alert('กรุณายืนยัน hCaptcha ก่อนเข้าสู่ระบบ');
                return false;
            }
        });
    </script>
</body>

</html>