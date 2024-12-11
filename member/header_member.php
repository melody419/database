<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,700">
	<link rel="stylesheet" type="text/css" href="/Library/member/css/header_member_style.css">
	<title>Library</title>
</head>
<body>
	<header>
		<div id="cd-logo">
			<a href="../">
				<img src="img/ic_logo.svg" alt="Logo">
				<p>CSIE LIBRARY</p>
			</a>
		</div>
		
		<div class="dropdown">
			<button class="dropbtn">
				<p id="librarian-name"><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></p>
			</button>
			<div class="dropdown-content">
				<a>
					<?php
						$query = $con->prepare("SELECT balance FROM member WHERE username = ?;");
						$query->bind_param("s", $_SESSION['username']);
						$query->execute();
						$result = $query->get_result();
						$balance = $result->fetch_array(MYSQLI_ASSOC)['balance'] ?? 0;
						echo htmlspecialchars((string)$balance, ENT_QUOTES, 'UTF-8') . "/3 books Available";
					?>
				</a>
				<a href="my_books.php">My books</a>
				<a href="wish.php">My favorite</a>
				<a href="../logout.php">Logout</a>
			</div>
		</div>
	</header>
</body>
</html>