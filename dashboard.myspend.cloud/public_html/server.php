<?php
 $servername = "localhost";
 $username = "myspen74_db";    
 $password = "74Pv3xRGcu8XEvGjS3eQ";
 $dbname = "myspen74_db";

 // Create connection
 $conn = new mysqli($servername, $username, $password, $dbname); // Create connection **สำคัญตั้งค่าส่วนนี้ให้ถูกต้อง** ใช้ตัวแปร $conn ในการเชื่อมต่อฐานข้อมูล
 $conn->set_charset("utf8");  

 // Check connection
 if (!$conn) {
     die("Connection failed: " . mysqli_connect_error());
 } 
?>