<?php
/**
 * Application configuration.
 * Adjust BASE_URL to match your XAMPP folder name.
 */

// App identity
define('APP_NAME', 'Smart University Complaint Portal');
define('APP_VERSION', '0.1.0');

// Base URL — change if you rename the project folder in htdocs
define('BASE_URL', 'http://localhost/complaint-portal');

// Database credentials (XAMPP defaults)
define('DB_HOST', 'localhost');
define('DB_NAME', 'university_complaints');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Session
define('SESSION_NAME', 'scp_session');
define('SESSION_LIFETIME', 1800); // 30 min idle timeout (per TC-I-03)

// File uploads
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'docx']);

// Error reporting (set APP_DEBUG to false in production)
define('APP_DEBUG', true);
error_reporting(APP_DEBUG ? E_ALL : 0);
ini_set('display_errors', APP_DEBUG ? '1' : '0');

// Default timezone
date_default_timezone_set('Asia/Karachi');
