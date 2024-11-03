<?php
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';

if ($action == 'set_cookie') {
    setcookie("user", "JohnDoe", time() + 3600, "/");
    $message = "Cookie 'user' has been set.";
} elseif ($action == 'read_cookie') {
    if (isset($_COOKIE["user"])) {
        $message = "Cookie 'user' is set. Value: " . $_COOKIE["user"];
    } else {
        $message = "Cookie 'user' is not set.";
    }
} elseif ($action == 'delete_cookie') {
    setcookie("user", "", time() - 3600, "/");
    $message = "Cookie 'user' has been deleted.";
} elseif ($action == 'check_session') {
    // Check if user is logged in by verifying session variables
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        $timeout_duration = 5 * 60;
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
            session_unset();
            session_destroy();
            $message = "Session timed out. Please log in again.";
        } else {
            $_SESSION['LAST_ACTIVITY'] = time();
            $message = "Session is active. Welcome, " . htmlspecialchars($_SESSION['username']) . "!";
        }
    } else {
        $message = "Session is not active. Please log in first.";
    }
} elseif ($action == 'delete_session') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    $message = "Session has been deleted.";
} else {
    $message = "No action specified.";
}

// Check login status for displaying different message styles
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie and Session Management - Crave Courier</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .manage-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow-md);
        }

        .manage-container h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }

        .message {
            background-color: var(--hover-bg);
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            color: var(--text-dark);
        }

        .message.active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.inactive {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .button-group {
            display: grid;
            gap: 15px;
            margin-top: 20px;
        }

        .manage-btn {
            background-color: var(--primary-color);
            color: var(--text-light);
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .manage-btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
        }

        .login-status {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .login-status.logged-in {
            background-color: #d4edda;
            color: #155724;
        }

        .login-status.logged-out {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .manage-container {
                margin: 20px;
                padding: 20px;
            }

            .manage-container h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>CRAVE COURIER</h1>
            <nav>
                <ul>
                    <li><a href="../html/index.html" class="btn">🏠︎ Home</a></li>
                    <li><a href="../html/menu.html" class="btn">🔰Menu</a></li>
                    <li><a href="../html/about.html" class="btn">🛈About Us</a></li>
                    <li><a href="../html/contact.html" class="btn">📞Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="../php/logout.php" class="btn">👤Logout</a></li>
                    <?php else: ?>
                        <li><a href="../html/login.html" class="btn">👤Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="manage-container">
        <h1>Cookie and Session Management</h1>
        
        <div class="login-status <?php echo $isLoggedIn ? 'logged-in' : 'logged-out'; ?>">
            <?php echo $isLoggedIn ? 'Logged in as: ' . htmlspecialchars($_SESSION['username']) : 'Not logged in'; ?>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php 
                if (strpos($message, 'active') !== false) {
                    echo 'active';
                } elseif (strpos($message, 'not active') !== false) {
                    echo 'inactive';
                }
            ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="button-group">
            <form action="manage.php" method="get">
                <input type="hidden" name="action" value="set_cookie">
                <button type="submit" class="manage-btn">Set Cookie</button>
            </form>

            <form action="manage.php" method="get">
                <input type="hidden" name="action" value="read_cookie">
                <button type="submit" class="manage-btn">Read Cookie</button>
            </form>

            <form action="manage.php" method="get">
                <input type="hidden" name="action" value="delete_cookie">
                <button type="submit" class="manage-btn"> Delete Cookie</button>
            </form>

            <form action="manage.php" method="get">
                <input type="hidden" name="action" value="check_session">
                <button type="submit" class="manage-btn">Check Session</button>
            </form>

            <form action="manage.php" method="get">
                <input type="hidden" name="action" value="delete_session">
                <button type="submit" class="manage-btn">Delete Session</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Crave Courier. All Rights Reserved.</p>
        <p>
            <a href="#">Privacy Policy</a> | 
            <a href="#">Terms of Service</a>
        </p>
    </footer>
</body>
</html>