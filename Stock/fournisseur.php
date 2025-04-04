<?php
require_once "connect_base.php"; 

// Vérifier si une action a été envoyée

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $success = false;
    $message = "";



if (isset($_POST['action-type'])){
    $actionType = $_POST['action-type']; // 'add' pour ajout, 'update' pour modification
    // Récupérer les données du formulaire
    
 
    $fournisseurNom= $_POST['fournisseur-nom'];
    $fournisseurPrenom = $_POST['fournisseur-prenom'];
    $fournisseurEmail = $_POST['fournisseur-email'];
    $fournisseurTel = $_POST['fournisseur-tel'];
    $productAdresse = $_POST['fournisseur-adresse']; 
    $productNumCompte= $_POST['fournisseur-numCompte']; 
    if ($actionType == 'add') {
        // Ajouter ke fournisseur à la base de données
        $sql = "INSERT INTO fournisseur (nom_fournisseur,prenom_fournisseur ,email , adresse , teleF,numCompte ) 
                VALUES (:nom, :prenom, :email, :adresse , :tel , :numCompte)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':nom', $fournisseurNom);
      $stmt->bindParam(':prenom', $fournisseurPrenom);
      $stmt->bindParam(':email', $fournisseurEmail);
      $stmt->bindParam(':adresse', $productAdresse);
      $stmt->bindParam(':tel', $fournisseurTel);
      $stmt->bindParam(':numCompte', $productNumCompte);
        
        if ($stmt->execute()) {
            $success = true;
            $message =  "Fournisseur ajouté avec succès !";
        } 
        else {
            $message = "Erreur lors de l'ajout du fournisseur.";
        }
    } elseif ($actionType == 'update') {
           // Mise à jour d'un fournisseur existant
    $fournisseurId = $_POST['fournisseur-id']; // Utiliser l'id du fournisseur pour la mise à jour

    // Mettre à jour le fournisseur
    $sql = "UPDATE fournisseur SET nom_fournisseur = :nom, prenom_fournisseur = :prenom, email = :email, adresse = :adresse, teleF = :tel, numCompte = :numCompte WHERE id_fournisseur = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nom', $fournisseurNom);
    $stmt->bindParam(':prenom', $fournisseurPrenom);
    $stmt->bindParam(':email', $fournisseurEmail);
    $stmt->bindParam(':adresse', $productAdresse);
    $stmt->bindParam(':tel', $fournisseurTel);
    $stmt->bindParam(':numCompte', $productNumCompte);
    $stmt->bindParam(':id', $fournisseurId);

    // Exécuter la requête
    if ($stmt->execute()) {
        $success = true;
        $message =  "Fournisseur mis à jour avec succès !";
    } else {
        $message =  "Erreur lors de la mise à jour du fournisseur.";
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
            <h1>Consulter Fournisseurs</h1>
            <div class="logo">
                <img src="images/maqlog.jpg" alt="TetraVilla Logo" class="logo-img">
                <span>TetraVilla</span>
            </div>
          </header>
          <!-- Bouton Ajouter un fourn -->
              <div class="action-buttons">
                    <a href="ajouter_produit.php" class="btn-add"><i class="fas fa-plus"></i> Ajouter un fourniseur </a>
                </div>

                <?php
                //  requête SQL pour obtenir les fournisseurs
                $sql = "SELECT * FROM fournisseur";
                $result = $conn->query($sql);

                if ($result->rowCount() > 0) {
                    ?>
                    <table>
                    <thead> 
            <th>ID du fournisseur</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Adresse</th><th>Numéro de compte</th><th>Actions</th> 
        </thead>
        <tbody> 
        <?php
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $id = $row['id_fournisseur'];
            $nom = $row['nom_fournisseur'];
            $prenom = $row['prenom_fournisseur'];
            $email = $row['email'];
            $tel = $row['teleF'];
            $adresse = $row['adresse'];
            $numCompte = $row['numCompte'];
            ?>
            <tr>
                <td> <?php echo htmlspecialchars($id); ?></td>
                <td> <?php echo htmlspecialchars($nom); ?></td>
                <td> <?php echo htmlspecialchars($prenom); ?></td>
                <td> <?php echo htmlspecialchars($email); ?> </td>
                <td> <?php echo htmlspecialchars($tel); ?> </td>
                <td> <?php echo htmlspecialchars($adresse); ?> </td>
                <td> <?php echo htmlspecialchars($numCompte); ?> </td>
                <td>
                    <!-- Ajouter des actions comme "modifier" ou "supprimer" ici -->
            <a href="modifier_fournisseur.php?id=<?php echo $id; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>

 
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
                    </table>
                    <?php
                } else {
                    echo "Aucun fournisseur existe";
                }
                ?>
            </div>

        
    </div>



  <!-- Fenêtre modale -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modal-title">Gestion des Fournisseurs</h2>
        <form id="modal-form" action="dashboard_stock2.php?page=fournisseur" method="POST">
            <input type="hidden" id="fournisseur-id" name="fournisseur-id">
            <input type="hidden" id="action-type" name="action-type"> <!-- Pour identifier si c'est une modification ou un ajout -->

            <label for="fournisseur-nom">Nom du fournisseur :</label>
            <input type="text" id="fournisseur-nom" name="fournisseur-nom" required>

            <label for="fournisseur-prenom">Prénom :</label>
            <input type="text" id="fournisseur-prenom" name="fournisseur-prenom" required>

            <label for="fournisseur-email">Email :</label>
            <input type="email" id="fournisseur-email" name="fournisseur-email" required>

            <label for="fournisseur-tel">Téléphone :</label>
            <input type="tel" id="fournisseur-tel" name="fournisseur-tel" required>

            <label for="fournisseur-adresse">Adresse :</label>
            <input type="text" id="fournisseur-adresse" name="fournisseur-adresse" required>

            <label for="fournisseur-numCompte">Numéro de compte :</label>
            <input type="text" id="fournisseur-numCompte" name="fournisseur-numCompte" required>

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

    const fournisseurIdField = document.getElementById("fournisseur-id");
    const fournisseurNomField = document.getElementById("fournisseur-nom");
    const fournisseurPrenomField = document.getElementById("fournisseur-prenom");
    const fournisseurEmailField = document.getElementById("fournisseur-email");
    const fournisseurTelField = document.getElementById("fournisseur-tel");
    const fournisseurAdresseField = document.getElementById("fournisseur-adresse");
    const fournisseurNumCompteField = document.getElementById("fournisseur-numCompte");
    const actionTypeField = document.getElementById("action-type");

    // Fonction pour afficher le modal
    function showModal(title, action, fournisseur = null) {
        modalTitle.textContent = title;
        if (action === "Ajouter") {
            actionTypeField.value = "add";
            modalForm.reset(); // Vide les champs si c'est un ajout
        } else {
            actionTypeField.value = "update";
            fournisseurIdField.value = fournisseur.id;
            fournisseurNomField.value = fournisseur.nom;
            fournisseurPrenomField.value = fournisseur.prenom;
            fournisseurEmailField.value = fournisseur.email;
            fournisseurTelField.value = fournisseur.telephone;
            fournisseurAdresseField.value = fournisseur.adresse;
            fournisseurNumCompteField.value = fournisseur.numCompte;
        }

        modal.style.display = "block"; // Affiche le modal
    }

    // Ouvrir le formulaire d'ajout
    document.querySelector(".btn-add").addEventListener("click", function (e) {
        e.preventDefault();
        showModal("Ajouter un Fournisseur", "Ajouter");
    });

    // Ouvrir le formulaire de modification
    document.querySelectorAll(".btn-edit").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            const row = this.closest("tr");
            const fournisseur = {
                id: row.cells[0].textContent.trim(),
                nom: row.cells[1].textContent.trim(),
                prenom: row.cells[2].textContent.trim(),
                email: row.cells[3].textContent.trim(),
                telephone: row.cells[4].textContent.trim(),
                adresse: row.cells[5].textContent.trim(),
                numCompte: row.cells[6].textContent.trim(),
            };
            showModal("Modifier le Fournisseur", "Modifier", fournisseur);
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




   
    </script>