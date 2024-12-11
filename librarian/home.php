<?php
	require "../db_connect.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
	<head>
		<title>Welcome</title>
		<link rel="stylesheet" type="text/css" href="css/home_style.css" />
	</head>
	<body>
		<div id="allTheThings">
			<a href="user.php">
				<input type="button" value="Customer list." />
			</a><br />
			<a href="register.php">
				<input type="button" value="Add librarian account" />
			</a><br />
			<a href="insert_book.php">
				<input type="button" value="Add a new book" />
			</a><br />
			<a href="update_copies.php">
				<input type="button" value="Update copies of a book" />
			</a><br />
			<a href="update_balance.php">
				<input type="button" value="Update balance of a member" />
			</a><br />
			<a href="view_borrowed.php">
				<input type="button" value="Browse borrowing status." />
			</a><br /><br />
		</div>
	</body>
</html>