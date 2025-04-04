<?php
// Configuration de la base de données
require_once "connect_base.php"; 
// Initialisation des variables
session_start();
$message = "";
$selected_fournisseur = "";
$produits = [];

// Si un fournisseur est sélectionné, charger ses produits
if(isset($_POST['select_fournisseur']) && !empty($_POST['fournisseur'])) {
    $selected_fournisseur = $_POST['fournisseur'];
    
    // Récupérer les produits du fournisseur
    $stmt = $conn->prepare("SELECT id_produit, nom_produit, prix_produit FROM produit WHERE id_fournisseur = ?");
    $stmt->execute([$selected_fournisseur]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Traitement du formulaire pour ajouter un produit à la commande
if(isset($_POST['add_to_commande'])) {
    $id_produit = $_POST['produit'];
    $quantite = $_POST['quantite'];
    $selected_fournisseur = $_POST['fournisseur']; // Conserver le fournisseur sélectionné
    
    // Récupérer les informations du produit
    $stmt = $conn->prepare("SELECT nom_produit, prix_produit FROM produit WHERE id_produit = ?");
    $stmt->execute([$id_produit]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($produit) {
        // Ajouter à la session de commande
        if(!isset($_SESSION['commande'])) {
            $_SESSION['commande'] = [];
            $_SESSION['id_fournisseur'] = $selected_fournisseur; // Sauvegarder l'ID du fournisseur
        }
        
        // Vérifier si le produit existe déjà dans la commande
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
        
        $message = "Produit ajouté à la commande avec succès!";
        
        // Recharger les produits pour le formulaire
        $stmt = $conn->prepare("SELECT id_produit, nom_produit, prix_produit FROM produit WHERE id_fournisseur = ?");
        $stmt->execute([$selected_fournisseur]);
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Traitement de la validation de la commande
if(isset($_POST['valider_commande']) && isset($_SESSION['commande']) && !empty($_SESSION['commande'])) {
    $id_fournisseur = $_SESSION['id_fournisseur'];
    
    // Récupérer l'email du fournisseur
    $stmt = $conn->prepare("SELECT email FROM fournisseur WHERE id_fournisseur = ?");
    $stmt->execute([$id_fournisseur]);
    $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculer le montant total
    $montant_total = 0;
    foreach($_SESSION['commande'] as $item) {
        $montant_total += $item['prix'] * $item['quantite'];
    }
    
    try {
        // Démarrer une transaction
        $conn->beginTransaction();
        
        // Insérer la commande
        $stmt = $conn->prepare("INSERT INTO commande (etat, date_commande, id_fournisseur) VALUES (?, NOW(), ?)");
        $stmt->execute(['En attente', $id_fournisseur]);
        $id_commande = $conn->lastInsertId();
        
        // Insérer les lignes de commande
        foreach($_SESSION['commande'] as $item) {
            $stmt = $conn->prepare("INSERT INTO ligne_commande (id_commande, id_produit, qte_comm) VALUES (?, ?, ?)");
            $stmt->execute([$id_commande, $item['id_produit'], $item['quantite']]);
        }
        
        // Créer la facture
        $stmt = $conn->prepare("INSERT INTO facture (description, montant, statut, type, id_comm) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(["Facture pour commande #$id_commande", $montant_total, 'À payer', 'Achat', $id_commande]);
        $id_facture = $conn->lastInsertId();
        
        // Mettre à jour l'ID de facture dans la commande
        $stmt = $conn->prepare("UPDATE commande SET id_fact = ? WHERE id_comm = ?");
        $stmt->execute([$id_facture, $id_commande]);
        
        // Valider la transaction
        $conn->commit();
        
        // Envoyer un email au fournisseur
        if(isset($fournisseur['email'])) {
            $sujet = "Nouvelle commande #$id_commande";
            
            $corps_message = "Bonjour,\n\nVous avez reçu une nouvelle commande (Commande #$id_commande).\n\n";
            $corps_message .= "Détails de la commande:\n";
            
            foreach($_SESSION['commande'] as $item) {
                $corps_message .= "- {$item['nom_produit']} x {$item['quantite']} : " . ($item['prix'] * $item['quantite']) . " €\n";
            }
            
            $corps_message .= "\nMontant total: $montant_total €\n\n";
            $corps_message .= "Merci de traiter cette commande dans les plus brefs délais.\n\nCordialement,";
            
            mail($fournisseur['email'], $sujet, $corps_message);
        }
        
        // Vider la commande en cours
        unset($_SESSION['commande']);
        unset($_SESSION['id_fournisseur']);
        $selected_fournisseur = "";
        
        $message = "Commande validée avec succès! Numéro de commande: $id_commande";
    } catch(Exception $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollBack();
        $message = "Erreur lors de la validation de la commande: " . $e->getMessage();
    }
}

// Fonction pour générer le récapitulatif de la commande
function genererRecapitulatif() {
    if(isset($_SESSION['commande']) && !empty($_SESSION['commande'])) {
        $html = '<div class="recap-commande">';
        $html .= '<h3>Récapitulatif de la commande</h3>';
        $html .= '<table class="table">';
        $html .= '<thead><tr><th>Produit</th><th>Prix unitaire</th><th>Quantité</th><th>Total</th></tr></thead>';
        $html .= '<tbody>';
        
        $total = 0;
        foreach($_SESSION['commande'] as $item) {
            $sous_total = $item['prix'] * $item['quantite'];
            $total += $sous_total;
            
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item['nom_produit']) . '</td>';
            $html .= '<td>' . number_format($item['prix'], 2) . ' €</td>';
            $html .= '<td>' . $item['quantite'] . '</td>';
            $html .= '<td>' . number_format($sous_total, 2) . ' €</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '<tfoot><tr><th colspan="3">Total</th><th>' . number_format($total, 2) . ' €</th></tr></tfoot>';
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }
    
    return '';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Commande</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
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
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #45a049;
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
            padding: 10px;
            text-align: left;
        }
        thead {
            background-color: #f2f2f2;
        }
        .recap-commande {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Formulaire de Commande</h1>
        
        <?php if($message): ?>
            <div class="message <?php echo strpos($message, 'Erreur') !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Première étape : sélection du fournisseur -->
        <form method="post" action="">
            <div class="form-group">
                <label for="fournisseur">Sélectionner un fournisseur:</label>
                <select name="fournisseur" id="fournisseur" required>
                    <option value="">-- Choisir un fournisseur --</option>
                    <?php
                    $stmt = $conn->query("SELECT id_fournisseur, nom_fournisseur, email FROM fournisseur");
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($selected_fournisseur == $row['id_fournisseur']) ? 'selected' : '';
                        echo '<option value="' . $row['id_fournisseur'] . '" ' . $selected . '>' 
                            . htmlspecialchars($row['nom_fournisseur']) . ' (' . htmlspecialchars($row['email']) . ')</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="select_fournisseur">Afficher les produits</button>
            </div>
        </form>
        
        <!-- Deuxième étape : sélection des produits -->
        <?php if($selected_fournisseur && !empty($produits)): ?>
            <form method="post" action="">
                <input type="hidden" name="fournisseur" value="<?php echo $selected_fournisseur; ?>">
                
                <div class="form-group">
                    <label for="produit">Sélectionner un produit:</label>
                    <select name="produit" id="produit" required>
                        <option value="">-- Choisir un produit --</option>
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
                    <button type="submit" name="add_to_commande">Ajouter à la commande</button>
                </div>
            </form>
        <?php elseif($selected_fournisseur): ?>
            <p>Aucun produit disponible pour ce fournisseur.</p>
        <?php endif; ?>
        
        <!-- Récapitulatif de la commande -->
        <?php echo genererRecapitulatif(); ?>
        
        <!-- Validation de la commande -->
        <?php if(isset($_SESSION['commande']) && !empty($_SESSION['commande'])): ?>
            <form method="post" action="">
                <div class="form-actions">
                    <button type="submit" name="valider_commande">proceder au paiement </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>