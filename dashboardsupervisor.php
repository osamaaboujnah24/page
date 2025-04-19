<?php
include 'data.php';
session_start();

class SupervisorDashboard {
    private $pdo;
    private $supervisorId;
    public $projects = [];
    public $error = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مشرف') {
            header("Location: login.php");
            exit;
        }

        $this->supervisorId = $_SESSION['user']['user_id'];  // تأكد من استخدام المفتاح الصحيح
    }

    public function loadProjects() {
        try {
            // التأكد من أن الاستعلام يجيب جميع المشاريع المرتبطة بالمشرف
            $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE manager_id = ?");
            $stmt->execute([$this->supervisorId]);
            $this->projects = $stmt->fetchAll();

            // التأكد من أن البيانات موجودة
            if (empty($this->projects)) {
                $this->error = "لا توجد مشاريع حالياً لتعرضها.";
            }

        } catch (PDOException $e) {
            // إضافة جملة الخطأ التي قد تحدث في استعلام SQL
            $this->error = "فشل في تحميل المشاريع: " . $e->getMessage();
        }
    }
}

$dashboard = new SupervisorDashboard($pdo);
$dashboard->loadProjects();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المشرف</title>
    <style>
        /* نفس التصميم كما في الكود السابق */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #2c3e50;
            color: #ecf0f1;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1abc9c;
            padding: 30px 0;
            text-align: center;
            color: white;
        }

        header h2 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
            background-color: #34495e;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        h3 {
            color: #1abc9c;
            font-size: 24px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 14px;
            text-align: center;
            border: 1px solid #2c3e50;
        }

        th {
            background-color: #1abc9c;
            color: white;
        }

        td {
            background-color: #3c556e;
        }

        a.button {
            padding: 10px 20px;
            background-color: #e67e22;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: inline-block;
        }

        a.button:hover {
            background-color: #d35400;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<header>
    <h2>لوحة تحكم المشرف</h2>
</header>

<div class="container">
    <h3>المشاريع التي تُشرف عليها</h3>

    <?php if ($dashboard->error): ?>
        <p style="color: red;"><?php echo $dashboard->error; ?></p>
    <?php elseif (empty($dashboard->projects)): ?>
        <p>لا توجد مشاريع حالياً.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>اسم المشروع</th>
                <th>الوصف</th>
                <th>الفريق</th>
                <th>الفترة</th>
                <th>خيارات</th>
            </tr>
            <?php foreach ($dashboard->projects as $project): ?>
            <tr>
                <td><?php echo htmlspecialchars($project['title']); ?></td>
                <td><?php echo htmlspecialchars($project['description']); ?></td>
                <td><?php echo htmlspecialchars($project['team_id']); ?></td>
                <td><?php echo htmlspecialchars($project['start_date']) . ' إلى ' . htmlspecialchars($project['end_date']); ?></td>
                <td>
                    <a href="report.php?project_id=<?php echo $project['project_id']; ?>" class="button">كتابة تقرير</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
