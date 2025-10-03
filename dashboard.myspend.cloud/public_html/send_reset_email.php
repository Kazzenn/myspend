<?php
session_start();
include('server.php');

// เปิด debug mode (ตั้งเป็น false เมื่อใช้งานจริง)
$debug_mode = true;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) {
    $_SESSION['error'] = "กรุณากรอกอีเมล";
    header("Location: forgot_password.php");
    exit();
}

$email = mysqli_real_escape_string($conn, trim($_POST['email']));

if ($debug_mode) {
    echo "<h3>🔍 Debug Mode</h3>";
    echo "<p>อีเมลที่กรอก: " . htmlspecialchars($email) . "</p>";
}

$query = "SELECT id, username, email FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $query);

if ($debug_mode) {
    echo "<p>SQL Query: <code>" . htmlspecialchars($query) . "</code></p>";
    echo "<p>จำนวนผลลัพธ์: " . mysqli_num_rows($result) . "</p>";
}

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "กรุณาตรวจสอบความถูกต้องของอีเมลอีกครั้ง";
    if (!$debug_mode) {
        header("Location: forgot_password.php");
        exit();
    } else {
        echo "<p style='color: red;'>❌ กรุณาตรวจสอบความถูกต้องของอีเมลอีกครั้ง</p>";
        
        // แสดง users ทั้งหมดในระบบ (เฉพาะ debug mode)
        $all_users = mysqli_query($conn, "SELECT email FROM users LIMIT 5");
        echo "<p>อีเมลที่มีในระบบ (5 รายการแรก):</p><ul>";
        while ($u = mysqli_fetch_assoc($all_users)) {
            echo "<li>" . htmlspecialchars($u['email']) . "</li>";
        }
        echo "</ul>";
        exit();
    }
}

$user = mysqli_fetch_assoc($result);

if ($debug_mode) {
    echo "<p style='color: green;'>✅ พบผู้ใช้: " . htmlspecialchars($user['username']) . "</p>";
}

// สร้าง Token แบบสุ่ม (ความยาว 64 ตัวอักษร)
$reset_token = bin2hex(random_bytes(32));
$token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

if ($debug_mode) {
    echo "<p>Token: <code>" . $reset_token . "</code></p>";
    echo "<p>หมดอายุ: " . $token_expiry . "</p>";
}

// บันทึก Token ลงในฐานข้อมูล
$update_query = "UPDATE users SET 
                 reset_token = '$reset_token', 
                 token_expiry = '$token_expiry' 
                 WHERE email = '$email'";

if ($debug_mode) {
    echo "<p>SQL Update: <code>" . htmlspecialchars($update_query) . "</code></p>";
}

if (!mysqli_query($conn, $update_query)) {
    $error_msg = "เกิดข้อผิดพลาดในการสร้างลิงก์รีเซ็ต: " . mysqli_error($conn);
    $_SESSION['error'] = $error_msg;
    
    if ($debug_mode) {
        echo "<p style='color: red;'>❌ " . $error_msg . "</p>";
        
        $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'reset_token'");
        if (mysqli_num_rows($check_columns) === 0) {
            echo "<p style='color: red;'><strong>⚠️ ตาราง users ไม่มีฟิลด์ reset_token!</strong></p>";
            echo "<p>กรุณารัน SQL นี้:</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px;'>";
            echo "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL;\n";
            echo "ALTER TABLE users ADD COLUMN token_expiry DATETIME NULL DEFAULT NULL;";
            echo "</pre>";
        }
        exit();
    }
    
    header("Location: ./forgot_password.php");
    exit();
}

if ($debug_mode) {
    echo "<p style='color: green;'>✅ บันทึก Token สำเร็จ!</p>";
    echo "<p>Affected rows: " . mysqli_affected_rows($conn) . "</p>";
}

// สร้างลิงก์รีเซ็ต
$reset_link = "https://dashboard.myspend.cloud/reset_password.php?token=" . urlencode($reset_token);

if ($debug_mode) {
    echo "<p>ลิงก์รีเซ็ต: <a href='" . $reset_link . "' target='_blank'>" . $reset_link . "</a></p>";
    echo "<hr>";
    echo "<p><strong>⚠️ Debug Mode เปิดอยู่ - จะไม่ส่งอีเมลจริง</strong></p>";
    echo "<p>ตั้งค่า <code>\$debug_mode = false;</code> เพื่อส่งอีเมลจริง</p>";
    echo "<p><a href='./forgot_password.php'>← กลับไปหน้าลืมรหัสผ่าน</a></p>";
    
    exit();
}

// ส่งอีเมล
$mail = new PHPMailer(true);

try {
    // กำหนดการเชื่อมต่อ SMTP
    $mail->isSMTP();
    $mail->Host       = 'mail.myspend.cloud'; // ใช้ SMTP ของโดเมนจริง
    $mail->SMTPAuth   = true;
    $mail->Username   = 'support@myspend.cloud'; //ใส่ email ที่สร้างบน directadmin
    $mail->Password   = '###########'; //ใส่ password ของ email ที่สร้างบน directadmin
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // ตั้งค่าผู้ส่งและผู้รับ
    $mail->setFrom('support@myspend.cloud', 'MySpend.Cloud'); // ใส่ email ที่สร้างบน directadmin และชื่อที่ใช้แสดง
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    // เนื้อหาอีเมล
    $mail->Subject = 'ลิงก์สำหรับรีเซ็ตรหัสผ่านของคุณ';
    $mail->Body    = '
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f4f4f4;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                padding-bottom: 20px;
                border-bottom: 2px solid #3b82f6;
            }
            h1 {
                color: #1e40af;
                margin: 0;
            }
            .content {
                padding: 30px 0;
            }
            .button {
                display: inline-block;
                padding: 15px 30px;
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                color: white !important;
                text-decoration: none;
                border-radius: 8px;
                font-weight: bold;
                margin: 20px 0;
            }
            .footer {
                padding-top: 20px;
                border-top: 1px solid #e0e0e0;
                text-align: center;
                color: #666;
                font-size: 0.9em;
            }
            .warning {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔐 รีเซ็ตรหัสผ่าน</h1>
            </div>
            <div class="content">
                <p>สวัสดีครับคุณ <strong>' . htmlspecialchars($user['username']) . '</strong></p>
                <p>เราได้รับคำขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณบน MySpend.Cloud</p>
                <p>กรุณาคลิกปุ่มด้านล่างเพื่อรีเซ็ตรหัสผ่านของคุณ:</p>
                <center>
                    <a href="' . htmlspecialchars($reset_link) . '" class="button">รีเซ็ตรหัสผ่านของฉัน</a>
                </center>
                <p>หรือคัดลอกลิงก์นี้ไปวางในเบราว์เซอร์:</p>
                <p style="word-break: break-all; background: #f8f9fa; padding: 10px; border-radius: 5px;">' . htmlspecialchars($reset_link) . '</p>
                <div class="warning">
                    <strong>⚠️ หมายเหตุ:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>ลิงก์นี้จะหมดอายุใน <strong>1 ชั่วโมง</strong></li>
                        <li>หากคุณไม่ได้เป็นผู้ส่งคำขอ กรุณาเพิกเฉยต่ออีเมลนี้</li>
                        <li>รหัสผ่านของคุณจะไม่เปลี่ยนแปลงจนกว่าคุณจะสร้างรหัสผ่านใหม่</li>
                    </ul>
                </div>
            </div>
            <div class="footer">
                <p>ขอบคุณที่ใช้บริการ MySpend.Cloud</p>
                <p style="font-size: 0.85em; color: #999;">อีเมลนี้ส่งโดยอัตโนมัติ กรุณาอย่าตอบกลับ</p>
            </div>
        </div>
    </body>
    </html>';
    
    $mail->AltBody = "สวัสดีครับ\n\nกรุณาคลิกลิงก์นี้เพื่อรีเซ็ตรหัสผ่าน: " . $reset_link . "\n\nลิงก์จะหมดอายุใน 1 ชั่วโมง\n\nขอบคุณครับ\nทีมงาน MySpend.Cloud";

    // ส่งอีเมล
    $mail->send();
    
    $_SESSION['success'] = "✅ ส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของคุณเรียบร้อยแล้ว กรุณาตรวจสอบกล่องจดหมายของคุณ";
    header("Location: ./forgot_password.php");
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการส่งอีเมล: " . $mail->ErrorInfo;
    header("Location: ./forgot_password.php");
    exit();
}
?>