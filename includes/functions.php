<?php
$connection = mysqli_connect("localhost", "root", "", "PharmEasy");
// $connection = mysqli_connect("localhost", "id18666014_md_taha_ahmed", "bGCL0+&4qT64IM_{", "id18666014_pharmeasy");

function post_redirect($url)
{
    ob_start();
    header('Location: ' . $url);
    ob_end_flush();
    die();
}

function get_redirect($url)
{
    echo " <script> 
    window.location.href = '" . $url . "'; 
    </script>";
}

function query($query)
{
    global $connection;
    $run = mysqli_query($connection, $query);
    if ($run) {
        while ($row = $run->fetch_assoc()) {
            $data[] = $row;
        }
        return !empty($data) ? $data : [];
    }
    return [];
}

function single_query($query)
{
    global $connection;
    if (mysqli_query($connection, $query)) {
        return "done";
    } else {
        die("no data: " . mysqli_connect_error($connection));
    }
}

function login()
{
    if (isset($_POST['login'])) {
        $userEmail = trim(strtolower($_POST['userEmail']));
        $password = trim($_POST['password']);
        if (empty($userEmail) or empty($password)) {
            $_SESSION['message'] = "empty_err";
            post_redirect("login.php");
        }
        global $connection;
        $query = "SELECT email, user_id, user_password FROM user WHERE email = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $userEmail);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (empty($data)) {
            $_SESSION['message'] = "loginErr";
            post_redirect("login.php");
        } elseif (password_verify($password, $data['user_password']) && $userEmail == $data['email']) {
            $_SESSION['user_id'] = $data['user_id'];
            post_redirect("index.php");
        } else {
            $_SESSION['message'] = "loginErr";
            post_redirect("login.php");
        }
    }
}

function singUp()
{
    if (isset($_POST['singUp'])) {
        $email = trim(strtolower($_POST['email']));
        $fname = trim($_POST['Fname']);
        $lname = trim($_POST['Lname']);
        $address = trim($_POST['address']);
        $passwd = trim($_POST['passwd']);
        if (empty($email) or empty($passwd) or empty($address) or empty($fname) or empty($lname)) {
            $_SESSION['message'] = "empty_err";
            post_redirect("signUp.php");
        } elseif (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
            $_SESSION['message'] = "signup_err_email";
            post_redirect("signUp.php");
        } elseif (!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,30}$/', $passwd)) {
            $_SESSION['message'] = "signup_err_password";
            post_redirect("signUp.php");
        }
        global $connection;
        $query = "SELECT email FROM user WHERE email = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        if (!empty($data)) {
            $_SESSION['message'] = "usedEmail";
            post_redirect("signUp.php");
        }

        $hashed_password = password_hash($passwd, PASSWORD_DEFAULT);

        $query = "INSERT INTO user (email, user_fname, user_lname, user_address, user_password) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $email, $fname, $lname, $address, $hashed_password);
        $queryStatus = mysqli_stmt_execute($stmt) ? "done" : "error";
        mysqli_stmt_close($stmt);

        if ($queryStatus == "done") {
            $query = "SELECT user_id FROM user WHERE email = ?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $data = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $data['user_id'];
            mysqli_stmt_close($stmt);
            post_redirect("index.php");
        } else {
            $_SESSION['message'] = "wentWrong";
            post_redirect("signUp.php");
        }
    }
}

function message()
{
    if (isset($_SESSION['message'])) {
        if ($_SESSION['message'] == "signup_err_password") {
            echo "   <div class='alert alert-danger' role='alert'>
        Please enter the password in correct form !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "loginErr") {
            echo "   <div class='alert alert-danger' role='alert'>
        The email or the password is incorrect !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "usedEmail") {
            echo "   <div class='alert alert-danger' role='alert'>
        This email is already used !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "wentWrong") {
            echo "   <div class='alert alert-danger' role='alert'>
        Something went wrong !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "empty_err") {
            echo "   <div class='alert alert-danger' role='alert'>
        Please don't leave anything empty !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "signup_err_email") {
            echo "   <div class='alert alert-danger' role='alert'>
        Please enter the email in the correct form !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "insufficient_stock") {
            echo "   <div class='alert alert-danger' role='alert'>
        Insufficient stock for one or more items !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "invalid_quantity") {
            echo "   <div class='alert alert-danger' role='alert'>
        Invalid quantity specified !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "order_success") {
            echo "   <div class='alert alert-success' role='alert'>
        Order placed successfully !!!
      </div>";
            unset($_SESSION['message']);
        }
    }
}

function search()
{
    if (isset($_GET['search'])) {
        $search_text = $_GET['search_text'];
        if ($search_text == "") {
            return;
        }
        $query = "SELECT * FROM item WHERE item_tags LIKE '%$search_text%'";
        $data = query($query);
        return $data;
    } elseif (isset($_GET['cat'])) {
        $cat = $_GET['cat'];
        $query = "SELECT * FROM item WHERE item_cat='$cat' ORDER BY RAND()";
        $data = query($query);
        return $data;
    }
}

function all_products()
{
    $query = "SELECT * FROM item ORDER BY RAND()";
    $data = query($query);
    return $data;
}

function total_price($data)
{
    $sum = 0;
    foreach ($data as $item) {
        if (isset($item['item_price']) && isset($item['quantity'])) {
            $sum += ($item['item_price'] * $item['quantity']);
        }
    }
    return $sum;
}

function get_item()
{
    if (isset($_GET['product_id'])) {
        $_SESSION['item_id'] = $_GET['product_id'];
        $id = $_GET['product_id'];
        $query = "SELECT * FROM item WHERE item_id='$id'";
        $data = query($query);
        return $data;
    }
}

function add_cart($item_id)
{
    global $connection;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

    if (empty($user_id)) {
        $_SESSION['message'] = "loginErr";
        get_redirect("login.php");
        return;
    }

    if ($quantity <= 0) {
        $_SESSION['message'] = "invalid_quantity";
        get_redirect("product.php?product_id=" . $item_id);
        return;
    }

    // Validate stock availability
    $query = "SELECT item_quantity FROM item WHERE item_id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $item = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (empty($item)) {
        $_SESSION['message'] = "wentWrong";
        get_redirect("product.php?product_id=" . $item_id);
        return;
    }

    if ($item['item_quantity'] < $quantity) {
        $_SESSION['message'] = "insufficient_stock";
        get_redirect("product.php?product_id=" . $item_id);
        return;
    }

    // Add to cart
    if (isset($_SESSION['cart'])) {
        $item_exists = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['item_id'] == $item_id) {
                $new_quantity = $cart_item['quantity'] + $quantity;
                // Re-check stock for updated quantity
                if ($item['item_quantity'] < $new_quantity) {
                    $_SESSION['message'] = "insufficient_stock";
                    get_redirect("product.php?product_id=" . $item_id);
                    return;
                }
                $cart_item['quantity'] = $new_quantity;
                $item_exists = true;
                break;
            }
        }
        if (!$item_exists) {
            $_SESSION['cart'][] = array('user_id' => $user_id, 'item_id' => $item_id, 'quantity' => $quantity);
        }
    } else {
        $_SESSION['cart'] = array(array('user_id' => $user_id, 'item_id' => $item_id, 'quantity' => $quantity));
    }

    if (isset($_GET['cart'])) {
        get_redirect("product.php?product_id=" . $item_id);
    } elseif (isset($_GET['buy'])) {
        get_redirect("cart.php");
    }
}

function get_cart()
{
    global $connection;
    $data = [];
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $cart_item) {
            $item_id = $cart_item['item_id'];
            $query = "SELECT item_id, item_image, item_title, item_quantity, item_price, item_brand FROM item WHERE item_id = ?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "i", $item_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $item = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if (!empty($item)) {
                $item['quantity'] = $cart_item['quantity'];
                $data[] = $item;
            }
        }
    }
    return $data;
}

function delete_from_cart()
{
    if (isset($_GET['delete'])) {
        $item_id = $_GET['delete'];
        $num = sizeof($_SESSION['cart']);
        for ($i = 0; $i < $num; $i++) {
            if ($_SESSION['cart'][$i]['item_id'] == $item_id) {
                unset($_SESSION['cart'][$i]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
        get_redirect("cart.php");
    } elseif (isset($_GET['delete_all'])) {
        unset($_SESSION['cart']);
        get_redirect("cart.php");
    }
}

function add_order()
{
    global $connection;
    if (isset($_GET['order']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        date_default_timezone_set("Asia/Kolkata");
        $date = date("Y-m-d H:i:s");

        // Start transaction
        mysqli_begin_transaction($connection);

        try {
            foreach ($_SESSION['cart'] as $cart_item) {
                $item_id = $cart_item['item_id'];
                $user_id = $cart_item['user_id'];
                $quantity = (int)$cart_item['quantity'];

                if ($quantity <= 0) {
                    throw new Exception("Invalid quantity for item ID: $item_id");
                }

                // Validate stock
                $query = "SELECT item_quantity FROM item WHERE item_id = ?";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "i", $item_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $item = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);

                if (empty($item)) {
                    throw new Exception("Item not found: $item_id");
                }

                if ($item['item_quantity'] < $quantity) {
                    throw new Exception("Insufficient stock for item ID: $item_id");
                }

                // Insert order
                $query = "INSERT INTO orders (user_id, item_id, order_quantity, order_date, order_status) VALUES (?, ?, ?, ?, 0)";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "iiss", $user_id, $item_id, $quantity, $date);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to create order for item ID: $item_id");
                }
                mysqli_stmt_close($stmt);

                // Update item quantity
                $new_quantity = $item['item_quantity'] - $quantity;
                $query = "UPDATE item SET item_quantity = ? WHERE item_id = ?";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $item_id);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to update stock for item ID: $item_id");
                }
                mysqli_stmt_close($stmt);
            }

            // Commit transaction
            mysqli_commit($connection);
            unset($_SESSION['cart']);
            $_SESSION['message'] = "order_success";
            get_redirect("final.php");
        } catch (Exception $e) {
            mysqli_rollback($connection);
            $_SESSION['message'] = "wentWrong";
            get_redirect("cart.php");
        }
    }
}

function check_user($id)
{
    $query = "SELECT user_id FROM user WHERE user_id = ?";
    $data = query($query);
    return !empty($data) ? 1 : 0;
}

function get_user($id)
{
    $query = "SELECT user_id, user_fname, user_lname, email, user_address FROM user WHERE user_id = ?";
    $data = query($query);
    return $data;
}

function get_item_id($id)
{
    $query = "SELECT * FROM item WHERE item_id = ?";
    $data = query($query);
    return $data;
}