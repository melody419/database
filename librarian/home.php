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
			<div id="Customer_list">
				<a href="Customer_list.php">
					<input type="button" value="Customer list" class="button-with-image"/>
				</a><br />
			</div>

			<div id="register">
				<a href="register.php">
					<input type="button" value="Add librarian account" class="button-with-image" />
				</a><br />
			</div>	

			<div id="insert_book">
				<a href="insert_book.php">
					<input type="button" value="Add a new book" class="button-with-image" />
				</a><br />
			</div>

			<div id="update_copies">
				<a href="update_copies.php">
					<input type="button" value="Update copies of a book" class="button-with-image" />
				</a><br />
			</div>

			<div id="update_balancet">
				<a href="update_balance.php">
					<input type="button" value="Update balance of a member" class="button-with-image" style="white-space: normal;"/>
				</a><br />
			</div>

			<div id="view_borrowed">
				<a href="view_borrowed.php">
					<input type="button" value="Browse borrowing status" class="button-with-image" />
				</a><br />
			</div>

			<div id="del">
				<a href="del.php">
					<input type="button" value="Delete books" class="button-with-image" />
				</a><br />
			</div>
	
			<div id="view activity_logs">
				<a href="view_act.php">
					<input type="button" value="view activity_logs" class="button-with-image" />
				</a><br />
			</div>

			<div id="decorate">
				
					<input type="button" value="To be continue..." class="button-with-image" onclick="changeBackgroundColor()" />
				<br />
			</div>
			<script>
				const colors = ["rgb(230, 223, 215)","rgb(240, 207, 168)"]; 
				let currentColorIndex = 0;

				function changeBackgroundColor() {
					currentColorIndex = (currentColorIndex + 1) % colors.length; 
					document.body.style.backgroundColor = colors[currentColorIndex];
				}
			</script>

		</div>
	</body>
</html>
