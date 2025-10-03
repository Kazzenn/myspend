
<?php
session_start();
include('server.php');

$errors = array();

if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // ตรวจสอบว่ามีการส่ง hCaptcha response 
    if (empty($_POST['h-captcha-response'])) {
        array_push($errors, "กรุณายืนยัน hCaptcha");
        $_SESSION['error'] = "กรุณายืนยัน hCaptcha";
        header('Location: login.php');
        exit();
    }

    // ตรวจสอบ hCaptcha กับ Server ของ hCaptcha
    $hcaptcha_secret = "#"; // ใส่ secret key hCaptcha ที่นี่
    $hcaptcha_response = $_POST['h-captcha-response'];
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // ส่งข้อมูลไป verify กับ hCaptcha
    $verify_url = "https://hcaptcha.com/siteverify";
    $verify_data = array(
        'secret' => $hcaptcha_secret,
        'response' => $hcaptcha_response,
        'remoteip' => $user_ip
    );

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($verify_data)
        )
    );

    $context = stream_context_create($options);
    $verify_response = file_get_contents($verify_url, false, $context);
    $response_data = json_decode($verify_response);

    // ตรวจสอบผลลัพธ์จาก hCaptcha
    if (!$response_data->success) {
        // Debug: แสดงรายละเอียด error จาก hCaptcha
        $error_details = "";
        if (isset($response_data->{'error-codes'})) {
            $error_details = " (Error codes: " . implode(', ', $response_data->{'error-codes'}) . ")";
        }
        
        array_push($errors, "การยืนยัน hCaptcha ไม่สำเร็จ กรุณาลองใหม่" . $error_details);
        $_SESSION['error'] = "การยืนยัน hCaptcha ไม่สำเร็จ กรุณาลองใหม่" . $error_details;
        
        // Debug: บันทึกข้อมูล
        error_log("hCaptcha Verification Failed: " . $verify_response);
        
        header('Location: login.php');
        exit();
    }

    // ตรวจสอบฟิลด์
    if (empty($username)) {
        array_push($errors, "กรุณากรอกชื่อผู้ใช้");
    }
    if (empty($password)) {
        array_push($errors, "กรุณากรอกรหัสผ่าน");
    }

    
    // หากไม่มี error ให้ดำเนินการตรวจสอบข้อมูลผู้ใช้
if (count($errors) == 0) {
    $query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $results = mysqli_query($conn, $query);

    if ($results && mysqli_num_rows($results) == 1) {
        $user = mysqli_fetch_assoc($results);
        $stored_hash = $user['password'];

        // 🔹 ตรวจสอบ bcrypt
        if (password_verify($password, $stored_hash)) {
            $_SESSION['username'] = $username;
            $_SESSION['success'] = "คุณได้เข้าสู่ระบบเรียบร้อยแล้ว";
            header('Location: index.php');
            exit();
        }

        // 🔹 ตรวจสอบ MD5 (hash เก่า)
        if (preg_match('/^[a-f0-9]{32}$/', $stored_hash)) {
            if (md5($password) === $stored_hash) {
                // login สำเร็จ และอัปเกรดเป็น bcrypt
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $update = "UPDATE users 
                           SET password='" . mysqli_real_escape_string($conn, $newHash) . "' 
                           WHERE id=" . intval($user['id']);
                mysqli_query($conn, $update);

                $_SESSION['username'] = $username;
                $_SESSION['success'] = "คุณได้เข้าสู่ระบบเรียบร้อยแล้ว (อัปเกรด password แล้ว)";
                header('Location: index.php');
                exit();
            }
        }

        // รหัสผ่านไม่ถูก
        array_push($errors, "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
        $_SESSION['error'] = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        header('Location: login.php');
        exit();
    } else {
        array_push($errors, "ไม่พบบัญชีผู้ใช้");
        $_SESSION['error'] = "ไม่พบบัญชีผู้ใช้";
        header('Location: login.php');
        exit();
    }
}   else {
        $_SESSION['error'] = implode(" ", $errors);
        header('Location: login.php');
        exit();
    }
}
?>
