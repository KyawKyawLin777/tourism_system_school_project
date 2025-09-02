<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkCustomerLogin()
{
    if (!isset($_SESSION['customer_id'])) {
        header('Location: login.php');
        exit();
    }
}

function isCustomerLoggedIn()
{
    return isset($_SESSION['customer_id']);
}

// function customerLogout()
// {
//     session_destroy();
//     header('Location: login.php');
//     exit();
// }

function getCustomerInfo()
{
    return [
        'id' => $_SESSION['customer_id'] ?? null,
        'name' => $_SESSION['customer_name'] ?? 'Customer',
        'email' => $_SESSION['customer_email'] ?? ''
    ];
}

function loginCustomer($email, $password, $db)
{
    try {
        $query = "SELECT id, full_name, email, password FROM customers WHERE email = ? LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['full_name'];
            $_SESSION['customer_email'] = $customer['email'];
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Customer login error: " . $e->getMessage());
        return false;
    }
}

function registerCustomer($full_name, $email, $phone, $address, $password, $db)
{
    try {
        // Check if email already exists
        $check_query = "SELECT id FROM customers WHERE email = ? LIMIT 1";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(1, $email);
        $check_stmt->execute();

        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new customer
        $query = "INSERT INTO customers (full_name, email, phone, address, password) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$full_name, $email, $phone, $address, $hashed_password])) {
            $customer_id = $db->lastInsertId();
            $_SESSION['customer_id'] = $customer_id;
            $_SESSION['customer_name'] = $full_name;
            $_SESSION['customer_email'] = $email;
            return ['success' => true, 'message' => 'Registration successful'];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    } catch (PDOException $e) {
        error_log("Customer registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed'];
    }
}


function customerLogout()
{
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../index.php'); // root index.php သို့ redirect
    exit();
}
