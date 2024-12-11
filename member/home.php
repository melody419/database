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
	ob_start();
	$query = $con->prepare("SELECT * FROM book ORDER BY title");
	$query->execute();
	$result = mysqli_stmt_get_result($query);
	if (!$result) {
		die("ERROR: Couldn't fetch books");
	}
	$rows = mysqli_num_rows($result);
	if ($rows === 0) {
		echo "<h2 align='center'>No books available</h2>";
	} else {
		echo "<form class='cd-form' method='POST' action='#'>";
		echo "<legend>Available books</legend>";
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
			for ($i = 0; $i < $rows; $i++) {
				$row = mysqli_fetch_array($result);
				echo "<tr>
								<td>
									<label class='control control--radio'>
										<input type='radio' name='rd_book' value=" . $row[0] . " />
									<div class='control__indicator'></div>
								</td>";
				for ($j = 0; $j < 5; $j++)
					if ($j == 4)
						echo "<td>" . $row[$j] . "  books available</td>";
					else
						echo "<td>" . $row[$j] . "</td>";
				echo "</tr><tr><td colspan='7'><hr style='border: 0; border-top: 1.5px solid #301602;'></td></tr>";
			}
		echo "</table>";
		echo "<br /><br /><input type='submit' name='m_request' value='Borrow book' />";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;"; // 插入4個空格
		echo "<input type='submit' name='m_favor' value='Add to favorites' />";
		echo "</form>";
	}

	if (isset($_POST['m_request'])) {
		$query1 = $con->prepare("SELECT balance FROM member WHERE username = ?");
		$query1->bind_param("s", $_SESSION['username']);
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
				$query->bind_param("s", $_SESSION['username']);
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
							$query = $con->prepare("SELECT balance FROM member WHERE username = ?");
							$query->bind_param("s", $_SESSION['username']);
							$query->execute();
							$memberBalance = mysqli_fetch_assoc(mysqli_stmt_get_result($query))['balance'];
							$query->close();
							$memberBalance=$memberBalance-1;
							$query = $con->prepare("UPDATE member SET balance = ? WHERE username = ?");
							$query->bind_param("is", $memberBalance, $_SESSION['username']);
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
							$query->bind_param("ss", $_SESSION['username'], $selectedBook);
							if (!$query->execute()) {
								echo error_without_field("ERROR: Couldn't request book");
							} else {							
								echo success("Book successfully requested. You will be notified by email when the book is issued to your account");
							}
							
						}
									
			}
		}
		header("Refresh:0");
	}
	if (isset($_POST['m_favor'])) {
             // 提取結果
		$selectedBook = $_POST['rd_book'] ?? null;

		$query = $con->prepare("INSERT INTO wishlist(member, book_isbn) VALUES(?, ?)");
		$query->bind_param("ss", $_SESSION['username'], $selectedBook);
		if (!$query->execute()) {
			echo error_without_field("ERROR: Couldn't insert into wishlist");
		} else {
			echo success("You have successfully added this book to your collection.");
		}
	}
	ob_end_flush();
				// }
			
		
			
/*mysqli_query
mysqli_query 是一個簡單的方法，用於直接執行 SQL 查詢。它適合用於簡
單的查詢，但在處理需要參數的查詢時，容易受到 SQL 注入攻擊的影響。

mysqli_prepare 和 mysqli_stmt
mysqli_prepare 和 mysqli_stmt 
相關的方法（如 bind_param 和 execute）用於準備和執行參數化查詢。
這種方法更安全，因為它可以防止 SQL 注入攻擊。*/
	?>
</body>
</html>
