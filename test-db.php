<!DOCTYPE html>
<html>
<head>
    <title>MySQL Database Test</title>
</head>
<body>
    <?php
    require_once __DIR__.'/db-config.php';
    echo '<h1>MySQL Database Configuration Test</h1>';
    try {
        $stmt = $pdo->query('SELECT NOW() as now');
        $row = $stmt->fetch();
        echo '<p style="color:green;">✅ Connection successful. Server time: ' . htmlspecialchars($row['now']) . '</p>';
        // check that tables exist
        $tbl = $pdo->query("SHOW TABLES LIKE 'maintenance_records'")->fetch();
        if ($tbl) {
            echo '<p style="color:green;">✅ maintenance_records table found.</p>';
        } else {
            echo '<p style="color:orange;">⚠️ maintenance_records table not present.</p>';
        }
    } catch (Exception $e) {
        echo '<p style="color:red;">❌ Connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    ?>
</body>
</html>
