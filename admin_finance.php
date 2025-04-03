<?php
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle "agent_financier"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent_financier') {
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

// Récupérer les informations de l'agent financier connecté
$user_id = $_SESSION['user_id'];
$sql = "SELECT nom_agentf, prenom_agentf, monnaieFinance FROM Agent_Financier WHERE id_agentf = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc() ?? ['nom_agentf' => 'Inconnu', 'prenom_agentf' => '', 'monnaieFinance' => 0];
$stmt->close();

// Liste des départements avec leurs images
$departements = [
    "Ménage" => "imag/menage.jpeg",
    "Sécurité" => "imag/securite.jpeg",
    "Service" => "imag/service.jpeg",
    "Restauration" => "imag/restauration.jpeg",
];

// Message de succès ou d'erreur
$message = "";

// Vérifier la section active via l’URL (ex. ?section=distribution)
$sectionActive = $_GET['section'] ?? '';

// Récupérer les rapports depuis la table rapports
// Récupérer les rapports depuis la table rapports
// Récupérer les rapports avec les informations de l'agent et du département
$rapports = [];
if ($sectionActive === 'rapport') {
    $sql = "SELECT r.*, ad.nom_agentd, ad.prenom_agentd, d.nom_dep 
            FROM rapport r
            JOIN agent_departement ad ON r.id_agentd = ad.id_agentd
            JOIN departement d ON ad.id_dep = d.id_dep
            ORDER BY r.date_rapp DESC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rapports[] = $row;
        }
    }
}


// Traitement de la distribution (si le formulaire est soumis)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['distribuer'])) {
    $totalDistribue = 0;
    foreach ($departements as $dep => $image) {
        $montant = floatval($_POST["montant_$dep"] ?? 0);
        if ($montant > 0) {
            $totalDistribue += $montant;
            // Vérifier si le budget est suffisant
            if ($totalDistribue <= $agent['monnaieFinance']) {
                // Mettre à jour la monnaie du département
                $sql = "UPDATE agent_departement ad
                        JOIN departement d ON ad.id_dep = d.id_dep
                        SET ad.monnaieDep = ad.monnaieDep + ?
                        WHERE d.nom_dep = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ds", $montant, $dep);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $message = "Erreur lors de la préparation de la requête.";
                    break;
                }
            } else {
                $message = "Erreur : Le montant total dépasse votre budget disponible.";
                break;
            }
        }
    }
    if (empty($message)) {
        // Mettre à jour le budget de l'agent financier
        $sql = "UPDATE agent_financier SET monnaieFinance = monnaieFinance - ? WHERE id_agentf = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $totalDistribue, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = "Monnaie distribuée avec succès !";
        // Rafraîchir les données de l'agent
        $sql = "SELECT nom_agentf, prenom_agentf, monnaieFinance FROM agent_financier WHERE id_agentf = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $agent = $result->fetch_assoc();
        $stmt->close();
    }
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
    <title>Admin Finance Dashboard - TetraVilla</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5 !important;
        }

        .dashboard-container {
            display: flex;
            width: 100%;
            height: 100vh;
            background-color: #fff;
        }

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

        .admin-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid #fff;
        }

        .sidebar-header h2 {
            font-size: 20px;
            font-weight: bold;
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
            padding: 35px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: #be9393;
        }

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
            margin-bottom: 10px;
        }

        .dashboard-card p {
            font-size: 16px;
            color: #4a4a4a;
            margin-bottom: 15px;
        }

        /* Distribution section */
        #distribution {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }

        .distribution-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .departement-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            background: #f1dddd;
            border-radius: 5px;
            text-align: center;
        }

        .departement-img {
            width: 120px;
            height: 120px;
            margin-bottom: 10px;
            border-radius: 5px;
            object-fit: cover;
            border: 2px solid #b68b8b;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .departement-item label {
            font-size: 16px;
            color: #4a4a4a;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .departement-item input {
            padding: 8px;
            border: 1px solid #b68b8b;
            border-radius: 5px;
            width: 120px;
            font-size: 14px;
            color: #4a4a4a;
            background-color: #fff;
            text-align: center;
        }

        .distribute-btn {
            background-color: #b68b8b;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .distribute-btn:hover {
            background-color: #9e6f6f;
        }

        .message {
            font-size: 14px;
            color: #b68b8b;
            margin-bottom: 15px;
            text-align: center;
        }

        /* Bouton de déconnexion */
        .logout-btn {
            background-color: #b68b8b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #9e6f6f;
        }

        /* Style pour la liste des rapports */
        .rapport-list {
            list-style: none;
            padding: 0;
        }

        .rapport-item {
            margin-bottom: 15px;
            padding: 10px;
            background: #f1dddd;
            border-radius: 5px;
        }

        .rapport-item p {
            margin: 5px 0;
        }

        .rapport-item .date {
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="admin.jpeg" alt="Admin Photo" class="admin-photo">
                <h2><?php echo $agent['prenom_agentf'] . " " . $agent['nom_agentf']; ?></h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="?section=budget">Budget Total</a></li>
                <li><a href="?section=distribution">Distribution de Monnaie</a></li>
                <li><a href="?section=rapport">Rapport</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Finance Dashboard</h1>
                <div class="logo">
                    <img src="maqlog.jpg" alt="TetraVilla Logo" class="logo-img">
                    <span>TetraVilla</span>
                </div>
            </header>

            <!-- Bouton de déconnexion -->
            <form method="POST" style="margin-bottom: 20px;">
                <button type="submit" name="logout" class="logout-btn">Se Déconnecter</button>
            </form>

            <!-- Dashboard Sections -->
            <div class="dashboard-sections">
                <!-- Budget Total -->
                <section id="budget" class="dashboard-card">
                    <h3>Budget Total</h3>
                    <?php if ($sectionActive === 'budget') { ?>
                        <p>
                            <?php echo "Budget de l'agent financier: DH" . number_format($agent['monnaieFinance'], 2); ?>
                        </p>
                    <?php } ?>
                </section>

                <!-- Distribution de Monnaie -->
                <section id="distribution" class="dashboard-card">
                    <h3>Distribution de Monnaie</h3>
                    <?php if ($sectionActive === 'distribution') { ?>
                        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
                        <form action="admin_finance.php?section=distribution" method="POST">
                            <div class="distribution-list">
                                <?php
                                foreach ($departements as $dep => $image) {
                                    echo "<div class='departement-item'>";
                                    echo "<img src='" . (file_exists($image) ? $image : 'images/default.jpg') . "' alt='$dep' class='departement-img'>";
                                    echo "<label>Département de $dep</label>";
                                    echo "<input type='number' name='montant_$dep' min='0' step='0.01' placeholder='Montant en DH'>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                            <button type="submit" name="distribuer" class="distribute-btn">Distribuer Monnaie</button>
                        </form>
                    <?php } ?>
                </section>

               
<!-- Rapport -->
<section id="rapport" class="dashboard-card">
    <h3>Rapport</h3>
    <?php if ($sectionActive === 'rapport') { ?>
        <?php if (empty($rapports)) { ?>
            <p>Aucun rapport disponible.</p>
        <?php } else { ?>
            <ul class="rapport-list">
                <?php foreach ($rapports as $rapport) { ?>
                    <li class="rapport-item">
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($rapport['description']); ?></p>
                        <p><strong>Agent:</strong> <?php echo htmlspecialchars($rapport['prenom_agentd'] . ' ' . $rapport['nom_agentd']); ?></p>
                        <p><strong>Département:</strong> <?php echo htmlspecialchars($rapport['nom_dep']); ?></p>
                        <p><strong>Revenu total:</strong> <?php echo number_format($rapport['revenu_total'], 2); ?> DH</p>
                        <p><strong>Dépenses totales:</strong> <?php echo number_format($rapport['depenses_total'], 2); ?> DH</p>
                        <p class="date"><?php echo date('d/m/Y', strtotime($rapport['date_rapp'])); ?></p>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    <?php } ?>
</section>
            </div>
        </div>
    </div>
</body>
</html>