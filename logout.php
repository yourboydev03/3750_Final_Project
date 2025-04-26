<?php
session_name('CALENDARSESSID');
session_start();

// Destroy PHP session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

// Clear cookie
setcookie('calendar_loggedIn', '', time() - 3600, '//Final_Project/');

// Redirect to login page
header('Location: login.html');
exit;

?>








