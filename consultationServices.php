<?php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    die("<h1>Aucune réservation trouvée.</h1>");
}

$reservationId = $_SESSION['reservation_id'];
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

// Requête pour récupérer les services disponibles
$sqlServices = "SELECT nom_service, description FROM service";
$resultServices = $conn->query($sqlServices);

if ($resultServices && $resultServices->num_rows > 0) {
    echo "<h1>Liste des Services Disponibles</h1>";
    echo "<form action='gererService.php' method='POST'>";
    
    while ($service = $resultServices->fetch_assoc()) {
        echo "<div class='service-item'>";
        echo "<h2>{$service['nom_service']}</h2>";
        echo "<p>Description : {$service['description']}</p>";
        echo "<button type='submit' name='service' value='{$service['nom_service']}'>Choisir ce Service</button>";
        echo "</div>";
    }
    
    echo "</form>";
} else {
    echo "<h1>Aucun service disponible pour le moment.</h1>";
}

// Fermer la connexion
$conn->close();
?>


