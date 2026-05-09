<?php
require_once __DIR__ . '/../includes/auth.php';
start_secure_session();

logout_user();

// Restart a clean session just to deliver the flash message on the login page.
start_secure_session();
flash('success', 'You have been logged out.');
redirect('public/login.php');
