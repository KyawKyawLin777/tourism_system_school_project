<?php
session_start(); // session အမြဲ အပေါ်ဆုံးမှာ

include_once 'auth.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// 1️⃣ Already logged in user redirect to index.php
if (isCustomerLoggedIn()) {
    header("Location: ../index.php"); // root index.php
    exit();
}

// 2️⃣ Handle POST login/register
if ($_POST) {
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (loginCustomer($email, $password, $db)) {
            header("Location: ../index.php"); // root index.php
            exit();
        } else {
            $message = '<div class="alert alert-danger">Invalid email or password</div>';
        }
    } elseif (isset($_POST['register'])) {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $message = '<div class="alert alert-danger">Passwords do not match</div>';
        } else {
            $result = registerCustomer($full_name, $email, $phone, $address, $password, $db);
            if ($result['success']) {
                header("Location: ../index.php"); // root index.php
                exit();
            } else {
                $message = '<div class="alert alert-danger">' . $result['message'] . '</div>';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Myanmar Tourism</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .nav-pills .nav-link {
            border-radius: 25px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
        }

        .btn {
            border-radius: 25px;
            padding: 12px 30px;
        }
    </style>
</head>

<body>
    <div class="login-container d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card login-card">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                                <h3 class="fw-bold">Myanmar Tourism</h3>
                                <p class="text-muted">Customer Portal</p>
                            </div>

                            <?php echo $message; ?>

                            <!-- Navigation Tabs -->
                            <ul class="nav nav-pills nav-justified mb-4" id="authTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="login-tab" data-bs-toggle="pill"
                                        data-bs-target="#login" type="button" role="tab">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="register-tab" data-bs-toggle="pill"
                                        data-bs-target="#register" type="button" role="tab">
                                        <i class="fas fa-user-plus me-2"></i>Register
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="authTabsContent">
                                <!-- Login Form -->
                                <div class="tab-pane fade show active" id="login" role="tabpanel">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="login_email" class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email Address
                                            </label>
                                            <input type="email" class="form-control" id="login_email"
                                                name="email" required>
                                        </div>

                                        <div class="mb-4">
                                            <label for="login_password" class="form-label">
                                                <i class="fas fa-lock me-2"></i>Password
                                            </label>
                                            <input type="password" class="form-control" id="login_password"
                                                name="password" required>
                                        </div>

                                        <button type="submit" name="login" class="btn btn-primary w-100 mb-3">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login
                                        </button>
                                    </form>
                                </div>

                                <!-- Register Form -->
                                <div class="tab-pane fade" id="register" role="tabpanel">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="full_name" class="form-label">
                                                <i class="fas fa-user me-2"></i>Full Name
                                            </label>
                                            <input type="text" class="form-control" id="full_name"
                                                name="full_name" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="register_email" class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email Address
                                            </label>
                                            <input type="email" class="form-control" id="register_email"
                                                name="email" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="phone" class="form-label">
                                                <i class="fas fa-phone me-2"></i>Phone Number
                                            </label>
                                            <input type="tel" class="form-control" id="phone"
                                                name="phone" maxlength="11"
                                                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                                required>
                                            <div class="form-text text-danger">Please enter up to 11 digits only.</div>
                                        </div>


                                        <div class="mb-3">
                                            <label for="address" class="form-label">
                                                <i class="fas fa-map-marker-alt me-2"></i>Address
                                            </label>
                                            <textarea class="form-control" id="address" name="address"
                                                rows="2" required></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="register_password" class="form-label">
                                                <i class="fas fa-lock me-2"></i>Password
                                            </label>
                                            <input type="password" class="form-control" id="register_password"
                                                name="password" required>
                                        </div>

                                        <div class="mb-4">
                                            <label for="confirm_password" class="form-label">
                                                <i class="fas fa-lock me-2"></i>Confirm Password
                                            </label>
                                            <input type="password" class="form-control" id="confirm_password"
                                                name="confirm_password" required>
                                        </div>

                                        <button type="submit" name="register" class="btn btn-success w-100 mb-3">
                                            <i class="fas fa-user-plus me-2"></i>Register
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <a href="../index.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Website
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>