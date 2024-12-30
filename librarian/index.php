<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "../verify_logged_out.php";
	require "../header.php";
?>

<html>
	<head>
		<title>Librarian Login</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css">
		<link rel="stylesheet" type="text/css" href="css/index_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
		
		<legend>Librarian Login</legend>
		
			<div class="error-message" id="error-message">
				<p id="error"></p>
			</div>
			
			<div class="icon">
				<input class="l-user" type="text" name="l_user" placeholder="account" required />
			</div>
			
			<div class="icon">
				<input class="l-pass" type="password" name="l_pass" placeholder="Password" required />
			</div>
			
			<input type="submit" value="Login" name="l_login"/>
			
		</form>
	</body>
	
	<?php
		if(isset($_POST['l_login']))
		{	
			$query = $con->prepare("SELECT * FROM librarian WHERE password = ? AND account = ?;");
			$l_user = $_POST['l_user'];
			$l_pass = sha1($_POST['l_pass']);
			$query->bind_param("ss", $l_pass,$l_user);
			$query->execute();
			$result = $query->get_result();
			$row = $result->fetch_assoc();
			if(mysqli_num_rows($result) != 1)
				echo error_without_field("Invalid account/password combination");
			else
			{
				
				$log_query = $con->prepare("INSERT INTO activity_logs (account, time, action) VALUES (?, NOW(), 'login(librarian)');");
				$log_query->bind_param("s", $_POST['l_user']);
				$log_query->execute();
				$log_query->close();
				$_SESSION['type'] = "librarian";		
				$_SESSION['account'] = $_POST['l_user'];
				header('Location: home.php');
			}
			$query->close();
		}
	?>
	
</html>