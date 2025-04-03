<?php 
include 'dbconfig.php';
// Récupération des notifications des demandes pour le RH
$result_notifications = $conn->query("SELECT  type,description_dem,date_dem,employe.nom_emp FROM demande JOIN employe ON demande.id_emp = employe.id_emp  WHERE statut_dem = 'En attente' ORDER BY date_dem DESC ");

// Fonction pour calculer le temps écoulé
function time_elapsed($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return "Il y a " . $diff . " secondes";
    } elseif ($diff < 3600) {
        return "Il y a " . floor($diff / 60) . " minutes";
    } elseif ($diff < 86400) {
        return "Il y a " . floor($diff / 3600) . " heures";
    } else {
        return "Le " . date('d/m/Y H:i', $timestamp);
    }
}

// Récupération du nombre d'employés
$result_employe = $conn->query("SELECT COUNT(*) AS total FROM employe");
$row_employe = $result_employe->fetch_assoc();
$total_employe = $row_employe['total'];

// Récupération du nombre de demandes
$result_demande = $conn->query("SELECT COUNT(*) AS total FROM demande WHERE statut_dem='En attente'");
$row_demande = $result_demande->fetch_assoc();
$total_demande = $row_demande['total'];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RH Manager - TetraVilla</title>
    <style>
        :root {
            --primary-color: #8b1e3f;
            --secondary-color: #b92989;
            --light-gray: #f5f5f5;
            --dark-gray: #333;
            --white: #fff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            background-color: #f1dddd;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: var(--white);
            height: 100vh;
            position: fixed;
            padding: 20px 0;
        }
        
        .sidebar-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid var(--white);
        }
        
        .sidebar-menu {
            list-style: none;
            margin-top: 30px;
        }
        
        .sidebar-menu li {
            padding: 15px 25px;
            transition: all 0.3s;
        }
        
        .sidebar-menu li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu a {
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo-img {
            width: 40px;
            margin-right: 10px;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: var(--white);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            font-size: 14px;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 14px;
            color: #666;
        }
        
        .card-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .card-icon {
            font-size: 24px;
            color: var(--secondary-color);
        }
        
        /* Notifications */
        .notifications-section {
            background-color: var(--white);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .notification-list {
            list-style: none;
        }
        
        .notification-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(139, 30, 63, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .notification-time {
            font-size: 12px;
            color: #888;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="RHpic.jpg" alt="RH Photo" class="admin-photo">
            <h2>RH MANAGER</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Gérer Employés</a></li>
            <li><a href="#"><i class="fas fa-clipboard-list"></i> Gérer Demandes</a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Dashboard RH</h1>
            <div class="logo">
                <img src="maqlog.jpg" alt="TetraVilla Logo" class="logo-img">
                <span>TetraVilla</span>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">EMPLOYÉS</span>
                    <i class="fas fa-users card-icon"></i>
                </div>
                <div class="card-value"><?php echo $total_employe; ?></div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <span class="card-title">DEMANDES</span>
                    <i class="fas fa-clipboard-list card-icon"></i>
                </div>
                <div class="card-value"><?php echo $total_demande; ?></div>
            </div>
            
        </div>

        <!-- Notifications Section -->
        <div class="notifications-section">
    <h2 class="section-title"><i class="fas fa-bell"></i> Demandes Récentes</h2>
    <ul class="notification-list">
        <?php if ($result_notifications->num_rows > 0) { ?>
            <?php while ($row = $result_notifications->fetch_assoc()) { ?>
                <li class="notification-item">
                    <div class="notification-icon">
                        <?php 
                        // Attribution des icônes selon le type de demande
                        $icons = [
                            "Demande de congé" => "fas fa-calendar-day",
                            "Demande d'avance" => "fas fa-euro-sign",
                            "Certificat médical" => "fas fa-file-medical",
                            "Demande de matériel" => "fas fa-tools"
                        ];
                        $icon_class = $icons[$row['type']] ?? "fas fa-file-alt";
                        ?>
                        <i class="<?= $icon_class ?>"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title"><?= htmlspecialchars($row['type']) ?> - <?= htmlspecialchars($row['nom_emp']) ?></div>
                        <div class="notification-time"><?= time_elapsed($row['date_dem']) ?></div>
                    </div>
                    <button class="btn-view">Voir</button>
                </li>
            <?php } ?>
        <?php } else { ?>
            <li class="notification-item">
                <div class="notification-content">
                    <div class="notification-title">Aucune nouvelle demande</div>
                </div>
            </li>
        <?php } ?>
        </ul>
        </div>
    </div>

    <script>
        // Vous pouvez ajouter du JavaScript ici pour gérer les interactions
        document.querySelectorAll('.btn-view').forEach(button => {
            button.addEventListener('click', function() {
                alert('Détails de la demande affichés');
            });
        });
    </script>
</body>
</html>