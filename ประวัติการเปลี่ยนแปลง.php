<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // ถ้ายังไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปยังหน้า login
    exit();
}

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึง username ของผู้ใช้ที่ล็อกอิน
$username = $_SESSION['username'];



// เตรียมคำสั่ง SQL
$sql = "
    SELECT 
        e.EmployeeID,
        CONCAT(e.FirstName, ' ', e.LastName) AS FullName,  -- รวมชื่อและนามสกุล
        MAX(t.TaskName) AS TaskName,
        MAX(t.Start) AS Start,
        MAX(t.End) AS End,
        MAX(t.Status) AS Status,
        'เพิ่มสิทธิ์' AS ActionType,
        NULL AS DeletedAt 
    FROM tasktransactions t
    JOIN employeesall e ON t.EmployeeID = e.EmployeeID
    WHERE e.Department = (SELECT Department FROM employeesall WHERE EmployeeID = ?) 
    GROUP BY e.EmployeeID, FullName 

    UNION

    SELECT 
        e.EmployeeID,
        CONCAT(e.FirstName, ' ', e.LastName) AS FullName,  -- รวมชื่อและนามสกุล
        MAX(d.TaskName) AS TaskName,
        NULL AS Start,
        NULL AS End,
        d.Status,
        'ลบสิทธิ์' AS ActionType, 
        d.DeletedAt 
    FROM deleted_tasks d
    JOIN employeesall e ON d.EmployeeID = e.EmployeeID
    WHERE e.Department = (SELECT Department FROM employeesall WHERE EmployeeID = ?) 
    GROUP BY e.EmployeeID, FullName 

    ORDER BY EmployeeID, ActionType;
";


// เตรียมและ bind parameters
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("เตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
}

$stmt->bind_param("ss", $username, $username);
$execute_result = $stmt->execute();
if ($execute_result === false) {
    die("รันคำสั่ง SQL ล้มเหลว: " . $stmt->error);
}

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ประวัติการเปลี่ยนแปลง</title>
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
  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">ทบทวนสิทธิ</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <!-- <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div> End Search Bar -->

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

<body>

  <!-- ======= Header ======= -->
  

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" href="index.php">
          <i class="bi bi-grid"></i>
          <span>หน้าหลัก</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
    <a class="nav-link " data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-menu-button-wide"></i><span>ประวัติ</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
      
     
    
      <li>
          <li>
            <a href="history.php">
              <i class="bi bi-circle"></i><span>ประวัติการเข้าใช้</span>
            </a>
          </li>
          <li>
            <a href="ประวัติการเปลี่ยนแปลง.php"class="active">
              <i class="bi bi-circle"></i><span>ประวัติการเปลี่ยนแปลง</span>
            </a>
          </li>
          
      
      
        </a>
      </li><!-- End Blank Page Nav -->
 
    </ul>

  </aside><!-- End Sidebar-->
  <main id="main" class="main">

  <div class="pagetitle">
  <h1 style="font-size: 28px;">ประวัติการเปลี่ยนแปลง</h1>
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php"style="font-size: 20px;">หน้าหลัก</a></li>
    <li class="breadcrumb-item"style="font-size: 20px;">ประวัติ</a></li>
    <li class="breadcrumb-item active" style="font-size: 20px;">ประวัติการเปลี่ยนแปลง</a></li>
</ol>
  </nav>
</div><!-- End Page Title -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f9ff;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffff;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #ddd;
            color: black;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        p {
            color: #333;
            font-size: 18px;
        }
    </style>
</head>
<body>
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <?php
            // ตรวจสอบว่ามีผลลัพธ์หรือไม่
            if ($result->num_rows > 0) {
                // ตารางสำหรับการเพิ่มสิทธิ์
                echo "<h3 style='font-size: 22px; font-weight: bold; margin-bottom: 20px;'>ข้อมูลการเพิ่มสิทธิ์</h3>";
                echo "<table style='width: 100%; border-collapse: collapse; border: 1px solid #ddd;'>
                        <tr style='background-color: #f8f9fa;'>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>ลำดับ</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>รหัสพนักงาน</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>ชื่อ-นามสกุล</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>ชื่อสิทธิ์</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>วันที่เริ่ม</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>วันที่สิ้นสุด</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>สถานะ</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>ประเภท</th>
                        </tr>";

                // ฟังก์ชันแปลงวัน เดือน ปี เป็นภาษาไทย
                function thai_date($datetime) {
                  if ($datetime == NULL) {
                      return '-';
                  }
              
                  $months = array(
                      '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
                      '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
                      '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน',
                      '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
                  );
              
                  $date = new DateTime($datetime);
                  $day = $date->format('d');
                  $month = $months[$date->format('m')];
                  $year = $date->format('Y') + 543;  // แปลง ค.ศ. เป็น พ.ศ.
              
                  return $day . ' ' . $month . ' ' . $year;
              }
              

                // แสดงผลข้อมูลการเพิ่ม
                $order = 1; // ตัวแปรสำหรับลำดับ
                while ($row = $result->fetch_assoc()) {
                    if ($row["ActionType"] === "เพิ่มสิทธิ์") { // เฉพาะการเพิ่ม
                        echo "<tr>";
                        echo "<td style='text-align: right; border: 1px solid #ddd; padding: 10px;'>" . $order++ . "</td>"; // แสดงลำดับและเพิ่มขึ้น
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'><a href='employee_tasks.php?employee_id=" . urlencode($row["EmployeeID"]) . "'>" . htmlspecialchars($row["EmployeeID"]) . "</a></td>";
                        echo "<td style='text-align: left; border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row["FullName"]) . "</td>";
                        echo "<td style='text-align: left; border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row["TaskName"]) . "</td>";
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'>" . ($row["Start"] ? thai_date($row["Start"]) : '-') . "</td>"; // ใช้วันที่ภาษาไทย
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'>" . ($row["End"] ? thai_date($row["End"]) : '-') . "</td>"; // ใช้วันที่ภาษาไทย
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row["Status"]) . "</td>";
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row["ActionType"]) . "</td>";
                        echo "</tr>";
                    }
                }
                echo "</table>";

                // ตารางสำหรับการลบสิทธิ์
                echo "<h3 style='font-size: 22px; font-weight: bold; margin-top: 40px;'>ข้อมูลการลบสิทธิ์</h3>";
                echo "<table style='width: 100%; border-collapse: collapse; border: 1px solid #ddd;'>
                        <tr style='background-color: #f8f9fa;'>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>ลำดับ</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>รหัสพนักงาน</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>ชื่อ-นามสกุล</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>ชื่อสิทธิ์</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>สถานะ</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>ประเภท</th>
                            <th style='text-align: center; border: 1px solid #ddd; padding: 10px;'>วันที่</th>
                        </tr>";

                // แสดงผลข้อมูลการลบ
                $result->data_seek(0); // กลับไปที่ตำแหน่งเริ่มต้นของผลลัพธ์
                $order = 1; // เริ่มต้นลำดับใหม่สำหรับการลบ
                while ($row = $result->fetch_assoc()) {
                    if ($row["ActionType"] === "ลบสิทธิ์") { // เฉพาะการลบ
                        echo "<tr>";
                        echo "<td style='text-align: right; border: 1px solid #ddd; padding: 10px;'>" . $order++ . "</td>"; // แสดงลำดับและเพิ่มขึ้น
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'><a href='employee_tasks.php?employee_id=" . urlencode($row["EmployeeID"]) . "'>" . htmlspecialchars($row["EmployeeID"]) . "</a></td>";
                        echo "<td style='text-align: left; border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row["FullName"]) . "</td>";
                        echo "<td style='text-align: left; border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row["TaskName"] ?? '-') . "</td>"; // ใช้ null coalescing operator เพื่อตรวจสอบ TaskName
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row["Status"]) . "</td>";
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row["ActionType"]) . "</td>";
                        echo "<td style='text-align: center; border: 1px solid #ddd; padding: 10px;'>" . ($row["DeletedAt"] ? thai_date($row["DeletedAt"]) : '-') . "</td>"; // ใช้วันที่ภาษาไทย
                        echo "</tr>";
                    }
                }
                echo "</table>";
            } else {
                echo "<p style='font-size: 18px; color: red; text-align: center;'>ไม่มีข้อมูลการเพิ่มหรือลบสิทธิ์สำหรับพนักงานในแผนกนี้</p>";
            }

            // ปิดการเชื่อมต่อฐานข้อมูล
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>
</section>





  <!-- ======= Footer ======= -->
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