<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
	<head>
		<title>Update balance</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css" />
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css" />
		<link rel="stylesheet" href="css/update_balance_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
			<legend>set the balance</legend>
			
				<div class="error-message" id="error-message">
					<p id="error"></p>
				</div>
				
				<div class="icon">
					<input class="m-user" type='text' name='m_user' id="m_user" placeholder="Member account" required />
				</div>
				
				<div class="icon">
					<input class="m-balance" type="number" name="m_balance" placeholder="Borrowing balance" required />
				</div>
				
				<input type="submit" name="m_add" value="Set Balance" />
		</form>
	</body>
	
	<?php
		if(isset($_POST['m_add']))
		{
			$query = $con->prepare("SELECT account FROM member WHERE account = ?;");
			$query->bind_param("s", $_POST['m_user']);
			$query->execute();
			if(mysqli_num_rows($query->get_result()) != 1)
				echo error_with_field("Invalid account", "m_user");
			else
			{
				$query = $con->prepare("UPDATE member SET balance = balance + ? WHERE account = ?;");
				$query->bind_param("ds", $_POST['m_balance'], $_POST['m_user']);
				if(!$query->execute())
					die(error_without_field("ERROR: Couldn\'t add balance"));
				echo success("Balance successfully updated");
			}
		}
	?>
</html>