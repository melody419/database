<?php
declare(strict_types=1);

require "../db_connect.php";
require "../message_display.php";
require "verify_librarian.php";
require "header_librarian.php";
?>

<html>
<head>
	<title>Users</title>
	<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
	<link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
	<link rel="stylesheet" type="text/css" href="../member/css/my_books_style.css">
	<link rel="stylesheet" href="../member/css/register_style.css">
</head>
<body>

<?php
$query = $con->prepare("SELECT * FROM member");
$query->execute();
$result = $query->get_result();
$rows = $result->num_rows;
if ($rows === 0) {
	echo "<h2 align='center'>No users found.</h2>";
} else {
	echo "<form class='cd-form' method='POST' action='#'>";
	echo "<legend>Users</legend>";
	echo "<div class='success-message' id='success-message'>
			<p id='success'></p>
		  </div>";
	echo "<div class='error-message' id='error-message'>
			<p id='error'></p>
		  </div>";
	echo "<table width='100%' cellpadding='10' cellspacing='10'>
			<tr>
				<th></th>
				<th>account<hr></th>
				<th>name<hr></th>
				<th>Email<hr></th>
				<th>Balance<hr></th>
			</tr>";

	$i = 0;
	while ($row = mysqli_fetch_assoc($result)) {
		echo "<tr>
				<td>
					<label class='control control--checkbox'>
						<input type='checkbox' name='cb_user{$i}' value='{$row['account']}'>
						<div class='control__indicator'></div>
					</label>
				</td>";
		if($row['account']==$_SESSION['account'])
			echo "<td>{$row['account']}(you)</td>";
		else echo "<td>{$row['account']}</td>";	
		echo "<td>{$row['name']}</td>";
		echo "<td>{$row['email']}</td>";
		echo "<td>{$row['balance']}</td>";
		$i++;
	}
	echo "</table><br />";
	echo "<input type='submit' name='b_delete' value='Delete selected users' />";
	echo "</form>";
}

if (isset($_POST['b_delete'])) {
	for ($i = 0; $i < $rows; $i++) {
		if (isset($_POST["cb_user{$i}"])) {
			$account = $_POST["cb_user{$i}"];
			if ($account == $_SESSION['account']) {
				$_SESSION['error_message']="You cannot delete yourself.";
				continue;
			}
			else{
				//不能刪除其他管理員
				$query = $con->prepare("SELECT account FROM librarian WHERE account = ?;");
				$query->bind_param("s", $account);
				$query->execute();
				if(mysqli_num_rows($query->get_result()) != 0){
					$_SESSION['error_message']="You cannot delete another librarian.";
					continue;
				}
			}
			$query = $con->prepare("SELECT book_isbn FROM borrowedbooks WHERE member = ?;");
			$query->bind_param("s", $account);
			$query->execute();
			$result = $query->get_result();
			while ($row = $result->fetch_assoc()) {
				$book_isbn = $row['book_isbn'];
				$update_query = $con->prepare("UPDATE book SET copies = copies + 1 WHERE isbn = ?;");
				$update_query->bind_param("s", $book_isbn);
				$update_query->execute();
				$update_query->close();
			}
			$query->close();
			$query = $con->prepare("DELETE FROM member WHERE account = ?;");
			$query->bind_param("s", $account);
			if (!$query->execute()) {
				die(error_without_field("ERROR: Couldn't delete the user"));
			}
			$query->close();
		}
	}
	header("Location: " . $_SERVER['REQUEST_URI']);
	exit(); // 停止腳本執行
}
if (isset($_SESSION['error_message'])) {
	echo error_without_field($_SESSION['error_message']);
	unset($_SESSION['error_message']); 
}
?>

</body>
</html>
