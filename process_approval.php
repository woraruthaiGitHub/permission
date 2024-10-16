<?php
session_start();
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// รับข้อมูล task ที่ถูกเลือก
if (isset($_POST['tasks'])) {
    $selectedTasks = $_POST['tasks'];

    // ทำการบันทึกการอนุมัติที่นี่ (เช่น บันทึกลงฐานข้อมูล)
    // ตัวอย่าง: บันทึกลงฐานข้อมูลหรือประมวลผลตามต้องการ

    // ตัวอย่างการตั้งค่าค่าของเปอร์เซ็นต์ (ปรับตามการประมวลผลจริง)
    $approvedCount = count($selectedTasks);
    $notApprovedCount = 0; // สมมติว่าไม่มีที่ไม่อนุมัติ
    $pendingCount = 0; // สมมติว่าไม่มีที่รออนุมัติ

    // คำนวณเปอร์เซ็นต์
    $totalTasks = $approvedCount + $notApprovedCount + $pendingCount;
    $approvedPercent = $totalTasks > 0 ? ($approvedCount / $totalTasks) * 100 : 0;
    $notApprovedPercent = $totalTasks > 0 ? ($notApprovedCount / $totalTasks) * 100 : 0;
    $pendingPercent = $totalTasks > 0 ? ($pendingCount / $totalTasks) * 100 : 0;

    // ส่งข้อมูลกลับไปยังหน้า index.php
    header("Location: index.php?approvedPercent=$approvedPercent&notApprovedPercent=$notApprovedPercent&pendingPercent=$pendingPercent");
    exit();
} else {
    echo "กรุณาเลือกอย่างน้อยหนึ่ง Task";
}
?>
