<?php
declare(strict_types=1);

require "../db_connect.php";
require "../message_display.php";
require "verify_member.php";
require "header_member.php";
?>

<html>
<head>
	<title>My books</title>
	<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
	<link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
	<link rel="stylesheet" type="text/css" href="css/my_books_style.css">
</head>
<body>

<?php
$query = $con->prepare("SELECT book_isbn FROM borrowedbooks WHERE member = ?;");
$query->bind_param("s", $_SESSION['account']);
$query->execute();
$result = $query->get_result();
$rows = $result->num_rows;

if ($rows === 0) {
	echo "<h2 align='center'>No books currently issued</h2>";
} else {
	echo "<form class='cd-form' method='POST' action='#'>";
	echo "<legend>My books</legend>";
	echo "<div class='success-message' id='success-message'>
			<p id='success'></p>
		  </div>";
	echo "<div class='error-message' id='error-message'>
			<p id='error'></p>
		  </div>";
	echo "<table width='100%' cellpadding='10' cellspacing='10'>";
	echo "<tr>
							<th></th>
							<th style=\"background-color: #f2f2f2;\">ISBN</th>
							<th style=\"background-color: #f2f2f2;\">Title</th>
							<th style=\"background-color: #f2f2f2;\">Author</th>
							<th style=\"background-color: #f2f2f2;\">Category</th>
							<th style=\"background-color: #f2f2f2;\">Copies available</th>
				</tr>";

	$i = 0;
	/*
	瘥�甈∟矽��� $result->fetch_assoc() ���嚗�摰����餈����銝����蝯�������銝剔��銝�銵�鞈����嚗�
	銝虫�������芸��撠����璅�蝘餃����唬��銝�銵�������甇歹��餈游����批�芾矽��其��prepare()銝�甈∴��
	$result->fetch_assoc() �����芸��靘�摨�霈����瘥�銝�銵�鞈����嚗���游�唳�������游��鞈������臭誑霈�������
	*/
	while ($row = $result->fetch_assoc()) {
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
							<div class='control__indicator'></div>
						</label>
					</td>";
			echo "<td>{$isbn}</td>";
			echo "<td><a href='book_content.php?isbn={$isbn}'>{$innerRow['title']}</a></td>";
			
			echo "<td>{$innerRow['author']}</td>";
			echo "<td>{$innerRow['category']}</td>";

			$query = $con->prepare("SELECT time FROM borrowedbooks WHERE member = ? AND book_isbn = ?;");
			$query->bind_param("ss", $_SESSION['account'], $isbn);
			$query->execute();
			$dueDateResult = $query->get_result();
			$dueDateRow = $dueDateResult->fetch_assoc();
			echo "<td>{$dueDateRow['time']}</td>";
			echo "</tr>";
			if ($dueDateRow['time'] < date("Y-m-d H:i:s")) {
				echo '<script>
						document.getElementById("error").innerHTML += "Book ' . $isbn . ' is overdue<br />";
						document.getElementById("error-message").style.display = "block";
					  </script>';
			$query = $con->prepare("DELETE FROM borrowedbooks WHERE member = ? AND book_isbn = ?;");
			$query->bind_param("ss", $_SESSION['account'], $isbn);
			if (!$query->execute()) {
				die(error_without_field("ERROR: Couldn't return the books"));
			}
			}
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
			
			$query = $con->prepare("DELETE FROM borrowedbooks WHERE member = ? AND book_isbn = ?;");
			$query->bind_param("ss", $_SESSION['account'], $isbn);
			if (!$query->execute()) {
				die(error_without_field("ERROR: Couldn't return the books"));
			}
			$query = $con->prepare("SELECT balance FROM member WHERE account = ?");
			$query->bind_param("s", $_SESSION['account']);
			$query->execute();
			$memberBalance = mysqli_fetch_assoc(mysqli_stmt_get_result($query))['balance'];
			$query->close();
			$memberBalance=$memberBalance+1;
			$query = $con->prepare("UPDATE member SET balance = ? WHERE account = ?");
			$query->bind_param("is", $memberBalance, $_SESSION['account']);
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
			$log_query = $con->prepare("INSERT INTO activity_logs (account, time, action,book_isbn) VALUES (?, NOW(), 'returned book',?);");
			$log_query->bind_param("ss", $_SESSION['account'], $isbn);
			$log_query->execute();
			$log_query->close();
			header('Location: my_books.php');
		}
	}

}
?>

</body>
</html>