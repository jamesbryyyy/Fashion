<?php
// download_backup.php
$conn = new mysqli("localhost", "root", "", "fashion");

if (isset($_POST['backup'])) {
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    // START OF FIX: Disable foreign key checks at the start of the file
    $return = "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        $result = $conn->query("SELECT * FROM $table");
        $num_fields = $result->field_count;

        $return .= "DROP TABLE IF EXISTS $table;";
        $row2 = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
        $return .= "\n\n" . $row2[1] . ";\n\n";

        while ($row = $result->fetch_row()) {
            $return .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) { $return .= '"' . $row[$j] . '"'; } else { $return .= '""'; }
                if ($j < ($num_fields - 1)) { $return .= ','; }
            }
            $return .= ");\n";
        }
        $return .= "\n\n";
    }

    // END OF FIX: Re-enable foreign key checks
    $return .= "\nSET FOREIGN_KEY_CHECKS = 1;";

    $filename = 'db_backup_' . date("Y-m-d_H-i") . '.sql';
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    echo $return;
    exit;
}
?>