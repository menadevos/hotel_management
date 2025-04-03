<?php
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle "agent_departement"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent_departement') {
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

// Récupérer les informations de l'agent de département connecté
$user_id = $_SESSION['user_id'];
$sql = "SELECT ad.*, d.nom_dep 
        FROM agent_departement ad
        JOIN departement d ON ad.id_dep = d.id_dep
        WHERE ad.id_agentd = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc();
$stmt->close();

// Variables pour gérer la section active
$section = $_GET['section'] ?? '';

// Traitement du formulaire de rapport
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rapport'])) {
    $description = $_POST['description'];
    $revenu_total = floatval($_POST['revenu_total']);
    $depenses_total = floatval($_POST['depenses_total']);
    
    $sql = "INSERT INTO rapport (date_rapp, description, revenu_total, depenses_total, id_agentd) 
            VALUES (CURDATE(), ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddi", $description, $revenu_total, $depenses_total, $user_id);
    
    if ($stmt->execute()) {
        $message = "Rapport soumis avec succès!";
    } else {
        $message = "Erreur lors de la soumission du rapport: " . $conn->error;
    }
    $stmt->close();
}

// Traitement des actions sur les demandes
if (isset($_GET['action']) && isset($_GET['demande_id'])) {
    $demande_id = intval($_GET['demande_id']);
    $action = $_GET['action'];
    
    if (in_array($action, ['accepter', 'refuser'])) {
        $nouveau_statut = ($action === 'accepter') ? 'Approuvée' : 'Rejetée';
        
        $sql = "UPDATE demande SET statut_dem = ? WHERE id_dem = ? AND id_agentd = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $nouveau_statut, $demande_id, $user_id);
        
        if ($stmt->execute()) {
            $message = "Demande $action avec succès!";
        } else {
            $message = "Erreur lors de la mise à jour de la demande: " . $conn->error;
        }
        $stmt->close();
    }
}

// Traitement de la distribution de salaire
if (isset($_GET['action']) && $_GET['action'] === 'distribuer_salaire' && isset($_GET['emp_id'])) {
    $emp_id = intval($_GET['emp_id']);
    // Logique pour "distribuer le salaire" (par exemple, marquer le salaire comme payé)
    // Ici, je vais simplement afficher un message pour l'exemple
    $message = "Salaire distribué avec succès pour l'employé ID $emp_id!";
    // Vous pouvez ajouter une logique pour mettre à jour une table ou enregistrer la distribution
}

// Récupérer les employés du département
$employes = [];
if ($section === 'distribution_salaire') {
    $sql = "SELECT * FROM employe WHERE id_dep = ? ORDER BY nom_emp";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $agent['id_dep']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $employes[] = $row;
    }
    $stmt->close();
}

// Récupérer les rapports existants
$rapports = [];
if ($section === 'rapport') {
    $sql = "SELECT * FROM rapport WHERE id_agentd = ? ORDER BY date_rapp DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rapports[] = $row;
    }
    $stmt->close();
}

// Récupérer les demandes du département
$demandes = [];
if ($section === 'demande') {
    $sql = "SELECT * FROM demande WHERE id_agentd = ? ORDER BY date_dem DESC";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Département Dashboard - TetraVilla</title>
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

        .admin-photo {
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

        .dashboard-card p {
            font-size: 16px;
            color: #4a4a4a;
            margin-bottom: 15px;
        }

        .dashboard-card ul {
            list-style: none;
            padding-left: 0;
        }

        .dashboard-card ul li {
            margin: 5px 0;
            font-size: 14px;
        }

        /* Ajouts pour le formulaire de rapport */
        .rapport-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .rapport-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .rapport-form input[type="text"],
        .rapport-form textarea,
        .rapport-form input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .rapport-form textarea {
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

        .rapport-list, .demande-list, .employe-list {
            list-style: none;
            padding: 0;
        }

        .rapport-item, .demande-item, .employe-item {
            background: #f1dddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
        }

        .rapport-date, .demande-date {
            font-size: 0.9em;
            color: #666;
        }

        /* Styles pour les boutons d'action */
        .action-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .accept-btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .reject-btn {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .distribute-salary-btn {
            background-color: #b68b8b;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .distribute-salary-btn:hover {
            background-color: #9e6f6f;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
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
                <img src="admin_new.jpeg" alt="Agent Photo" class="admin-photo">
                <h2><?php echo htmlspecialchars($agent['prenom_agentd'] . " " . $agent['nom_agentd']); ?></h2>
                <p><?php echo htmlspecialchars($agent['nom_dep']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="?section=budget" class="<?php echo $section === 'budget' ? 'active' : ''; ?>">Budget Département</a></li>
                <li><a href="?section=rapport" class="<?php echo $section === 'rapport' ? 'active' : ''; ?>">Rapport</a></li>
                <li><a href="?section=demande" class="<?php echo $section === 'demande' ? 'active' : ''; ?>">Demande</a></li>
                <li><a href="?section=distribution_salaire" class="<?php echo $section === 'distribution_salaire' ? 'active' : ''; ?>">Distribution Salaire</a></li>
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
                <h1>Tableau de Bord - <?php echo htmlspecialchars($agent['nom_dep']); ?></h1>
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
                <!-- Budget Département -->
                <?php if ($section === 'budget'): ?>
                <section id="budget" class="dashboard-card">
                    <h3>Budget Département</h3>
                    <p>
                        <?php
                            echo "Budget disponible: " . number_format($agent['monnaieDep'], 2) . " DH";
                        ?>
                    </p>
                </section>
                <?php endif; ?>

                <!-- Rapport -->
                <?php if ($section === 'rapport'): ?>
                <section id="rapport" class="dashboard-card">
                    <h3>Rapport</h3>
                    
                    <form method="POST" class="rapport-form">
                        <label for="description">Description:</label>
                        <textarea name="description" required></textarea>
                        
                        <label for="revenu_total">Revenu Total (DH):</label>
                        <input type="number" name="revenu_total" step="0.01" min="0" required>
                        
                        <label for="depenses_total">Dépenses Total (DH):</label>
                        <input type="number" name="depenses_total" step="0.01" min="0" required>
                        
                        <button type="submit" name="submit_rapport" class="submit-btn">Soumettre Rapport</button>
                    </form>
                    
                    <h4>Rapports précédents:</h4>
                    <ul class="rapport-list">
                        <?php foreach ($rapports as $rapport): ?>
                            <li class="rapport-item">
                                <p><?php echo htmlspecialchars($rapport['description']); ?></p>
                                <p>Revenu: <?php echo number_format($rapport['revenu_total'], 2); ?> DH</p>
                                <p>Dépenses: <?php echo number_format($rapport['revenu_total'], 2); ?> DH</p>
                                <p class="rapport-date">Date: <?php echo date('d/m/Y', strtotime($rapport['date_rapp'])); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <!-- Demande -->
                <?php if ($section === 'demande'): ?>
                <section id="demande" class="dashboard-card">
                    <h3>Demandes</h3>
                    <ul class="demande-list">
                        <?php foreach ($demandes as $demande): ?>
                            <li class="demande-item">
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($demande['type']); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($demande['description_dem']); ?></p>
                                <p><strong>Statut:</strong> 
                                    <span class="status status-<?php 
                                        echo strtolower($demande['statut_dem']) === 'approuvée' ? 'approved' : 
                                             (strtolower($demande['statut_dem']) === 'rejetée' ? 'rejected' : 'pending'); 
                                    ?>">
                                        <?php echo htmlspecialchars($demande['statut_dem']); ?>
                                    </span>
                                </p>
                                <p class="demande-date">Date: <?php echo date('d/m/Y', strtotime($demande['date_dem'])); ?></p>
                                
                                <?php if ($demande['statut_dem'] === 'En attente'): ?>
                                <div class="action-buttons">
                                    <a href="?section=demande&action=accepter&demande_id=<?php echo $demande['id_dem']; ?>" class="accept-btn">Accepter</a>
                                    <a href="?section=demande&action=refuser&demande_id=<?php echo $demande['id_dem']; ?>" class="reject-btn">Refuser</a>
                                </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <!-- Distribution Salaire -->
                <?php if ($section === 'distribution_salaire'): ?>
                <section id="distribution_salaire" class="dashboard-card">
                    <h3>Distribution Salaire</h3>
                    <ul class="employe-list">
                        <?php if (empty($employes)): ?>
                            <li class="employe-item">
                                <p>Aucun employé trouvé dans ce département.</p>
                            </li>
                        <?php else: ?>
                            <?php foreach ($employes as $employe): ?>
                                <li class="employe-item">
                                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($employe['prenom_emp'] . " " . $employe['nom_emp']); ?></p>
                                    <p><strong>Salaire:</strong> <?php echo number_format($employe['salaire'], 2); ?> DH</p>
                                    <div class="action-buttons">
                                        <a href="?section=distribution_salaire&action=distribuer_salaire&emp_id=<?php echo $employe['id_emp']; ?>" class="distribute-salary-btn">Distribuer Salaire</a>
                                    </div>
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
