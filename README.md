# MySpend - ระบบบันทึกรายรับรายจ่าย
Demo: [Myspend.cloud](https://myspend.cloud/)  

<div align="center">

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

</div>





## 📦 ความต้องการของระบบ
### Server Requirements
| List | Requirements | versions |
|--------|-------------|----------|
| **PHP** | >= 7.4 | แนะนำ 8.0+ |
| **MySQL** | >= 5.7 | หรือ MariaDB 10.2+ |
| **Web Server** | Apache/Nginx | รองรับ .htaccess |
| **SMTP Server** | Required | สำหรับส่งอีเมล |
| **SSL Certificate** | แนะนำ | สำหรับความปลอดภัย |

### PHP Extensions
```
- mysqli
- openssl
- mbstring
- json
```

### External Services
- **Hosting:** ที่รองรับ DirectAdmin + phpMyAdmin 
- **Domain:** สำกรับตั้งค่ารองรับอีเมลและเว็บไซต์
- **hCaptcha:** Site Key และ Secret Key
- **Email Account:** สำหรับ SMTP

---

## Installation 
(หากทดสอบบน localhost หรือ xampp ข้ามส่วนนี้ไปได้เลย)

### 1: Setup Hosting และ Domain

**การติดตั้ง:** [วิธีการซื้อโดเมนและ Hosting](https://youtu.be/PrhfkUXv0ys?si=rJNeIm_60POJtNpK)  
**Credit:** Mujidev

### 2: Fork หรือ Download Project

```bash
# Clone repository
git clone https://github.com/yourusername/myspend.git

# หรือดาวน์โหลด ZIP และแตกไฟล์
```

### 3: อัพโหลดไฟล์

1. เข้า **File Manager** ใน **DirectAdmin**
2. อัพโหลดไฟล์ทั้งหมดไปที่ `public_html/`
3. ตรวจสอบ Permission ไฟล์ 

---

## ⚙️Setup Database

### 1. ตั้งค่าฐานข้อมูล

#### 1.1 Create Database

1. เข้า **Databases** บน DirectAdmin (บน Account Manager)
1.1 หากรับบน localhost ให้ไปที่ localhost/phpmyadmin
2. เลือก **Create Database**
3. ตั้งชื่อฐานข้อมูลแล้วบันทึก
4. เลื่อนลงไปที่หัวข้อ **Extra Features**  แล้วกด **phpMyAdmmin**
5. กดไปที่ชื่อ **database** ที่สร้างแล้วกด **SQL** เพื่อนสร้างตาราง


#### 1.2 สร้างตาราง users

```sql
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) COLLATE utf8mb3_general_ci NOT NULL,
  `email` VARCHAR(100) COLLATE utf8mb3_general_ci NOT NULL,
  `password` VARCHAR(255) COLLATE utf8mb3_general_ci NOT NULL,
  `reset_token` VARCHAR(255) COLLATE utf8mb3_general_ci DEFAULT NULL,
  `token_expiry` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb3 
COLLATE=utf8mb3_general_ci;
```

#### 1.3 สร้างตาราง transactions

```sql
CREATE TABLE `transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(255) COLLATE utf8mb3_general_ci NOT NULL,
  `type` ENUM('income', 'expense') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `category` VARCHAR(100) COLLATE utf8mb3_general_ci NOT NULL,
  `description` TEXT COLLATE utf8mb3_general_ci,
  `date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_date` (`date`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb3 
COLLATE=utf8mb3_general_ci;
```

### 2. แก้ไขไฟล์ server.php

```php
<?php
$servername = "localhost";
$username = "your_db_username";      // แก้ไขที่นี่
$password = "your_db_password";      // แก้ไขที่นี่
$dbname = "your_db_name";            // แก้ไขที่นี่

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

### 3. ตั้งค่า Email Account

#### 3.1 สร้าง Email Account

1. เข้า DirectAdmin > **E-mail Accounts**
2. คลิก **Create Account**
3. ตั้งค่า:
   - Email
   - Password

#### 3.2 หาก PHPMailer มีปัญหา
ทดลองโหลดไฟล์ใหม่ [ที่นี่](https://github.com/PHPMailer/PHPMailer/archive/master.zip) แล้วนำไปวางในไฟล์เดียวกับโฟลเดอร์เดียวกับ **send_reset_email.php** (ตรวจสอบว่า redirect ถูกต้องหรือไม่ที่ส่วนนี้)
```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
```

#### 3.3 แก้ไขไฟล์ send_reset_email.php

```php
// กำหนดการเชื่อมต่อ SMTP
$mail->isSMTP();
$mail->Host       = 'mail.yourdomain.com';       // แก้ไขที่นี่
$mail->SMTPAuth   = true;
$mail->Username   = 'support@yourdomain.com';    // แก้ไขที่นี่
$mail->Password   = 'your_email_password';       // แก้ไขที่นี่
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port       = 465;
```

#### 3.4 แก้ไข URL ในอีเมล

```php
$reset_link = "https://yourdomain.com/reset_password.php?token=" . urlencode($reset_token);
```

### 4. ตั้งค่า hCaptcha

#### 4.1 สมัครและรับ Keys

1. ไปที่ [hCaptcha.com](https://www.hcaptcha.com/)
2. สมัครสมาชิกและสร้าง Site
3. คัดลอก **Site Key** และ **Secret Key**

#### 4.2 แก้ไขไฟล์ register.php

```html
<!-- ค้นหาบรรทัดนี้และแก้ไข -->
<div class="h-captcha" data-sitekey="YOUR_SITE_KEY"></div>
```

#### 4.3 แก้ไขไฟล์ reg_db.php

```php
// ตรวจสอบ hCaptcha
$secret = "YOUR_SECRET_KEY";  // แก้ไขที่นี่
```

#### 4.4 แก้ไขไฟล์ login.php 

```html
<div class="h-captcha" data-sitekey="YOUR_SITE_KEY"></div> // แก้ไขที่นี่
```

#### 4.5 แก้ไขไฟล์ login_db.php

```php
$secret = "YOUR_SECRET_KEY";  // แก้ไขที่นี่
```

### 5. ปิด Debug Mode (หลังทดสอบแล้ว ถ้าใช้งานได้ให้เปลี่ยนเป็น false)

#### 5.1 ในไฟล์ send_reset_email.php

```php
$debug_mode = false;  // เปลี่ยนจาก true เป็น false 
```

#### 5.2 ในไฟล์ reset_password.php

```php
$debug_mode = false;  // เปลี่ยนจาก true เป็น false
```

---

## ตัวอย่าง structure จะประมาณนี้

### ตาราง users

| Field | Type | Description |
|-------|------|-------------|
| id | INT(11) | Primary Key, Auto Increment |
| username | VARCHAR(100) | ชื่อผู้ใช้ (Unique) |
| email | VARCHAR(100) | อีเมล (Unique) |
| password | VARCHAR(255) | รหัสผ่านที่เข้ารหัส |
| reset_token | VARCHAR(255) | Token สำหรับรีเซ็ตรหัสผ่าน |
| token_expiry | DATETIME | วันหมดอายุของ Token |

### ตาราง transactions

| Field | Type | Description |
|-------|------|-------------|
| id | INT(11) | Primary Key, Auto Increment |
| user_id | VARCHAR(255) | รหัสผู้ใช้ |
| type | ENUM | 'income' หรือ 'expense' |
| amount | DECIMAL(10,2) | จำนวนเงิน |
| category | VARCHAR(100) | หมวดหมู่ |
| description | TEXT | รายละเอียด |
| date | DATE | วันที่ทำรายการ |
| created_at | TIMESTAMP | วันที่บันทึก |

---

## 📁 โครงสร้างไฟล์
```
project/
├── index.php                   # หน้าแรก
├── login.php                   # หน้าเข้าสู่ระบบ
├── login_db.php               # ประมวลผล login
├── register.php               # หน้าลงทะเบียน
├── reg_db.php                 # ประมวลผลลงทะเบียน
├── dashboard.php              # หน้าหลักระบบ
├── server.php                 # การเชื่อมต่อฐานข้อมูล
├── forgot_password.php        # หน้าลืมรหัสผ่าน
├── send_reset_email.php       # ส่งอีเมลรีเซ็ต
├── reset_password.php         # หน้ารีเซ็ตรหัสผ่าน
├── style.css                   # Stylesheet หลัก
├── PHPMailer/                 # PHPMailer Library
│   ├── Exception.php
│   ├── PHPMailer.php
│   └── SMTP.php
└── README.md                  # ไฟล์นี้
```


## 👥 Credits

- **Hosting Setup Tutorial**: [Mujidev](https://youtu.be/PrhfkUXv0ys?si=rJNeIm_60POJtNpK)
- **PHPMailer**: [PHPMailer/PHPMailer](https://github.com/PHPMailer/PHPMailer)
- **hCaptcha**: [hCaptcha.com](https://www.hcaptcha.com/)

---

## นี่เป็นแค่ตัวอย่างโปรเจกต์มีโค้ดหลายส่วนที่ไม่ถูกต้อง ขออภัยในความไม่สะดวก


