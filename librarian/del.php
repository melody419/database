<?php
declare(strict_types=1);
//PHP 會強制要求這些類型必須完全匹配，否則會拋出 TypeError 異常。
require "../db_connect.php";
require "../message_display.php";
require "verify_librarian.php";
require "header_librarian.php";
?>

<html>
<head>
	<title>Welcome</title>
	<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
	<link rel="stylesheet" type="text/css" href="../member/css/home_style.css">
	<link rel="stylesheet" type="text/css" href="../css/custom_radio_button_style.css">
</head>
<body>
<?php
	ob_start();
	echo "<form class='cd-form' method='GET' action=''>";
    echo "<div class='search-container'>";
    echo "<input type='text' name='search' placeholder='Title/Author/ISBN...' value='" . (isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '') . "'>";
    echo "<select name='search_type'>";
    echo "<option value='all'" . (isset($_GET['search_type']) && $_GET['search_type'] == 'all' ? ' selected' : '') . ">All</option>";
    echo "<option value='title'" . (isset($_GET['search_type']) && $_GET['search_type'] == 'title' ? ' selected' : '') . ">Title</option>";
    echo "<option value='author'" . (isset($_GET['search_type']) && $_GET['search_type'] == 'author' ? ' selected' : '') . ">Author</option>";
    echo "<option value='isbn'" . (isset($_GET['search_type']) && $_GET['search_type'] == 'isbn' ? ' selected' : '') . ">ISBN</option>";
    echo "</select>";
    echo "<input type='submit' value='Search'>";
    echo "</div>";
    echo "</form>";
	 $search = isset($_GET['search']) ? $_GET['search'] : '';
    $search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'all';
    
    if ($search != '') {
        switch($search_type) {
            case 'title':
                $query = $con->prepare("SELECT * FROM book WHERE title LIKE ? ORDER BY title");
                $search_term = "%$search%";
                $query->bind_param("s", $search_term);
                break;
            case 'author':
                $query = $con->prepare("SELECT * FROM book WHERE author LIKE ? ORDER BY title");
                $search_term = "%$search%";
                $query->bind_param("s", $search_term);
                break;
            case 'isbn':
                $query = $con->prepare("SELECT * FROM book WHERE isbn LIKE ? ORDER BY title");
                $search_term = "%$search%";
                $query->bind_param("s", $search_term);
                break;
            default:
                $query = $con->prepare("SELECT * FROM book WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? ORDER BY title");
                $search_term = "%$search%";
                $query->bind_param("sss", $search_term, $search_term, $search_term);
				break;
			}
			$query->execute();
			$result = mysqli_stmt_get_result($query);
    }
	else {
	$query = $con->prepare("SELECT * FROM book ORDER BY title");
	$query->execute();
	$result = mysqli_stmt_get_result($query);
		if (!$result) {
			die("ERROR: Couldn't fetch books");
		}
	}
	$rows = mysqli_num_rows($result);
	if ($rows === 0) {
		echo "<h2 align='center'>No books available</h2>";
	} else {
		echo "<form class='cd-form' method='POST' action='#'>";
		echo "<legend>All books</legend>";
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
		echo "<br /><br /><input type='submit' name='m_request' value='detele book' />";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;"; // 插入4個空格
		echo "</form>";
	}

	if (isset($_POST['m_request'])) {
        $query->close();
		$query = $con->prepare("SELECT balance FROM member WHERE account = ?");
		$query->bind_param("s", $_SESSION['account']);
		$query->execute();
		$query->bind_result($balance); // 將結果綁定到變數 $balance
		$query->fetch();              // 提取結果
		$selectedBook = $_POST['rd_book'] ?? null;
		if (empty($selectedBook)) {
			$_SESSION['error_message'] = "Please select a book to issue";
			//echo error_without_field("Please select a book to issue");
		}
		else {
            $query->close();
            // 先取得書籍資訊
            $query = $con->prepare("SELECT title FROM book WHERE isbn = ?");
            $query->bind_param("s", $selectedBook);
            $query->execute();
            $query->bind_result($bookTitle);
            $query->fetch();
            $query->close();


            
			$log_query = $con->prepare("INSERT INTO activity_logs (account, time, action, details) VALUES (?, NOW(), 'deleted book', ?);");
			$log_query->bind_param("ss", $_SESSION['account'], $selectedBook);
			if (!$log_query->execute()) {
				$_SESSION['error_message'] = "Couldn't log the deletion";
				die();
			}
            $log_query->close();

            $query1 = $con->prepare("SELECT member FROM borrowedbooks WHERE book_isbn  = ?");
            $query1->bind_param("s", $selectedBook);
            $query1->execute();
            $result1 = mysqli_stmt_get_result($query1);
            while ($row1 = mysqli_fetch_assoc($result1)) {
                $member = $row1['member'];
                $update_query = $con->prepare("UPDATE member SET balance = balance + 1 WHERE account = ?");
                $update_query->bind_param("s", $member);
                $update_query->execute();
                $update_query->close();
            }
            $query1->close();

            $query = $con->prepare("DELETE FROM book WHERE isbn = ?");
            $query->bind_param("s", $selectedBook);
            if (!$query->execute()) {
                $_SESSION['error_message'] = "Couldn't delete book";
                die();
            }
            else
                $_SESSION['success_message'] = "Book $bookTitle deleted successfully";
						
									
			
		}
		header("Location: " . $_SERVER['REQUEST_URI']);
		exit(); // 停止腳本執行
	}
	if (isset($_SESSION['success_message'])) {
		echo success($_SESSION['success_message']);
		unset($_SESSION['success_message']); // 清除訊息
	}
	if (isset($_SESSION['error_message'])) {
		echo error_without_field($_SESSION['error_message']);
		unset($_SESSION['error_message']); // 皜���方�����
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
		<style>
.search-container {
    margin: 20px 0;
    padding: 10px;

    border-radius: 5px;
}

.search-container input[type="text"] {
    width: 50%;
    padding: 8px;
    margin-right: 10px;
}

.search-container select {
    padding: 8px;
    margin-right: 10px;
}

.search-container input[type="submit"] {
    padding: 8px 15px;
    background: #9f685bba;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}
</style>
</body>
</html>
