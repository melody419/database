<?php
declare(strict_types=1);

require "../db_connect.php"; // 確保連接到資料庫
require "../message_display.php"; // 顯示錯誤或成功消息
require "verify_member.php"; // 確保使用者已登錄

if (!isset($_GET['isbn'])) {
    die(error_without_field("ERROR: No book selected."));
}

$isbn = $_GET['isbn'];

// 查詢書籍內容
$query = $con->prepare("SELECT title, content FROM book WHERE isbn = ?");
$query->bind_param("s", $isbn);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die(error_without_field("ERROR: Book not found."));
}

$row = $result->fetch_assoc();
$title = $row['title'];
$content = $row['content'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="css/my_books_style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Content</title>
    <link rel="stylesheet" type="text/css" href="../css/global_styles.css">
</head>
<body>
    <h1><?php echo htmlspecialchars($title); ?></h1>
    <div>
        <h3>Book Content</h3>
        <p style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($content)); ?></p>
    </div>
    <a href="my_books.php" class="button-link">Back to My Books</a>
</body>
</html>
