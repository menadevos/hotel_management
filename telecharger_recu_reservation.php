<?php
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['client_id'])) {
    header("Location: login_user.html");
    exit;
}

require_once 'vendor/autoload.php';

use Mpdf\Mpdf;

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "tetravilla");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupération des paramètres
$reservation_id = intval($_GET['reservation_id']);
$transaction_id = intval($_GET['transaction_id']);
$total = floatval($_GET['total']);

// 1. Récupération des informations de base de la réservation
$sql_reservation = "SELECT r.*, c.nom, c.prenom, c.email, c.Tel, ch.type_chambre, ch.tarif
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
    die("Réservation non trouvée!");
}

// 2. Récupération des services associés à la réservation
$services = [];
$sql_services = "SELECT s.nom_service, s.type_service, s.prix
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
$sql_paquets = "SELECT pr.nom_paquet, pr.prix
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
$prix_chambre = $reservation['tarif'] * $nuits;

// Initialisation des totaux
$prix_services = 0;
$prix_paquets = 0;

// Préparation du contenu HTML
try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A5',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'default_font' => 'helvetica'
    ]);

    // Logo et en-tête
    $html = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 10px; }
        .title { color: #2c3e50; font-size: 14pt; font-weight: bold; margin-bottom: 5px; }
        .hotel-name { color: #e74c3c; font-size: 16pt; font-weight: bold; margin-bottom: 5px; }
        .info { margin: 5px 0; }
        .section { margin-top: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        .section-title { font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        .item { margin: 3px 0; }
        .total { font-weight: bold; font-size: 12pt; text-align: right; margin-top: 15px; padding-top: 5px; border-top: 2px solid #2c3e50; }
        .footer { text-align: center; font-size: 8pt; margin-top: 20px; color: #666; }
        .transaction { font-size: 9pt; color: #666; text-align: right; }
    </style>
    
    <div class="header">
        <div class="hotel-name">TETRAVILLA</div>
        <div class="title">CONFIRMATION DE RÉSERVATION</div>
        <div class="transaction">Transaction #'.$transaction_id.'</div>
    </div>';
    
    // Informations client
    $html .= '
    <div class="section">
        <div class="section-title">INFORMATIONS CLIENT</div>
        <div class="item"><strong>Nom:</strong> '.htmlspecialchars($reservation['nom']).' '.htmlspecialchars($reservation['prenom']).'</div>
        <div class="item"><strong>Email:</strong> '.htmlspecialchars($reservation['email']).'</div>
        <div class="item"><strong>Téléphone:</strong> '.htmlspecialchars($reservation['Tel']).'</div>
    </div>';
    
    // Détails de la réservation
    $html .= '
    <div class="section">
        <div class="section-title">DÉTAILS DE LA RÉSERVATION</div>
        <div class="item"><strong>Référence:</strong> RES-'.$reservation_id.'</div>
        <div class="item"><strong>Chambre:</strong> '.htmlspecialchars($reservation['type_chambre']).'</div>
        <div class="item"><strong>Période:</strong> Du '.date('d/m/Y', strtotime($reservation['date_arrivee'])).' au '.date('d/m/Y', strtotime($reservation['date_depart'])).' ('.$nuits.' nuits)</div>
        <div class="item"><strong>Prix/nuit:</strong> '.number_format($reservation['tarif'], 2).' DH</div>
        <div class="item"><strong>Total chambre:</strong> '.number_format($prix_chambre, 2).' DH</div>
    </div>';
    
    // Services supplémentaires
    if (!empty($services)) {
        $html .= '
        <div class="section">
            <div class="section-title">SERVICES SUPPLÉMENTAIRES</div>';
        
            foreach ($services as $service) {
                $prix_services += $service['prix'];
                
                // Ne pas afficher le prix si c'est un service de restauration
                if ($service['type_service'] === 'restauration') {
                    $html .= '
                    <div class="item">- '.htmlspecialchars($service['nom_service']).'</div>';
                } else {
                    $html .= '
                    <div class="item">- '.htmlspecialchars($service['nom_service']).': '.number_format($service['prix'], 2).' DH</div>';
                }
            }
        
        $html .= '
            <div class="item"><strong>Total services:</strong> '.number_format($prix_services, 2).' DH</div>
        </div>';
    }
    
    // Paquets de restauration
    if (!empty($paquets)) {
        $html .= '
        <div class="section">
            <div class="section-title">OPTIONS RESTAURATION</div>';
        
        foreach ($paquets as $paquet) {
            $prix_paquets += $paquet['prix'];
            $html .= '
            <div class="item">- '.htmlspecialchars($paquet['nom_paquet']).': '.number_format($paquet['prix'], 2).' DH</div>';
        }
        
        $html .= '
            <div class="item"><strong>Total restauration:</strong> '.number_format($prix_paquets, 2).' DH</div>
        </div>';
    }
    
    // Total et footer
    $total_calculated = $prix_chambre + $prix_services + $prix_paquets;
    
    $html .= '
    <div class="total">
        TOTAL: '.number_format($total_calculated, 2).' DH
    </div>
    
    <div class="footer">
        <div>Merci pour votre confiance et à bientôt chez Tetravilla!</div>
        <div>Reçu généré le '.date('d/m/Y à H:i').'</div>
    </div>';
    
    $mpdf->WriteHTML($html);
    
    // Nom du fichier
    $filename = "Reçu_Tetravilla_".$reservation_id.".pdf";
    
    // Téléchargement direct
    $mpdf->Output($filename, 'D');

} catch (Exception $e) {
    die("Erreur lors de la génération du reçu: " . $e->getMessage());
}
?>