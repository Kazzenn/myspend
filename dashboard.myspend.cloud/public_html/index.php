<?php
session_start();
include('server.php');
date_default_timezone_set('Asia/Bangkok'); 
//  ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

//  ออกจากระบบ
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header("Location: login.php");
    exit();
}

//  เพิ่มรายการใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $amount = (float) $_POST['amount'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    $query = "INSERT INTO transactions (user_id, type, amount, category, description, date, created_at) 
              VALUES ('$username', '$type', '$amount', '$category', '$description', '$date', NOW())";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "บันทึกรายการเรียบร้อยแล้ว!";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }

    header("Location: index.php");
    exit();
}

//  ลบรายการ
if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    $query = "DELETE FROM transactions WHERE id = '$delete_id' AND user_id = '$username'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "ลบรายการเรียบร้อยแล้ว!";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
    header("Location: index.php");
    exit();
}

//  กำหนดโหมดการแสดงผล
$view_mode = isset($_GET['view']) && $_GET['view'] === 'all' ? 'all' : 'recent';
$limit_sql = $view_mode === 'all' ? '' : 'LIMIT 50'; 

//  ดึงข้อมูล transactions
$sql = "SELECT id, DATE(date) as date, type, description, amount, category 
        FROM transactions 
        WHERE user_id = '$username'
        ORDER BY date DESC, created_at DESC 
        $limit_sql";

$result = mysqli_query($conn, $sql);

$records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $records[$row['date']][] = $row;
}

//  คำนวณรายรับ/รายจ่ายทั้งหมด
$summary_sql = "SELECT 
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
    COUNT(*) as total_transactions
    FROM transactions WHERE user_id = '$username'";
$summary_result = mysqli_query($conn, $summary_sql);
$summary = mysqli_fetch_assoc($summary_result);

$total_income = $summary['total_income'] ?? 0;
$total_expense = $summary['total_expense'] ?? 0;
$balance = $total_income - $total_expense;
$total_transactions = $summary['total_transactions'] ?? 0;

// หมวดหมู่ fallback
$categories = [
    'income' => [
        ['name' => 'เงินเดือน', 'icon' => '💰'],
        ['name' => 'รายได้พิเศษ', 'icon' => '💵'],
        ['name' => 'ของขวัญ', 'icon' => '🎁'],
        ['name' => 'การลงทุน', 'icon' => '📈'],
        ['name' => 'รายรับอื่นๆ', 'icon' => '📝']
    ],
    'expense' => [
        ['name' => 'อาหาร', 'icon' => '🍽️'],
        ['name' => 'ขนส่ง', 'icon' => '🚗'],
        ['name' => 'ช้อปปิ้ง', 'icon' => '🛍️'],
        ['name' => 'ค่าใช้จ่ายที่อยู่อาศัย', 'icon' => '🏠'],
        ['name' => 'สุขภาพ', 'icon' => '🏥'],
        ['name' => 'การศึกษา', 'icon' => '📚'],
        ['name' => 'บันเทิง', 'icon' => '🎬'],
        ['name' => 'รายจ่ายอื่นๆ', 'icon' => '📝']
    ]
];

// เตรียมข้อมูลสำหรับการแสดงผล
if ($view_mode === 'all') {
    // แสดงตามวันที่ที่มีข้อมูล
    $dates_to_show = array_keys($records);
} else {
    // แสดงย้อนหลัง 7 วัน
    $dates_to_show = [];
    for ($i = 0; $i < 7; $i++) {
        $dates_to_show[] = date("Y-m-d", strtotime("-$i day"));
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>MySpend Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css?v=1.0">
<meta name="description" content="เพิ่มและแก้ไขข้อมูลรายรับรายจ่ายในระบบ MySpend บันทึกการเงินส่วนตัวได้ง่ายและรวดเร็ว">
<meta name="keywords" content="เพิ่มรายรับ, เพิ่มรายจ่าย, บันทึกค่าใช้จ่าย, MySpend">
</head>

<body>
    <div class="header-section">
    <h1>MySpend Dashboard</h1>
    <p>ยินดีต้อนรับ, <?php echo htmlspecialchars($username); ?> (<a href="index.php?logout=1">ออกจากระบบ</a>)</p>
    </div>

    <!-- สรุปยอด -->
    <div class="summary-cards">
        <div class="card income">รายรับทั้งหมด: ฿<?php echo number_format($total_income, 2); ?></div>
        <div class="card expense">รายจ่ายทั้งหมด: ฿<?php echo number_format($total_expense, 2); ?></div>
        <div class="card balance">
            ยอดคงเหลือ: ฿<?php echo number_format($balance, 2); ?>
        </div>
    </div>


    <!-- สถิติ -->
    <div class="stats-info">
        มีรายการทั้งหมด <?php echo number_format($total_transactions); ?> รายการ
    </div>

    <!-- ปุ่มสลับโหมดการแสดงผล -->
    <div class="view-toggle">
        <a href="index.php?view=recent" class="toggle-btn <?php echo $view_mode === 'recent' ? 'active' : ''; ?>">
            ย้อนหลัง 7 วัน
        </a>
        <a href="index.php?view=all" class="toggle-btn <?php echo $view_mode === 'all' ? 'active' : ''; ?>">
            ดูทั้งหมด
        </a>
    </div>

    <!-- ฟอร์มเพิ่มรายการ -->
    <div class="form-section">
        <h3>เพิ่มรายการใหม่</h3>
        <form method="POST">
            <label>ประเภท:
                <select name="type" id="type" onchange="updateCategories()" required>
                    <option value="">เลือกประเภท</option>
                    <option value="income">รายรับ</option>
                    <option value="expense">รายจ่าย</option>
                </select>
            </label>
            <label>จำนวนเงิน:
                <input type="number" name="amount" step="0.01" min="0" required>
            </label>
            <label>วันที่:
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </label>
            <label>หมวดหมู่:
                <select name="category" id="category" required>
                    <option value="">เลือกหมวดหมู่</option>
                </select>
            </label>
            <label>รายละเอียด:
                <input type="text" name="description" placeholder="รายละเอียดเพิ่มเติม">
            </label>
            <button type="submit" name="add_transaction">บันทึก</button>
        </form>
    </div>

    <!-- แสดงประวัติ -->
    <div class="transactions-table">
        <h3>
            <?php echo $view_mode === 'all' ? 'ประวัติทั้งหมด' : 'ประวัติย้อนหลัง 7 วัน'; ?>
        </h3>

        <?php if (empty($records) && $view_mode === 'all'): ?>
            <div class="no-records">
                ยังไม่มีรายการในระบบ<br>
                เริ่มต้นบันทึกรายรับรายจ่ายของคุณกันเลย!
            </div>
        <?php else: ?>
            <?php
            foreach ($dates_to_show as $date) {
                // แสดงชื่อวัน
                if ($view_mode === 'recent' || isset($records[$date])) {
                    $dayName = ['Sun' => 'อา.', 'Mon' => 'จ.', 'Tue' => 'อ.', 'Wed' => 'พ.', 'Thu' => 'พฤ.', 'Fri' => 'ศ.', 'Sat' => 'ส.'][date("D", strtotime($date))];
                    $dateDisplay = date("d/m/Y", strtotime($date));

                    // คำนวณจำนวนวันที่ผ่านมา
                    $days_ago = (strtotime(date('Y-m-d')) - strtotime($date)) / (60 * 60 * 24);
                    $date_suffix = '';
                    if ($days_ago == 0) $date_suffix = ' (วันนี้)';
                    elseif ($days_ago == 1) $date_suffix = ' (เมื่อวาน)';
                    elseif ($days_ago > 1 && $view_mode === 'all') $date_suffix = " ({$days_ago} วันที่แล้ว)";

                    echo "<div class='day-section'>";
                    echo "<h4> วัน{$dayName} {$dateDisplay}{$date_suffix}</h4>";

                    if (!empty($records[$date])) {
                        $income = $expense = 0;
                        echo "<table><tr><th>ประเภท</th><th>หมวดหมู่</th><th>รายละเอียด</th><th>จำนวนเงิน</th><th>จัดการ</th></tr>";
                        foreach ($records[$date] as $row) {
                            if ($row['type'] === 'income') $income += $row['amount'];
                            if ($row['type'] === 'expense') $expense += $row['amount'];
                            echo "<tr>
                                    <td>" . ($row['type'] === 'income' ? '📈 รายรับ' : '📉 รายจ่าย') . "</td>
                                    <td>{$row['category']}</td>
                                    <td>{$row['description']}</td>
                                    <td>" . ($row['type'] === 'income' ? '+฿' : '-฿') . number_format($row['amount'], 2) . "</td>
                                    <td><a href='?delete_id={$row['id']}&view={$view_mode}' onclick='return confirm(\"ลบรายการนี้?\")'>❌ ลบ</a></td>
                                  </tr>";
                        }
                        echo "</table>";
                        echo "<div style='display: flex; justify-content: space-between; padding: 15px 20px; background: #f8f9fa; font-weight: 500;'>";
                        echo "<span style='color: #1088f0ff;'>รายรับ: ฿" . number_format($income, 2) . "</span>";
                        echo "<span style='color: #f65353ff;'>รายจ่าย: ฿" . number_format($expense, 2) . "</span>";
                        echo "<span style='color: " . (($income - $expense) >= 0 ? '#4facfe' : '#fa709a') . ";'>สุทธิ: ฿" . number_format($income - $expense, 2) . "</span>";
                        echo "</div>";
                    } else {
                        echo "<p class='no-data'>วันนี้ไม่มีรายการ</p>";
                    }
                    echo "</div>";
                }
            }
            ?>
        <?php endif; ?>
    </div>

    <script>
        const categories = <?php echo json_encode($categories); ?>;

        function updateCategories() {
            const typeSelect = document.getElementById("type").value;
            const categorySelect = document.getElementById("category");
            categorySelect.innerHTML = '<option value="">เลือกหมวดหมู่</option>';
            if (categories[typeSelect]) {
                categories[typeSelect].forEach(cat => {
                    const opt = document.createElement("option");
                    opt.value = cat.name;
                    opt.textContent = cat.icon + " " + cat.name;
                    categorySelect.appendChild(opt);
                });
            }
        }

        // เพิ่ม loading effect ให้ปุ่ม
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtns = document.querySelectorAll('.toggle-btn');
            toggleBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!this.classList.contains('active')) {
                        this.innerHTML += ' <span style="animation: spin 1s linear infinite;">⏳</span>';
                    }
                });
            });
        });
    </script>

    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</body>

</html>