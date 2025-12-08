<?php
require_once "../config/database.php";

$db = new Database();
$conn = $db->getConnection();

echo "Aplikasi Cuaca sedang berjalan!";
?>
