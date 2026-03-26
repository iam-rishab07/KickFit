<?php
/**
 * KickFit - Secure Logout Handler
 */

// 1. Initialize the session to access it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Unset all of the session variables
$_SESSION = array();

// 3. If it's desired to kill the session, also delete the session cookie.
// Note: This will completely log out the user from the browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Finally, destroy the session data on the server
session_destroy();

// 5. Redirect to the login page or homepage with a logout flag
// The 'logout=1' allows you to show a "Success" message on the landing page
header("Location: index.php?logout=1");
exit();
?>