<?php
require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    // Add face_descriptor column if it does not exist
    $db->exec("ALTER TABLE user ADD COLUMN face_descriptor TEXT DEFAULT NULL");
    echo "Column face_descriptor added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column face_descriptor already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
