<?php
$connection = mysqli_connect("localhost", "root", "", "PharmEasy");
$query = "SELECT user_id, user_password FROM user";
$result = mysqli_query($connection, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $hashed_password = password_hash($row['user_password'], PASSWORD_DEFAULT);
    $user_id = $row['user_id'];
    $update_query = "UPDATE user SET user_password = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($connection, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
echo "Passwords hashed successfully!";
?>