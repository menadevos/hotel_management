<?php
session_start();
try {
    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die("<h1>Méthode non autorisée</h1>");
    }

    // Connexion à la base de données
    $conn = new mysqli('localhost', 'root', '', 'tetravilla');
    if ($conn->connect_error) {
        http_response_code(500);
        die("<h1>Erreur de connexion à la base de données</h1>");
    }

    // Récupérer les données du formulaire
    $dateArrivee = $_POST['date_arrivee'] ?? null;
    $dateDepart = $_POST['date_depart'] ?? null;
    $typeChambre = $_POST['type_chambre'] ?? null;
    $nbrePersonnes = $_POST['nombrepersonnes'] ?? 0;

    // Validation des données
    if (!$dateArrivee || !$dateDepart || !$typeChambre || !$nbrePersonnes) {
        http_response_code(400);
        die("<h1>Erreur : Tous les champs sont obligatoires.</h1>");
    }

    // Sécuriser les entrées utilisateur
    $dateArrivee = $conn->real_escape_string($dateArrivee);
    $dateDepart = $conn->real_escape_string($dateDepart);
    $typeChambre = $conn->real_escape_string($typeChambre);

    // Vérifier la disponibilité des chambres et la capacité
    $sqlChambre = "SELECT id_chambre, capacite
                   FROM chambre 
                   WHERE type_chambre = '$typeChambre' 
                    AND statut = 'disponible'
                   AND NOT EXISTS (
                       SELECT 1 
                       FROM reservation 
                       WHERE id_chambre = chambre.id_chambre 
                       AND date_depart > '$dateArrivee' 
                       AND date_arrivee < '$dateDepart'
                   )
                   LIMIT 1";

    $result = $conn->query($sqlChambre);

    if ($result && $result->num_rows > 0) {
        // Récupérer l'ID de la chambre disponible et sa capacité
        $row = $result->fetch_assoc();
        $idChambre = $row['id_chambre'];
        $capacite = $row['capacite'];

        // Vérifier si le nombre de personnes dépasse la capacité
        if ($nbrePersonnes > $capacite) {
            http_response_code(400);
            die("<h1>Erreur : Le nombre de personnes dépasse la capacité maximale de la chambre.</h1>");
        }

        // Insérer la réservation
        $sqlReservation = "INSERT INTO reservation (date_arrivee, date_depart, etat_reservation, nbre_personnes, id_chambre)
                           VALUES ('$dateArrivee', '$dateDepart', 'en cours', '$nbrePersonnes', '$idChambre')";

        if ($conn->query($sqlReservation)) {
            $reservationId = $conn->insert_id; // ID de la réservation
            // $_SESSION['reservation_id'] = $reservationId; // Stocker dans la session
            header("Location: DetailsChambre.php?id_reservation=$reservationId");
            exit;
        } else {
            http_response_code(500);
            die("<h1>Erreur lors de la création de la réservation</h1>");
        }
    } else {
        // Aucune chambre disponible
        header("Location: indisponible.php");
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    die("<h1>Erreur interne : " . $e->getMessage() . "</h1>");
}
?>
