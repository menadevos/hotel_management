<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire du stock Dashboard - TetraVilla</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
   
</head>
<body>
    <?php
    require_once "connect_base.php"; 
    $page = isset($_GET['page']) ? $_GET['page'] : 'stock'; // Page par défaut
    ?>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/admin_new.jpeg" alt="Admin Photo" class="admin-photo">
                <h2>Gestionnaire de stock </h2>
            </div>
            <ul class="sidebar-menu">
            <li><a href="?page=stock"><i class="fas fa-box"></i> Consulter Stock</a></li>
            <li><a href="?page=newCommande"><i class="fas fa-cart-plus"></i> Passer Commande</a></li>
            <li><a href="?page=commandes"><i class="fas fa-file-invoice"></i> Consulter Commandes</a></li>
            <li><a href="?page=fournisseur"><i class="fas fa-truck"></i> Consulter Fournisseur</a></li>
            <li><a href="/hotel_management/hotel_management/login.php"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a></li>
        </ul>
        </div>

        <div class="main-content">
        <?php include "$page.php"; ?>
      
        </div>


        <script>
    // Fonction pour gérer les liens de navigation
    const menuLinks = document.querySelectorAll('.menu-link');
    const contentSections = document.querySelectorAll('.content-section');

    // Initialement, seule la section du stock est visible
    document.querySelector('#stock').classList.add('active');

    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Empêche le rechargement de la page
            const target = document.querySelector(this.getAttribute('href')); // Sélectionne la section cible

            // Cache toutes les sections
            contentSections.forEach(section => section.classList.remove('active'));

            // Affiche la section cible
            target.classList.add('active');
        });
    });
    </script>
</body>
</html>
