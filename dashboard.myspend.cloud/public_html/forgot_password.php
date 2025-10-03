<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="0h7321kLraMejXYiKWOK9Urn8QwDXqIt0vzg8uOfQ-Y" />
    <title>ลืมรหัสผ่าน - MySpend</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            color: #1e40af;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2em;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
            font-size: 0.95em;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: #334155;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.95em;
        }

        input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1em;
            font-family: 'Kanit', sans-serif;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        input[type="email"]:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Kanit', sans-serif;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #1e40af;
        }

        .icon {
            text-align: center;
            font-size: 3em;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 1.6em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ลืมรหัสผ่าน?</h2>
        <p class="subtitle">กรอกอีเมลของคุณเพื่อรับลิงก์รีเซ็ตรหัสผ่าน</p>

        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="send_reset_email.php" method="POST">
            <div class="form-group">
                <label for="email">อีเมล</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" required>
            </div>

            <button type="submit">ส่งลิงก์รีเซ็ตรหัสผ่าน</button>
        </form>

        <div class="back-link">
            <a href="login.php">← กลับไปหน้าเข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>