<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensuite le reste du code PHP
require_once "connect_base.php";
require_once "Email_pdf.php";
$id_transaction = null; // Variable pour stocker l'ID de transaction
$id_facture = null; // Variable pour stocker l'ID de facture


$type_stock = $_GET['type_stock'] ?? '';
if(empty($type_stock)) die("Type de stock non spécifié");

$id_gest = $_GET['id'] ?? '';
if(empty($id_gest)) die("id gestionnaire non spécifié");

$message = "";
$selected_fournisseur = "";
$produits = [];
$show_facture = false;
$facture_data = [];
$show_success = false; // Ajoutez cette ligne

// Fonction pour envoyer un email au fournisseur - modifiée pour utiliser votre fonction sendemail
function envoyerEmailFournisseur($fournisseur, $id_commande, $lignes_commande, $montant_total) {
    $to = $fournisseur['email'];
    $subject = "Nouvelle commande #$id_commande";
    
    // Construction du corps du mail en HTML pour une meilleure présentation
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background-color: #f0f0f0; padding: 15px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .total { font-weight: bold; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Bonjour " . $fournisseur['nom_fournisseur'] . ",</h2>
            <p>Nous avons le plaisir de vous passer la commande suivante :</p>
        </div>
        <p><strong>Numéro de commande:</strong> $id_commande</p>
        <p><strong>Date:</strong> " . date('d/m/Y') . "</p>
        
        <h3>Détails de la commande:</h3>
        <table>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>";
    
    foreach($lignes_commande as $item) {
        $total_ligne = $item['prix_produit'] * $item['qte_comm'];
        $body .= "
            <tr>
                <td>" . $item['nom_produit'] . "</td>
                <td>" . $item['qte_comm'] . "</td>
                <td>" . number_format($item['prix_produit'], 2) . " €</td>
                <td>" . number_format($total_ligne, 2) . " €</td>
            </tr>";
    }
    
    $body .= "
        </table>
        <p class='total'>Total: " . number_format($montant_total, 2) . " €</p>
        <p>Cordialement,<br>L'équipe de TetraVilla</p>
    </body>
    </html>";
    
    // Utilisation de la fonction sendemail de votre fichier Email_pdf.php
    return sendemail($to, $subject, $body);
}
// Charger produits si fournisseur choisi
if(isset($_POST['select_fournisseur']) && !empty($_POST['fournisseur'])) {
    $selected_fournisseur = $_POST['fournisseur'];

    // SOLUTION: Réinitialiser la commande si on change de fournisseur
    if(isset($_SESSION['id_fournisseur']) && $_SESSION['id_fournisseur'] != $selected_fournisseur) {
        unset($_SESSION['commande']);
        unset($_SESSION['id_fournisseur']);
    }

    $stmt = $conn->prepare("SELECT id_produit, nom_produit, prix_produit FROM produit WHERE id_fournisseur = ?");
    $stmt->execute([$selected_fournisseur]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Ajouter produit à la commande
if(isset($_POST['add_to_commande'])) {
    $id_produit = $_POST['produit'];
    $quantite = $_POST['quantite'];
    $selected_fournisseur = $_POST['fournisseur'];

    $stmt = $conn->prepare("SELECT nom_produit, prix_produit FROM produit WHERE id_produit = ?");
    $stmt->execute([$id_produit]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if($produit) {
        if(!isset($_SESSION['commande'])) {
            $_SESSION['commande'] = [];
            $_SESSION['id_fournisseur'] = $selected_fournisseur;
        }

        $existe = false;
        foreach($_SESSION['commande'] as $key => $item) {
            if($item['id_produit'] == $id_produit) {
                $_SESSION['commande'][$key]['quantite'] += $quantite;
                $existe = true;
                break;
            }
        }

        if(!$existe) {
            $_SESSION['commande'][] = [
                'id_produit' => $id_produit,
                'nom_produit' => $produit['nom_produit'],
                'prix' => $produit['prix_produit'],
                'quantite' => $quantite
            ];
        }

        $message = "Produit ajouté à la commande.";

        $stmt = $conn->prepare("SELECT id_produit, nom_produit, prix_produit FROM produit WHERE id_fournisseur = ?");
        $stmt->execute([$selected_fournisseur]);
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Valider la commande et générer facture
if(isset($_POST['valider_commande']) && isset($_SESSION['commande']) && !empty($_SESSION['commande'])) {
    $id_fournisseur = $_SESSION['id_fournisseur'];
    $stmt = $conn->prepare("SELECT nom_fournisseur, prenom_fournisseur, email FROM fournisseur WHERE id_fournisseur = ?");
    $stmt->execute([$id_fournisseur]);
    $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

    $montant_total = 0;
    foreach($_SESSION['commande'] as $item) {
        $montant_total += $item['prix'] * $item['quantite'];
    }

    // Vérifier le budget disponible
    $stmt = $conn->prepare("SELECT monnaiestock FROM gestionnaire_stock WHERE id_gestionnaire = ? AND type_stock = ?");
    $stmt->execute([$id_gest, $type_stock]);
    $monnaie_stock = $stmt->fetchColumn();

    if($monnaie_stock < $montant_total) {
        $message = "Erreur: Budget insuffisant. Budget disponible: " . number_format($monnaie_stock, 2) . " €, Montant de la commande: " . number_format($montant_total, 2) . " €";
    } else {
        try {
            $conn->beginTransaction();

            // 1. Créer la commande avec id_gestionnaire
            $stmt = $conn->prepare("INSERT INTO commande (etat, date_commande, id_fournisseur, id_gestionnaire_stock) VALUES (?, NOW(), ?, ?)");
            $stmt->execute(['En attente', $id_fournisseur, $id_gest]);
            $id_commande = $conn->lastInsertId();

            // 2. Ajouter les lignes de commande
            foreach($_SESSION['commande'] as $item) {
                $stmt = $conn->prepare("INSERT INTO ligne_commande (id_commande, id_produit, qte_comm) VALUES (?, ?, ?)");
                $stmt->execute([$id_commande, $item['id_produit'], $item['quantite']]);
            }

            // 3. Créer la facture
            $description_facture = "Facture pour commande du stock #$id_commande - Fournisseur: " . $fournisseur['nom_fournisseur'];
            $stmt = $conn->prepare("INSERT INTO facture (description, montant, statut, type, id_comm) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$description_facture, $montant_total, 'A payer', 'stock', $id_commande]);
            $id_facture = $conn->lastInsertId();

            // 4. Lier la facture à la commande
            $stmt = $conn->prepare("UPDATE commande SET id_fact = ? WHERE id_comm = ?");
            $stmt->execute([$id_facture, $id_commande]);

            $conn->commit();

            // Préparer les données pour l'affichage
            $facture_data = [
                'id_commande' => $id_commande,
                'id_facture' => $id_facture,
                'fournisseur' => $fournisseur['nom_fournisseur'] . ' ' . $fournisseur['prenom_fournisseur'],
                'date' => date('d/m/Y H:i'),
                'montant_total' => $montant_total,
                'lignes' => $_SESSION['commande']
            ];

            $show_facture = true;
            unset($_SESSION['commande'], $_SESSION['id_fournisseur']);

        } catch(Exception $e) {
            $conn->rollBack();
            $message = "Erreur lors de la validation de la commande: " . $e->getMessage();
        }
    }
}

// Traitement du paiement
if(isset($_POST['proceder_paiement'])) {
    $id_facture = $_POST['id_facture'];
    $montant_total = $_POST['montant_total'];
    $id_commande = $_POST['id_commande'];
    
    try {
        $conn->beginTransaction();
        
        // 1. Vérifier à nouveau le budget
        $stmt = $conn->prepare("SELECT monnaiestock FROM gestionnaire_stock WHERE id_gestionnaire = ? AND type_stock = ?");
        $stmt->execute([$id_gest, $type_stock]);
        $monnaie_stock = $stmt->fetchColumn();
        
        if($monnaie_stock < $montant_total) {
            throw new Exception("Budget insuffisant pour effectuer le paiement");
        }
        
        // 2. Créer la transaction
        $stmt = $conn->prepare("INSERT INTO transaction (montant_trans, date_trans, typeTrans, id_agent_financier) VALUES (?, NOW(), 'Paiement Stock', NULL)");
        $stmt->execute([$montant_total]);
        $id_transaction = $conn->lastInsertId();
        
        // 3. Mettre à jour la facture avec l'ID de transaction
        $stmt = $conn->prepare("UPDATE facture SET statut = 'Payée', id_transaction = ? WHERE id_fac = ?");
        $stmt->execute([$id_transaction, $id_facture]);
        
        // ✅ Mettre à jour la commande comme validée
        $stmt = $conn->prepare("UPDATE commande SET etat = 'Validée' WHERE id_comm = ?");
        $stmt->execute([$id_commande]); 

        // 4. Mettre à jour le budget
        $nouveau_budget = $monnaie_stock - $montant_total;
        $stmt = $conn->prepare("UPDATE gestionnaire_stock SET monnaiestock = ? WHERE id_gestionnaire = ? AND type_stock = ?");
        $stmt->execute([$nouveau_budget, $id_gest, $type_stock]);
        
        // 5. Générer le reçu
        $details_recu = "Paiement de la facture #$id_facture pour la commande du stock #$id_commande";
        $stmt = $conn->prepare("INSERT INTO recu (details, type, DateEmission, id_transaction) VALUES (?, 'Paiement Stock', NOW(), ?)");
        $stmt->execute([$details_recu, $id_transaction]);
        
        // 6. Envoyer la commande au fournisseur par email
        $stmt = $conn->prepare("SELECT nom_fournisseur, prenom_fournisseur, email FROM fournisseur WHERE id_fournisseur = (SELECT id_fournisseur FROM commande WHERE id_comm = ?)");
        $stmt->execute([$id_commande]);
        $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $conn->prepare("SELECT p.nom_produit, lc.qte_comm, p.prix_produit FROM ligne_commande lc JOIN produit p ON lc.id_produit = p.id_produit WHERE lc.id_commande = ?");
        $stmt->execute([$id_commande]);
        $lignes_commande = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Appel à la fonction modifiée
        envoyerEmailFournisseur($fournisseur, $id_commande, $lignes_commande, $montant_total);
        
        $conn->commit();
        
       
        $message = "Paiement effectué avec succès!<br>"
             . "Montant payé: " . number_format($montant_total, 2) . " €<br>"
             . "Nouveau solde disponible: " . number_format($nouveau_budget, 2) . " €<br>"
             . "La commande a été envoyée au fournisseur.";
    
        $show_facture = false; // Cache la facture après paiement
        $show_success = true; // Afficher le message de succès avec bouton de téléchargement
        
        // Stocker les IDs pour le téléchargement PDF
        $_SESSION['pdf_id_facture'] = $id_facture;
        $_SESSION['pdf_id_transaction'] = $id_transaction;
        // SOLUTION: Ajoutez ces lignes pour réinitialiser complètement la commande
        unset($_SESSION['commande']);
        unset($_SESSION['id_fournisseur']);
        
    } catch(Exception $e) {
        $conn->rollBack();
        $message = "Erreur lors du paiement: " . $e->getMessage();
    }
}

include("telecharger_recu_stock.php");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes</title>
    <style>
      
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 15px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #8b1e3f;
        }
        .btn-primary:hover {
            background-color: #8b1e3f;
        }
        .btn-paiement {
            background-color:#be9393;
        }
        .btn-paiement:hover {
            background-color:#be9393;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        thead {
            background-color: #f2f2f2;
        }
        tfoot {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .facture {
            margin-top: 30px;
            padding: 25px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .facture-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .facture-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .facture-details {
            text-align: right;
            color: #555;
        }
        .fournisseur-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Commande d'Approvisionnement</h1>
        
        <?php if($message): ?>
            <div class="message <?php echo strpos($message, 'Erreur') !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if($show_success && isset($_SESSION['pdf_id_facture']) && isset($_SESSION['pdf_id_transaction'])): ?>
            <div style="margin-top: 15px; margin-bottom: 20px;">
                <a href="dashboard_stock2.php?page=newCommande&type_stock=<?php echo urlencode($type_stock); ?>&id=<?php echo urlencode($id_gest); ?>&download_recu=1&id_facture=<?php echo $_SESSION['pdf_id_facture']; ?>&id_transaction=<?php echo $_SESSION['pdf_id_transaction']; ?>" class="btn-download" style="display: inline-block; text-decoration: none; padding: 10px 15px; color: white; background-color: #FF9800; border-radius: 4px;">Télécharger le reçu (PDF)</a>
            </div>
            
            <!-- Ajout du formulaire de sélection de fournisseur après le message de succès -->
            <h3>Nouvelle commande</h3>
            <form method="post" action="">
                <div class="form-group">
                    <label for="fournisseur">Fournisseur:</label>
                    <select name="fournisseur" id="fournisseur" required>
                        <option value="">-- Sélectionnez un fournisseur --</option>
                        <?php
                        $sql = "SELECT id_fournisseur, nom_fournisseur, prenom_fournisseur, email 
                                FROM fournisseur 
                                WHERE categorie_fournit = :type_stock
                                ORDER BY nom_fournisseur";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':type_stock', $type_stock);
                        $stmt->execute();

                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $nom_complet = htmlspecialchars($row['nom_fournisseur'] . ' ' . htmlspecialchars($row['prenom_fournisseur']));
                            echo "<option value='{$row['id_fournisseur']}'>$nom_complet ({$row['email']})</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="select_fournisseur" class="btn-primary">Afficher les produits</button>
                </div>
            </form>
        <?php elseif(!$show_facture): ?>
            <!-- Étape 1: Sélection du fournisseur -->
            <form method="post" action="">
                <div class="form-group">
                    <label for="fournisseur">Fournisseur:</label>
                    <select name="fournisseur" id="fournisseur" required>
                        <option value="">-- Sélectionnez un fournisseur --</option>
                        <?php
                        $sql = "SELECT id_fournisseur, nom_fournisseur, prenom_fournisseur, email 
                                FROM fournisseur 
                                WHERE categorie_fournit = :type_stock
                                ORDER BY nom_fournisseur";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':type_stock', $type_stock);
                        $stmt->execute();

                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($selected_fournisseur == $row['id_fournisseur']) ? 'selected' : '';
                            $nom_complet = htmlspecialchars($row['nom_fournisseur'] . ' ' . htmlspecialchars($row['prenom_fournisseur']));
                            echo "<option value='{$row['id_fournisseur']}' $selected>$nom_complet ({$row['email']})</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="select_fournisseur" class="btn-primary">Afficher les produits</button>
                </div>
            </form>
            
            <!-- Étape 2: Sélection des produits -->
            <?php if($selected_fournisseur && !empty($produits)): ?>
                <form method="post" action="">
                    <input type="hidden" name="fournisseur" value="<?php echo $selected_fournisseur; ?>">
                    
                    <div class="form-group">
                        <label for="produit">Produit:</label>
                        <select name="produit" id="produit" required>
                            <option value="">-- Sélectionnez un produit --</option>
                            <?php foreach($produits as $produit): ?>
                                <option value="<?php echo $produit['id_produit']; ?>">
                                    <?php echo htmlspecialchars($produit['nom_produit']) . ' - ' . number_format($produit['prix_produit'], 2) . ' €'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantite">Quantité:</label>
                        <input type="number" name="quantite" id="quantite" min="1" value="1" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="add_to_commande" class="btn-primary">Ajouter à la commande</button>
                    </div>
                </form>
            <?php elseif($selected_fournisseur): ?>
                <div class="message error">Aucun produit disponible pour ce fournisseur.</div>
            <?php endif; ?>
            
            <!-- Validation de la commande -->
            <?php if(isset($_SESSION['commande']) && !empty($_SESSION['commande'])): ?>
                <form method="post" action="">
                    <div class="form-actions">
                        <button type="submit" name="valider_commande" class="btn-primary">Valider la commande</button>
                    </div>
                </form>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Affichage de la facture après validation -->
            <div class="facture">
                <div class="facture-header">
                    <div class="facture-title">Facture N° <?php echo $facture_data['id_facture']; ?></div>
                    <div class="facture-details">
                        <div><strong>Date:</strong> <?php echo $facture_data['date']; ?></div>
                        <div><strong>Commande N°:</strong> <?php echo $facture_data['id_commande']; ?></div>
                    </div>
                </div>
                
                <div class="fournisseur-info">
                    <strong>Fournisseur:</strong> <?php echo htmlspecialchars($facture_data['fournisseur']); ?>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($facture_data['lignes'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nom_produit']); ?></td>
                                <td><?php echo number_format($item['prix'], 2); ?> €</td>
                                <td><?php echo $item['quantite']; ?></td>
                                <td><?php echo number_format($item['prix'] * $item['quantite'], 2); ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Total TTC</strong></td>
                            <td><strong><?php echo number_format($facture_data['montant_total'], 2); ?> €</strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="form-actions">
                    <!-- CORRECTION: Remplacement du onclick par un formulaire qui soumet correctement les données -->
                    <form method="post" action="">
                        <input type="hidden" name="id_facture" value="<?php echo $facture_data['id_facture']; ?>">
                        <input type="hidden" name="id_commande" value="<?php echo $facture_data['id_commande']; ?>">
                        <input type="hidden" name="montant_total" value="<?php echo $facture_data['montant_total']; ?>">
                        <button type="submit" name="proceder_paiement" class="btn-paiement">Procéder au paiement</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>