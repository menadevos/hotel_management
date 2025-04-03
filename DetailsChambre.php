<?php
session_start();

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

// Récupérer l'ID de la réservation depuis l'URL
$idReservation = $_GET['id_reservation'] ?? null;
$_SESSION['reservation_id']= $idReservation;

if (!$idReservation) {
    echo "<h1>Aucune réservation spécifiée.</h1>";
    exit;
}

// Requête pour récupérer les détails de la chambre via la réservation
$sql = "SELECT c.type_chambre, c.capacite, c.statut, c.tarif
        FROM chambre c
        INNER JOIN reservation r ON c.id_chambre = r.id_chambre
        WHERE r.id_reservation = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $idReservation);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $chambre = $result->fetch_assoc();
    // Afficher les détails de la chambre
    echo "<h1>Détails de la Chambre</h1>";
    echo "<p>Type de chambre : {$chambre['type_chambre']}</p>";
    echo "<p>Capacité : {$chambre['capacite']} personnes</p>";
    echo "<p>Statut : {$chambre['statut']}</p>";
    echo "<p>Tarif : {$chambre['tarif']} DH</p>";
    echo "<a href='consultationServices.php' style='display: inline-block; background-color: #007BFF; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px;'>Consulter les Services</a>";
    echo"<style> 
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 20px;
    padding: 20px;
}

.chambre-details {
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    max-width: 600px;
    margin: auto;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.title {
    color: #333;
    font-size: 24px;
    text-align: center;
    margin-bottom: 20px;
}

.info {
    font-size: 18px;
    color: #555;
    margin: 10px 0;
}

.value {
    color: #000;
    font-weight: bold;
}

.error {
    color: #ff0000;
    text-align: center;
}   
    </style>";
} else {
    echo "<h1>Aucune chambre correspondante trouvée pour cette réservation.</h1>";
}

// Fermer la connexion
$conn->close();
?>
