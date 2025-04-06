<?php
// Doit être la toute première ligne du fichier
session_start();
require_once "connect_base.php";

$page = isset($_GET['page']) ? $_GET['page'] : 'stock';
$type_stock = isset($_GET['type_stock']) ? $_GET['type_stock'] : '';
$id_gest = isset($_GET['id']) ? $_GET['id'] : '';

if(empty($type_stock) || empty($id_gest)) {
    die("Type de stock ou ID non spécifié");
}

// Fonction pour générer les URLs
function generateUrl($page, $type_stock, $id_gest) {
    return "?page=" . urlencode($page) . "&type_stock=" . urlencode($type_stock) . "&id=" . urlencode($id_gest);
}

// Récupérer les infos du gestionnaire
$info_gest = [];
try {
    $stmt = $conn->prepare("SELECT * FROM gestionnaire_stock WHERE id_gestionnaire = :id");
    $stmt->bindParam(':id', $id_gest, PDO::PARAM_INT);
    $stmt->execute();
    $info_gest = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Traitement avant tout affichage HTML
ob_start(); // Démarre la temporisation de sortie
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestionnaire du stock Dashboard - TetraVilla</title>

    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Modal styles */
        #modal-gestionnaire {
            display: none;
            position: fixed;
            top: 10%;
            left: 50%;
            transform: translate(-50%, 0);
            background: white;
            padding: 20px;
            border: 1px solid #ccc;
            z-index: 1000;
            width: 400px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            border-radius: 8px;
        }
        #modal-gestionnaire h3 {
            margin-top: 0;
        }
        #modal-overlay {
            display: none;
            position: fixed;
            top:0; left:0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
        }
        .admin-photo {
            cursor: pointer;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            object-fit: cover;
        }


       
    /* MODAL DESIGN */
    #modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: 999;
    }

    #modal-gestionnaire {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        width: 350px;
        border-radius: 12px;
        box-shadow: 0 20px 30px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        overflow: hidden;
        font-family: Arial, sans-serif;
    }

    #modal-gestionnaire .modal-header {
        background-color: #1e90ff;
        color: white;
        padding: 16px;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
    }

    #modal-gestionnaire .modal-body {
        padding: 20px;
    }

    #modal-gestionnaire .modal-body p {
        margin: 10px 0;
        font-size: 15px;
        color: #333;
    }

    #modal-gestionnaire .modal-body strong {
        color: #555;
    }

    #modal-gestionnaire .modal-footer {
        padding: 10px 20px;
        text-align: right;
        background-color: #f9f9f9;
        border-top: 1px solid #ddd;
    }

    #modal-gestionnaire .modal-footer button {
        background-color: #1e90ff;
        color: white;
        padding: 8px 14px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    #modal-gestionnaire .modal-footer button:hover {
        background-color: #0f6ec3;
    }


    .close-modal {
    position: absolute;
    top: 12px;
    right: 16px;
    font-size: 20px;
    cursor: pointer;
    color: black ;
    font-weight: bold;
    transition: color 0.2s ease;
}

.close-modal:hover {
    color: #ccc;
}

    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <!-- 👇 Clic sur l'image déclenche l'affichage du modal -->
            <img src="images/admin_new.jpeg" alt="Admin Photo" class="admin-photo" id="admin-photo">
            <h2>Gestionnaire Stock <?php echo htmlspecialchars($type_stock); ?></h2>
        </div>
        <ul class="sidebar-menu">
        <li>
  <a href="<?php echo generateUrl('budget', $type_stock , $id_gest); ?>">
    <i class="fas fa-dollar-sign"></i> Budget Du stock
  </a>
</li>

            <li><a href="<?php echo generateUrl('stock', $type_stock , $id_gest) ; ?>"><i class="fas fa-box"></i> Consulter Stock</a></li>
            <li><a href="<?php echo generateUrl('newCommande', $type_stock , $id_gest); ?>"><i class="fas fa-cart-plus"></i> Passer Commande</a></li>
            <li><a href="<?php echo generateUrl('commandes', $type_stock , $id_gest); ?>"><i class="fas fa-file-invoice"></i> Consulter Commandes</a></li>
            <li><a href="<?php echo generateUrl('fournisseur', $type_stock , $id_gest); ?>"><i class="fas fa-truck"></i> Consulter Fournisseur</a></li>
            <li><a href="/hotel_management/hotel_management/login.php"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a></li>
        </ul>
    </div>

        <!-- Contenu principal -->
        <div class="main-content">
            <?php 
            // Inclusion de la page demandée
            $included_file = "$page.php";
            if(file_exists($included_file)) {
                // Passez les variables nécessaires
                $_GET['type_stock'] = $type_stock;
                $_GET['id'] = $id_gest;
                
                // Inclure le fichier
                include $included_file;
            } else {
                echo "<p>Page non trouvée</p>";
            }
            ?>
        </div>
    </div>
    
    <!-- Vos modals et scripts -->

    <!-- Overlay + Modal -->
<div id="modal-overlay" onclick="closeModal()"></div>
<div id="modal-gestionnaire">
<div class="modal-header">
    Mes Informations
    <span class="close-modal" onclick="closeModal()">&times;</span>
</div>
    <div class="modal-body">
        <?php if ($info_gest): ?>
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($info_gest['nom_gestionnaire']); ?></p>
            <p><strong>Prénom :</strong> <?php echo htmlspecialchars($info_gest['prenom_gestionnaire']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($info_gest['email_gestionnaire']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($info_gest['telephone']); ?></p>
        <?php else: ?>
            <p>Informations indisponibles.</p>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <button onclick="closeModal()">Fermer</button>
    </div>
</div>
    <script>
    const modal = document.getElementById('modal-gestionnaire');
    const overlay = document.getElementById('modal-overlay');
    const photo = document.getElementById('admin-photo');

    photo.addEventListener('click', () => {
        modal.style.display = 'block';
        overlay.style.display = 'block';
    });

    function closeModal() {
        modal.style.display = 'none';
        overlay.style.display = 'none';
    }
</script>
</body>
</html>
<?php
ob_end_flush(); // Envoie le contenu temporisé au navigateur
?>