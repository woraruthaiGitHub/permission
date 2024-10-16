<?php
$servername = "localhost"; // ชื่อเซิร์ฟเวอร์
$username = "root";     // ชื่อผู้ใช้ฐานข้อมูล
$password = "";     // รหัสผ่านฐานข้อมูล
$dbname = "dbbrn";       // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
