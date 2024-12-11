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
		<!--
		<style>
		
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .cd-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .cd-form legend {
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }
        .cd-form .icon {
            margin-bottom: 15px;
        }
        .cd-form input[type="text"],
        .cd-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .cd-form input[type="submit"] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .cd-form input[type="submit"]:hover {
            background-color: #45a049;
        }
        .cd-form .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        .cd-form p {
            text-align: center;
        }
        .cd-form p a {
            color: #4CAF50;
            text-decoration: none;
        }
        .cd-form p a:hover {
            text-decoration: underline;
        }
    </style>
	-->
	</head>
	<body>
	<form class="cd-form" method="POST" action="#">
        <legend>Member Login</legend>
        <div class="error-message" id="error-message">
            <p id="error"></p>
        </div>
        <div class="icon">
            <input class="m-user" type="text" name="m_user" placeholder="Username" required />
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
			// Retrieve the username and password from the form
			$m_user = $_POST['m_user'];
			$m_pass = $_POST['m_pass'];

			// Prepare a SQL statement to select the member's details based on the username
			//$query = $con->prepare("SELECT id, balance, password FROM member WHERE username = ?");
			$query = $con->prepare("SELECT * FROM member WHERE username = ? AND password = ?");
            $l_pass = sha1($_POST['m_pass']);
            $query->bind_param("ss",$m_user, $l_pass );
			$query->execute();
			$result = $query->get_result();
			
			// Check if the username exists in the database
			if($result->num_rows != 1) {
				// Display an error message if the username/password combination is invalid
				echo error_without_field("Invalid username/password combination");
			} else {
				// Fetch the result row as an associative array
				$resultRow = $result->fetch_assoc();
				// Verify the password
                //close
            $query->close();
           // $query = $con->prepare("SELECT id FROM member WHERE username = ? AND password = ?;");
            $query = $con->prepare("SELECT * FROM member WHERE username = ? AND password = ?");
			$l_user = $_POST['m_user'];
			$l_pass = sha1($_POST['m_pass']);
            $query->bind_param("ss",$m_user, $l_pass );
			$query->execute();
			$result = $query->get_result();

			if(mysqli_num_rows($result) != 1)
				echo error_without_field("Invalid username/password combination");
/*
			$hashedPassword = password_hash($resultRow['password'], PASSWORD_DEFAULT);
				if (!password_verify($m_pass, $hashedPassword)) {
					// Display an error message if the password is incorrect
					//echo error_without_field($m_pass."  ".$hashedPassword);
					echo error_without_field("Invalid username/password combination");
				} */
            else {
					// Check if the account balance is negative
					$balance = $resultRow['balance'];
					if($balance < 0) {
						// Display an error message if the account is suspended
						echo error_without_field("Your account has no borrowing balance");
					} else {
						// Set session variables for the logged-in member
						$_SESSION['type'] = "member";
						//$_SESSION['id'] = $resultRow['id'];
						$_SESSION['username'] = $m_user;
						// Redirect to the member's home page
						header('Location: home.php');
					}
				}
			}
		}
	?>
	
</html>