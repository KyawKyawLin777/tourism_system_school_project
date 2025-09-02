<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

function getAdminInfo() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? null,
        'name' => $_SESSION['admin_name'] ?? 'Admin',
        'role' => $_SESSION['admin_role'] ?? 'Administrator'
    ];
}

function hasPermission($required_role = 'Admin') {
    $admin_role = $_SESSION['admin_role'] ?? '';
    
    // Define role hierarchy
    $roles = ['Super Admin' => 3, 'Admin' => 2, 'Manager' => 1];
    
    $user_level = $roles[$admin_role] ?? 0;
    $required_level = $roles[$required_role] ?? 2;
    
    return $user_level >= $required_level;
}
?>
