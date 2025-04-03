<?php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    die("<h1>Aucune réservation trouvée.</h1>");
}

$idReservation = $_SESSION['reservation_id'];
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

// Récupérer les données du formulaire
$service = $_POST['service'] ?? null;
$paquet = $_POST['paquet'] ?? null;
$date = $_POST['date'] ?? null;
$prix = $_POST['prix'] ?? null;



// Validation des données
if (!$service || !$idReservation) {
    die("<h1>Erreur : Service et ID réservation sont obligatoires.</h1>");
}

// Récupérer l'ID du service principal
$sqlService = "SELECT id_service FROM service WHERE nom_service = ?";
$stmtService = $conn->prepare($sqlService);
$stmtService->bind_param('s', $service);
$stmtService->execute();
$resultService = $stmtService->get_result();

if ($resultService && $resultService->num_rows > 0) {
    $serviceData = $resultService->fetch_assoc();
    $idService = $serviceData['id_service'];

    if ($service === 'Restauration' && $paquet) {
        // Récupérer l'ID du paquet sélectionné
        $sqlPaquet = "SELECT id_Paquet FROM restauration WHERE nomPaquet = ?";
        $stmtPaquet = $conn->prepare($sqlPaquet);
        $stmtPaquet->bind_param('s', $paquet);
        $stmtPaquet->execute();
        $resultPaquet = $stmtPaquet->get_result();

        if ($resultPaquet && $resultPaquet->num_rows > 0) {
            $paquetData = $resultPaquet->fetch_assoc();
            $idPaquet = $paquetData['id_Paquet'];

            // MISE À JOUR de la réservation existante
            $sqlUpdate = "UPDATE reservation SET id_service = ? WHERE id_reservation = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param('ii', $idService, $idReservation);

            if ($stmtUpdate->execute()) {
                // Stocker l'association avec le paquet (dans une table de liaison si nécessaire)
                session_start();
                $_SESSION['id_paquet'] = $idPaquet;

                echo "<h1>Réservation mise à jour !</h1>";
                echo "<p>Service : $service</p>";
                echo "<p>Paquet : $paquet</p>";
                header("Location:infospersonnels.php");
                exit ;
            } else {
                die("<h1>Erreur lors de la mise à jour.</h1>");
            }
        } else {
            die("<h1>Erreur : Paquet introuvable.</h1>");
        }
    } else {
        // MISE À JOUR uniquement avec le service principal
        $sqlUpdate = "UPDATE reservation SET id_service = ? WHERE id_reservation = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param('ii', $idService, $idReservation);

        if ($stmtUpdate->execute()) {
            echo "<h1>Réservation mise à jour pour le service : $service !</h1>";
        } else {
            die("<h1>Erreur lors de la mise à jour.</h1>");
        }
        if($service === 'Spa' && $date){
            
            $sqlInsertSpa  = "INSERT INTO spa (date,prix,id_service) VALUES (?,?,1)";
            $stmtInsertSpa = $conn->prepare($sqlInsertSpa);
            $stmtInsertSpa->bind_param('sd', $date, $prix);

            if ($stmtInsertSpa->execute()) {
                echo "<h1>Date du service SPA mise à jour !</h1>";
                header("Location:infospersonnels.php");
                exit ;
            } else {
                die("<h1>Erreur lors de la mise à jour de la date.</h1>");
            }
        }
        if($service === 'Gym'){
            $sqlInsertGym  = "INSERT INTO gym (prix,id_service) VALUES (?,1)";
            $stmtInsertGym = $conn->prepare($sqlInsertGym);
            $stmtInsertGym->bind_param('d', $prix);

            if ($stmtInsertGym->execute()) {
                echo "<h1>Date du service Gym mise à jour !</h1>";
                header("Location:infospersonnels.php");
                exit ;
            } else {
                die("<h1>Erreur lors de la mise à jour de la date.</h1>");
            }
        }

    }
} else {
    die("<h1>Erreur : Service introuvable.</h1>");
}

// Fermer la connexion
$conn->close();
?>
