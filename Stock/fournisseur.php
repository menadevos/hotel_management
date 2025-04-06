<?php
 // Ajout de la gestion de session pour les messages
require_once "connect_base.php"; 

// Récupérer le type_stock passé en paramètre
$type_stock = $_GET['type_stock'] ?? '';
if(empty($type_stock)) {
    die("Type de stock non spécifié");
}



// Vérifier si une action a été envoyée
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gestion de la suppression
    if(isset($_POST['delete'])) {
        $fournisseurId = $_POST['fournisseur_id'];
        
        try {
            // Commencer une transaction
            $conn->beginTransaction();
            
            // 1. Mettre à jour les produits pour les dissocier du fournisseur
            $sqlUpdateProducts = "UPDATE produit SET id_fournisseur = NULL WHERE id_fournisseur = :id";
            $stmtUpdate = $conn->prepare($sqlUpdateProducts);
            $stmtUpdate->bindParam(':id', $fournisseurId);
            $stmtUpdate->execute();
            
            // 2. Supprimer le fournisseur
            $sqlDelete = "DELETE FROM fournisseur WHERE id_fournisseur = :id AND categorie_fournit = :categorie";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bindParam(':id', $fournisseurId);
            $stmtDelete->bindParam(':categorie', $type_stock);
            $stmtDelete->execute();
            
            // Valider la transaction
            $conn->commit();
            
            $_SESSION['success'] = "Fournisseur supprimé avec succès. Les produits associés n'ont plus de fournisseur.";
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $conn->rollBack();
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
        
        header("Location: dashboard_stock2.php?page=fournisseur&type_stock=".urlencode($type_stock)."&id=".urlencode($id_gest));
        exit;

    }
    // Gestion de l'ajout/modification
    elseif(isset($_POST['action-type'])) {
        $actionType = $_POST['action-type'];
        $fournisseurNom = $_POST['fournisseur-nom'];
        $fournisseurPrenom = $_POST['fournisseur-prenom'];
        $fournisseurEmail = $_POST['fournisseur-email'];
        $fournisseurTel = $_POST['fournisseur-tel'];
        $productAdresse = $_POST['fournisseur-adresse']; 
        $productNumCompte = $_POST['fournisseur-numCompte'];

        try {
            if ($actionType == 'add') {
                // Ajouter le fournisseur avec la catégorie automatique
                $sql = "INSERT INTO fournisseur (nom_fournisseur, prenom_fournisseur, email, adresse, teleF, numCompte, categorie_fournit) 
                        VALUES (:nom, :prenom, :email, :adresse, :tel, :numCompte, :categorie)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nom', $fournisseurNom);
                $stmt->bindParam(':prenom', $fournisseurPrenom);
                $stmt->bindParam(':email', $fournisseurEmail);
                $stmt->bindParam(':adresse', $productAdresse);
                $stmt->bindParam(':tel', $fournisseurTel);
                $stmt->bindParam(':numCompte', $productNumCompte);
                $stmt->bindParam(':categorie', $type_stock);
                
                if($stmt->execute()) {
                    $_SESSION['success'] = "Fournisseur ajouté avec succès !";
                }
            } 
            elseif ($actionType == 'update') {
                $fournisseurId = $_POST['fournisseur-id'];
                
                // Mise à jour sans changer la catégorie
                $sql = "UPDATE fournisseur SET 
                        nom_fournisseur = :nom, 
                        prenom_fournisseur = :prenom, 
                        email = :email, 
                        adresse = :adresse, 
                        teleF = :tel, 
                        numCompte = :numCompte 
                        WHERE id_fournisseur = :id AND categorie_fournit = :categorie";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nom', $fournisseurNom);
                $stmt->bindParam(':prenom', $fournisseurPrenom);
                $stmt->bindParam(':email', $fournisseurEmail);
                $stmt->bindParam(':adresse', $productAdresse);
                $stmt->bindParam(':tel', $fournisseurTel);
                $stmt->bindParam(':numCompte', $productNumCompte);
                $stmt->bindParam(':id', $fournisseurId);
                $stmt->bindParam(':categorie', $type_stock);
                
                if($stmt->execute()) {
                    $_SESSION['success'] = "Fournisseur mis à jour avec succès !";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
        }
        
        // Redirection après ajout/modification
        header("Location: dashboard_stock2.php?page=fournisseur&type_stock=".urlencode($type_stock)."&id=".urlencode($id_gest));
        exit;
    }
}

// Afficher les messages de session s'ils existent
if (isset($_SESSION['success'])) {
    echo '<div class="success-message"><i class="fas fa-check-circle"></i> ' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<header>
    <h1>Fournisseurs - <?php echo htmlspecialchars($type_stock); ?></h1>
    <div class="logo">
        <img src="images/maqlog.jpg" alt="TetraVilla Logo" class="logo-img">
        <span>TetraVilla</span>
    </div>
</header>

<div class="action-buttons">
    <button class="btn-add" onclick="showAddModal()">
        <i class="fas fa-plus"></i> Ajouter un fournisseur
    </button>
</div>

<?php
// Récupérer les fournisseurs de la catégorie
$sql = "SELECT * FROM fournisseur WHERE categorie_fournit = :type_stock";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':type_stock', $type_stock);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Adresse</th>
                <th>Numéro compte</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_fournisseur']); ?></td>
                    <td><?php echo htmlspecialchars($row['nom_fournisseur']); ?></td>
                    <td><?php echo htmlspecialchars($row['prenom_fournisseur']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['teleF']); ?></td>
                    <td><?php echo htmlspecialchars($row['adresse']); ?></td>
                    <td><?php echo htmlspecialchars($row['numCompte']); ?></td>
                    <td>
                        <button class="btn-edit" onclick="showEditModal(
                            <?php echo $row['id_fournisseur']; ?>,
                            '<?php echo addslashes($row['nom_fournisseur']); ?>',
                            '<?php echo addslashes($row['prenom_fournisseur']); ?>',
                            '<?php echo addslashes($row['email']); ?>',
                            '<?php echo addslashes($row['teleF']); ?>',
                            '<?php echo addslashes($row['adresse']); ?>',
                            '<?php echo addslashes($row['numCompte']); ?>'
                        )">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        
                        <form method="POST" style="display:inline;" action="dashboard_stock2.php?page=fournisseur&type_stock=<?php echo urlencode($type_stock); ?>">
                            <input type="hidden" name="fournisseur_id" value="<?php echo $row['id_fournisseur']; ?>">
                            <button type="submit" name="delete" class="btn-delete" 
                                    onclick="return confirmDelete()">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
} else {
    echo "<p>Aucun fournisseur trouvé pour cette catégorie.</p>";
}
?>

<!-- Modal avec nouveaux styles -->
<div id="fournisseurModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2 id="modal-title">Ajouter un fournisseur</h2>
        <form method="POST" id="modal-form" action="dashboard_stock2.php?page=fournisseur&type_stock=<?php echo urlencode($type_stock); ?>&id=<?php echo urlencode($id_gest); ?>">
            <input type="hidden" name="action-type" id="actionType" value="add">
            <input type="hidden" name="fournisseur-id" id="fournisseurId">
            <input type="hidden" name="product-category" value="<?php echo htmlspecialchars($type_stock); ?>">
            
            <div class="form-group">
                <label for="fournisseurNom">Nom:</label>
                <input type="text" id="fournisseurNom" name="fournisseur-nom" required>
            </div>
            
            <div class="form-group">
                <label for="fournisseurPrenom">Prénom:</label>
                <input type="text" id="fournisseurPrenom" name="fournisseur-prenom" required>
            </div>
            
            <div class="form-group">
                <label for="fournisseurEmail">Email:</label>
                <input type="email" id="fournisseurEmail" name="fournisseur-email" required>
            </div>
            
            <div class="form-group">
                <label for="fournisseurTel">Téléphone:</label>
                <input type="tel" id="fournisseurTel" name="fournisseur-tel" required>
            </div>
            
            <div class="form-group">
                <label for="fournisseurAdresse">Adresse:</label>
                <input type="text" id="fournisseurAdresse" name="fournisseur-adresse" required>
            </div>
            
            <div class="form-group">
                <label for="fournisseurNumCompte">Numéro de compte:</label>
                <input type="text" id="fournisseurNumCompte" name="fournisseur-numCompte" required>
            </div>
            
            <button type="submit" id="modal-confirm" class="btn-submit">Confirmer</button>
        </form>
    </div>
</div>

<script>
// Gestion du modal
function showAddModal() {
    document.getElementById('modal-title').textContent = 'Ajouter un fournisseur';
    document.getElementById('actionType').value = 'add';
    document.getElementById('fournisseurId').value = '';
    document.getElementById('modal-form').reset();
    document.getElementById('fournisseurModal').style.display = 'block';
    document.getElementById('fournisseurModal').classList.add('show');
}

function showEditModal(id, nom, prenom, email, tel, adresse, numCompte) {
    document.getElementById('modal-title').textContent = 'Modifier le fournisseur';
    document.getElementById('actionType').value = 'update';
    document.getElementById('fournisseurId').value = id;
    document.getElementById('fournisseurNom').value = nom;
    document.getElementById('fournisseurPrenom').value = prenom;
    document.getElementById('fournisseurEmail').value = email;
    document.getElementById('fournisseurTel').value = tel;
    document.getElementById('fournisseurAdresse').value = adresse;
    document.getElementById('fournisseurNumCompte').value = numCompte;
    document.getElementById('fournisseurModal').style.display = 'block';
    document.getElementById('fournisseurModal').classList.add('show');
}

function closeModal() {
    document.getElementById('fournisseurModal').style.display = 'none';
    document.getElementById('fournisseurModal').classList.remove('show');
}

function confirmDelete() {
    return confirm("Êtes-vous sûr de vouloir supprimer ce fournisseur?\n\nLes produits associés n'auront plus de fournisseur.");
}

// Fermer si on clique en dehors
window.onclick = function(event) {
    const modal = document.getElementById('fournisseurModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>