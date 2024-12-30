<?php
declare(strict_types=1);

require "../db_connect.php";
require "../message_display.php";
require "verify_librarian.php";
require "header_librarian.php";
?>

<html>
<head>
    <title>Active Logs</title>
    <link rel="stylesheet" type="text/css" href="../css/global_styles.css">
    <link rel="stylesheet" type="text/css" href="../css/form_styles.css" />
    <link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
    <link rel="stylesheet" type="text/css" href="../member/css/my_books_style.css">
    
</head>
<body>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_id']) && isset($_POST['details'])) {
    $log_ids = $_POST['log_id'];
    $details = $_POST['details'];
    $success = true;

    foreach ($log_ids as $index => $log_id) {
        $detail = $details[$index];
        $update_query = $con->prepare("UPDATE activity_logs SET details = ? WHERE log_id = ?");
        $update_query->bind_param('si', $detail, $log_id);

        if (!$update_query->execute()) {
            $success = false;
            break;
        }
    }

    if ($success) {
        echo "<div class='success-message' id='success-message'>
                <p id='success'>Details updated successfully.</p>
              </div>";
    } else {
        echo "<div class='error-message' id='error-message'>
                <p id='error'>Failed to update details.</p>
              </div>";
    }
}

$query = $con->prepare("SELECT * FROM activity_logs");
$query->execute();
$result = $query->get_result();
$rows = $result->num_rows;
if ($rows === 0) {
    echo "<h2 align='center'>No active logs found.</h2>";
} else {
    echo "<form class='cd-form' method='POST' action='#'>";
    echo "<legend>Active Logs</legend>";
    echo "<table width='100%' cellpadding='10' cellspacing='10'>
            <tr>
                <th>Log ID<hr></th>
                <th>account<hr></th>
                <th>Book ISBN<hr></th>
                <th>Action<hr></th>
                <th>Details<hr></th>
                <th>Time<hr></th>
                <th>Edit Details<hr></th>
            </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['log_id']}</td>";
        echo "<td>{$row['account']}</td>";
        echo "<td>{$row['book_isbn']}</td>";
        echo "<td>{$row['action']}</td>";
        echo "<td>{$row['details']}</td>";
        echo "<td>{$row['time']}</td>";
        echo "<td>
                <input type='hidden' name='log_id[]' value='{$row['log_id']}'>
                <input type='text' name='details[]' value='{$row['details']}'>
              </td>";
        echo "</tr>";
    }
    echo "</table><br />";
    echo "<input type='submit' value='Update details'>";
    echo "</form>";
}
?>

</body>
</html>
