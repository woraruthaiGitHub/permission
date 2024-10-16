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

// ดึงประวัติการเข้าสู่ระบบจาก LoginHistory1

// ดึงข้อมูลประวัติการเข้าสู่ระบบจาก LoginHistory1
$sql = "SELECT * FROM   loginhistory1 ORDER BY login_time DESC"; // Query ดึงข้อมูลทุกแถว
$result = $conn->query($sql); // รันคำสั่ง SQL


$log_sql = "SELECT * FROM loginhistory1 WHERE username = ? ORDER BY login_time DESC";
$log_stmt = $conn->prepare($log_sql);
$log_stmt->bind_param("s", $_SESSION['username']);
$log_stmt->execute();
$log_result = $log_stmt->get_result();
// รับข้อมูลผู้ใช้จากฐานข้อมูล
  $username = $_SESSION['username'];
$sql = "SELECT * FROM UserRole WHERE Username='$username'";
$result = $conn->query($sql);
$userData = $result->fetch_assoc();

// ดึงรายชื่อพนักงานในแผนกเดียวกันจาก EmployeesAll และ EmployeesAll1
$department = $userData['Department'];

// ดึงข้อมูลการอนุมัติ
$approvalSql = "
    SELECT status, COUNT(*) as count 
    FROM approvals 
    WHERE department = '$department' 
    AND status IN ('อนุมัติแล้ว', 'ยังไม่ได้ดำเนินการ')
    GROUP BY status
";
$approvalResult = $conn->query($approvalSql);

// กำหนดค่าเริ่มต้น
$approved = 0;
$notApproved = 0;

// จัดการกับผลลัพธ์การอนุมัติ
while ($row = $approvalResult->fetch_assoc()) {
    switch ($row['status']) {
        case 'อนุมัติแล้ว':
            $approved = $row['count'];
            break;
        case 'ยังไม่ได้ดำเนินการ':
            $notApproved = $row['count'];
            break;
    }
}

// คำนวณเปอร์เซ็นต์
$total = $approved + $notApproved;
$approvedPercent = $total > 0 ? ($approved / $total) * 100 : 0;
$notApprovedPercent = $total > 0 ? ($notApproved / $total) * 100 : 0;

?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>หน้าหลัก</title>
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
        <a class="nav-link " href="index.php"class="active">
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
            <a href="history.php">
              <i class="bi bi-circle"></i><span>ประวัติการเข้าใช้</span>
            </a>
          </li>
          <li>
            <a href="ประวัติการเปลี่ยนแปลง.php">
              <i class="bi bi-circle"></i><span>ประวัติการเปลี่ยนแปลง</span>
            </a>
          </li>
          
      
          
      
      
        </a>
      </li><!-- End Blank Page Nav -->
 
    </ul>

  </aside><!-- End Sidebar-->
  <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            flex: 1; /* ใช้พื้นที่ที่เหลืออยู่ทั้งหมด */
        }
        footer.footer {
            width: 100%; /* ให้ footer กว้างเต็มหน้า */
            position: fixed; /* ติด footer ที่ด้านล่างสุดของหน้าจอ */
            bottom: 0; /* ติดกับขอบล่าง */
            background-color: #f8f9fa; /* สีพื้นหลังของ footer */
            text-align: center; /* จัดข้อความให้อยู่ตรงกลาง */
            padding: 10px 0; /* กำหนดระยะห่างด้านใน */
        }
    </style>

  <main id="main" class="main">

  <div class="pagetitle">
    <h1 style="font-size: 28px;">หน้าหลัก</h1>
</div><!-- End Page Title -->

<?php
// ดึงจำนวนพนักงานในแผนกเดียวกันจากตาราง EmployeesAll
$totalEmployeesSql = "
    SELECT COUNT(*) as total FROM EmployeesAll WHERE Department='$department'
";
$totalEmployeesResult = $conn->query($totalEmployeesSql);

// ตรวจสอบการ query สำเร็จหรือไม่
if (!$totalEmployeesResult) {
    die("Error executing query: " . $conn->error);
}

// เก็บจำนวนพนักงาน
$totalEmployees = 0;
if ($row = $totalEmployeesResult->fetch_assoc()) {
    $totalEmployees = $row['total'];
}
?>

<section class="section dashboard">
    <div class="row">
        <!-- Left side columns -->
        <div class="col-lg-8">
            <div class="row">
                <!-- Customers Card -->
                <div class="col-12 col-md-6 half-height">
                    <div class="card info-card customers-card">
                        <div class="filter"></div>
                        <a href="displayname.php">
                            <div class="card-body">
                                <h5 class="card-title">พนักงานทั้งหมด <span>|ทั้งหมด</span></h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $totalEmployees; ?></h6><span>|คน</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- End Customers Card -->

<div class="col-lg-10">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">การอนุมัติ</h5>
            <div id="pieChart"></div>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const approvedPercent = <?php echo $approvedPercent; ?>;
                    const notApprovedPercent = <?php echo $notApprovedPercent; ?>;

                    const options = {
                        series: [approvedPercent, notApprovedPercent],
                        chart: {
                            height: 350,
                            type: 'pie',
                            toolbar: { show: true },
                            events: {
                                dataPointSelection: function(event, chartContext, config) {
                                    const selectedLabel = config.w.config.labels[config.dataPointIndex];
                                    if (selectedLabel === 'อนุมัติสำเร็จแล้ว') {
                                        window.location.href = 'อนุมัติแล้ว.php'; 
                                    } else if (selectedLabel === 'ยังไม่ได้ดำเนินการอนุมัติ') {
                                        window.location.href = 'ยังไม่ได้อนุมัติ.php'; 
                                    }
                                }
                            }
                        },
                        labels: ['อนุมัติสำเร็จแล้ว', 'ยังไม่ได้ดำเนินการอนุมัติ'],
                        colors: ['#28a745', '#dc3545']
                    };

                    const chart = new ApexCharts(document.querySelector("#pieChart"), options);
                    chart.render();
                });
            </script>
        </div>
    </div>
</div>

  
        
  
      </div>
    </section>
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; สำนักงานหลักประกันสุขภาพแห่งชาติ <strong><span>ทบทวนสิทธิ</span></strong>
        </div>
    </footer><!-- End Footer -->
  </main>
  


      </div>
    </section>

  </main><!-- End #main -->

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