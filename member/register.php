<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "../header.php";
?>

<html>
	<head>
		<title>Register</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css">
		<link rel="stylesheet" href="css/register_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
			<legend>Register</legend>
			
				<div class="error-message" id="error-message">
					<p id="error"></p>
				</div>
				
				<div class="icon">
					<input class="m-user" type="text" name="m_user" id="m_user" placeholder="account" required />
				</div>
				
				<div class="icon">
					<input class="m-pass" type="password" name="m_pass" placeholder="Password" required />
				</div>
				<div class="icon">
					<input class="m-pass" type="password" name="m_pass1" placeholder="Confirm Password" required />
				</div>
				
				<div class="icon">
					<input class="m-name" type="text" name="m_name" placeholder="Full Name" required />
				</div>
				
				<div class="icon">
					<input class="m-email" type="email" name="m_email" id="m_email" placeholder="Email" required />
				</div>
				<br />
				<input type="submit" name="m_register" value="Register" />
		</form>
	</body>
	
	<?php
		if(isset($_POST['m_register']))
		{			
				$query = $con->prepare("(SELECT account FROM member WHERE account = ?)");
				$query->bind_param("s", $_POST['m_user']);
				$query->execute();
				if(mysqli_num_rows($query->get_result()) != 0)
					echo error_with_field("The account you entered is already exist", "m_user");
				else
				{
					// 將表單資料賦值給變數
				$m_user = $_POST['m_user'];
				$m_pass = $_POST['m_pass'];
				$m_pass1 = $_POST['m_pass1'];
				$m_name = $_POST['m_name'];
				$m_email = $_POST['m_email'];
				$m_balance = 3;

					$query = $con->prepare("(SELECT email FROM member WHERE email = ?);");
					$query->bind_param("s", $_POST['m_email']);
					$query->execute();
					if(mysqli_num_rows($query->get_result()) != 0)
						echo error_with_field("The email you entered is already exist", "m_email");
					else if($m_pass1 != $m_pass)
						echo error_with_field("The passwords don't match", "m_pass");
					else
					{	$query->close();
						$query2 = $con->prepare("INSERT INTO member(account, password, name, email, balance) VALUES(?, ?, ?, ?, ?);");					
// 使用變數來綁定參數
						$m_pass = sha1($m_pass);
						$query2->bind_param("ssssd", $m_user, $m_pass, $m_name, $m_email, $m_balance);
						if($query2->execute()){
							echo success("Successfully registered.");
							$log_query = $con->prepare("INSERT INTO activity_logs (account, time, action) VALUES (?, NOW(), 'register');");
							$log_query->bind_param("s", $m_user);
							$log_query->execute();
							$log_query->close();
						}
							
						else
							echo error_without_field("Please try again later");
					}
				}
			
		}
	?>
	
</html>