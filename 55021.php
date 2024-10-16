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
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// พิมพ์ค่าที่ส่งมาจาก URL สำหรับการดีบัก
print_r($_GET); // แสดงค่าที่ส่งมาจาก URL

// ตรวจสอบว่ามีการส่งค่า $_GET มาหรือไม่
if (isset($_GET['employeeID']) && !empty($_GET['employeeID'])) {
    $employeeId = $_GET['employeeID']; // เก็บค่า EmployeeID ที่ส่งมาจาก URL
    


    echo "Employee ID ที่ได้รับ: " . htmlspecialchars($employeeId) . "<br>";
    
    // ดึงข้อมูลพนักงานจากตาราง EmployeesAll ตาม EmployeeID
    $sql = "SELECT FirstName, LastName, Position FROM EmployeesAll WHERE EmployeeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();

   // ตรวจสอบว่าพบข้อมูลพนักงานหรือไม่
if ($result->num_rows > 0) {
  $employee = $result->fetch_assoc();
  $firstName = $employee['FirstName'];
  $lastName = $employee['LastName'];
} else {
  echo "ไม่พบข้อมูลพนักงานที่เลือก";
  exit();
}

// ดึงข้อมูล tasks จากฐานข้อมูล tasktransactions ตาม EmployeeID
$tasksSql = "SELECT TaskName, Start, End, Status FROM tasktransactions WHERE EmployeeID = ? ORDER BY TaskName COLLATE utf8_general_ci"; 
$stmt = $conn->prepare($tasksSql);
$stmt->bind_param("s", $employeeId);
$stmt->execute();
$tasksResult = $stmt->get_result();

// ตรวจสอบการ query สำเร็จหรือไม่
if (!$tasksResult) {
  die("Error executing query: " . $conn->error);
}

// จัดเก็บข้อมูล tasks ในอาเรย์
$tasks = [];
while ($row = $tasksResult->fetch_assoc()) {
  $tasks[] = [
      'TaskName' => $row['TaskName'], 
      'StartDate' => $row['Start'], 
      'EndDate' => $row['End'], // เพิ่มการเก็บ EndDate
      'Status' => $row['Status']
  ]; 
}


    // ปิด statement
    $stmt->close();
} else {
    echo "ไม่มีรหัสพนักงานที่ส่งมาจาก URL";
    exit();
}

// จัดการเมื่อกดปุ่มยืนยันการอนุมัติ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tasks'])) {
        $selectedTasks = $_POST['tasks'];

        // ดำเนินการอนุมัติที่นี่
        foreach ($selectedTasks as $task) {
            // สร้างคำสั่ง SQL เพื่อบันทึกการอนุมัติ
            $approvalSql = "INSERT INTO approvals (employee_id, username, task) VALUES (?, ?, ?)";
            $approvalStmt = $conn->prepare($approvalSql);
            $approvalStmt->bind_param("sss", $employeeId, $username, $task);
            $approvalStmt->execute();
            
            // ตรวจสอบผลลัพธ์ของการบันทึก
            if ($approvalStmt->affected_rows <= 0) {
                echo "ไม่สามารถบันทึกการอนุมัติสำหรับ Task: " . htmlspecialchars($task);
                exit();
            }
            $approvalStmt->close();
        }

        // แสดงข้อความยืนยัน
        echo "<h3>อนุมัติสิทธิของ " . htmlspecialchars($firstName . " " . $lastName) . " สำเร็จแล้ว</h3>";
        echo "<h4>Tasks ที่อนุมัติ:</h4>";
        echo "<ul>";
        foreach ($selectedTasks as $task) {
            echo "<li>" . htmlspecialchars($task) . "</li>";
        }
        echo "</ul>";
        exit();
    } else {
        echo "กรุณาเลือกอย่างน้อยหนึ่ง Task";
        exit();
    }
}

// ปิดการเชื่อมต่อฐานข้อมูลเมื่อไม่ใช้งาน
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>อนุมัติสิทธิ์</title>
  <meta content="" name="description">
  <meta content="" name="keywords">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center">
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
        </li>

        <li class="nav-item dropdown">

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


  <main id="main" class="main">
  <div class="pagetitle">
    <h1 style="font-size: 28px;">อนุมัติสิทธิ์</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.php" style="font-size: 18px;">หน้าหลัก</a>
            </li>
            <li class="breadcrumb-item">
                <a href="displayname.php" style="font-size: 18px;">รายชื่อพนักงานในแผนก</a>
            </li>
            <li class="breadcrumb-item active" style="font-size: 18px;">
                <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?>
            </li> <!-- แสดง FirstName และ LastName ของพนักงาน -->
        </ol>
    </nav>
</div><!-- End Page Title -->

    <style>
        .table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 20px;
        }
    
        .table th, .table td {
          border: 1px solid #ddd;
          padding: 8px;
          text-align: left;
        }
    
        .table th {
          background-color: #f2f2f2;
          font-weight: bold;
        }
    
        .button-container {
          text-align: center;
          margin-top: 20px;
        }
    
        .button-container button, .button-container a {
          padding: 10px 20px;
          margin: 10px;
          border: none;
          border-radius: 5px;
          background-color: #4CAF50;
          color: white;
          font-size: 16px;
          cursor: pointer;
          text-decoration: none;
        }
    
        .button-container a {
          background-color: #008CBA;
        }
    
        .button-container button:hover, .button-container a:hover {
          background-color: #45a049;
        }
    
        .button-container a:hover {
          background-color: #005f6b;
        }
      </style>
    </head>
    <body>
    <section class="section">
    <div class="row">
        <div class="col-lg-12">

            <?php
            // ฟังก์ชันสำหรับแปลงวันที่เป็นภาษาไทย
            function formatDateThai($date)
            {
                $thaiMonths = array(
                    "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน",
                    "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม",
                    "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
                );
                $dateTime = new DateTime($date);
                $day = $dateTime->format('d');
                $month = $thaiMonths[$dateTime->format('m')];
                $year = $dateTime->format('Y') + 543; // แปลงเป็นปีพุทธศักราช
                return "$day $month $year";
            }
            ?>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tasks'])): ?>
                <div class="alert alert-success">
                    <h3>อนุมัติสิทธิของ <?php echo htmlspecialchars($firstName . " " . $lastName); ?> สำเร็จแล้ว</h3>
                    <h4>Tasks ที่อนุมัติ:</h4>
                    <ul>
                        <?php foreach ($selectedTasks as $task): ?>
                            <li><?php echo htmlspecialchars($task); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <table class="table table-bordered" style="border-collapse: collapse; width: 100%;">
    <thead class="thead-light">
        <tr>
            <th style="text-align: center; border: 1px solid #ddd; padding: 8px;">ลำดับ</th>
            <th style="text-align: center; border: 1px solid #ddd; padding: 8px;">รหัสพนักงาน</th>
            <th style="text-align: center; border: 1px solid #ddd; padding: 8px;">ชื่อสิทธิ์</th>
            <th style="text-align: center; border: 1px solid #ddd; padding: 8px;">วันเริ่มต้น</th>
            <th style="text-align: center; border: 1px solid #ddd; padding: 8px;">วันสิ้นสุด</th>
            <th style="text-align: center; border: 1px solid #ddd; padding: 8px;">สถานะ</th>
            <th style="text-align: center; border: 1px solid #ddd; padding: 8px;">การดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $index => $task): ?>
            <tr>
                <td style="text-align: right; border: 1px solid #ddd; padding: 8px;"><?php echo $index + 1; ?></td>
                <td style="text-align: center; border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($employeeId); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($task['TaskName']); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars(formatDateThai($task['StartDate'])); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars(formatDateThai($task['EndDate'])); ?></td>
                <td style="text-align: center; border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($task['Status']); ?></td>
                <td style="text-align: center; border: 1px solid #ddd; padding: 8px;">
                    <form action="deleted_tasks.php" method="POST" style="display:inline;">
                        <input type="hidden" name="taskName" value="<?php echo htmlspecialchars($task['TaskName']); ?>">
                        <input type="hidden" name="employeeID" value="<?php echo htmlspecialchars($employeeId); ?>">
                        <button type="submit" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสิทธิ์นี้?');">ลบ</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


            <div class="button-container">
                <a href="modify_tasks.php?id=<?php echo urlencode($employeeId); ?>" class="btn btn-secondary">แก้ไข</a>
                <a href="index.php" class="btn btn-secondary">กลับไปหน้าหลัก</a>
            </div>

        </div>
    </div>
</section>

</body>

<!-- ไม่มี JavaScript สำหรับ confirmSelection เนื่องจากเราจะส่งฟอร์ม -->

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