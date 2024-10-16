<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // ถ้ายังไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปยังหน้า login
    exit();
}
if (!isset($_SESSION['username'])) {
  header('Location: login.php'); // ถ้าผู้ใช้ยังไม่ล็อกอิน จะถูกพาไปที่หน้าล็อกอิน
  exit();
}

// ดึง username ของผู้ใช้ที่ล็อกอิน
$username = $_SESSION['username'];

// ตรวจสอบว่ามีการส่งค่า username มาหรือไม่
if (isset($_GET['username']) && !empty($_GET['username'])) {
    $employeeUsername = $_GET['username']; // รับค่าจาก URL ที่ส่งมาคือ username
} else {
    echo "ไม่มีชื่อผู้ใช้งานที่ส่งมาจาก URL. กรุณาลองอีกครั้ง.";
    exit();
}

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    // ส่วนอื่นๆ ของโค้ดที่ใช้งาน $username
} else {
    echo "ไม่พบข้อมูล username.";
    // หรือ redirect ไปหน้าอื่น
    // header('Location: somepage.php');
    exit();
}


// ดึงข้อมูลพนักงานตาม username จากตาราง employeesall
$sql = "SELECT FirstName, LastName, Position, Department FROM employeesall WHERE EmployeeID = ?";  // ใช้ Username ในการดึงข้อมูลพนักงาน
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง: " . $conn->error;
    exit();
}

$stmt->bind_param("s", $employeeUsername); // ใช้ค่า username ที่ส่งมา
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่าพบข้อมูลพนักงานหรือไม่
if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    $employeeFirstName = $employee['FirstName'];
    $employeeLastName = $employee['LastName'];
    $employeePosition = $employee['Position'];
    $employeeDepartment = $employee['Department'];
} else {
    echo "ไม่พบข้อมูลพนักงานที่เลือก";
    exit();
}

// ดึงข้อมูลพนักงานคนอื่น ๆ ในแผนกเดียวกัน
$sql2 = "SELECT  EmployeeID,FirstName, LastName, Position FROM employeesall WHERE Department = ? AND EmployeeID != ?"; // ดึงพนักงานในแผนกเดียวกันยกเว้นคนที่เลือก
$stmt2 = $conn->prepare($sql2);

if (!$stmt2) {
    echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง: " . $conn->error;
    exit();
}

$stmt2->bind_param("ss", $employeeDepartment, $employeeUsername); // ใช้ค่า Department และยกเว้นพนักงานที่เลือก
$stmt2->execute();
$result2 = $stmt2->get_result();

/// ดึงข้อมูลงานที่อนุมัติแล้วจากตาราง tasktransactions
$sql3 = "
SELECT TaskName, Status, EmployeeID, Start, End
FROM tasktransactions
WHERE Status = 'Y'
AND EmployeeID IN (SELECT EmployeeID FROM employeesall WHERE Department = ?)"; // ใช้ EmployeeID จากแผนกเดียวกัน

$stmt3 = $conn->prepare($sql3);

if (!$stmt3) {
echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง: " . $conn->error;
exit();
}

$stmt3->bind_param("s", $employeeDepartment); // ใช้ Department ในการดึงข้อมูล
$stmt3->execute();
$result3 = $stmt3->get_result();


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>รายชื่อพนักงานในแผนก</title>
  <meta content="" name="description">
  <meta content="" name="keywords">


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
  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="indexadmin.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">ทบทวนสิทธิ</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

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
    <!-- แสดงชื่อผู้ใช้ ถ้าไม่มีจะแสดง "ชื่อผู้ใช้" -->
    <span class="d-none d-md-block dropdown-toggle ps-2">
        <?php 
        if (!empty($username)) { 
            echo htmlspecialchars($username); 
        } else { 
            echo 'ชื่อผู้ใช้';  // ข้อความเริ่มต้น ถ้าไม่มีข้อมูล username
        } 
        ?>
    </span>
</a><!-- End Profile Image Icon -->

<ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
    <li class="dropdown-header">
        <!-- แสดงชื่อผู้ใช้ ถ้าไม่มีจะแสดง "ชื่อผู้ใช้" -->
        <h6>
            <?php 
            if (!empty($username)) { 
                echo htmlspecialchars($username); 
            } else { 
                echo 'ชื่อผู้ใช้';  // ข้อความเริ่มต้น ถ้าไม่มีข้อมูล username
            } 
            ?>
        </h6>
        <!-- แสดงตำแหน่ง ถ้าไม่มีจะแสดง "ตำแหน่ง" -->
        <span>
            <?php 
            if (!empty($position)) { 
                echo htmlspecialchars($position); 
            } else { 
                echo 'ตำแหน่ง';  // ข้อความเริ่มต้น ถ้าไม่มีข้อมูล position
            } 
            ?>
        </span>
    </li>
    <li>
        <hr class="dropdown-divider">
    </li>

    <li>
        <a class="dropdown-item d-flex align-items-center" href="users-profile2.php">
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

<body>

  <!-- ======= Header ======= -->
  

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="indexadmin.php"class="active">
          <i class="bi bi-grid"></i>
          <span>หน้าหลัก</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>ประวัติ</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          
        
          <li>
            <a href="history2.php">
              <i class="bi bi-circle"></i><span>ประวัติการเข้าใช้</span>
            </a>
          </li>
       
        </a>
      </li>
      </ul>
      
      <li class="nav-item">
        <a class="nav-link collapsed" href="ประวัติการเปลี่ยนแปลง2.php">
          <i class="bi bi-envelope"></i>
          <span>ข้อความ </span>
        </a>
      </li><!-- End Contact Page Nav -->
      
        </a>
      </li>
    </ul>

  </aside>
  <main id="main" class="main">
    <div class="pagetitle">
        <h1>รายชื่อพนักงาน</h1>
      
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="indexadmin.php">หน้าหลัก</a></li>
    <li class="breadcrumb-item active "><a href="employee_tasks2.php?username=<?php echo urlencode($employeeFirstName . ' ' . $employeeLastName); ?>">
        <?php echo htmlspecialchars($employeeFirstName . ' ' . $employeeLastName); ?>
    </a></li>
</ol>
  </nav>
  <section class="section">
    <div class="row">
        <div class="col-lg-12">
            <!-- ตารางรายชื่อพนักงาน -->
            <table class="table datatable" id="employeesTable" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
    <thead>
        <tr>
            <th style="text-align: center; border: 1px solid #ddd;">ลำดับ</th>
            <th style="text-align: center; border: 1px solid #ddd;">รหัสพนักงาน</th>
            <th style="text-align: center; border: 1px solid #ddd;">ชื่อ-นามสกุล</th>
            <th style="text-align: center; border: 1px solid #ddd;">ตำแหน่ง</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result2->num_rows > 0): ?>
            <?php $index = 1; // เริ่มต้นลำดับที่ 1 ?>
            <?php while ($colleague = $result2->fetch_assoc()): ?>
                <tr>
                    <td style="border: 1px solid #ddd; text-align: right;"><?php echo $index++; ?></td> <!-- แสดงลำดับ -->
                    <td style="border: 1px solid #ddd; text-align: center;"><?php echo htmlspecialchars($colleague['EmployeeID']); ?></td> <!-- แสดงรหัสพนักงาน -->
                    <td style="border: 1px solid #ddd;"><?php echo htmlspecialchars($colleague['FirstName'] . ' ' . $colleague['LastName']); ?></td> <!-- แสดงชื่อ-นามสกุล -->
                    <td style="border: 1px solid #ddd;"><?php echo htmlspecialchars($colleague['Position']); ?></td> <!-- แสดงตำแหน่ง -->
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center" style="border: 1px solid #ddd;">ไม่มีพนักงานคนอื่นในแผนกนี้</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


            <!-- ตารางงานที่อนุมัติแล้ว -->
            <h3 style="font-size: 20px; margin-top: 30px;">งานที่อนุมัติแล้ว</h3>
            <table class="table datatable" id="Te" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <thead>
                    <tr>
                        <th style="text-align: center; border: 1px solid #ddd;">ลำดับ</th>
                        <th style="text-align: center; border: 1px solid #ddd;">รหัสพนักงาน</th>
                        <th style="text-align: center; border: 1px solid #ddd;">งาน</th>
                        <th style="text-align: center; border: 1px solid #ddd;">สถานะ</th>
                        <th style="text-align: center; border: 1px solid #ddd;">วันที่เริ่ม</th>
                        <th style="text-align: center; border: 1px solid #ddd;">วันที่สิ้นสุด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result3->num_rows > 0): ?>
                        <?php $index = 1; // เริ่มต้นลำดับที่ 1 ?>
                        <?php while ($task = $result3->fetch_assoc()): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; text-align: right;"><?php echo $index++; ?></td> <!-- แสดงลำดับ -->
                                <td style="border: 1px solid #ddd; text-align: center;"><?php echo htmlspecialchars($task['EmployeeID']); ?></td>
                                <td style="border: 1px solid #ddd;"><?php echo htmlspecialchars($task['TaskName']); ?></td>
                                <td style="border: 1px solid #ddd; text-align: center;"><?php echo htmlspecialchars($task['Status']); ?></td>
                                <td style="border: 1px solid #ddd; text-align: center;">
                                    <?php 
                                    // แปลงวันที่เริ่มให้เป็นภาษาไทย
                                    $startDate = date("j ", strtotime($task['Start'])) . monthThai(date("n", strtotime($task['Start']))) . date(" Y", strtotime($task['Start'])); 
                                    echo $startDate; 
                                    ?>
                                </td>
                                <td style="border: 1px solid #ddd; text-align: center;">
                                    <?php 
                                    // แปลงวันที่สิ้นสุดให้เป็นภาษาไทย
                                    $endDate = date("j ", strtotime($task['End'])) . monthThai(date("n", strtotime($task['End']))) . date(" Y", strtotime($task['End'])); 
                                    echo $endDate; 
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center" style="border: 1px solid #ddd;">ไม่พบข้อมูลงานที่อนุมัติแล้ว</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php
// ฟังก์ชันสำหรับแปลงเดือนเป็นภาษาไทย
function monthThai($month) {
    $months = [
        1 => 'มกราคม', 
        2 => 'กุมภาพันธ์', 
        3 => 'มีนาคม', 
        4 => 'เมษายน', 
        5 => 'พฤษภาคม', 
        6 => 'มิถุนายน', 
        7 => 'กรกฎาคม', 
        8 => 'สิงหาคม', 
        9 => 'กันยายน', 
        10 => 'ตุลาคม', 
        11 => 'พฤศจิกายน', 
        12 => 'ธันวาคม'
    ];
    return $months[$month];
}
?>


<footer id="footer" class="footer">
  <div class="copyright">
    &copy; สำนักงานหลักประกันสุขภาพแห่งชาติ <strong><span>ทบทวนสิทธิ</span>
  </div>
  <div class="credits">
    <!-- All the links in the footer should remain intact. -->
    <!-- You can delete the links only if you purchased the pro version. -->
    <!-- Licensing information: https://bootstrapmade.com/license/ -->
    <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
   
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

<!-- Template Main JS File -->
<script src="assets/js/main.js"></script>

</body>

</html>