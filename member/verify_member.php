<?php
session_start();

if (empty($_SESSION['type'])) {
	header("Location: ..");
	exit;
}

if ($_SESSION['type'] === "librarian") {
	header("Location: ../librarian/home.php");
	exit;
}
?>