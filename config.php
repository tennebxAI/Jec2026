<?php
// ====================================================================
// BÜFA PDF Landing Page - Konfiguration
// ====================================================================

// SESSION EINSTELLUNGEN
session_start();

// ADMIN LOGIN CREDENTIALS
// Ändere hier dein Passwort (wird automatisch gehashed)
$ADMIN_USERNAME = 'admin';
$ADMIN_PASSWORD = 'buefa2026'; // BITTE ÄNDERN!

// Passwort hashen (für sichere Speicherung)
$ADMIN_PASSWORD_HASH = password_hash($ADMIN_PASSWORD, PASSWORD_DEFAULT);

// SESSION EINSTELLUNGEN
define('SESSION_TIMEOUT', 7200); // 2 Stunden in Sekunden
define('REMEMBER_ME_DURATION', 604800); // 7 Tage in Sekunden

// FUNKTIONEN
function isLoggedIn() {
    // Remember Me Cookie prüfen
    if (!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];
        if ($token === $_SESSION['remember_token'] ?? '') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['last_activity'] = time();
        }
    }
    
    // Session prüfen
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        // Timeout prüfen
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            // Session abgelaufen
            session_unset();
            session_destroy();
            return false;
        }
        
        // Aktivität aktualisieren
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    return false;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function logout() {
    // Cookie löschen
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    // Session beenden
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
