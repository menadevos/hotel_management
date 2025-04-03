<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent_departement') {
    header("Location: login.php");
    exit;
}

require_once 'vendor/autoload.php'; // Assurez-vous que mPDF est installé via Composer

use Mpdf\Mpdf;

$conn = new mysqli("localhost", "root", "", "hotel");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$facture_id = intval($_GET['facture_id']);
$user_id = $_SESSION['user_id'];

// Requête pour récupérer les détails du reçu, de la transaction et de la facture
$sql = "SELECT r.*, t.id_trans, t.montant_trans, t.date_trans, t.typeTrans, 
               f.description AS facture_desc, f.montant AS facture_montant, f.type AS facture_type
        FROM recu r 
        JOIN transaction t ON r.id_transaction = t.id_trans 
        JOIN facture f ON f.id_transaction = t.id_trans
        WHERE f.id_fac = ? AND f.id_agent_departement = ? AND f.statut = 'Payée'";
        
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erreur de préparation: " . $conn->error);
}

$stmt->bind_param("ii", $facture_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recu = $result->fetch_assoc();
$stmt->close();
$conn->close();

if ($recu) {
    try {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        // Contenu HTML du reçu
        $html = "
            <style>
                body { font-family: Arial, sans-serif; font-size: 12pt; }
                h1 { color: #b68b8b; text-align: center; font-size: 18pt; margin-bottom: 20px; }
                .recu-info { margin: 20px 0; }
                .recu-info p { margin: 8px 0; }
                .section-title { font-weight: bold; font-size: 14pt; margin-top: 20px; color: #4a4a4a; }
                .signature { margin-top: 50px; border-top: 1px solid #000; width: 200px; text-align: center; }
                .footer { position: absolute; bottom: 30px; width: 100%; text-align: center; font-size: 10pt; color: #666; }
            </style>
            <h1>Reçu de Paiement - TetraVilla</h1>
            <div class='recu-info'>
                <p><strong>ID Reçu:</strong> {$recu['id_recu']}</p>
                <p><strong>Détails:</strong> {$recu['details']}</p>
                <p><strong>Type:</strong> {$recu['type']}</p>
                <p><strong>Date d'émission:</strong> " . date('d/m/Y H:i', strtotime($recu['DateEmission'])) . "</p>
            </div>

            <div class='section-title'>Détails de la Transaction</div>
            <div class='recu-info'>
                <p><strong>ID Transaction:</strong> {$recu['id_trans']}</p>
                <p><strong>Montant:</strong> " . number_format($recu['montant_trans'], 2) . " DH</p>
                <p><strong>Date Transaction:</strong> " . date('d/m/Y H:i', strtotime($recu['date_trans'])) . "</p>
                <p><strong>Type Transaction:</strong> {$recu['typeTrans']}</p>
            </div>

            <div class='section-title'>Détails de la Facture</div>
            <div class='recu-info'>
                <p><strong>ID Facture:</strong> {$facture_id}</p>
                <p><strong>Description:</strong> {$recu['facture_desc']}</p>
                <p><strong>Type:</strong> {$recu['facture_type']}</p>
                <p><strong>Montant:</strong> " . number_format($recu['facture_montant'], 2) . " DH</p>
            </div>

            <div class='signature'>
                <p>Signature</p>
            </div>

            <div class='footer'>
                <p>Généré par TetraVilla - " . date('d/m/Y') . "</p>
            </div>
        ";

        // Écrire le contenu dans le PDF
        $mpdf->WriteHTML($html);

        // Chemin pour sauvegarder le fichier sur le serveur
        $file_path = __DIR__ . "/recus/recu_facture_{$facture_id}.pdf";
        if (!file_exists(__DIR__ . "/recus")) {
            mkdir(__DIR__ . "/recus", 0777, true); // Créer le dossier s'il n'existe pas
        }

        // Sauvegarder le fichier PDF sur le serveur
        $mpdf->Output($file_path, 'F');

        // Télécharger le fichier pour l'utilisateur
        $mpdf->Output("recu_facture_{$facture_id}.pdf", 'D');

    } catch (Exception $e) {
        die("Erreur lors de la génération du PDF: " . $e->getMessage());
    }
} else {
    die("Reçu non trouvé ou vous n'êtes pas autorisé à accéder à ce reçu!");
}
?>