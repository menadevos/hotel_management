<?php
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle "employe"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employe') {
    header("Location: login.php");
    exit;
}

// Connexion à la base de données
$host = "localhost";
$username = "root";
$password = "";
$dbname = "hotel";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les informations de l'employé connecté
$user_id = $_SESSION['user_id'];
$sql = "SELECT e.*, d.nom_dep 
        FROM employe e
        JOIN departement d ON e.id_dep = d.id_dep
        WHERE e.id_emp = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employe = $result->fetch_assoc();
$stmt->close();

// Variables pour gérer la section active
$section = $_GET['section'] ?? 'salaires';

// Traitement de la création d'une demande
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_demande'])) {
    $type = $_POST['type'];
    $description = $_POST['description'];
    $statut = 'En attente';
    $id_agentd = $employe['id_agentd']; // ID de l'agent de département lié à l'employé
    $id_rh = $employe['idRH']; // ID du RH lié à l'employé

    $sql = "INSERT INTO demande (type, description_dem, date_dem, statut_dem, id_emp, id_agentd, idRH) 
            VALUES (?, ?, CURDATE(), ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $type, $description, $statut, $user_id, $id_agentd, $id_rh);
    
    if ($stmt->execute()) {
        $message = "Demande créée avec succès!";
    } else {
        $message = "Erreur lors de la création de la demande: " . $conn->error;
    }
    $stmt->close();
}

// Récupérer les salaires de l'employé (via les transactions)
$salaires = [];
if ($section === 'salaires') {
    $sql = "SELECT t.montant_trans, t.date_trans, t.typeTrans, 
                   CASE WHEN t.id_trans IS NOT NULL THEN 'Transmis' ELSE 'Non transmis' END AS statut
            FROM employe e
            LEFT JOIN transaction t ON t.id_emp = e.id_emp AND t.typeTrans = 'Paiement Salaire'
            WHERE e.id_emp = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $salaires[] = $row;
    }
    $stmt->close();
}

// Récupérer les demandes de l'employé
$demandes = [];
if ($section === 'mes_demandes') {
    $sql = "SELECT * FROM demande WHERE id_emp = ? ORDER BY date_dem DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $demandes[] = $row;
    }
    $stmt->close();
}

// Traitement de la déconnexion
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Employé - TetraVilla</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
            background-color: #fff;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #f1dddd;
            color: #000000;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar-header h2 {
            font-size: 20px;
            font-weight: bold;
        }

        .employe-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid #fff;
        }

        .sidebar-menu {
            list-style: none;
            width: 100%;
        }

        .sidebar-menu li {
            margin: 10px 0;
        }

        .sidebar-menu a {
            color: #000000;
            text-decoration: none;
            font-size: 16px;
            display: block;
            padding: 35px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #be9393;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        header h1 {
            font-size: 28px;
            color: #4a4a4a;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo-img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }

        .logo span {
            font-size: 20px;
            font-weight: bold;
            color: #4a4a4a;
        }

        /* Dashboard Sections */
        .dashboard-sections {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .dashboard-card {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card h3 {
            font-size: 20px;
            color: #b68b8b;
            margin-bottom: 15px;
        }

        .dashboard-card h4 {
            font-size: 18px;
            color: #b68b8b;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        /* Formulaire de demande */
        .demande-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .demande-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .demande-form select,
        .demande-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .demande-form textarea {
            height: 100px;
        }

        .submit-btn {
            background-color: #b68b8b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #9e6f6f;
        }

        /* Liste des salaires et demandes */
        .salaire-list, .demande-list {
            list-style: none;
            padding: 0;
        }

        .salaire-item, .demande-item {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #b68b8b;
        }

        .salaire-item p, .demande-item p {
            font-size: 14px;
            color: #4a4a4a;
            margin: 5px 0;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .status-transmis {
            background-color: #4CAF50;
            color: white;
        }

        .status-non-transmis {
            background-color: #f44336;
            color: white;
        }

        .status-pending {
            background-color: #FFC107;
            color: #000;
        }

        .status-approved {
            background-color: #4CAF50;
            color: white;
        }

        .status-rejected {
            background-color: #f44336;
            color: white;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: #dff0d8;
            color: #3c763d;
        }

        /* Bouton de déconnexion */
        .logout-btn {
            background-color: #b68b8b;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 10px;
        }

        .logout-btn:hover {
            background-color: #9e6f6f;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="admin_new.jpeg" alt="Employé Photo" class="employe-photo">
                <h2><?php echo htmlspecialchars($employe['prenom_emp'] . " " . $employe['nom_emp']); ?></h2>
                <p><?php echo htmlspecialchars($employe['nom_dep']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="?section=salaires" class="<?php echo $section === 'salaires' ? 'active' : ''; ?>">Salaires</a></li>
                <li><a href="?section=mes_demandes" class="<?php echo $section === 'mes_demandes' ? 'active' : ''; ?>">Mes Demandes</a></li>
                <li>
                    <form method="POST">
                        <button type="submit" name="logout" class="logout-btn">Se Déconnecter</button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Tableau de Bord - <?php echo htmlspecialchars($employe['nom_dep']); ?></h1>
                <div class="logo">
                    <img src="maqlog.jpg" alt="TetraVilla Logo" class="logo-img">
                    <span>TetraVilla</span>
                </div>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Dashboard Sections -->
            <div class="dashboard-sections">
                <!-- Salaires -->
                <?php if ($section === 'salaires'): ?>
                <section id="salaires" class="dashboard-card">
                    <h3>Mes Salaires</h3>
                    <p>Salaire de base: <?php echo number_format($employe['salaire'], 2); ?> DH</p>
                    <h4>Historique des paiements</h4>
                    <ul class="salaire-list">
                        <?php if (empty($salaires)): ?>
                            <li class="salaire-item">
                                <p>Aucun paiement de salaire trouvé.</p>
                            </li>
                        <?php else: ?>
                            <?php foreach ($salaires as $salaire): ?>
                                <li class="salaire-item">
                                    <p><strong>Montant:</strong> <?php echo $salaire['montant_trans'] ? number_format($salaire['montant_trans'], 2) . " DH" : "N/A"; ?></p>
                                    <p><strong>Date:</strong> <?php echo $salaire['date_trans'] ? date('d/m/Y', strtotime($salaire['date_trans'])) : "N/A"; ?></p>
                                    <p><strong>Statut:</strong> 
                                        <span class="status status-<?php echo strtolower(str_replace(' ', '-', $salaire['statut'])); ?>">
                                            <?php echo htmlspecialchars($salaire['statut']); ?>
                                        </span>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <!-- Mes Demandes -->
                <?php if ($section === 'mes_demandes'): ?>
                <section id="mes_demandes" class="dashboard-card">
                    <h3>Mes Demandes</h3>
                    <form method="POST" class="demande-form">
                        <label for="type">Type de demande:</label>
                        <select name="type" required>
                            <option value="Congé">Congé</option>
                            <option value="Salaire">Salaire</option>
                        </select>
                        <label for="description">Description:</label>
                        <textarea name="description" required></textarea>
                        <button type="submit" name="submit_demande" class="submit-btn">Créer Demande</button>
                    </form>

                    <h4>Demandes existantes:</h4>
                    <ul class="demande-list">
                        <?php if (empty($demandes)): ?>
                            <li class="demande-item">
                                <p>Aucune demande trouvée.</p>
                            </li>
                        <?php else: ?>
                            <?php foreach ($demandes as $demande): ?>
                                <li class="demande-item">
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($demande['type']); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($demande['description_dem']); ?></p>
                                    <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($demande['date_dem'])); ?></p>
                                    <p><strong>Statut:</strong> 
                                        <span class="status status-<?php 
                                            echo strtolower($demande['statut_dem']) === 'approuvée' ? 'approved' : 
                                                 (strtolower($demande['statut_dem']) === 'rejetée' ? 'rejected' : 'pending'); 
                                        ?>">
                                            <?php echo htmlspecialchars($demande['statut_dem']); ?>
                                        </span>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>