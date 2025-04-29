<?php
session_name('CALENDARSESSID');
session_start();
if (empty($_SESSION['user_id'])) {
  header('Location: login.html');
  exit;
}

$uid = $_SESSION['user_id'];
$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) die('DB error');

// Fetch old filename so if new one is uploaded, old one is deleted
$oldPic = '';
$stmt = $mysqli->prepare('SELECT profile_pic FROM user_profiles WHERE user_id = ?')
    or die('Prepare failed: '.$mysqli->error);
$stmt->bind_param('i',$uid);
$stmt->execute();
$stmt->bind_result($oldPic);
$stmt->fetch();
$stmt->close();

// Handle from POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean input
    $display = trim($_POST['display_name'] ?? '');
    $bio     = trim($_POST['bio'] ?? '');
    
    // Profile picture upload
    if (!empty($_FILES['profile_pic']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $filename = "profile_{$uid}_" . time() . ".$ext";
            $dest = __DIR__ . "/uploads/$filename";
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
                // Delete old file it if exists and isn't placeholder
                if ($oldPic && file_exists(__DIR__ . "/uploads/$oldPic")) {
                    @unlink(__DIR__ . "/uploads/$oldPic");
                }
                // Update with new pic
                $upPic = $mysqli->prepare(
                    'UPDATE user_profiles SET profile_pic=? WHERE user_id=?'
                ) or die('Prepare failed: '.$mysqli->error);
                $upPic->bind_param('si', $filename, $uid);
                $upPic->execute();
                $upPic->close();
            }
        }
    }
    
    // Update display & bio
    $upd = $mysqli->prepare(
        'UPDATE user_profiles SET display_name=?, bio=? WHERE user_id=?'
    );
    $upd->bind_param('ssi', $display, $bio, $uid);
    $upd->execute();
    $upd->close();
    
    // Refresh
    header('Location: profile.php?updated=1');
    exit;
}
    
// GET: fetch current profile
$stmt = $mysqli->prepare(
    'SELECT u.email, p.display_name, p.bio, p.profile_pic, p.join_date
    FROM users u
    JOIN user_profiles p ON p.user_id=u.id
    WHERE u.id=?'
);
$stmt->bind_param('i',$uid);
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
    <link rel="stylesheet" href="themes.css">
    <script src="themes.js" defer></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        header {
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        .profile {
            max-width: 600px;
            margin: 2rem auto;
            text-align: center;
        }
        .profile img {
            display: block;
            margin: 0 auto 1rem;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
        }
        .profile form {
            text-align: center;
        }
        .profile form p {
            margin-bottom: 1rem;
        }
        .file-input-wrapper {
            text-align: center;
            margin: 0.5rem auto;
        }
        .file-input-wrapper input[type="file"] {
            display: none;
        }
        .file-input-wrapper .file-btn {
            display: inline-block;
            padding: 0.3em 0.6em;
            border: 1px solid #888;
            border-radius: 4px;
            cursor: pointer;
        }
        .file-input-wrapper #file-name {
            margin-top: 0.5em;
            font-size: 0.9em;
            color: #333;
            text-align: center;
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
    </style>
</head>

<body>
    
    <header>
        <h1>Your Profile</h1>
    </header>
    
    <!-- Navbar -->
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
    <script src="/components/auth-nav.js"></script>
    
    
    <div class="profile">
        <?php if (isset($_GET['updated'])): ?>
            <p class="success">Profile updated!</p>
        <?php endif; ?>
        
        <h2>View/Edit Your Profile</h2>
        <img src="<?php
            echo $pic
                ? "uploads/$pic"
                : "images/placeholder.png";
            ?>" alt="Profile Picture">
        
        <p><strong>Email:</strong> <?=htmlspecialchars($email)?></p>
        <p><strong>Joined:</strong> <?=$join?></p>
        
        <form action="profile.php" method="post" enctype="multipart/form-data">
            <p>
                <label>Display Name:<br>
                <input type="text" name="display_name" value="<?=htmlspecialchars($disp)?>">
                </label>
            </p>
            <p>
                <label>Bio:<br>
                <textarea name="bio" rows="4"><?=htmlspecialchars($bio)?></textarea>
                </label>
            </p>
            <p>
                <label>Profile Picture:</label><br>
                <div class="file-input-wrapper">
                    <label for="profile_pic" class="file-btn">Choose File</label>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                    <div id="file-name">No file chosen</div>
                </div>
            </p>
            <p><button type="submit">Save Profile</button></p>
        </form>
    </div>
    
    
    <script>
        document.getElementById('profile_pic')
            .addEventListener('change', function() {
                const name = this.files[0]?.name || 'No file chosen';
                document.getElementById('file-name').textContent = name;
            });
    </script>
    
    
</body>
</html>









