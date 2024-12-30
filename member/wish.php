<?php
declare(strict_types=1);
//PHP 會強制要求這些類型必須完全匹配，否則會拋出 TypeError 異常。
require "../db_connect.php";
require "../message_display.php";
require "verify_member.php";
require "header_member.php";
?>

<html>
<head>
	<title>Welcome</title>
	<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
	<link rel="stylesheet" type="text/css" href="css/home_style.css">
	<link rel="stylesheet" type="text/css" href="../css/custom_radio_button_style.css">
</head>
<body>
	<?php
	$query = $con->prepare("SELECT book_isbn FROM wishlist WHERE member = ?");
	$query->bind_param("s", $_SESSION['account']);
	$query->execute();
	$result = mysqli_stmt_get_result($query);
	if (!$result) {
		die("ERROR: Couldn't fetch books");
	}

	$wishlistBooks = [];//一直執行，直到 mysqli_fetch_assoc($result) 返回 false 為止。
	while ($row = mysqli_fetch_assoc($result)) {
		$wishlistBooks[] = $row['book_isbn'];
	}//[] 表示將新元素添加到陣列的末尾。
	$query->close();

	if (empty($wishlistBooks)) {
		echo "<h2 align='center'>No books in Wish list</h2>";
	} else {
		//將陣列的元素轉換為字串，並用指定的分隔符連接。
		$placeholders = implode(',', array_fill(0, count($wishlistBooks), '?'));
		$query = $con->prepare("SELECT * FROM book WHERE isbn IN ($placeholders) ORDER BY title");
		$query->bind_param(str_repeat('s', count($wishlistBooks)), ...$wishlistBooks);
		$query->execute();
		$result = mysqli_stmt_get_result($query);
		if (!$result) {
			die("ERROR: Couldn't fetch books");
		}
		$rows = mysqli_num_rows($result);
		if ($rows === 0) {
			echo "<h2 align='center'>No books in Wish list</h2>";
		} else {
			echo "<form class='cd-form' method='POST' action='#'>";
			echo "<legend>Wish list</legend>";
			echo "<div class='error-message' id='error-message'>
					<p id='error'></p>
				</div>";
			echo "<table width='100%' cellpadding=10 cellspacing=10>";
			echo "<tr>
							<th></th>
							<th style=\"background-color: #f2f2f2;\">ISBN</th>
							<th style=\"background-color: #f2f2f2;\">Title</th>
							<th style=\"background-color: #f2f2f2;\">Author</th>
							<th style=\"background-color: #f2f2f2;\">Category</th>
							<th style=\"background-color: #f2f2f2;\">Copies available</th>
				</tr>";
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<tr>
									<td>
										<label class='control control--radio'>
											<input type='radio' name='rd_book' value=" . $row['isbn'] . " />
										<div class='control__indicator'></div>
									</td>";
					echo "<td>" . $row['isbn'] . "</td>";
					echo "<td>" . $row['title'] . "</td>";
					echo "<td>" . $row['author'] . "</td>";
					echo "<td>" . $row['category'] . "</td>";
					echo "<td>" . $row['copies'] . " books available</td>";
					echo "</tr><tr><td colspan='7'><hr style='border: 0; border-top: 1.5px solid #301602;'></td></tr>";
				}
			echo "</table>";
			echo "<br /><br /><input type='submit' name='m_request' value='Borrow book' />";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;"; // 插入4個空格
			echo "<input type='submit' name='m_favor' value='Remove from favorites' />";
			echo "</form>";
		}
	}

	if (isset($_POST['m_request'])) {
		$query1 = $con->prepare("SELECT balance FROM member WHERE account = ?");
		$query1->bind_param("s", $_SESSION['account']);
		$query1->execute();
		$query1->bind_result($balance); // 將結果綁定到變數 $balance
		$query1->fetch();              // 提取結果
		$selectedBook = $_POST['rd_book'] ?? null;
		if (empty($selectedBook)) {
			echo error_without_field("Please select a book to issue");
		}
 		else if ($balance == 0) {
			echo error_without_field("You are already borrowing 3 books");
			$query1->close();
		}
		else {
			$query1->close();
//? 是一個佔位符，將在後續使用 bind_param 方法綁定具體的 isbn 值。
			$query = $con->prepare("SELECT copies FROM book WHERE isbn = ?");
			$query->bind_param("s", $selectedBook);//string
			$query->execute();

			$copies = mysqli_fetch_assoc(mysqli_stmt_get_result($query))['copies'];
			$query->close();
			if ($copies === 0) {
				echo error_without_field("No copies of the selected book are available");
			} else {
				$query = $con->prepare("SELECT book_isbn FROM borrowedbooks WHERE member = ?");
				$query->bind_param("s", $_SESSION['account']);
				$query->execute();
				$result = $query->get_result();
						$alreadyIssued = false;
						while ($row = mysqli_fetch_assoc($result)) {
							if (strcmp($row['book_isbn'], $selectedBook) === 0) {
								$alreadyIssued = true;
								break;
							}
						}
						if ($alreadyIssued) {
							echo error_without_field("You have already issued a copy of this book");		
						} else {
							$query->close(); // 釋放資源
							$query = $con->prepare("SELECT balance FROM member WHERE account = ?");
							$query->bind_param("s", $_SESSION['account']);
							$query->execute();
							$memberBalance = mysqli_fetch_assoc(mysqli_stmt_get_result($query))['balance'];
							$query->close();
							$memberBalance=$memberBalance-1;
							$query = $con->prepare("UPDATE member SET balance = ? WHERE account = ?");
							$query->bind_param("is", $memberBalance, $_SESSION['account']);
							$query->execute();
							$query->close(); // 釋放資源

							$query = $con->prepare("SELECT copies FROM book WHERE isbn = ?");
							$query->bind_param("s", $selectedBook);
							$query->execute();
							$copies = mysqli_fetch_assoc(mysqli_stmt_get_result($query))['copies'];
							$query->close();
							$copies=$copies-1;
							$query = $con->prepare("UPDATE book SET copies = ? WHERE isbn = ?");
							$query->bind_param("is", $copies, $selectedBook);
							$query->execute();
							$query->close();

							$query = $con->prepare("INSERT INTO borrowedbooks(member, book_isbn) VALUES(?, ?)");
							$query->bind_param("ss", $_SESSION['account'], $selectedBook);
							if (!$query->execute()) {
								echo error_without_field("ERROR: Couldn't request book");
							} else {
								echo success("Book successfully requested.");
							}

						}
					
				// }
			}
		}
	}
	?>
	<?php
	if (isset($_POST['m_favor'])) {
		$query = $con->prepare("DELETE FROM wishlist WHERE member = ? AND book_isbn = ?");
		$query->bind_param("ss", $_SESSION['account'], $_POST['rd_book']);
		$query->execute();
		$query->close();
		header('Location: wish.php');
}
	?>
</body>
</html>
