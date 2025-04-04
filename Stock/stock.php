<div id="stock" class="content-section">
<?php
require_once "connect_base.php"; 

// Vérifier si une action a été envoyée

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $success = false;
    $message = "";


if(empty($_POST['action-type']) && isset($_POST['delete'])){
$productId = $_POST['product_id'];
    $success = false;
    $message = "";
    
    try {
        // Commencer une transaction
        $conn->beginTransaction();
        
        // Supprimer d'abord les données de stock
        $sqlDeleteStock = "DELETE FROM stock WHERE id_stock = :id";
        $stmtDeleteStock = $conn->prepare($sqlDeleteStock);
        $stmtDeleteStock->bindParam(':id', $productId);
        $stmtDeleteStock->execute();
        
        // Puis supprimer le produit
        $sqlDeleteProduct = "DELETE FROM produit WHERE id_produit = :id";
        $stmtDeleteProduct = $conn->prepare($sqlDeleteProduct);
        $stmtDeleteProduct->bindParam(':id', $productId);
        $stmtDeleteProduct->execute();
        
        // Valider la transaction
        $conn->commit();
        
        $success = true;
        $message = "Produit supprimé avec succès !";
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollBack();
        $message = "Erreur lors de la suppression du produit : " . $e->getMessage();
    }
}

elseif(isset($_POST['action-type'])){
    $actionType = $_POST['action-type']; // 'add' pour ajout, 'update' pour modification
    // Récupérer les données du formulaire
    $productName = $_POST['product-name'];
    $productCategory = $_POST['product-category'];
    $productPrice = $_POST['product-price'];
    $productStock = $_POST['product-stock'];
    $productSupplier = $_POST['product-supplier']; // Fournisseur ajouté
    if ($actionType == 'add') {
        // Ajouter un produit à la base de données
        $sql = "INSERT INTO produit (nom_produit, categorie_produit, prix_produit, id_fournisseur) 
                VALUES (:name, :category, :price, :supplier)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $productName);
        $stmt->bindParam(':category', $productCategory);
        $stmt->bindParam(':price', $productPrice);
        $stmt->bindParam(':supplier', $productSupplier);
        
        if ($stmt->execute()) {
            // Récupérer l'ID du produit ajouté
            $productId = $conn->lastInsertId();

            // Ajouter le stock pour le produit
            $sqlStock = "INSERT INTO stock (id_stock, qte_stock) VALUES (:product_id, :stock)";
            $stmtStock = $conn->prepare($sqlStock);
            $stmtStock->bindParam(':product_id', $productId);
            $stmtStock->bindParam(':stock', $productStock);
            
            if ($stmtStock->execute()) {
                $success = true;
                $message = "Produit ajouté avec succès !";
            } else {
                $message = "Erreur lors de l'ajout du stock.";
            }
        } else {
            $message = "Erreur lors de l'ajout du produit.";
        }
    } elseif ($actionType == 'update') {
        // Mise à jour d'un produit existant
        $productId = $_POST['product-id'];

        // Mettre à jour le produit
        $sql = "UPDATE produit SET nom_produit = :name, categorie_produit = :category, prix_produit = :price, id_fournisseur = :supplier WHERE id_produit = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $productName);
        $stmt->bindParam(':category', $productCategory);
        $stmt->bindParam(':price', $productPrice);
        $stmt->bindParam(':supplier', $productSupplier);
        $stmt->bindParam(':id', $productId);

        if ($stmt->execute()) {
            // Mettre à jour le stock
            $sqlStock = "UPDATE stock SET qte_stock = :stock WHERE id_stock = :id";
            $stmtStock = $conn->prepare($sqlStock);
            $stmtStock->bindParam(':stock', $productStock);
            $stmtStock->bindParam(':id', $productId);
            
            if ($stmtStock->execute()) {
                $success = true;
                $message = "Produit mis à jour avec succès !";
            } else {
                $message = "Erreur lors de la mise à jour du stock.";
            }
        } else {
            $message = "Erreur lors de la mise à jour du produit.";
        }
    }
    
 
}
    

    // Afficher le message approprié
    if ($success) {
        echo '<div class="success-message"><i class="fas fa-check-circle"></i> ' . $message . '</div>';
    } else {
        echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' . $message . '</div>';
    }
}
?>
            <header>
            <h1>Consulter Stock</h1>
            <div class="logo">
                <img src="images/maqlog.jpg" alt="TetraVilla Logo" class="logo-img">
                <span>TetraVilla</span>
            </div>
          </header>
          <!-- Bouton Ajouter un Produit -->
              <div class="action-buttons">
                    <a href="ajouter_produit.php" class="btn-add"><i class="fas fa-plus"></i> Ajouter un produit</a>
                </div>

                <?php
                //  requête SQL pour obtenir les produits en stock
                $sql = "SELECT p.id_produit, p.nom_produit, p.categorie_produit,f.email AS fournisseur_email ,p.prix_produit, s.qte_stock
                         FROM produit p
                         JOIN stock s ON p.id_produit = s.id_stock
                         JOIN fournisseur f ON p.id_fournisseur = f.id_fournisseur;";
                $result = $conn->query($sql);

                if ($result->rowCount() > 0) {
                    ?>
                    <table>
                        <thead> 
                            <th>ID du produit</th><th>Nom du produit</th><th>Catégorie</th><th>Fournisseur</th><th>Quantité en stock</th><th>Prix</th>   <th>Actions</th> 
                        </thead>
                        <tbody> 
                        <?php
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                            $id = $row['id_produit'];
                            $nom = $row['nom_produit'];
                            $categorie = $row['categorie_produit'];
                            $fournisseur = $row['fournisseur_email'];
                            $qte_stock = $row['qte_stock'];
                            $prix = $row['prix_produit'];
                            ?>
                            <tr>
                                <td> <?php echo htmlspecialchars($id); ?></td>
                                <td> <?php echo htmlspecialchars($nom); ?></td>
                                <td> <?php echo htmlspecialchars($categorie); ?></td>
                                <td> <?php echo htmlspecialchars($fournisseur); ?> </td>
                                <td> <?php echo htmlspecialchars($qte_stock); ?> </td>
                                <td> <?php echo htmlspecialchars($prix); ?> </td>
                                <td>
                                    <a href="modifier_produit.php?id=<?php echo $id; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>

                                  <!-- Remplacer le bouton de suppression existant par celui-ci -->
<form method="POST" style="display: inline;">
    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
    <button type="submit" class="btn-delete" name="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
        <i class="fas fa-trash"></i> Supprimer
    </button>
</form>

                 
                                </td> 
                                <td>
     
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?php
                } else {
                    echo "Aucun produit en stock.";
                }
                ?>
            </div>

        
    </div>
</div>


    <!-- Fenêtre modale -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modal-title">Titre</h2>
        <form id="modal-form" action="dashboard_stock2.php?page=stock" method="POST">
            <input type="hidden" id="product-id" name="product-id">
            <input type="hidden" id="action-type" name="action-type"> <!-- Pour identifier si c'est une modification ou un ajout -->

            <label for="product-name">Nom du produit :</label>
            <input type="text" id="product-name" name="product-name" required>

            <label for="product-category">Catégorie :</label>
            <input type="text" id="product-category" name="product-category" required>

            <label for="product-price">Prix :</label>
            <input type="number" id="product-price" name="product-price" required>

            <label for="product-stock">Quantité :</label>
            <input type="number" id="product-stock" name="product-stock" required>

            <!-- Sélectionner le fournisseur -->
            <label for="product-supplier">Fournisseur :</label>
            <select id="product-supplier" name="product-supplier" required>
                <!-- La liste des fournisseurs sera insérée ici directement -->
                <?php
                    // Connexion à la base de données
                    require_once "connect_base.php";

                    // Récupérer la liste des fournisseurs
                    $sql = "SELECT id_fournisseur, email FROM fournisseur";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Afficher les fournisseurs dans le select
                    foreach ($suppliers as $supplier) {
                        echo "<option value='" . $supplier['id_fournisseur'] . "'>" . $supplier['email'] . "</option>";
                    }
                ?>
            </select>

            <button type="submit" id="modal-confirm">Confirmer</button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modal");
    const modalTitle = document.getElementById("modal-title");
    const modalForm = document.getElementById("modal-form");
    const closeModal = document.querySelector(".close-btn");

    const productIdField = document.getElementById("product-id");
    const productNameField = document.getElementById("product-name");
    const productCategoryField = document.getElementById("product-category");
    const productPriceField = document.getElementById("product-price");
    const productStockField = document.getElementById("product-stock");
    const productSupplierField = document.getElementById("product-supplier");
    const actionTypeField = document.getElementById("action-type");

    // Fonction pour afficher le modal
    function showModal(title, action, product = null) {
        modalTitle.textContent = title;
        if (action === "Ajouter") {
            actionTypeField.value = "add";
            modalForm.reset(); // Vide les champs si c'est un ajout
        } else {
            actionTypeField.value = "update";
            productIdField.value = product.id;
            productNameField.value = product.nom;
            productCategoryField.value = product.categorie;
            productPriceField.value = product.prix;
            productStockField.value = product.stock;
            productSupplierField.value = product.fournisseur_id; // Sélectionner le fournisseur actuel
        }

        modal.style.display = "block"; // Affiche le modal
    }

    // Ouvrir le formulaire d'ajout
    document.querySelector(".btn-add").addEventListener("click", function (e) {
        e.preventDefault();
        showModal("Ajouter un Produit", "Ajouter");
    });

    // Ouvrir le formulaire de modification
    document.querySelectorAll(".btn-edit").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            const row = this.closest("tr");
            const product = {
                id: row.cells[0].textContent.trim(),
                nom: row.cells[1].textContent.trim(),
                categorie: row.cells[2].textContent.trim(),
                prix: row.cells[5].textContent.trim(),
                stock: row.cells[4].textContent.trim(),
                fournisseur_id: row.cells[3].textContent.trim(), // L'ID du fournisseur
            };
            showModal("Modifier le Produit", "Modifier", product);
        });
    });

    // Fermer le modal
    closeModal.addEventListener("click", function () {
        modal.style.display = "none";
    });

    // Fermer le modal si on clique en dehors
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});
</script>




    