<?php
session_start();

$id_client = $_GET['id_client'];
if (!isset($id_client)) {
    header("Location: login_user.html");
    exit;
}

require_once 'vendor/autoload.php';

use Mpdf\Mpdf;

$conn = new mysqli("localhost", "root", "", "tetravilla");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$reservation_id = intval($_GET['reservation_id']);

// 1. Récupération des informations de base de la réservation
$sql_reservation = "SELECT r.*, c.*, ch.* 
                    FROM reservation r
                    JOIN client c ON r.id_client = c.id_client
                    JOIN chambre ch ON r.id_chambre = ch.id_chambre
                    WHERE r.id_reservation = ?";

$stmt = $conn->prepare($sql_reservation);
if (!$stmt) {
    die("Erreur de préparation: " . $conn->error);
}

$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$result = $stmt->get_result();
$reservation = $result->fetch_assoc();
$stmt->close();

if (!$reservation) {
    die("Réservation non trouvée ou vous n'êtes pas autorisé à accéder à ce ticket!");
}

// 2. Récupération des services associés à la réservation
$services = [];
$sql_services = "SELECT s.id_service, s.no_service, s.description, s.prix
                 FROM reservation_service rs
                 JOIN service s ON rs.id_service = s.id_service
                 WHERE rs.id_reservation = ?";
$stmt_services = $conn->prepare($sql_services);
$stmt_services->bind_param("i", $reservation_id);
$stmt_services->execute();
$result_services = $stmt_services->get_result();
while ($row = $result_services->fetch_assoc()) {
    $services[] = $row;
}
$stmt_services->close();

// 3. Récupération des paquets de restauration associés à la réservation
$paquets = [];
$sql_paquets = "SELECT pr.d, pr.nom_paquet, pr.description, pr.prix
                FROM reservation_paquet_restauration rpr
                JOIN paquet_restauration pr ON rpr.paquet_restauration_id = pr.id
                WHERE rpr.reservation_id = ?";
$stmt_paquets = $conn->prepare($sql_paquets);
$stmt_paquets->bind_param("i", $reservation_id);
$stmt_paquets->execute();
$result_paquets = $stmt_paquets->get_result();
while ($row = $result_paquets->fetch_assoc()) {
    $paquets[] = $row;
}
$stmt_paquets->close();

$conn->close();

// Calcul des dates et durées
$date_arrivee = new DateTime($reservation['date_arrivee']);
$date_depart = new DateTime($reservation['date_depart']);
$nuits = $date_depart->diff($date_arrivee)->days;

// recuperer le total
$total = $_GET['total'] ;

// Préparation du contenu HTML
try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => [80, 150], // Format ticket
        'margin_left' => 5,
        'margin_right' => 5,
        'margin_top' => 5,
        'margin_bottom' => 5,
    ]);

    // Entête du ticket
    $html = "
    <style>
        body { font-family: Arial, sans-serif; font-size: 9pt; }
        h1 { color: #b68b8b; text-align: center; font-size: 12pt; margin-bottom: 5px; }
        .info { margin: 3px 0; }
        .section { margin-top: 8px; border-top: 1px dashed #ccc; padding-top: 5px; }
        .total { font-weight: bold; margin-top: 10px; }
        .footer { text-align: center; font-size: 8pt; margin-top: 10px; color: #666; }
    </style>
    
    <h1>TICKET DE RÉSERVATION</h1>
    <div style='text-align: center;'>Référence: <b>RES-{$reservation['id_reservation']}</b></div>
    
    <div class='section'>
        <div style='font-weight: bold;'>INFORMATIONS CLIENT</div>
        <div class='info'>Nom: {$reservation['nom']} {$reservation['prenom']}</div>
        <div class='info'>Email: {$reservation['email']}</div>
        <div class='info'>Téléphone: {$reservation['telephone']}</div>
    </div>
    
    <div class='section'>
        <div style='font-weight: bold;'>DÉTAILS RÉSERVATION</div>
        <div class='info'>Chambre: {$reservation['id_chambre']} ({$reservation['type']})</div>
        <div class='info'>Arrivée: " . date('d/m/Y', strtotime($reservation['date_arrivee'])) . "</div>
        <div class='info'>Départ: " . date('d/m/Y', strtotime($reservation['date_depart'])) . "</div>
        <div class='info'>Nuitées: $nuits</div>
        <div class='info'>Prix/nuit: " . number_format($reservation['tarif'], 2) . " DH</div>
        <div class='info'>Total paye: " . $total . " DH</div>
    </div>";

    // Services supplémentaires
    if (!empty($services)) {
        $html .= "
        <div class='section'>
            <div style='font-weight: bold;'>SERVICES SUPPLÉMENTAIRES</div>";
        
        foreach ($services as $service) {
            $prix_services += $service['prix']; // On ajoute simplement le prix du service
            $html .= "
            <div class='info'>
                - {$service['nom']}: " . number_format($service['prix'], 2) . " DH
            </div>";
        }
        
        $html .= "</div>";
    }

    // Paquets de restauration
    if (!empty($paquets)) {
        $html .= "
        <div class='section'>
            <div style='font-weight: bold;'>PAQUETS RESTAURATION</div>";
        
        foreach ($paquets as $paquet) {
            $prix_paquets += $paquet['prix'];
            $date_utilisation = date('d/m/Y', strtotime($paquet['date_utilisation']));
            $html .= "
            <div class='info'>
                - {$paquet['nom']} ($date_utilisation): " . number_format($paquet['prix'], 2) . " DH
            </div>";
        }
        
        $html .= "</div>";
    }

    // Total
    $total = $prix_chambre + $prix_services + $prix_paquets;
    
    $html .= "
    <div class='section total'>
        <div>TOTAL: " . number_format($total, 2) . " DH</div>
    </div>
    
    <div class='footer'>
        <div>Merci pour votre confiance!</div>
        <div>Généré le " . date('d/m/Y H:i') . "</div>
    </div>";

    $mpdf->WriteHTML($html);

    // Sauvegarde et téléchargement
    $filename = "ticket_reservation_{$reservation_id}.pdf";
    $filepath = __DIR__ . "/tickets/" . $filename;
    
    if (!file_exists(__DIR__ . "/tickets")) {
        mkdir(__DIR__ . "/tickets", 0777, true);
    }

    $mpdf->Output($filepath, 'F');
    $mpdf->Output($filename, 'D');

} catch (Exception $e) {
    die("Erreur lors de la génération du ticket: " . $e->getMessage());
}
?>