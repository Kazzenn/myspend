<?php
session_start();
include('server.php');

// Optional: For better error visibility during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = array();

if (isset($_POST['reg_user'])) {
    // Sanitize user inputs
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_1 = mysqli_real_escape_string($conn, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($conn, $_POST['password_2']);

    $hcaptcha_verified = false;
    if (!empty($_POST['h-captcha-response'])) {
       
        $hcaptcha_secret = "#"; //ใส่ Secret Key ของ hCaptcha ที่นี่
        $hcaptcha_response = $_POST['h-captcha-response'];
        $user_ip = $_SERVER['REMOTE_ADDR'];

        $verify_url = "https://hcaptcha.com/siteverify";
        $verify_data = http_build_query([
            'secret' => $hcaptcha_secret,
            'response' => $hcaptcha_response,
            'remoteip' => $user_ip
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verify_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $verify_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $verify_response = curl_exec($ch);
        curl_close($ch);

        $response_data = json_decode($verify_response);

        if ($response_data && $response_data->success) {
            $hcaptcha_verified = true;
        } else {
          
            $error_message = "การยืนยัน hCaptcha ไม่ถูกต้อง กรุณาลองใหม่";
            if (isset($response_data->{'error-codes'}) && is_array($response_data->{'error-codes'})) {
                if (in_array('timeout-or-duplicate', $response_data->{'error-codes'})) {
                    $error_message = "hCaptcha หมดอายุ กรุณายืนยันใหม่อีกครั้ง";
                }
            }
            array_push($errors, $error_message);
        }
    } else {
        array_push($errors, "กรุณายืนยันว่าคุณไม่ใช่โปรแกรมอัตโนมัติ (hCaptcha)");
    }
   

    if ($hcaptcha_verified) {

        if (empty($username)) { 
            array_push($errors, "กรุณากรอกชื่อผู้ใช้"); 
        }
        if (empty($email)) { 
            array_push($errors, "กรุณากรอกอีเมล"); 
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, "รูปแบบอีเมลไม่ถูกต้อง");
        }
        if (empty($password_1)) { 
            array_push($errors, "กรุณากรอกรหัสผ่าน"); 
        }
        if (strlen($password_1) < 6) {
            array_push($errors, "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร");
        }
        if ($password_1 != $password_2) {
            array_push($errors, "รหัสผ่านทั้งสองไม่ตรงกัน");
        }

        if (count($errors) == 0) {
            $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email' LIMIT 1";
            $query = mysqli_query($conn, $user_check_query);
            $result = mysqli_fetch_assoc($query);

            if ($result) {
                if ($result['username'] === $username) {
                    array_push($errors, "ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว");
                }
                if ($result['email'] === $email) {
                    array_push($errors, "อีเมลนี้มีอยู่ในระบบแล้ว");
                }
            }
        }
    }

    if (count($errors) == 0 && $hcaptcha_verified) {
        
        $password = md5($password_1); 
        $sql = "INSERT INTO users (username, email, password) VALUES('$username', '$email', '$password')";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['username'] = $username;
            $_SESSION['success'] = "คุณได้ลงทะเบียนเรียบร้อยแล้ว";
            header('Location: ./index.php');
            exit();
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการสมัครสมาชิก กรุณาลองใหม่";
            header('Location: ./register.php');
            exit();
        }
    } else {
       
        $_SESSION['error'] = implode("<br>", $errors);
        header('Location: ./register.php');
        exit();
    }
}
?>