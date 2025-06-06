<?php
session_name('CALENDARSESSID');
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$viewId = intval($_GET['user_id'] ?? 0);
if ($viewId < 1) {
    header('Location: profile.php');
    exit;
}

$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    die('DB error');
}

$stmt = $mysqli->prepare(
    'SELECT u.email, p.display_name, p.bio, p.profile_pic, p.join_date
       FROM users u
       JOIN user_profiles p ON p.user_id = u.id
      WHERE u.id = ?'
);
if (!$stmt) {
    die('DB error');
}
$stmt->bind_param('i', $viewId);
$stmt->execute();
$stmt->bind_result($email, $disp, $bio, $pic, $join);

if (!$stmt->fetch()) {
    die('User not found');
}
$stmt->close();
$mysqli->close();
?>


<!-- Render Page -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=htmlspecialchars($disp)?>’s Profile</title>
        <link rel="stylesheet" href="/navbar.css">
        <link rel="stylesheet" href="themes.css">
        <script src="themes.js" defer></script>
        <style>
            .profile-view { max-width:600px; margin:2rem auto; }
            .profile-view img { width:120px; height:120px; border-radius:50%; object-fit:cover; }
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
            }
            header {
                padding: 10px;
                text-align: center;
            }
            .auth-nav {
                list-style: none;
                display: flex;
                gap: 20px;
                margin: 0;
                padding: 0;
                position: absolute;
                right: 20px;
                transform: translateY(-100%);
            }
            .profile-view {
                max-width: 600px;
                margin: 2rem auto;
                text-align: center;
            }
            .profile-view img {
                display: block;             
                margin: 0 auto 1rem;        
                width: 120px;
                height: 120px;
                object-fit: cover;
                border-radius: 50%;
            }
        </style>
        
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    </head>
    
    <body>
        
        <header>
            <h1><?=htmlspecialchars($disp)?>’s Profile</h1>
        </header>
        
        <!-- Navbar -->
	    <div id="navbar"></div>
        <script src="/components/navbar.js"></script>
        
        <nav>
        <ul>
            <li><a href="showcalendar_inJS.html">Calendar</a></li>
            <li><a href="proposal.html">Proposal</a></li>
            <li><a href="about.html">About</a></li>
            <li class="search-li">
                <input type="text" id="user-search" placeholder="Search users…" autocomplete="off">
                <ul id="search-results" class="search-results"></ul>
            </li>
        </ul>
        <ul class="auth-nav">
            <li id="auth-link"><a href="login.html">Login</a></li>
        </ul>
        </nav>
        <script src="/components/auth-nav.js"></script>
        <script src="/components/search-nav.js"></script>
        
        <div class="profile-view">
            <h2><?= htmlspecialchars($disp)?></h2>
            <img src="<?= htmlspecialchars($pic ? "/Assignments/Final_Project/uploads/{$pic}" : "/Assignments/Final_Project/images/placeholder.png") ?>" alt="Avatar">
            <p><strong>Joined:</strong> <?= htmlspecialchars($join) ?></p>
             <?php if (trim($bio) !== ''): ?>
                <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($bio)) ?></p>
            <?php endif; ?>
        </div>
        
        
    </body>
</html>






