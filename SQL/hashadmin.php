<?php
session_start();
include "includes/functions.php"; // Ensure the path to functions.php is correct

$connection = mysqli_connect("localhost", "root", "", "PharmEasy");
// $connection = mysqli_connect("localhost", "id18666014_md_taha_ahmed", "bGCL0+&4qT64IM_{", "id18666014_pharmeasy");
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "SELECT admin_id, admin_email, admin_password FROM admin";
$admins = query($query);

if (empty($admins)) {
    echo "No admins found or query failed.";
}
 else {
    foreach ($admins as $admin) {
        // Check if the password is not already hashed
        if (!password_get_info($admin['admin_password'])['algo']) {
            $hashed_password = password_hash($admin['admin_password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE admin SET admin_password='$hashed_password' WHERE admin_id='{$admin['admin_id']}'";
            if (single_query($update_query)) {
                echo "Password for admin {$admin['admin_email']} hashed successfully.<br>";
            } else {
                echo "Failed to hash password for admin {$admin['admin_email']}.<br>";
            }
        } else {
            echo "Password for admin {$admin['admin_email']} is already hashed.<br>";
        }
    }
    echo "Password hashing process completed.";
}
