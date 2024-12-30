<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
<head>
	<title>Add book</title>
	<link rel="stylesheet" type="text/css" href="../css/global_styles.css" />
	<link rel="stylesheet" type="text/css" href="../css/form_styles.css" />
	<link rel="stylesheet" href="css/insert_book_style.css">
</head>
<body>
	<form class="cd-form" method="POST" action="insert_book.php" enctype="multipart/form-data">
		<legend>Upload book</legend>
		
		<div class="error-message" id="error-message">
			<p id="error"></p>
		</div>
		
		<div class="icon">
			<input class="b-isbn" id="b_isbn" type="text" name="b_isbn" placeholder="ISBN" required />
		</div>
		
		<div class="icon">
		<input class="b-title" type="text" name="b_title" placeholder="Title" required />
		</div>

		<div class="icon">
		<input class="b-isbn" type="file" name="pdf" accept="application/pdf" required />
		<label>upload</label>
		</div>
		<div class="icon">
			<input class="b-author" type="text" name="b_author" placeholder="Author" required />
		</div>

		<div>
			<h4>Category</h4>
			<p class="cd-select icon">
				<select class="b-category" name="b_category">
					<option>Algorithms and Data Structures</option>
					<option>Computer Systems and Architecture</option>
					<option>Artificial Intelligence and Machine Learning</option>
					<option>Non</option>
				</select>
			</p>
		</div>
						
		<div class="icon">
			<input class="b-copies" type="number" name="b_copies" placeholder="Copies" required />
		</div>
		
		</div>
		
		<br />
		<input class="b-isbn" type="submit" name="b_add" value="Add book" />
	</form>
</body>

	<?php
		if (isset($_POST['b_add'])) {
			// 檢查文件是否上傳成功
			if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == 0) {
				$fileData = file_get_contents($_FILES['pdf']['tmp_name']);
				$title = $_POST['b_title'];	
				echo "文件已成功上傳！";
			} else {
				echo "There was an error uploading the file.";
			}

			$query = $con->prepare("SELECT isbn FROM book WHERE isbn = ?;");
			$query->bind_param("s", $_POST['b_isbn']);
			$query->execute();
			
			if(mysqli_num_rows($query->get_result()) != 0)
				echo error_with_field("A book with that ISBN already exists", "b_isbn");
				else
				{
					$query = $con->prepare("INSERT INTO book VALUES(?, ?, ?, ?, ?,?);");
					$query->bind_param("ssssib", $_POST['b_isbn'], $_POST['b_title'], $_POST['b_author'], $_POST['b_category'], $_POST['b_copies'],$fileData);
					
					if(!$query->execute())
						die(error_without_field("ERROR: Couldn't add book"));
					$query->close();

					$query = $con->prepare("INSERT INTO activity_logs (account,book_isbn ,action ) VALUES (?, ?, ?);");
					$action = "Added book";
					$query->bind_param("sss", $_SESSION['account'],  $_POST['b_isbn'],$action);
					
					if (!$query->execute()) {
						die(error_without_field("ERROR: Couldn't log activity"));
					}
					echo success("Successfully added book");
				}
		}		
	?>
</html>
