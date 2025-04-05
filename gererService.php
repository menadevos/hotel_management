<?php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    die("<h1>Aucune réservation trouvée.</h1>");
}

$reservationId = $_SESSION['reservation_id'];
$conn = new mysqli('localhost', 'root', '', 'hotel');

if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

// Début du HTML + CSS
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Enregistrement des services</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .message-container {
            text-align: center;
            padding: 50px;
            margin: 100px auto;
            width: 80%;
            max-width: 600px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .message-container h2.success {
            color: #4CAF50;
        }

        .message-container h2.error {
            color: #f44336;
        }

        .message-container p {
            font-size: 18px;
            margin: 20px 0;
        }

        .message-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .message-container a:hover {
            background: #45a049;
        }

        .message-container a.back {
            background: #f44336;
        }

        .message-container a.back:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>";

$conn->begin_transaction();

try {
    $queryRestauration = $conn->query("SELECT id_service FROM service WHERE type_service = 'restauration' LIMIT 1");
    if (!$queryRestauration || $queryRestauration->num_rows == 0) {
        throw new Exception("Le service de restauration est introuvable.");
    }
    $idRestauration = $queryRestauration->fetch_assoc()['id_service'];

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

    if (isset($_POST['paquets_restauration']) && is_array($_POST['paquets_restauration']) && count($_POST['paquets_restauration']) > 0) {
        $insertRestauration = $conn->prepare("INSERT INTO reservation_service (id_reservation, id_service) VALUES (?, ?)");
        $insertRestauration->bind_param("ii", $reservationId, $idRestauration);
        if (!$insertRestauration->execute() && $conn->errno != 1062) {
            throw new Exception("Erreur ajout service restauration: " . $conn->error);
        }
        $insertRestauration->close();

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

    echo "<div class='message-container'>";
    echo "<h2 class='success'>Enregistrement réussi!</h2>";
    echo "<p>Vos services ont bien été enregistrés.</p>";
    echo "<a href='infospersonnels.php?id_reservation=$reservationId'>Valider</a>";
    echo "</div>";

    echo "<script>setTimeout(() => { window.location.href = 'infospersonnels.php?id_reservation=$reservationId'; }, 5000);</script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<div class='message-container'>";
    echo "<h2 class='error'>Erreur lors de l'enregistrement</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<a href='consultationServices.php' class='back'>Retour aux services</a>";
    echo "</div>";
}

$conn->close();

echo "</body></html>";
?>
