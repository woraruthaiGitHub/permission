<?php
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// ตรวจสอบว่ามีการส่งรหัสพนักงานเข้ามาหรือไม่
if (isset($_GET['id'])) {
    $employeeId = $_GET['id'];
    
    // ดึงข้อมูลสิทธิ์สำหรับพนักงานนี้จากฐานข้อมูล
    // ตัวอย่าง: SELECT * FROM permissions WHERE employee_id = '$employeeId'
    // $tasks = ... // ข้อมูลสิทธิ์ที่ดึงมา
} else {
    echo "ไม่มีรหัสพนักงาน";
    exit();
}

// จัดการเมื่อกดปุ่มยืนยันการอนุมัติ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tasks'])) {
        $selectedTasks = $_POST['tasks'];
        $username = $_SESSION['username'];
        
        // ดำเนินการอนุมัติที่นี่
        foreach ($selectedTasks as $task) {
            // ตัวอย่าง: INSERT INTO approvals (employee_id, username, task) VALUES ('$employeeId', '$username', '$task')
        }

        // แสดงข้อความยืนยัน
        echo "อนุมัติสิทธิของ " . htmlspecialchars($employeeId) . " สำเร็จแล้ว<br>";
        echo "Tasks ที่อนุมัติ:<br>";
        foreach ($selectedTasks as $task) {
            echo htmlspecialchars($task) . "<br>";
        }
        exit();
    } else {
        echo "กรุณาเลือกอย่างน้อยหนึ่ง Task";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>อนุมัติสิทธิ์สำหรับ: <?php echo htmlspecialchars($employeeId); ?></title>
    <!-- รวม CSS และ JS ที่ต้องการ -->
</head>
<body>
    <h1>อนุมัติสิทธิ์สำหรับ: <?php echo htmlspecialchars($employeeId); ?></h1>
    <form method="post" action="">
        <table>
            <thead>
                <tr>
                    <th>เลือก</th>
                    <th>สิทธิ์</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="tasks[]" value="<?php echo htmlspecialchars($task['TaskName']); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($task['TaskName']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">ยืนยัน</button>
    </form>
    <a href="index.php">กลับไปหน้าหลัก</a>
</body>
</html>
