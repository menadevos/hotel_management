<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

// Récupérer l'ID de la réservation depuis l'URL
$idReservation = $_GET['id_reservation'] ?? null;

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
} else {
    echo "<h1>Aucune chambre correspondante trouvée pour cette réservation.</h1>";
}

// Fermer la connexion
$conn->close();
?>
