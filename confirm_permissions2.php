<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $employeeID = isset($_POST['employeeID']) ? $_POST['employeeID'] : '';
    $tasks = isset($_POST['tasks']) ? $_POST['tasks'] : [];

    // ตรวจสอบว่ามีการเลือก task หรือไม่
    if (!empty($tasks)) {
        // เตรียมคำสั่ง SQL สำหรับอัพเดตสถานะในฐานข้อมูล
        $taskPlaceholders = implode(',', array_fill(0, count($tasks), '?'));

        // สร้างคำสั่ง SQL
        $sql = "UPDATE tasks SET status='อนุมัติแล้ว' WHERE TaskName IN ($taskPlaceholders) AND EmployeeID=?";
        $stmt = $conn->prepare($sql);
        
        // ทำการ merge tasks และ employeeID เข้าด้วยกัน เพื่อเตรียมค่าให้กับ bind_param
        $types = str_repeat('s', count($tasks)) . 's'; // สร้าง string ประเภทพารามิเตอร์ เช่น "sss"
        $params = array_merge($tasks, [$employeeID]); // รวม tasks กับ employeeID เข้าด้วยกัน

        // ใช้ฟังก์ชัน call_user_func_array เพื่อ bind ค่า
        $stmt->bind_param($types, ...$params);

        // ประมวลผลการ execute
        $stmt->execute();
    }

    // ตรวจสอบว่าเกิดข้อผิดพลาดในการอัปเดตสิทธิ์หรือไม่
  // บันทึกการอนุมัติในฐานข้อมูล
$employeeID = $_POST['employeeID'];
$taskNames = $_POST['tasks']; // ถ้ามีหลายชื่อสามารถวนลูปได้

foreach ($taskNames as $taskName) {
    $sqlInsert = "INSERT INTO approvals (EmployeeID, TaskName, status) VALUES (?, ?, 'อนุมัติแล้ว')";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ss", $employeeID, $taskName);
    $stmtInsert->execute();
}

// หลังจากบันทึกเสร็จให้เปลี่ยนเส้นทางไปยังหน้าอนุมัติแล้ว
header('Location: อนุมัติแล้ว.php');
exit();

}
?>
