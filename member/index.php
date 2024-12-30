<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "../verify_logged_out.php";
	require "../header.php";
?>

<html>
	<head>
		<title>Member Login</title>
		<!--  -->
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css">
		<link rel="stylesheet" type="text/css" href="css/index_style.css">
	</head>
	<body>
	<form class="cd-form" method="POST" action="#">
        <legend>Member Login</legend>
        <div class="error-message" id="error-message">
            <p id="error"></p>
        </div>
        <div class="icon">
            <input class="m-user" type="text" name="m_user" placeholder="account" required />
        </div>
        <div class="icon">
            <input class="m-pass" type="password" name="m_pass" placeholder="Password" required />
        </div>
        <input type="submit" value="Login" name="m_login" />
        <p>Don't have an account?&nbsp;<a href="register.php">Sign up</a></p>
    </form>
</body>

	
	<?php
		// Check if the login form has been submitted
		if(isset($_POST['m_login']))
		{
			// Retrieve the account and password from the form
			$m_user = $_POST['m_user'];
			$m_pass = $_POST['m_pass'];
			// Prepare a SQL statement to select the member's details based on the account
			//$query = $con->prepare("SELECT id, balance, password FROM member WHERE account = ?");
			$query = $con->prepare("SELECT * FROM member WHERE account = ? AND password = ?");
            $l_pass = sha1($_POST['m_pass']);
            $query->bind_param("ss",$m_user, $l_pass );
			$query->execute();
			$result = $query->get_result();
			
			// Check if the account exists in the database
			if($result->num_rows != 1) {
				// Display an error message if the account/password combination is invalid
				echo error_without_field("Invalid account/password combination");
			} else {
				// Fetch the result row as an associative array
				$resultRow = $result->fetch_assoc();
				// Verify the password
                //close
            $query->close();
           // $query = $con->prepare("SELECT id FROM member WHERE account = ? AND password = ?;");
            $query = $con->prepare("SELECT * FROM member WHERE account = ? AND password = ?");
			$l_user = $_POST['m_user'];
			$l_pass = sha1($_POST['m_pass']);
            $query->bind_param("ss",$m_user, $l_pass );
			$query->execute();
			$result = $query->get_result();

			if(mysqli_num_rows($result) != 1)
				echo error_without_field("Invalid account/password combination");
            else {
                $log_query = $con->prepare("INSERT INTO activity_logs (account, time, action) VALUES (?, NOW(), 'login');");
				$log_query->bind_param("s", $l_user);
				$log_query->execute();
				$log_query->close();
                // Set session variables for the logged-in member
                $_SESSION['type'] = "member";
                //$_SESSION['id'] = $resultRow['id'];
                $_SESSION['account'] = $m_user;
                // Redirect to the member's home page
                header('Location: home.php');
					
				}
			}
		}
	?>
	
</html>