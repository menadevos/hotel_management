<?php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    die("<h1>Aucune réservation trouvée.</h1>");
}

$reservationId = $_SESSION['reservation_id'];
$conn = new mysqli('localhost', 'root', '', 'tetravilla');

if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

// Démarrer une transaction
$conn->begin_transaction();

try {
    // Obtenir dynamiquement l'ID du service de type restauration
    $queryRestauration = $conn->query("SELECT id FROM service WHERE type_service = 'restauration' LIMIT 1");
    if (!$queryRestauration || $queryRestauration->num_rows == 0) {
        throw new Exception("Le service de restauration est introuvable.");
    }
    $idRestauration = $queryRestauration->fetch_assoc()['id'];

    // Traitement des services normaux (hors restauration)
    if (isset($_POST['services']) && is_array($_POST['services'])) {
        $insertService = $conn->prepare("INSERT INTO reservation_service (id_reservation, id_service) VALUES (?, ?)");
        
        foreach ($_POST['services'] as $idService) {
            if ($idService == $idRestauration) continue;
            
            $insertService->bind_param("ii", $reservationId, $idService);
            if (!$insertService->execute() && $conn->errno != 1062) {
                throw new Exception("Erreur service ID $idService: " . $conn->error);
            }
        }
        $insertService->close();
    }

    // Traitement des paquets de restauration
    if (isset($_POST['paquets_restauration']) && is_array($_POST['paquets_restauration']) && count($_POST['paquets_restauration']) > 0) {
        // Ajouter le service restauration si des paquets sont choisis
        $insertRestauration = $conn->prepare("INSERT INTO reservation_service (id_reservation, id_service) VALUES (?, ?)");
        $insertRestauration->bind_param("ii", $reservationId, $idRestauration);
        if (!$insertRestauration->execute() && $conn->errno != 1062) {
            throw new Exception("Erreur ajout service restauration: " . $conn->error);
        }
        $insertRestauration->close();

        // Ajouter les paquets choisis
        $insertPaquet = $conn->prepare("INSERT INTO reservation_paquet_restauration (reservation_id, paquet_restauration_id) VALUES (?, ?)");
        
        foreach ($_POST['paquets_restauration'] as $idPaquet) {
            $insertPaquet->bind_param("ii", $reservationId, $idPaquet);
            if (!$insertPaquet->execute() && $conn->errno != 1062) {
                throw new Exception("Erreur paquet ID $idPaquet: " . $conn->error);
            }
        }
        $insertPaquet->close();
    }

    $conn->commit();

    // Début de la sortie HTML
    echo "<div style='text-align: center; padding: 50px;'>";
    echo "<h2 style='color: green;'>Enregistrement réussi!</h2>";
    echo "<p>Vos services ont bien été enregistrés.</p>";
    echo "<a href='infospersonnel.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Voir ma réservation</a>";
    echo "</div>";

    // Redirection automatique après 5 secondes
    echo "<script>setTimeout(() => { window.location.href = 'infospersonnel.php'; }, 5000);</script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<div style='text-align: center; padding: 50px; color: red;'>";
    echo "<h2>Erreur lors de l'enregistrement</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<a href='services.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #f44336; color: white; text-decoration: none; border-radius: 5px;'>Retour aux services</a>";
    echo "</div>";
}

$conn->close();
?>
