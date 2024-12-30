<?php
declare(strict_types=1);

require "../db_connect.php";
require "../message_display.php";
require "verify_librarian.php";
require "header_librarian.php";
?>

<html>
<head>
	<title>borrowed books</title>
	<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
	<link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
	<link rel="stylesheet" type="text/css" href="../member/css/my_books_style.css">
</head>
<body>

<?php
ob_start();
$query = $con->prepare("SELECT * FROM borrowedbooks");
$query->execute();
$result = $query->get_result();
$rows = $result->num_rows;
if ($rows === 0) {
	echo "<h2 align='center'>A book that has not been borrowed.</h2>";
} else {
	echo "<form class='cd-form' method='POST' action='#'>";
	echo "<legend>Borrowed books</legend>";
	echo "<div class='success-message' id='success-message'>
			<p id='success'></p>
		  </div>";
	echo "<div class='error-message' id='error-message'>
			<p id='error'></p>
		  </div>";
	echo "<table width='100%' cellpadding='10' cellspacing='10'>
			<tr>
				<th></th>
				<th>member<hr></th>
				<th>ISBN<hr></th>
				<th>Title<hr></th>
				<th>Author<hr></th>
				<th>Category<hr></th>
				<th>Due Date<hr></th>
			</tr>";

	$i = 0;
	/*
	每次調用 $result->fetch_assoc() 時，它會返回一個結果集中的一行資料，
	並且會自動將指標移動到下一行。因此，迴圈內只調用了prepare()一次，
	$result->fetch_assoc() 會自動依序讀取每一行資料，直到沒有更多資料可以讀取。
	*/
	while ($row = mysqli_fetch_assoc($result)) {
		$isbn = $row['book_isbn'];
		if ($isbn !== null) {
			$query = $con->prepare("SELECT title, author, category FROM book WHERE isbn = ?;");
			$query->bind_param("s", $isbn);
			$query->execute();
			$innerResult = $query->get_result();
			$innerRow = $innerResult->fetch_assoc();
			
			echo "<tr>
					<td>
						<label class='control control--checkbox'>
							<input type='checkbox' name='cb_book{$i}' value='{$isbn}'>
							<input type='hidden' name='book_info[$i]' value='{$row['member']}'>
							<div class='control__indicator'></div>
						</label>
					</td>";
			echo "<td>{$row['member']}</td>";	
			echo "<td>{$isbn}</td>";
			echo "<td>{$innerRow['title']}</td>";
			echo "<td>{$innerRow['author']}</td>";
			echo "<td>{$innerRow['category']}</td>";
			if ($row['time'] < date("Y-m-d H:i:s"))
			echo "<td style='color: red;'>{$row['time']} expired</td>";
			else
				echo "<td>{$row['time']}</td>";
		$i++;
		}
	}
	echo "</table><br />";
	echo "<input type='submit' name='b_return' value='Return selected books' />";
	echo "</form>";
}

if (isset($_POST['b_return']) ) {
	for ($i = 0; $i < $rows; $i++) {
		if (isset($_POST["cb_book{$i}"])) {
			$isbn = $_POST["cb_book{$i}"];
			$m_name=$_POST['book_info'][$i];
			$query = $con->prepare("DELETE FROM borrowedbooks WHERE member = ? AND book_isbn = ?;");
			$query->bind_param("ss", $m_name, $isbn);
			if (!$query->execute()) {
				die(error_without_field("ERROR: Couldn't return the books"));
			}
			$query = $con->prepare("SELECT balance FROM member WHERE account = ?");
			$query->bind_param("s", $m_name);
			$query->execute();
			$memberBalance = mysqli_fetch_assoc(mysqli_stmt_get_result($query))['balance'];
			$query->close();
			$memberBalance=$memberBalance+1;
			$query = $con->prepare("UPDATE member SET balance = ? WHERE account = ?");
			$query->bind_param("is", $memberBalance, $m_name);
			$query->execute();
			$query->close(); // ?????
			
			$query = $con->prepare("SELECT copies FROM book WHERE isbn = ?");
			$query->bind_param("s", $isbn);
			$query->execute();
			$copies = mysqli_fetch_assoc(mysqli_stmt_get_result($query))['copies'];
			$query->close();
			$copies=$copies+1;
			$query = $con->prepare("UPDATE book SET copies = ? WHERE isbn = ?");
			$query->bind_param("is", $copies, $isbn);
			$query->execute();
			$query->close();
		}
	}
	header("Location: " . $_SERVER['REQUEST_URI']);
	exit(); // 停止腳本執行
}
ob_end_flush();
?>

</body>
</html>