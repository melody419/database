<?php
declare(strict_types=1);

require "../db_connect.php";
$con->set_charset("binary");
require "../message_display.php";
require "verify_member.php";

if (!isset($_GET['isbn'])) {
    die(error_without_field("ERROR: No book selected."));
}

$isbn = $_GET['isbn'];
$query = $con->prepare("SELECT title, content FROM book WHERE isbn = ?");
$query->bind_param("s", $isbn);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die(error_without_field("ERROR: Book not found."));
}

$row = $result->fetch_assoc();
$title = $row['title'];
$pdfData = $row['content'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="css/my_books_style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" type="text/css" href="../css/global_styles.css">
</head>
<body>
    <h1><?php echo htmlspecialchars($title); ?></h1>
    <div>
        <h3>Book Preview (PDF)</h3>
        <iframe
            src="data:application/pdf;base64,<?php echo base64_encode($pdfData); ?>#toolbar=0"
            width="100%"
            height="600"
            style="border: none;"
        ></iframe>
    </div>
    <a href="my_books.php" class="button-link">Back to My Books</a>
</body>
</html>
