<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "tetravilla";  // Le nom de ta base de données

// Création de la connexion
$conn = new mysqli($host, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
