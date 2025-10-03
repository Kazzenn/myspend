<?php
session_start();
include('server.php');

// ตรวจ token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['error'] = "ลิงก์รีเซ็ตไม่ถูกต้อง";
    header("Location: forgot_password.php");
    exit();
}

$token = mysqli_real_escape_string($conn, $_GET['token']);

// ตรวจ token ว่ามีในฐานข้อมูลและยังไม่หมดอายุ
$query = "SELECT id, username, token_expiry FROM users WHERE reset_token = '$token' LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "❌ ลิงก์รีเซ็ตไม่ถูกต้องหรือหมดอายุแล้ว";
    header("Location: forgot_password.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// ตรวจสอบวันหมดอายุ
if (strtotime($user['token_expiry']) < time()) {
    $_SESSION['error'] = "⏰ ลิงก์รีเซ็ตหมดอายุแล้ว กรุณาขอใหม่อีกครั้ง";
    header("Location: forgot_password.php");
    exit();
}

// ถ้ามีการ submit ฟอร์มรีเซ็ต
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (strlen($password) < 8) {
        $_SESSION['error'] = "รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "รหัสผ่านไม่ตรงกัน";
    } else {
      
        $newHash = password_hash($password, PASSWORD_BCRYPT);

        $update = "UPDATE users 
                   SET password='" . mysqli_real_escape_string($conn, $newHash) . "', 
                       reset_token=NULL, 
                       token_expiry=NULL 
                   WHERE id=" . intval($user['id']);

        if (mysqli_query($conn, $update)) {
            $_SESSION['success'] = "✅ รีเซ็ตรหัสผ่านสำเร็จแล้ว คุณสามารถเข้าสู่ระบบได้เลย";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รีเซ็ตรหัสผ่าน</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
         background: linear-gradient(135deg, #939393ff 0%, #d3d3d3ff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 20s infinite ease-in-out;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            animation: float 15s infinite ease-in-out reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }

        .container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 480px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
            position: relative;
            z-index: 1;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon {
            text-align: center;
            margin-bottom: 25px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #284df3ff 0%, #095fffff 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5em;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        h2 {
            color: #2d3748;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2em;
            font-weight: 700;
        }

        .subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 35px;
            font-size: 0.95em;
            font-weight: 300;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.95em;
            animation: slideIn 0.4s ease-out;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .input-wrapper {
            position: relative;
        }

        input[type="password"] {
            width: 100%;
            padding: 16px 50px 16px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 1em;
            font-family: 'Kanit', sans-serif;
            transition: all 0.3s ease;
            background-color: #f8fafc;
            font-weight: 400;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.3em;
            color: #94a3b8;
            transition: color 0.3s ease;
            padding: 0;
            width: auto;
        }

        .toggle-password:hover {
            color: #667eea;
            box-shadow: none;
            transform: translateY(-50%) scale(1.1);
        }

        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #0024c5ff 0%, #0077cbff 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Kanit', sans-serif;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(102, 126, 234, 0.5);
        }

        button[type="submit"]:active {
            transform: translateY(-1px);
        }

        .password-requirements {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 18px;
            border-radius: 12px;
            margin-top: 15px;
            font-size: 0.88em;
            color: #64748b;
            border-left: 3px solid #0024c5ff;
        }

        .password-requirements strong {
            color: #475569;
            display: block;
            margin-bottom: 10px;
        }

        .password-requirements ul {
            margin: 0 0 0 20px;
            padding: 0;
        }

        .password-requirements li {
            margin: 6px 0;
            color: #64748b;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #0029deff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-link a:hover {
            color: #19b2ffff;
            gap: 10px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 40px 25px;
            }

            h2 {
                font-size: 1.7em;
            }

            .icon-circle {
                width: 70px;
                height: 70px;
                font-size: 2.2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <div class="icon-circle">🔐</div>
        </div>
        
        <h2>รีเซ็ตรหัสผ่าน</h2>
        <p class="subtitle">กรุณากรอกรหัสผ่านใหม่ของคุณ</p>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <span>✓</span>
                <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>รหัสผ่านใหม่</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" required minlength="8" placeholder="กรอกรหัสผ่านใหม่">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')"></button>
                </div>
            </div>

            <div class="form-group">
                <label>ยืนยันรหัสผ่านใหม่</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="กรอกรหัสผ่านอีกครั้ง">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')"></button>
                </div>
            </div>

            <div class="password-requirements">
                <strong>คำแนะนำในการตั้งรหัสผ่านใหม่:</strong>
                <ul>
                    <li>มีความยาวอย่างน้อย 8 ตัวอักษร</li>
                    <li>ควรประกอบด้วยตัวอักษรและตัวเลข</li>
                    <li>แนะนำให้ใช้อักขระพิเศษ เช่น !@#$%</li>
                </ul>
            </div>

            <button type="submit">รีเซ็ตรหัสผ่าน</button>
        </form>

        <div class="back-link">
            <a href="login.php">← กลับไปหน้าเข้าสู่ระบบ</a>
        </div>
    </div>

</body>
</html>