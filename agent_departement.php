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
    
    // Récupérer les informations de l'employé et du budget
    $sql = "SELECT e.salaire, e.prenom_emp, e.nom_emp, ad.monnaieDep 
            FROM employe e 
            JOIN agent_departement ad ON e.id_dep = ad.id_dep 
            WHERE e.id_emp = ? AND ad.id_agentd = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $emp_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if ($data) {
        $salaire = floatval($data['salaire']);
        $budget_actuel = floatval($data['monnaieDep']);
        $nom_employe = $data['prenom_emp'] . ' ' . $data['nom_emp'];

        if ($budget_actuel >= $salaire) {
            $nouveau_budget = $budget_actuel - $salaire;
            
            // Commencer une transaction
            $conn->begin_transaction();
            
            try {
                // 1. Mettre à jour le budget du département
                $sql = "UPDATE agent_departement SET monnaieDep = ? WHERE id_agentd = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("di", $nouveau_budget, $user_id);
                $stmt->execute();
                $stmt->close();
                
                // 2. Enregistrer la transaction avec l'ID de l'employé
                $description = "Paiement salaire pour " . $nom_employe;
                $sql = "INSERT INTO transaction (montant_trans, date_trans, typeTrans, id_agent_departement, id_emp) 
                        VALUES (?, NOW(), 'Paiement Salaire', ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("dii", $salaire, $user_id, $emp_id);
                $stmt->execute();
                $transaction_id = $stmt->insert_id;
                $stmt->close();
                
                // 3. Générer un reçu
                $sql = "INSERT INTO recu (details, type, DateEmission, id_transaction) 
                        VALUES (?, 'Paiement Salaire', NOW(), ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $description, $transaction_id);
                $stmt->execute();
                $stmt->close();
                
                // Valider la transaction
                $conn->commit();
                
                $message = "Salaire de " . number_format($salaire, 2) . " DH distribué avec succès pour l'employé " . $nom_employe . "! Nouveau budget: " . number_format($nouveau_budget, 2) . " DH";
                $agent['monnaieDep'] = $nouveau_budget;
            } catch (Exception $e) {
                // Annuler en cas d'erreur
                $conn->rollback();
                $message = "Erreur lors de la distribution du salaire: " . $e->getMessage();
            }
        } else {
            $message = "Budget insuffisant! Budget actuel: " . number_format($budget_actuel, 2) . " DH, Salaire requis: " . number_format($salaire, 2) . " DH";
        }
    } else {
        $message = "Employé ou département non trouvé!";
    }
}
// Traitement de la génération de facture
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generer_facture'])) {
    $description = $_POST['description'];
    $montant = floatval($_POST['montant']);
    $type = $_POST['type'];
    $statut = 'En attente'; // Statut initial

    $sql = "INSERT INTO facture (description, montant, statut, type, id_agent_departement) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssi", $description, $montant, $statut, $type, $user_id);
    
    if ($stmt->execute()) {
        $message = "Facture générée avec succès!";
    } else {
        $message = "Erreur lors de la génération de la facture: " . $conn->error;
    }
    $stmt->close();
}
// Traitement du paiement de la facture
if (isset($_GET['action']) && $_GET['action'] === 'payer' && isset($_GET['facture_id'])) {
    $facture_id = intval($_GET['facture_id']);
    
    // Vérifier le budget et récupérer les détails de la facture
    $sql = "SELECT f.montant, f.description, f.type, ad.monnaieDep 
            FROM facture f 
            JOIN agent_departement ad ON f.id_agent_departement = ad.id_agentd 
            WHERE f.id_fac = ? AND f.id_agent_departement = ? AND f.statut = 'Approuvée'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $facture_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if ($data) {
        $montant = floatval($data['montant']);
        $budget_actuel = floatval($data['monnaieDep']);
        $description_facture = $data['description'];
        $type_facture = $data['type'];

        if ($budget_actuel >= $montant) {
            // Démarrer une transaction pour garantir l'intégrité des données
            $conn->begin_transaction();

            try {
                // 1. Mettre à jour le budget
                $nouveau_budget = $budget_actuel - $montant;
                $sql = "UPDATE agent_departement SET monnaieDep = ? WHERE id_agentd = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("di", $nouveau_budget, $user_id);
                $stmt->execute();
                $stmt->close();

                // 2. Enregistrer la transaction
                $sql = "INSERT INTO transaction (montant_trans, date_trans, typeTrans, id_agent_departement) 
                        VALUES (?, NOW(), 'Paiement Facture', ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("di", $montant, $user_id);
                $stmt->execute();
                $transaction_id = $stmt->insert_id;
                $stmt->close();

                // 3. Mettre à jour la facture avec l'ID de la transaction
                $sql = "UPDATE facture SET statut = 'Payée', id_transaction = ? WHERE id_fac = ? AND id_agent_departement = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $transaction_id, $facture_id, $user_id);
                $stmt->execute();
                $stmt->close();

                // 4. Générer le reçu avec les informations de la facture et de la transaction
                $description_recu = "Paiement de la facture ID $facture_id - Description: $description_facture - Type: $type_facture";
                $sql = "INSERT INTO recu (details, type, DateEmission, id_transaction) 
                        VALUES (?, 'Paiement Facture', NOW(), ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $description_recu, $transaction_id);
                $stmt->execute();
                $stmt->close();

                // Valider la transaction
                $conn->commit();

                $message = "Paiement effectué avec succès! Budget mis à jour: " . number_format($nouveau_budget, 2) . " DH";
                $agent['monnaieDep'] = $nouveau_budget;
            } catch (Exception $e) {
                // Annuler en cas d'erreur
                $conn->rollback();
                $message = "Erreur lors du paiement de la facture: " . $e->getMessage();
            }
        } else {
            $message = "Budget insuffisant pour payer la facture! Budget actuel: " . number_format($budget_actuel, 2) . " DH";
        }
    } else {
        $message = "Facture non trouvée ou non approuvée!";
    }
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

// Récupérer les demandes du département (uniquement type "salaire")
$demandes = [];
if ($section === 'demande') {
    $sql = "SELECT * FROM demande WHERE id_agentd = ? AND type = 'salaire' ORDER BY date_dem DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $demandes[] = $row;
    }
    $stmt->close();
}

// Récupérer les factures liées à l'agent de département
$factures = [];
if ($section === 'generer_facture') {
    $sql = "SELECT * FROM facture WHERE id_agent_departement = ? ORDER BY id_fac DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $factures[] = $row;
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
        .rapport-form, .facture-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .rapport-form label, .facture-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .rapport-form input[type="text"],
        .rapport-form textarea,
        .rapport-form input[type="number"],
        .facture-form input[type="text"],
        .facture-form input[type="number"],
        .facture-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .rapport-form textarea {
            height: 100px;
        }

        .submit-btn, .generer-btn {
            background-color: #b68b8b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-btn:hover, .generer-btn:hover {
            background-color: #9e6f6f;
        }

        /* Style amélioré pour la liste des rapports */
        .rapport-list, .facture-list {
            list-style: none;
            padding: 0;
        }

        .rapport-item, .facture-item {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #b68b8b;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .rapport-item:hover, .facture-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .rapport-item .rapport-header, .facture-item .facture-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .rapport-item .rapport-header .date, .facture-item .facture-header .date {
            font-size: 12px;
            color: #888;
            background-color: #f1dddd;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .rapport-item .rapport-details, .facture-item .facture-details {
            margin-bottom: 10px;
        }

        .rapport-item .rapport-details p, .facture-item .facture-details p {
            font-size: 14px;
            color: #4a4a4a;
            margin: 5px 0;
        }

        .rapport-item .rapport-details p.description, .facture-item .facture-details p.description {
            font-style: italic;
            color: #666;
        }

        .rapport-item .rapport-finances {
            display: flex;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .rapport-item .rapport-finances p {
            font-size: 14px;
            font-weight: bold;
        }

        .rapport-item .rapport-finances .revenu {
            color: #4CAF50;
        }

        .rapport-item .rapport-finances .depenses {
            color: #f44336;
        }

        /* Styles pour la section Demande */
        .demande-list {
            list-style: none;
            padding: 0;
        }

        .demande-item {
            background: #f1dddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
        }

        .demande-date {
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

        .distribute-salary-btn, .payer-btn, .telecharger-btn {
            background-color: #b68b8b;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .distribute-salary-btn:hover, .payer-btn:hover, .telecharger-btn:hover {
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

        .status-paid {
            background-color: #2196F3;
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

        /* Styles pour la section Distribution Salaire */
        .employe-list {
            list-style: none;
            padding: 0;
        }

        .employe-item {
            background: #f1dddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
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
                <li><a href="?section=generer_facture" class="<?php echo $section === 'generer_facture' ? 'active' : ''; ?>">Générer Facture</a></li>
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
                        <?php echo "Budget disponible: " . number_format($agent['monnaieDep'], 2) . " DH"; ?>
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
                                <div class="rapport-header">
                                    <span class="date"><?php echo date('d/m/Y', strtotime($rapport['date_rapp'])); ?></span>
                                </div>
                                <div class="rapport-details">
                                    <p class="description"><?php echo htmlspecialchars($rapport['description']); ?></p>
                                </div>
                                <div class="rapport-finances">
                                    <p class="revenu">Revenu: <?php echo number_format($rapport['revenu_total'], 2); ?> DH</p>
                                    <p class="depenses">Dépenses: <?php echo number_format($rapport['depenses_total'], 2); ?> DH</p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <!-- Demande -->
                <?php if ($section === 'demande'): ?>
                <section id="demande" class="dashboard-card">
                    <h3>Demandes de Salaire</h3>
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
                        <?php if (empty($demandes)): ?>
                            <li class="demande-item">
                                <p>Aucune demande de salaire trouvée.</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <!-- Distribution Salaire -->
                <?php if ($section === 'distribution_salaire'): ?>
                <section id="distribution_salaire" class="dashboard-card">
                    <h3>Distribution Salaire</h3>
                    <p>Budget disponible: <?php echo number_format($agent['monnaieDep'], 2); ?> DH</p>
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
                                    <?php if ($agent['monnaieDep'] >= $employe['salaire']): ?>
                                        <div class="action-buttons">
                                            <a href="?section=distribution_salaire&action=distribuer_salaire&emp_id=<?php echo $employe['id_emp']; ?>" 
                                               class="distribute-salary-btn">Distribuer Salaire</a>
                                        </div>
                                    <?php else: ?>
                                        <p style="color: #f44336;">Budget insuffisant pour ce salaire</p>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <!-- Générer Facture -->
                <?php if ($section === 'generer_facture'): ?>
                <section id="generer_facture" class="dashboard-card">
                    <h3>Générer Facture</h3>
                    <form method="POST" class="facture-form">
                        <label for="description">Description:</label>
                        <textarea name="description" required></textarea>
                        <label for="montant">Montant (DH):</label>
                        <input type="number" name="montant" step="0.01" min="0" required>
                        <label for="type">Type:</label>
                        <select name="type" required>
                        <option value="Salaire">Événement organisé par le département</option>
<option value="Fourniture">Avance accordée à un employé</option>
<option value="Service">Autres services (précisez les détails dans la description)</option>

                        </select>
                        <button type="submit" name="generer_facture" class="generer-btn">Générer Facture</button>
                    </form>

                    <h4>Factures Existantes</h4>
                    <ul class="facture-list">
                        <?php if (empty($factures)): ?>
                            <li class="facture-item">
                                <p>Aucune facture trouvée.</p>
                            </li>
                        <?php else: ?>
                            <?php foreach ($factures as $facture): ?>
                                <li class="facture-item">
                                    <div class="facture-header">
                                        <span class="date"><?php echo "ID: " . $facture['id_fac']; ?></span>
                                    </div>
                                    <div class="facture-details">
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($facture['description']); ?></p>
                                        <p><strong>Montant:</strong> <?php echo number_format($facture['montant'], 2); ?> DH</p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($facture['type']); ?></p>
                                        <p><strong>Statut:</strong> 
                                            <span class="status status-<?php 
                                                echo strtolower($facture['statut']) === 'approuvée' ? 'approved' : 
                                                     (strtolower($facture['statut']) === 'rejetée' ? 'rejected' : 
                                                     (strtolower($facture['statut']) === 'payée' ? 'paid' : 'pending')); 
                                            ?>">
                                                <?php echo htmlspecialchars($facture['statut']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <?php if ($facture['statut'] === 'Approuvée'): ?>
                                        <div class="action-buttons">
                                            <a href="?section=generer_facture&action=payer&facture_id=<?php echo $facture['id_fac']; ?>" 
                                               class="payer-btn">Procéder au Paiement</a>
                                        </div>
                                    <?php elseif ($facture['statut'] === 'Payée'): ?>
                                        <div class="action-buttons">
                                            <a href="telecharger_recu.php?facture_id=<?php echo $facture['id_fac']; ?>" 
                                               class="telecharger-btn" target="_blank">Télécharger Reçu</a>
                                        </div>
                                    <?php endif; ?>
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
