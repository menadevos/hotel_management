<?php
include 'dbconfig.php';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id_dem = intval($_POST['id_dem']);
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $statut = ($action === 'approve') ? 'Approuvée' : 'Rejetée';
        $idRH = 1; // À remplacer par l'ID du RH connecté
        
        $sql = "UPDATE demande SET statut_dem = ?, idRH = ? WHERE id_dem = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $statut, $idRH, $id_dem);
        
        if ($stmt->execute()) {
            $message = "Demande de congé #$id_dem $statut avec succès";
        } else {
            $error = "Erreur lors de la mise à jour";
        }
    }
}

// Récupération des demandes de type "Congé" seulement
$sql = "SELECT d.*, e.nom_emp, e.prenom_emp 
        FROM demande d
        JOIN employe e ON d.id_emp = e.id_emp
        WHERE d.type = 'Congé'
        ORDER BY d.date_dem DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Congés - TetraVilla</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            background-color: #f1dddd;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h1 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border-radius: 4px;
            border: 1px solid var(--primary-color);
            background-color: white;
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .filter-btn:hover {
            opacity: 0.9;
        }
        
        .demandes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .demandes-table th {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        
        .demandes-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .demandes-table tr:hover {
            background-color: rgba(139, 30, 63, 0.05);
        }
        
        .statut {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            color: white;
        }
        
        .statut-en-attente {
            background-color: #f39c12;
        }
        
        .statut-approuvee {
            background-color: #2ecc71;
        }
        
        .statut-rejetee {
            background-color: #e74c3c;
        }
        
        .action-btns {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .btn-approve {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-reject {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-action:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .message {
            max-width: 400px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .success-msg {
            background-color: #4caf75;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        
        .error-msg {
            background-color: #e74c3c;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-calendar-day"></i> Gestion des Demandes de Congé</h1>
        
        <?php if (isset($message)): ?>
            <div class="success-msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="filters">
            <button class="filter-btn active" data-filter="all">Toutes</button>
            <button class="filter-btn" data-filter="en-attente">En attente</button>
            <button class="filter-btn" data-filter="approuvees">Approuvées</button>
            <button class="filter-btn" data-filter="rejetees">Rejetées</button>
        </div>
        
        <table class="demandes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employé</th>
                    <th>Statut</th>
                    <th>Description</th>
                    <th>Date Demande</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-statut="<?php echo strtolower(str_replace(' ', '-', $row['statut_dem'])); ?>">
                            <td><?php echo htmlspecialchars($row['id_dem']); ?></td>
                            <td><?php echo htmlspecialchars($row['prenom_emp'] . ' ' . $row['nom_emp']); ?></td>
                            <td>
                                <?php 
                                $statut_class = '';
                                if ($row['statut_dem'] === 'En attente') $statut_class = 'statut-en-attente';
                                elseif ($row['statut_dem'] === 'Approuvée') $statut_class = 'statut-approuvee';
                                else $statut_class = 'statut-rejetee';
                                ?>
                                <span class="statut <?php echo $statut_class; ?>">
                                    <?php echo htmlspecialchars($row['statut_dem']); ?>
                                </span>
                            </td>
                            <td class="message" title="<?php echo htmlspecialchars($row['description_dem']); ?>">
                                <?php echo htmlspecialchars($row['description_dem']); ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['date_dem'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id_dem" value="<?php echo $row['id_dem']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-action btn-approve" <?php echo ($row['statut_dem'] !== 'En attente') ? 'disabled' : ''; ?>>
                                            <i class="fas fa-check"></i> Accepter
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id_dem" value="<?php echo $row['id_dem']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-action btn-reject" <?php echo ($row['statut_dem'] !== 'En attente') ? 'disabled' : ''; ?>>
                                            <i class="fas fa-times"></i> Refuser
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Aucune demande de congé trouvée</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Filtrage des demandes
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Active le bouton cliqué
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                const rows = document.querySelectorAll('.demandes-table tbody tr');
                
                rows.forEach(row => {
                    if (filter === 'all') {
                        row.style.display = '';
                    } else {
                        const statut = row.dataset.statut;
                        if (filter === 'en-attente' && statut === 'en-attente') {
                            row.style.display = '';
                        } else if (filter === 'approuvees' && statut === 'approuvée') {
                            row.style.display = '';
                        } else if (filter === 'rejetees' && statut === 'rejetée') {
                            row.style.display = '';
                        } else if (filter !== 'all') {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });
        
        // Confirmation avant action
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const action = this.querySelector('input[name="action"]').value;
                const id_dem = this.querySelector('input[name="id_dem"]').value;
                
                if (action === 'reject' && !confirm('Êtes-vous sûr de vouloir refuser la demande de congé #' + id_dem + ' ?')) {
                    e.preventDefault();
                }
                if (action === 'approve' && !confirm('Êtes-vous sûr de vouloir accepter la demande de congé #' + id_dem + ' ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>