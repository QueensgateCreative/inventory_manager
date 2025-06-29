<?php
require_once '../../config.php';
?>
<form action="../../handlers/auth/login_handler.php" method="POST">
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>