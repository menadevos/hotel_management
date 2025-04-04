<?php
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "hotel";

try {
    // Connexion à la base de données avec PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Définir le mode d'erreur de PDO pour qu'il lève des exceptions en cas d'erreur
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // echo "Connexion réussie!";
} catch (PDOException $e) {
    // Si la connexion échoue, une exception sera lancée et ce bloc sera exécuté
    echo "Échec de la connexion: " . $e->getMessage();
}
?>

