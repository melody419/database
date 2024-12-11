<?php
// See the password_hash() example to see where this came from.
//$hash = '$2y$10$.vGA1O9wmRjrwAVXD98HNOgsNpDczlqm3Jq7KnEd1rVAGv3Fykk1a';
$hash ='$2y$10$VciPbXeihWCt5gONIaQORes4ScaphEByo6oxuyYErm74W30Z0eTvG';
$hashedPassword = password_hash('melody', PASSWORD_DEFAULT);
if (password_verify('melody', '$2y$10$n.LRMXgXKxw4xhPfjhiTWuNd9vaShtG2U')) {
    echo 'Password is valid!<br>';
    echo $hashedPassword;
} else {
    echo 'Invalid password.';
    //echo error_without_field("Please select a book to issue");
    
}
?>
