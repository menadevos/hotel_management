<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Récupération des paramètres de l'URL
$type_stock = isset($_GET['type_stock']) ? $_GET['type_stock'] : '';
$id_gest = isset($_GET['id']) ? $_GET['id'] : '';
$filtre_fournisseur = isset($_GET['fournisseur']) ? $_GET['fournisseur'] : '';
$filtre_etat = isset($_GET['etat']) ? $_GET['etat'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : '';

// Traitement du formulaire de livraison
if(isset($_POST['action']) && $_POST['action'] == 'marquer_livre') {
    $id_commande = isset($_POST['id_commande']) ? $_POST['id_commande'] : '';
    $date_livraison = isset($_POST['date_livraison']) ? $_POST['date_livraison'] : '';
    
    if(!empty($id_commande) && !empty($date_livraison)) {
        try {
            // Mise à jour de la commande
            $sqlUpdate = "UPDATE commande 
                          SET etat = 'Livrée', date_livraison = :date_livraison 
                          WHERE id_comm = :id_commande";
            
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':date_livraison', $date_livraison, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id_commande', $id_commande, PDO::PARAM_INT);
            $stmtUpdate->execute();
            
            // Redirection pour éviter les soumissions multiples
            header("Location: dashboard_stock2.php?page=commandes&id=" . $id_gest . "&type_stock=" . $type_stock . 
                   "&fournisseur=" . $filtre_fournisseur . "&etat=" . $filtre_etat);
            exit;
        } catch (PDOException $e) {
            echo "<div style='color:red;'>Erreur lors de la mise à jour: " . $e->getMessage() . "</div>";
        }
    }
}

// Vérifier que l'ID du gestionnaire est bien défini
if(empty($id_gest)) {
    die("ID du gestionnaire non spécifié");
}

echo "<h1>Page des commandes</h1>";

// Connexion à la base de données déjà établie dans connect_base.php (utilisant PDO)

// Récupérer la liste des fournisseurs selon le type_stock
$sqlFournisseurs = "SELECT id_fournisseur, email 
                    FROM fournisseur 
                    WHERE categorie_fournit = :type_stock";

try {
    $stmtFournisseurs = $conn->prepare($sqlFournisseurs);
    $stmtFournisseurs->bindParam(':type_stock', $type_stock, PDO::PARAM_STR);
    $stmtFournisseurs->execute();
    $fournisseurs = $stmtFournisseurs->fetchAll(PDO::FETCH_ASSOC);
    
    // Afficher le formulaire de filtrage

    echo "<div style='
    margin: 20px 0;
    padding: 20px;
    background-color:#f2dede;
    border-left: 6px solid #711732;
    border-radius: 8px;
    color: black;
    font-family: Arial, sans-serif;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
'>";
echo "<form method='get' action='dashboard_stock2.php' id='filtreForm'>";
echo "<input type='hidden' name='page' value='commandes'>";
echo "<input type='hidden' name='id' value='" . htmlspecialchars($id_gest) . "'>";
echo "<input type='hidden' name='type_stock' value='" . htmlspecialchars($type_stock) . "'>";

    // Filtre par fournisseur
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='fournisseur'><strong>Filtrer par fournisseur:</strong></label> ";
    echo "<select name='fournisseur' id='fournisseur' style='padding: 5px;' onchange='this.form.submit()'>";
    echo "<option value=''>Tous les fournisseurs</option>";
    
    foreach($fournisseurs as $fournisseur) {
        $selected = ($filtre_fournisseur == $fournisseur['id_fournisseur']) ? 'selected' : '';
        echo "<option value='" . htmlspecialchars($fournisseur['id_fournisseur']) . "' $selected>" . htmlspecialchars($fournisseur['email']) . "</option>";
    }
    
    echo "</select> ";
    
    // Filtre par état
    echo "<label for='etat' style='margin-left: 20px;'><strong>Filtrer par état:</strong></label> ";
    echo "<select name='etat' id='etat' style='padding: 5px;' onchange='this.form.submit()'>";
    echo "<option value=''>Tous les états</option>";
    echo "<option value='En attente'" . ($filtre_etat == 'En attente' ? ' selected' : '') . ">En attente</option>";
    echo "<option value='Validée'" . ($filtre_etat == 'Validée' ? ' selected' : '') . ">Validée</option>";
    echo "<option value='Livrée'" . ($filtre_etat == 'Livrée' ? ' selected' : '') . ">Livrée</option>";
    echo "</select>";
    echo "</div>";
    echo "</form>";
    echo "</div>";

    // Construction de la requête SQL avec les filtres
    $sql = "SELECT c.*, f.email AS email_fournisseur 
            FROM commande c 
            LEFT JOIN fournisseur f ON c.id_fournisseur = f.id_fournisseur 
            WHERE c.id_gestionnaire_stock = :id_gest";
    
    // Ajouter les conditions de filtrage
    if(!empty($filtre_fournisseur)) {
        $sql .= " AND c.id_fournisseur = :filtre_fournisseur";
    }
    
    if(!empty($filtre_etat)) {
        $sql .= " AND c.etat = :filtre_etat";
    }
    
    $sql .= " ORDER BY c.date_commande DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_gest', $id_gest, PDO::PARAM_INT);
    
    if(!empty($filtre_fournisseur)) {
        $stmt->bindParam(':filtre_fournisseur', $filtre_fournisseur, PDO::PARAM_INT);
    }
    
    if(!empty($filtre_etat)) {
        $stmt->bindParam(':filtre_etat', $filtre_etat, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Afficher les données dans un tableau HTML
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>
            <th>ID</th>
            <th>État</th>
            <th>Date commande</th>
            <th>Date livraison</th>
            <th>Fournisseur</th>
            <th>Actions</th>
          </tr>";

    if(count($commandes) > 0) {
        foreach($commandes as $commande) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($commande['id_comm']) . "</td>";
            echo "<td>" . htmlspecialchars($commande['etat']) . "</td>";
            echo "<td>" . htmlspecialchars($commande['date_commande']) . "</td>";
            echo "<td>" . ($commande['date_livraison'] ? htmlspecialchars($commande['date_livraison']) : 'Non définie') . "</td>";
            echo "<td>" . htmlspecialchars($commande['email_fournisseur']) . "</td>";
            echo "<td>
                    <button onclick='afficherDetails(" . htmlspecialchars($commande['id_comm']) . ")' style='padding: 5px 10px; background-color:#be9393; color: white; border: none; border-radius: 4px; cursor: pointer;'>Voir détails</button>";
            
            // Ajout du bouton "Marquer comme livré" uniquement pour les commandes validées
            if($commande['etat'] == 'Validée') {
                echo " <button onclick='marquerCommelivre(" . htmlspecialchars($commande['id_comm']) . ")' style='margin-left: 5px; padding: 5px 10px; background-color:#6B8E23; color: white; border: none; border-radius: 4px; cursor: pointer;'>Marquer comme livré</button>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>Aucune commande ne correspond aux critères de filtrage.</td></tr>";
    }

    echo "</table>";

    // Construction de la requête pour les détails avec les mêmes filtres
    $sqlDetails = "SELECT 
                    c.*, 
                    f.email AS email_fournisseur,
                    lc.id_produit, 
                    lc.qte_comm, 
                    p.nom_produit, 
                    p.prix_produit,
                    (lc.qte_comm * p.prix_produit) AS montant_ligne
                  FROM commande c
                  LEFT JOIN fournisseur f ON c.id_fournisseur = f.id_fournisseur
                  LEFT JOIN ligne_commande lc ON c.id_comm = lc.id_commande
                  LEFT JOIN produit p ON lc.id_produit = p.id_produit
                  WHERE c.id_gestionnaire_stock = :id_gest";

    // Ajouter les conditions de filtrage
    if(!empty($filtre_fournisseur)) {
        $sqlDetails .= " AND c.id_fournisseur = :filtre_fournisseur";
    }
    
    if(!empty($filtre_etat)) {
        $sqlDetails .= " AND c.etat = :filtre_etat";
    }
    
    $sqlDetails .= " ORDER BY c.date_commande DESC";

    $stmtDetails = $conn->prepare($sqlDetails);
    $stmtDetails->bindParam(':id_gest', $id_gest, PDO::PARAM_INT);
    
    if(!empty($filtre_fournisseur)) {
        $stmtDetails->bindParam(':filtre_fournisseur', $filtre_fournisseur, PDO::PARAM_INT);
    }
    
    if(!empty($filtre_etat)) {
        $stmtDetails->bindParam(':filtre_etat', $filtre_etat, PDO::PARAM_STR);
    }
    
    $stmtDetails->execute();
    $detailsCommandes = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

    // Organiser les données par commande pour l'affichage
    $commandesDetailsOrganises = [];
    foreach($detailsCommandes as $detail) {
        $idComm = $detail['id_comm'];
        if(!isset($commandesDetailsOrganises[$idComm])) {
            $commandesDetailsOrganises[$idComm] = [
                'infos' => [
                    'id_comm' => $detail['id_comm'],
                    'etat' => $detail['etat'],
                    'date_commande' => $detail['date_commande'],
                    'date_livraison' => $detail['date_livraison'],
                    'email_fournisseur' => $detail['email_fournisseur']
                ],
                'produits' => []
            ];
        }
        
        if($detail['id_produit']) {
            $commandesDetailsOrganises[$idComm]['produits'][] = [
                'id_produit' => $detail['id_produit'],
                'nom_produit' => $detail['nom_produit'],
                'prix_produit' => $detail['prix_produit'],
                'qte_comm' => $detail['qte_comm'],
                'montant_ligne' => $detail['montant_ligne']
            ];
        }
    }

     // Stocker les détails de toutes les commandes dans des divs cachés
     echo "<div id='details-container' style='display:none;'>";
     foreach($commandesDetailsOrganises as $idComm => $commandeDetails) {
         echo "<div id='details-" . htmlspecialchars($idComm) . "'>";
         
         // SUPPRIMER LES INFORMATIONS DE COMMANDE (ne garder que les produits)
         // echo "<h3 style='color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;'>Détails de la commande #" . htmlspecialchars($idComm) . "</h3>";
         // echo "<p><strong>État:</strong> " . htmlspecialchars($commandeDetails['infos']['etat']) . "</p>";
         // echo "<p><strong>Date commande:</strong> " . htmlspecialchars($commandeDetails['infos']['date_commande']) . "</p>";
         // echo "<p><strong>Date livraison:</strong> " . ($commandeDetails['infos']['date_livraison'] ? htmlspecialchars($commandeDetails['infos']['date_livraison']) : 'Non définie') . "</p>";
         // echo "<p><strong>Fournisseur:</strong> " . htmlspecialchars($commandeDetails['infos']['email_fournisseur']) . "</p>";
         
         // Afficher UNIQUEMENT les produits commandés
         if(!empty($commandeDetails['produits'])) {
             echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
             echo "<tr style='background-color: #f2f2f2;'>
                     <th style='padding: 8px; text-align: left;'>Produit</th>
                     <th style='padding: 8px; text-align: right;'>Prix unitaire</th>
                     <th style='padding: 8px; text-align: center;'>Quantité</th>
                     <th style='padding: 8px; text-align: right;'>Montant</th>
                   </tr>";
             
             $totalCommande = 0;
             
             foreach($commandeDetails['produits'] as $produit) {
                 echo "<tr>";
                 echo "<td style='padding: 8px;'>" . htmlspecialchars($produit['nom_produit']) . "</td>";
                 echo "<td style='padding: 8px; text-align: right;'>" . number_format($produit['prix_produit'], 2, ',', ' ') . " €</td>";
                 echo "<td style='padding: 8px; text-align: center;'>" . htmlspecialchars($produit['qte_comm']) . "</td>";
                 echo "<td style='padding: 8px; text-align: right;'>" . number_format($produit['montant_ligne'], 2, ',', ' ') . " €</td>";
                 echo "</tr>";
                 
                 $totalCommande += $produit['montant_ligne'];
             }
             
             echo "<tr style='font-weight:bold; background-color: #f9f9f9;'>";
             echo "<td colspan='3' style='padding: 8px; text-align: right;'>Total :</td>";
             echo "<td style='padding: 8px; text-align: right;'>" . number_format($totalCommande, 2, ',', ' ') . " €</td>";
             echo "</tr>";
             
             echo "</table>";
         } else {
             echo "<p>Aucun produit dans cette commande.</p>";
         }
         
         echo "</div>";
     }
     echo "</div>";
 
 } catch (PDOException $e) {
     die("Erreur d'exécution de la requête: " . $e->getMessage());
 }
?>

<!-- Fenêtre modale pour les détails -->
<div id="modal-details" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:relative; background-color:white; margin:5% auto; padding:20px; width:80%; max-width:800px; border-radius:5px; box-shadow:0 4px 8px rgba(0,0,0,0.1); max-height:80vh; overflow-y:auto;">
        <button onclick="document.getElementById('modal-details').style.display='none'" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:24px; cursor:pointer; color:#aaa;">&times;</button>
        <div id="modal-content">
            <!-- Les détails de la commande seront chargés ici -->
        </div>
    </div>
</div>

<!-- Fenêtre modale pour marquer comme livré -->
<div id="modal-livraison" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:relative; background-color:white; margin:15% auto; padding:20px; width:50%; max-width:500px; border-radius:5px; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
        <button onclick="document.getElementById('modal-livraison').style.display='none'" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:24px; cursor:pointer; color:#aaa;">&times;</button>
        <h3>Marquer la commande comme livrée</h3>
        <form id="form-livraison" method="post" action="">
            <input type="hidden" name="action" value="marquer_livre">
            <input type="hidden" name="id_commande" id="id_commande_livre">
            <div style="margin-bottom: 15px;">
                <label for="date_livraison"><strong>Date de livraison:</strong></label><br>
                <input type="date" name="date_livraison" id="date_livraison" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="text-align: center;">
                <button type="submit" style="padding: 8px 20px; background-color:#6B8E23; color: white; border: none; border-radius: 4px; cursor: pointer;">Confirmer</button>
                <button type="button" onclick="document.getElementById('modal-livraison').style.display='none'" style="padding: 8px 20px; background-color:#999; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fonction pour afficher les détails dans la modale
function afficherDetails(idCommande) {
    // Récupérer le contenu des détails cachés
    var detailsContent = document.getElementById('details-' + idCommande).innerHTML;
    
    // Injecter le contenu dans la modale
    document.getElementById('modal-content').innerHTML = detailsContent;
    
    // Afficher la modale
    document.getElementById('modal-details').style.display = 'block';
}

// Fonction pour afficher la modale de livraison
function marquerCommelivre(idCommande) {
    // Définir la date d'aujourd'hui comme valeur par défaut
    var today = new Date();
    var day = ('0' + today.getDate()).slice(-2);
    var month = ('0' + (today.getMonth() + 1)).slice(-2);
    var year = today.getFullYear();
    var defaultDate = year + '-' + month + '-' + day;
    
    document.getElementById('date_livraison').value = defaultDate;
    document.getElementById('id_commande_livre').value = idCommande;
    document.getElementById('modal-livraison').style.display = 'block';
}

// Fermer les modales si on clique en dehors
window.onclick = function(event) {
    var modalDetails = document.getElementById('modal-details');
    var modalLivraison = document.getElementById('modal-livraison');
    
    if (event.target == modalDetails) {
        modalDetails.style.display = "none";
    }
    
    if (event.target == modalLivraison) {
        modalLivraison.style.display = "none";
    }
}
</script>