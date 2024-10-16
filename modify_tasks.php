<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // ถ้ายังไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปยังหน้า login
    exit();
}

// รับข้อมูลผู้ใช้จากฐานข้อมูล
$username = $_SESSION['username'];
$sql = "SELECT * FROM UserRole WHERE Username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username); // 's' คือ string
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่ามีข้อมูลหรือไม่
if ($result->num_rows === 0) {
    die("No user data found.");
}

$userData = $result->fetch_assoc();

// ตรวจสอบการส่ง EmployeeID มาจาก GET
$employeeId = null;
$firstName = $lastName = ""; // กำหนดตัวแปรสำหรับชื่อและนามสกุล

if (isset($_GET['id'])) {
    $employeeId = $_GET['id']; // เก็บค่า EmployeeID ที่ส่งมาจาก URL

    // ดึงข้อมูลพนักงานจากฐานข้อมูลตาม EmployeeID เฉพาะจาก EmployeesAll
    $sql = "SELECT EmployeeID, FirstName, LastName, Position FROM EmployeesAll WHERE EmployeeID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $employeeId); // 's' คือ string
    $stmt->execute();
    $result = $stmt->get_result();

    // ตรวจสอบว่าพบข้อมูลพนักงานหรือไม่
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        $employeeId = $employee['EmployeeID']; // เก็บ EmployeeID
        $firstName = $employee['FirstName'];
        $lastName = $employee['LastName'];
    } else {
        // หากไม่พบพนักงาน ให้แสดงข้อความหรือเปลี่ยนเส้นทาง
        echo "ไม่พบข้อมูลพนักงานที่เลือก";
        exit(); // หยุดการทำงานของสคริปต์
    }
}

// ดึงข้อมูล tasks ทั้งหมด
$tasksSql = "SELECT * FROM tasks ORDER BY TaskName"; 
$tasksResult = $conn->query($tasksSql);

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (!$tasksResult) {
    die("Error executing query: " . $conn->error);
}

// การจัดเก็บข้อมูลลงใน array
$tasks = [];
while ($row = $tasksResult->fetch_assoc()) {
    $tasks[] = $row;
}

// ดึงข้อมูล tasktransactions ทั้งหมด
$taskTransactionsSql = "SELECT * FROM tasktransactions ORDER BY Start"; 
$taskTransactionsResult = $conn->query($taskTransactionsSql);


// ตรวจสอบว่ามีข้อมูลหรือไม่
if (!$taskTransactionsResult) {
    die("Error executing query: " . $conn->error);
}

// การจัดเก็บข้อมูลลงใน array
$taskTransactions = [];
while ($row = $taskTransactionsResult->fetch_assoc()) {
    $taskTransactions[] = $row;
}

// ตรวจสอบการส่งข้อมูล POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่า EmployeeID
    $employeeId = $_POST['employeeId']; // รับ EmployeeID

    // ตรวจสอบว่ามีข้อมูลใน EmployeeID หรือไม่
    $checkSql = "SELECT * FROM tasktransactions WHERE EmployeeID = ? AND TaskName = ?";
    $checkStmt = $conn->prepare($checkSql);
    $insertedTasks = []; // เพื่อเก็บชื่อสิทธิ์ที่ถูกบันทึก

    if (!empty($_POST['selectedTasks'])) {
        foreach ($_POST['selectedTasks'] as $taskId) {
            // ดึงชื่อสิทธิ์จากตาราง tasks โดยใช้ Task_Id
            $taskSql = "SELECT TaskName FROM tasks WHERE Task_Id = ?";
            $stmt = $conn->prepare($taskSql);
            $stmt->bind_param("i", $taskId);
            $stmt->execute();
            $taskResult = $stmt->get_result();
    
            if ($taskResult->num_rows > 0) {
                $task = $taskResult->fetch_assoc();
    
                // ตรวจสอบ TaskName ว่ามีอยู่แล้วหรือไม่
                $checkTaskStmt = $conn->prepare($checkSql);
                $checkTaskStmt->bind_param("ss", $employeeId, $task['TaskName']);
                $checkTaskStmt->execute();
                $checkTaskResult = $checkTaskStmt->get_result();
    
                if ($checkTaskResult->num_rows === 0) {
                    // รับค่าจากฟอร์ม
                    $startDate = $_POST['startDate'][$taskId]; // รับวันที่เริ่มต้น
                    $endDate = $_POST['endDate'][$taskId];     // รับวันที่สิ้นสุด
                    $status = $_POST['status'][$taskId];
                    
                    // เพิ่มข้อมูลไปยังฐานข้อมูล
                    $addSql = "INSERT INTO tasktransactions (EmployeeID, TaskName, Start, End, Status) VALUES (?, ?, ?, ?, ?)";
                    $stmtAdd = $conn->prepare($addSql);
                    $stmtAdd->bind_param("sssss", $employeeId, $task['TaskName'], $startDate, $endDate, $status);
                    if ($stmtAdd->execute()) {
                        $insertedTasks[] = $task['TaskName'];
                    }
                } else {
                    echo "TaskName '{$task['TaskName']}' มีอยู่แล้วสำหรับ EmployeeID นี้.";
                }
            }
        }
    }
    

    // เปลี่ยนเส้นทางไปยัง 55021.php พร้อมส่งข้อมูลชื่อสิทธิ์ที่บันทึก
    header("Location: 55021.php?insertedTasks=" . urlencode(implode(',', $insertedTasks)) . "&employeeID=" . urlencode($employeeId));
    exit; // หยุดการทำงานหลังจาก redirect
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>จัดการสิทธิ์ </title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

<?php

// ตรวจสอบและดึงข้อมูลจาก session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : ''; // ดึงชื่อผู้ใช้จาก session ถ้าไม่มีให้เป็นค่าว่าง
?>


    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <img src="assets/img/logo.png" alt="">
                <span class="d-none d-lg-block">ทบทวนสิทธิ</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle " href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li><!-- End Search Icon-->
<!--    <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications" style="max-height: 300px; overflow-y: auto;">
            <li class="dropdown-header">
             4 รายการ 
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <a href="components-cards.html">
              <div>
                
                <h4>แจ้งเตือนรายงาน</h4>
                <p>รายงานการขอเข้าใช้สิทธิ</p>
                <p>30 นาที. ที่ผ่านมา</p>
              
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

          <!--     <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>การแจ้งเตือนระบบ</h4>
                <p>............</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>แจ้งเตือน......</h4>
                <p>............</p>
                <p>2 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>ระบบ.............</h4>
                <p>..............</p>
                <p>4 hrs. ago</p>
              </div>
            </li>
          </a>
            <li>
              <hr class="dropdown-divider">
            </li>

          </ul><!-- End Notification Dropdown Items 

        </li><!-- End Notification Nav 
</a> -->

                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
                        <span class="d-none d-md-block dropdown-toggle ps-2">
                            <?php 
                            if (!empty($username)) { 
                                echo htmlspecialchars($username); 
                            } else { 
                                echo 'ชื่อผู้ใช้';  
                            } 
                            ?>
                        </span>
                    </a><!-- End Profile Image Icon -->
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>
                                <?php 
                                if (!empty($username)) { 
                                    echo htmlspecialchars($username); 
                                } else { 
                                    echo 'ชื่อผู้ใช้';  
                                } 
                                ?>
                            </h6>
                            <span>
                                <?php 
                                if (!empty($position)) { 
                                    echo htmlspecialchars($position); 
                                } else { 
                                    echo 'ตำแหน่ง';  
                                } 
                                ?>
                            </span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="users-profile.php">
                                <i class="bi bi-person"></i>
                                <span>โปรไฟล์</span>
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="login.php">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>ออกจากระบบ</span>
                            </a>
                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->
            </ul>
        </nav><!-- End Icons Navigation -->

    </header>

   <!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link active" href="index.php">
                <i class="bi bi-grid"></i>
                <span>หน้าหลัก</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-menu-button-wide"></i><span>ประวัติ</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="components-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
            <li>
            <a href="history.php">
              <i class="bi bi-circle"></i><span>ประวัติการเข้าใช้</span>
            </a>
          </li>
          <li>
            <a href="ประวัติการเปลี่ยนแปลง.php">
              <i class="bi bi-circle"></i><span>ประวัติการเปลี่ยนแปลง</span>
            </a>
          </li>
          
                
            </ul>
        </li><!-- End History Nav -->
    </ul>
</aside><!-- End Sidebar-->
<main id="main" class="main">
<div class="pagetitle">
    <h1 style="font-size: 28px;">จัดการสิทธิ์</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="font-size: 20px;">หน้าแรก</a></li>
            <li class="breadcrumb-item"><a href="displayname.php" style="font-size: 20px;">รายชื่อพนักงานในแผนก</a></li>
            <li class="breadcrumb-item" style="font-size: 20px;">
                <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?>
            </li> <!-- แสดง FirstName และ LastName ของพนักงาน -->
            <li class="breadcrumb-item active" style="font-size: 20px;">เพิ่มสิทธิ์</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

    <!-- ฟอร์มเพิ่มข้อมูล -->
    <h2 style="font-size: 20px;">เพิ่มสิทธิ์ใหม่</h2>
    <form action="modify_tasks.php" method="POST">
    <input type="hidden" name="employeeId" value="<?php echo htmlspecialchars($employeeId); ?>">

    <table class="table datatable" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
        <thead class="thead-light">
            <tr>
                <th style="text-align: center; border: 1px solid #ddd;">ลำดับ</th>
                <th style="text-align: center; border: 1px solid #ddd;">รหัสพนักงาน</th>
                <th style="text-align: center; border: 1px solid #ddd;">ชื่อสิทธิ์</th>
                <th style="text-align: center; border: 1px solid #ddd;">วันเริ่มต้น</th>
                <th style="text-align: center; border: 1px solid #ddd;">วันสิ้นสุด</th>
                <th style="text-align: center; border: 1px solid #ddd;">สถานะ</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($tasks as $index => $task): ?>
            <tr>
                <td style="text-align: right; border: 1px solid #ddd;">
                    <input type="checkbox" name="selectedTasks[]" value="<?php echo htmlspecialchars($task['Task_Id']); ?>" class="task-checkbox" data-task-id="<?php echo $task['Task_Id']; ?>">
                    <?php echo $index + 1; ?>
                </td>
                <td style="text-align: center; border: 1px solid #ddd;"><?php echo htmlspecialchars($employeeId); ?></td>
                <td style="text-align: left; border: 1px solid #ddd;"><?php echo htmlspecialchars($task['TaskName']); ?></td>
                <td style="text-align: center; border: 1px solid #ddd;">
                    <input type="date" name="startDate[<?php echo $task['Task_Id']; ?>]" value="<?php echo date('Y-m-d'); ?>" required onchange="convertToThaiDate(this)">
                    <span id="thaiStartDate-<?php echo $task['Task_Id']; ?>"></span>
                </td>
                <td style="text-align: center; border: 1px solid #ddd;">
                    <input type="date" name="endDate[<?php echo $task['Task_Id']; ?>]" value="<?php echo date('Y-m-d'); ?>" required onchange="convertToThaiDate(this)">
                    <span id="thaiEndDate-<?php echo $task['Task_Id']; ?>"></span>
                </td>
                <td style="text-align: center; border: 1px solid #ddd;">
                    <span>Y</span>
                    <input type="hidden" name="status[<?php echo $task['Task_Id']; ?>]" value="Y">
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 20px;">
        <input type="submit" name="addTask" value="บันทึก" class="btn btn-primary" style="padding: 10px 20px;">
    </div>
</form>


</main><!-- End #main -->




<!-- ======= Footer ======= -->
<footer id="footer" class="footer">
  <div class="copyright">
    &copy; สำนักงานหลักประกันสุขภาพแห่งชาติ <strong><span>ทบทวนสิทธิ</span>
  </div>
</footer><!-- End Footer -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/chart.js/chart.umd.js"></script>
<script src="assets/vendor/echarts/echarts.min.js"></script>
<script src="assets/vendor/quill/quill.js"></script>
<script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="assets/vendor/tinymce/tinymce.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>

<script src="assets/js/main.js"></script>

</body>

</html>