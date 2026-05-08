<?php
require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    try {
        $db->exec("ALTER TABLE user ADD COLUMN login_attempts INT DEFAULT 0");
        echo "Column login_attempts added.\n";
    } catch(PDOException $e) {}
    try {
        $db->exec("ALTER TABLE user ADD COLUMN locked_until DATETIME DEFAULT NULL");
        echo "Column locked_until added.\n";
    } catch(PDOException $e) {}
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
