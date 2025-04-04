<?php
session_start();
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$total = $_GET['total'] ?? 0;
$reservation_id = $_GET['id_reservation'] ?? null;
if (!$reservation_id) {
    die("<h1>Erreur : Aucune réservation trouvée.</h1>");
}
// recuperer id du client 
$client_id = $_SESSION['client_id'] ?? null;
if (!$client_id) {
    die("<h1>Erreur : Vous devez être connecté pour effectuer un paiement.</h1>");
}

if(isset($_POST['payer'])) {
    $card_number = $_POST['card_number'];
    $card_name = $_POST['card_name'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
  
    // Vérification des informations
    if(empty($card_number) || empty($card_name) || empty($expiry_date) || empty($cvv)) {
        die("<h1>Erreur : Veuillez remplir tous les champs.</h1>");
    }
    // vérification le numero de carte est valide c-a-d il contient 16 chiffres
    if(!preg_match('/^\d{16}$/', $card_number)) {
        die("<h1>Erreur : Le numéro de carte doit contenir 16 chiffres.</h1>");
    }
    // vérification le nom de la carte est valide c-a-d il contient que des lettres
    if(!preg_match('/^[a-zA-Z\s]+$/', $card_name)) {
        die("<h1>Erreur : Le nom sur la carte doit contenir uniquement des lettres.</h1>");
    }
    // vérification la date d'expiration est valide c-a-d elle est au format MM/AA
    if(!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $expiry_date)) {
        die("<h1>Erreur : La date d'expiration doit être au format MM/AA.</h1>");
    }
    // vérification le cvv est valide c-a-d il contient 3 chiffres
    if(!preg_match('/^\d{3}$/', $cvv)) {
        die("<h1>Erreur : Le CVV doit contenir 3 chiffres.</h1>");
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
        header("Location: telecharger_recu_reservation.php?reservation_id=$reservation_id & transaction_id=$transaction_id &id_client=$client_id &total=$total");
        exit(); // Important après un header Location
    } else {
        echo "<h1>Erreur : " . $stmt->error . "</h1>";
    }
    
    $stmt->close();
    $conn->close();
}
?>