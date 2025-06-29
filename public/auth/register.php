<?php
require_once '../../config.php';
// Any other PHP logic for the registration page can go here.

// If you want to set the title dynamically from this page
$page_title = 'Register - Inventory Manager';

// Include the header, which contains the opening HTML, head, and body tags
include '../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card mt-5">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Register</h2>
                <form action="../../handlers/auth/register_handler.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" name="email" id="email" class="form-control" required placeholder="Enter your email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
                <p class="text-center mt-3 mb-0">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

