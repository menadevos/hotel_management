<?php
session_start();
// Fonction pour afficher un message d'erreur stylisé
function styled_die($message) {
    echo '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur</title>
        <style>
            .error-container {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 30px;
                border-radius: 10px;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .error-title {
                font-size: 24px;
                margin-bottom: 15px;
                color: #dc3545;
            }
            .error-icon {
                font-size: 50px;
                margin-bottom: 20px;
                color: #dc3545;
            }
            .back-link {
                display: inline-block;
                margin-top: 20px;
                padding: 8px 15px;
                background-color: #dc3545;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s;
            }
            .back-link:hover {
                background-color: #c82333;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">✕</div>
            <h1 class="error-title">Erreur</h1>
            <p>'.$message.'</p>
        </div>
    </body>
    </html>';
    exit();
}

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'hotel');
if ($conn->connect_error) {
    styled_die("Connection failed: " . $conn->connect_error);
}

$total = $_GET['total'] ?? 0;
$reservation_id = $_GET['id_reservation'] ?? null;
if (!$reservation_id) {
    styled_die("Aucune réservation trouvée.");
}

// recuperer id du client 
$client_id = $_SESSION['client_id'] ?? null;
if (!$client_id) {
    styled_die("Vous devez être connecté pour effectuer un paiement.");
}

if(isset($_POST['payer'])) {
    $card_number = $_POST['card_number'];
    $card_name = $_POST['card_name'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
  
    // Vérification des informations
    if(empty($card_number) || empty($card_name) || empty($expiry_date) || empty($cvv)) {
        styled_die("Veuillez remplir tous les champs.");
    }
    // vérification le numero de carte est valide c-a-d il contient 16 chiffres
    if(!preg_match('/^\d{16}$/', $card_number)) {
        styled_die("Le numéro de carte doit contenir 16 chiffres.");
    }
    // vérification le nom de la carte est valide c-a-d il contient que des lettres
    if(!preg_match('/^[a-zA-Z\s]+$/', $card_name)) {
        styled_die("Le nom sur la carte doit contenir uniquement des lettres.");
    }
    // vérification la date d'expiration est valide c-a-d elle est au format MM/AA
    if(!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $expiry_date)) {
        styled_die("La date d'expiration doit être au format MM/AA.");
    }
    // vérification le cvv est valide c-a-d il contient 3 chiffres
    if(!preg_match('/^\d{3}$/', $cvv)) {
        styled_die("Le CVV doit contenir 3 chiffres.");
    }
    // Date de la transaction
    $datetrans = date('Y-m-d H:i:s');

    // Correction de la requête préparée
    $stmt = $conn->prepare("INSERT INTO transaction (montant_trans, date_trans, typeTrans, id_reservation) VALUES (?, ?, 'réservation', ?)");
    $stmt->bind_param("dsi", $total, $datetrans, $reservation_id);
    
    if ($stmt->execute()) {
        echo "<h1>Merci pour votre paiement de {$total}DH !</h1>";
        echo "<p>Votre réservation est confirmée.</p>";
        // recuperer id de transaction
        $transaction_id = $stmt->insert_id;
        $_SESSION['transaction_id'] = $transaction_id;

        // Mise à jour de la réservation
        $sqlreservation = "UPDATE reservation SET etat_reservation = 'confirmée' WHERE id_reservation = ?";
        $stmtreservation = $conn->prepare($sqlreservation);
        $stmtreservation->bind_param("i", $reservation_id);
        $stmtreservation->execute();
        $stmtreservation->close();

        // Mise à jour du statut de la chambre
        $sqlchambre = "UPDATE chambre SET statut = 'reservée' WHERE id_chambre = (SELECT id_chambre FROM reservation WHERE id_reservation = ?)";
        $stmtchambre = $conn->prepare($sqlchambre);
        $stmtchambre->bind_param("i", $reservation_id);
        $stmtchambre->execute();
        $stmtchambre->close();

        // Mise à jour de l'ID client dans la réservation
        $sqlresrvclient = "UPDATE reservation SET id_client = ? WHERE id_reservation = ?";
        $stmtresrvclient = $conn->prepare($sqlresrvclient);
        $stmtresrvclient->bind_param("ii", $client_id, $reservation_id);
        $stmtresrvclient->execute();
        $stmtresrvclient->close();

        // Redirection vers la page de téléchargement du reçu 
        header("Location: telecharger_recu_reservation.php?reservation_id=$reservation_id&transaction_id=$transaction_id&id_client=$client_id&total=$total");
        exit();
    } else {
        styled_die("Erreur lors du paiement: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>