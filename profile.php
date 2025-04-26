<?php
session_name('CALENDARSESSID');
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$uid = $_SESSION['user_id'];
require 'db.php';
if ($mysqli->connect_errno) die('DB error');

// Handle from POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $display = trim($_POST['display_name'] ?? '');
    $bio     = trim($_POST['bio'] ?? '');

    if (!empty($_FILES['profile_pic']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $filename = "profile_{$uid}_" . time() . ".$ext";
            $dest = __DIR__ . "/uploads/$filename";
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest);
            $upPic = $mysqli->prepare('UPDATE user_profiles SET profile_pic=? WHERE user_id=?');
            $upPic->bind_param('si', $filename, $uid);
            $upPic->execute();
            $upPic->close();
        }
    }

    $upd = $mysqli->prepare('UPDATE user_profiles SET display_name=?, bio=? WHERE user_id=?');
    $upd->bind_param('ssi', $display, $bio, $uid);
    $upd->execute();
    $upd->close();

    header('Location: profile.php?updated=1');
    exit;
}

$stmt = $mysqli->prepare('SELECT u.email, p.display_name, p.bio, p.profile_pic, p.join_date FROM users u JOIN user_profiles p ON p.user_id=u.id WHERE u.id=?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($email, $disp, $bio, $pic, $join);
$stmt->fetch();
$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <link rel="stylesheet" href="/navbar.css">
    <style>
        :root {
            --bg-color: #f4f4f4;
            --text-color: #000;
            --header-bg: #522d80;
            --header-text: #fff;
        }
        body.dark {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --header-bg: #333;
            --header-text: #fafafa;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }
        header {
            background-color: var(--header-bg);
            color: var(--header-text);
            padding: 10px;
            text-align: center;
        }
        .profile {
            max-width: 120px;
            margin: 2rem auto;
        }
        .profile img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
        }
        .success {
            color: green;
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
        .dark-mode-toggle {
            text-align: center;
            margin: 1rem;
        }
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            vertical-align: middle;
        }
        .auth-nav img.avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            vertical-align: middle;
            }
    </style>
</head>
<header>
    <div id="navbar"></div>
   <script src="../script.js"></script>
 </header>
<body>
    
    <div id="navbar"></div>
    <script src="/components/navbar.js"></script>

    <nav>
        <ul>
            <li><a href="showcalendar_inJS.html">Calendar</a></li>
        </ul>
        <ul class="auth-nav">
            <li id="auth-link"><a href="login.html">Login</a></li>
            <li><a href="register.html" class="active">Register</a></li>
        </ul>
    </nav>
    <script src="components/auth-nav.js"></script>

    <!-- Dark Mode Toggle -->
    <div class="dark-mode-toggle">
        <label>
            <input type="checkbox" id="darkToggle"> Dark Mode
        </label>
    </div>

    <div class="profile">
        <?php if (isset($_GET['updated'])): ?>
            <p class="success">Profile updated!</p>
        <?php endif; ?>

        <h2>Your Profile</h2>
        <img src="<?= $pic ? "uploads/$pic" : "images/placeholder.png" ?>" alt="Profile Picture">

        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Joined:</strong> <?= $join ?></p>

        <form action="profile.php" method="post" enctype="multipart/form-data">
            <p>
                <label>Display Name:<br>
                <input type="text" name="display_name" value="<?= htmlspecialchars($disp) ?>">
                </label>
            </p>
            <p>
                <label>Bio:<br>
                <textarea name="bio" rows="4"><?= htmlspecialchars($bio) ?></textarea>
                </label>
            </p>
            <p>
                <label>Profile Picture:<br>
                <input type="file" name="profile_pic" accept="image/*">
                </label>
            </p>
            <p><button type="submit">Save Profile</button></p>
        </form>
    </div>

    <script>
        const darkToggle = document.getElementById('darkToggle');
        const body = document.body;

        // Load setting
        if (localStorage.getItem('dark-mode') === 'enabled') {
            body.classList.add('dark');
            darkToggle.checked = true;
        }

        darkToggle.addEventListener('change', () => {
            if (darkToggle.checked) {
                body.classList.add('dark');
                localStorage.setItem('dark-mode', 'enabled');
            } else {
                body.classList.remove('dark');
                localStorage.setItem('dark-mode', 'disabled');
            }
        });
    </script>

</body>
</html>
