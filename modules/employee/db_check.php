<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../../app/Config/database.php';

echo "<h2>Table: attendance_corrections</h2>";
$result = $conn->query("DESCRIBE attendance_corrections");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $val) {
            echo "<td>" . htmlspecialchars($val) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error showing columns: " . $conn->error;
}
?>
