<?php
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "hotel";

try {
    // Connexion à la base de données avec PDO (en incluant le port 4306)
    $conn = new PDO("mysql:host=$servername;port=3306;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connexion réussie!";
} catch (PDOException $e) {
    echo "Échec de la connexion: " . $e->getMessage();
}
?>

