<?php
require_once 'vendor/autoload.php'; // Assurez-vous que mPDF est installé via Composer

use Mpdf\Mpdf;

if(isset($_GET['download_recu']) && isset($_GET['id_facture']) && isset($_GET['id_transaction'])) {
    $id_facture = $_GET['id_facture'];
    $id_transaction = $_GET['id_transaction'];
    
 
     // Requête SQL corrigée (virgule en trop supprimée après f.type AS facture_type)
     $sql = "SELECT r.*, t.id_trans, t.montant_trans, t.date_trans, t.typeTrans, 
     f.description AS facture_desc, f.montant AS facture_montant, f.type AS facture_type
     FROM recu r 
     JOIN transaction t ON r.id_transaction = t.id_trans
     JOIN facture f ON f.id_transaction = t.id_trans
     JOIN commande c ON f.id_comm = c.id_comm
     JOIN fournisseur fr ON c.id_fournisseur = fr.id_fournisseur
     WHERE f.id_fac = ? AND t.id_trans = ? AND f.statut = 'Payée'";

$stmt = $conn->prepare($sql);
$stmt->execute([$id_facture, $id_transaction]);
$recu_data = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $conn->prepare($sql);
    if($recu_data) {
        // Récupérer les détails des produits dans la commande
        $sql_produits = "SELECT p.nom_produit, lc.qte_comm, p.prix_produit 
                         FROM ligne_commande lc 
                         JOIN produit p ON lc.id_produit = p.id_produit 
                         WHERE lc.id_commande = ?";
        $stmt = $conn->prepare($sql_produits);
        $stmt->execute([$recu_data['id_comm']]);
        $produits_commande = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Générer le PDF avec mPDF
       // require_once 'vendor/autoload.php'; // Assurez-vous que mpdf est installé
  
      
       
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 15,
            'margin_right' => 15,
            'margin_bottom' => 15,
            'margin_left' => 15
        ]);
        
        // Construire le contenu HTML du PDF
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; margin-bottom: 20px; }
                .title { font-size: 22px; font-weight: bold; }
                .info { margin-bottom: 15px; }
                .info-row { margin-bottom: 5px; }
                .info-label { font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; }
                .total { font-weight: bold; text-align: right; padding: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">REÇU DE PAIEMENT</div>
                <div>TetraVilla</div>
            </div>
            
            <div class="info">
                <div class="info-row"><span class="info-label">N° de Reçu:</span> ' . $recu_data['id_recu'] . '</div>
                <div class="info-row"><span class="info-label">Date d\'émission:</span> ' . date('d/m/Y H:i', strtotime($recu_data['DateEmission'])) . '</div>
                <div class="info-row"><span class="info-label">Type:</span> ' . $recu_data['type'] . '</div>
            </div>
            
            <div class="info">
                <div class="info-row"><span class="info-label">N° de Transaction:</span> ' . $recu_data['id_trans'] . '</div>
                <div class="info-row"><span class="info-label">Date de Transaction:</span> ' . date('d/m/Y H:i', strtotime($recu_data['date_trans'])) . '</div>
                <div class="info-row"><span class="info-label">Type de Transaction:</span> ' . $recu_data['typeTrans'] . '</div>
            </div>
            
            <div class="info">
                <div class="info-row"><span class="info-label">N° de Facture:</span> ' . $id_facture . '</div>
                <div class="info-row"><span class="info-label">Description Facture:</span> ' . $recu_data['facture_desc'] . '</div>
                <div class="info-row"><span class="info-label">Type Stock:</span> ' . $recu_data['type_stock'] . '</div>
            </div>
            
            <div class="info">
                <div class="info-row"><span class="info-label">Fournisseur:</span> ' . $recu_data['nom_fournisseur'] . ' ' . $recu_data['prenom_fournisseur'] . '</div>
            </div>
            
            <h3>Détails de la Commande</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';
        
        $total = 0;
        foreach($produits_commande as $produit) {
            $prix_total = $produit['qte_comm'] * $produit['prix_produit'];
            $total += $prix_total;
            $html .= '
                <tr>
                    <td>' . $produit['nom_produit'] . '</td>
                    <td>' . $produit['qte_comm'] . '</td>
                    <td>' . number_format($produit['prix_produit'], 2) . ' €</td>
                    <td>' . number_format($prix_total, 2) . ' €</td>
                </tr>';
        }
        
        $html .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="total">Total:</td>
                        <td>' . number_format($recu_data['montant_trans'], 2) . ' €</td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="info">
                <div class="info-row"><span class="info-label">Détails:</span> ' . $recu_data['details'] . '</div>
            </div>
            
            <div class="footer">
                <p>Ce reçu est généré électroniquement et est valide sans signature.</p>
                <p>Pour toute question concernant cette transaction, veuillez contacter le service financier.</p>
            </div>
        </body>
        </html>';
        
        $mpdf->WriteHTML($html);
        
        // Sortie du PDF
        $filename = 'Recu_factureStock' . $id_facture . '_' . date('Ymd') . '.pdf';
        $mpdf->Output($filename, 'D');
        exit;



    }
}
?>