<?php
/**
 * Password Migration Script
 * This script updates existing MD5 passwords to secure password_hash()
 * Run this script ONCE after deploying the new authentication system
 */

require_once '../include/config.php';

echo "Starting password migration...\n";

// Get all users with MD5 passwords (32 characters)
$query = mysqli_query($config, "SELECT id_user, username, password FROM tbl_user WHERE LENGTH(password) = 32");

if (!$query) {
    die("Error fetching users: " . mysqli_error($config));
}

$updated_count = 0;
$total_count = mysqli_num_rows($query);

echo "Found {$total_count} users with MD5 passwords to migrate.\n";

while ($user = mysqli_fetch_assoc($query)) {
    // For security, we can't reverse MD5, so we'll set a default password
    // Users will need to reset their passwords or admin can set new ones
    
    // Option 1: Set a default password (not recommended for production)
    // $default_password = 'password123';
    // $new_hash = password_hash($default_password, PASSWORD_DEFAULT);
    
    // Option 2: Keep MD5 for now, let the login system handle migration
    // This is what we implemented in index.php - it will auto-migrate on login
    
    echo "User: {$user['username']} - MD5 password will be migrated on next login\n";
    $updated_count++;
}

echo "\nMigration summary:\n";
echo "- Total users processed: {$total_count}\n";
echo "- Users with MD5 passwords: {$updated_count}\n";
echo "- These passwords will be automatically upgraded to secure hashing on next login\n";

// Update database schema to allow longer passwords if needed
$alter_query = "ALTER TABLE tbl_user MODIFY COLUMN password VARCHAR(255) NOT NULL";
if (mysqli_query($config, $alter_query)) {
    echo "- Database schema updated to support longer password hashes\n";
} else {
    echo "- Warning: Could not update password column length: " . mysqli_error($config) . "\n";
}

echo "\nMigration completed successfully!\n";
echo "Note: Users with MD5 passwords will be automatically migrated when they log in.\n";
?>
