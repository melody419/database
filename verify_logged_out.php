<?php
session_start();

if (empty($_SESSION['type'])) {
	// Do nothing, user is logged out
} else if ($_SESSION['type'] === "librarian") {
	header("Location: ../librarian/home.php");
	exit();
} else if ($_SESSION['type'] === "member") {
	header("Location: ../member/home.php");
	exit();
}
?>