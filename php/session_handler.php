<?php
function check_session() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../html/login.html");
        exit();
    }
}

function set_session($user_data) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['last_login'] = time();
}
?>