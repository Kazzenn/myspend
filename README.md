# 💰 MySpend - ระบบบันทึกรายรับรายจ่าย

<div align="center">

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

</div>



## 🛠 เทคโนโลยีที่ใช้

### Backend
- ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white) PHP 7.4+
- ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white) MySQL 5.7+
- ![PHPMailer](https://img.shields.io/badge/PHPMailer-6.x-red) PHPMailer 6.x

### Frontend
- ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white) HTML5
- ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white) CSS3 (Custom Styling)
- ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black) Vanilla JavaScript


## 📦 ความต้องการของระบบ
### Server Requirements
| รายการ | ความต้องการ | หมายเหตุ |
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
- **Hosting:** DirectAdmin + phpMyAdmin support
- **Domain:** ชื่อโดเมน (เช่น myspend.cloud)
- **hCaptcha:** Site Key และ Secret Key
- **Email Account:** สำหรับ SMTP

---

## Installation

### ขั้นตอนที่ 1: เตรียม Hosting และ Domain

สามารถดูคู่มือการซื้อและตั้งค่าได้ที่:

**Video Tutorial:** [วิธีการซื้อโดเมนและ Hosting](https://youtu.be/PrhfkUXv0ys?si=rJNeIm_60POJtNpK)  
**Credit:** Mujidev

### ขั้นตอนที่ 2: ดาวน์โหลดโปรเจกต์

```bash
# Clone repository
git clone https://github.com/yourusername/myspend.git

# หรือดาวน์โหลด ZIP และแตกไฟล์
```

### ขั้นตอนที่ 3: อัพโหลดไฟล์

1. เข้าสู่ **File Manager** ใน DirectAdmin
2. อัพโหลดไฟล์ทั้งหมดไปที่ `public_html/`
3. ตรวจสอบ Permission ของไฟล์ (แนะนำ 644)

---

## ⚙️ การตั้งค่า

### 1. ตั้งค่าฐานข้อมูล

#### 1.1 สร้าง Database

1. เข้า **phpMyAdmin** ผ่าน DirectAdmin
2. เลือก **Databases** > **Create Database**
3. ตั้งชื่อฐานข้อมูล (เช่น `myspen74_db`)

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

#### 3.2 แก้ไขไฟล์ send_reset_email.php

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

#### 3.3 แก้ไข URL ในอีเมล

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

#### 4.4 แก้ไขไฟล์ login.php (ถ้ามี)

```html
<div class="h-captcha" data-sitekey="YOUR_SITE_KEY"></div>
```

#### 4.5 แก้ไขไฟล์ login_db.php

```php
$secret = "YOUR_SECRET_KEY";  // แก้ไขที่นี่
```

### 5. ปิด Debug Mode (สำคัญ!)

#### 5.1 ในไฟล์ send_reset_email.php

```php
$debug_mode = false;  // เปลี่ยนจาก true เป็น false
```

#### 5.2 ในไฟล์ reset_password.php

```php
$debug_mode = false;  // เปลี่ยนจาก true เป็น false
```

---

## 🗄️ โครงสร้างฐานข้อมูล

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

## 📖 คู่มือการใช้งาน

### การลงทะเบียน

1. เข้าหน้า `register.php`
2. กรอกข้อมูล:
   - Username (ภาษาอังกฤษเท่านั้น)
   - Email (ต้องเป็นอีเมลที่ใช้งานได้)
   - Password (อย่างน้อย 6 ตัวอักษร)
3. กด Captcha เพื่อยืนยัน
4. คลิก "สมัครสมาชิก"

### การเข้าสู่ระบบ

1. เข้าหน้า `login.php`
2. กรอก Username และ Password
3. คลิก "เข้าสู่ระบบ"

### การลืมรหัสผ่าน

1. คลิก "ลืมรหัสผ่าน?" ที่หน้า Login
2. กรอกอีเมลที่ลงทะเบียนไว้
3. ตรวจสอบอีเมลและคลิกลิงก์รีเซ็ต
4. กรอกรหัสผ่านใหม่

### การบันทึกรายรับรายจ่าย

1. เข้าสู่ระบบสำเร็จจะเข้าหน้า Dashboard
2. เลือกประเภท: รายรับ หรือ รายจ่าย
3. กรอกจำนวนเงิน
4. เลือกหมวดหมู่
5. เลือกวันที่
6. (ไม่บังคับ) ใส่รายละเอียดเพิ่มเติม
7. คลิก "บันทึก"

### การดูสรุปยอด

- **การ์ดสรุป**: แสดงรายรับทั้งหมด, รายจ่ายทั้งหมด, ยอดคงเหลือ
- **ย้อนหลัง 7 วัน**: แสดงรายการล่าสุด 7 วัน
- **ดูทั้งหมด**: แสดงรายการทั้งหมด

---

## 🔧 การแก้ปัญหา

### ปัญหา: เข้าสู่ระบบไม่ได้หลังรีเซ็ตรหัสผ่าน

**สาเหตุ:** ระบบ login ยังใช้ MD5 แต่รหัสผ่านใหม่เป็น bcrypt

**วิธีแก้:** ใช้ไฟล์ `login.php` เวอร์ชันที่รองรับทั้ง MD5 และ bcrypt

### ปัญหา: ส่งอีเมลรีเซ็ตรหัสผ่านไม่ได้

**ตรวจสอบ:**
1. SMTP credentials ถูกต้อง
2. Email account ถูกสร้างใน DirectAdmin
3. Port 465 (SMTPS) เปิดใช้งาน
4. ตรวจสอบ error log

### ปัญหา: Token หมดอายุตลอด

**สาเหตุ:** Timezone ไม่ตรงกัน

**วิธีแก้:** เพิ่มในไฟล์ `server.php`
```php
date_default_timezone_set('Asia/Bangkok');
```

### ปัญหา: hCaptcha ไม่ทำงาน

**ตรวจสอบ:**
1. Site Key และ Secret Key ถูกต้อง
2. Domain ตรงกับที่ลงทะเบียนใน hCaptcha
3. มีการโหลด JavaScript ของ hCaptcha

### ปัญหา: ตัวอักษรภาษาไทยแสดงผิด

**วิธีแก้:**
```php
// เพิ่มใน server.php
$conn->set_charset("utf8");
```

---

## 📁 โครงสร้างไฟล์
```
myspend/
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
├── dash.css                   # Stylesheet หลัก
├── PHPMailer/                 # PHPMailer Library
│   ├── Exception.php
│   ├── PHPMailer.php
│   └── SMTP.php
└── README.md                  # ไฟล์นี้
```




## 👥 Credits

- **Original Tutorial**: [Mujidev](https://youtu.be/PrhfkUXv0ys?si=rJNeIm_60POJtNpK)
- **PHPMailer**: [PHPMailer/PHPMailer](https://github.com/PHPMailer/PHPMailer)
- **hCaptcha**: [hCaptcha.com](https://www.hcaptcha.com/)
- **Font**: Google Fonts - Kanit

---

## 📧 ติดต่อ

