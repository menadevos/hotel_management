<?php
session_start();

if (!isset($_SESSION['client_id'])) {
    header("Location: login_user.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "tetravilla");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer seulement les réservations confirmées
$client_id = $_SESSION['client_id'];
$sql = "SELECT r.id_reservation, r.date_arrivee, r.date_depart, 
               ch.type_chambre, ch.tarif, t.id_trans
        FROM reservation r
        JOIN chambre ch ON r.id_chambre = ch.id_chambre
        LEFT JOIN transaction t ON t.id_reservation = r.id_reservation
        WHERE r.id_client = ? AND r.etat_reservation = 'confirmée'
        ORDER BY r.date_arrivee DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$reservations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations - Tetravilla</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
            --radius: 12px;
            --shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            
        }
        
        header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }
        
        h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .reservations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .reservation-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        
        }
        
        .reservation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: var(--primary);
            color: white;
            padding: 1.5rem;
            position: relative;
        }
        
        .reservation-id {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--success);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary);
        }
        
        .detail-value {
            text-align: right;
        }
        
        .nuits {
            color: var(--secondary);
            font-weight: 600;
        }
        
        .card-footer {
            padding: 1rem 1.5rem;
            background: #f9f9f9;
            text-align: center;
        }
        
        .download-btn {
            display: inline-flex;
            align-items: center;
            background: var(--secondary);
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        .download-btn:hover {
            background: #2980b9;
        }
        
        .download-btn i {
            margin-right: 0.5rem;
        }
        
        .no-reservations {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            grid-column: 1 / -1;
        }
        
        .no-reservations p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 1.5rem;
        }
        
        .explore-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        
        .explore-btn:hover {
            transform: translateY(-3px);
        }
        
        @media (max-width: 768px) {
            .reservations-grid {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Mes Réservations</h1>
            <p class="subtitle">Retrouvez ici l'historique de vos séjours chez Tetravilla</p>
        </header>
        
        <div class="reservations-grid">
            <?php if (empty($reservations)): ?>
                <div class="no-reservations">
                    <h2>Vous n'avez aucune réservation confirmée</h2>
                    <p>Découvrez nos chambres et services pour planifier votre prochain séjour inoubliable.</p>
                    <a href="index.php" class="explore-btn">Explorer nos offres</a>
                </div>
            <?php else: ?>
                <?php foreach ($reservations as $reservation): ?>
                    <?php
                    $date_arrivee = new DateTime($reservation['date_arrivee']);
                    $date_depart = new DateTime($reservation['date_depart']);
                    $nuits = $date_depart->diff($date_arrivee)->days;
                    $total = $nuits * $reservation['tarif'];
                    ?>
                    
                    <div class="reservation-card">
                        <div class="card-header">
                            <div class="reservation-id">Réservation #<?= $reservation['id_reservation'] ?></div>
                            <div class="status-badge">Confirmée</div>
                        </div>
                        
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Chambre</span>
                                <span class="detail-value"><?= htmlspecialchars($reservation['type_chambre']) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Période</span>
                                <span class="detail-value">
                                    <?= date('d/m/Y', strtotime($reservation['date_arrivee'])) ?> - 
                                    <?= date('d/m/Y', strtotime($reservation['date_depart'])) ?>
                                    <div class="nuits">(<?= $nuits ?> nuits)</div>
                                </span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Prix/nuit</span>
                                <span class="detail-value"><?= number_format($reservation['tarif'], 2) ?> DH</span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Total</span>
                                <span class="detail-value"><?= number_format($total, 2) ?> DH</span>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <a href="telecharger_recu_reservation.php?reservation_id=<?= $reservation['id_reservation'] ?>&transaction_id=<?= $reservation['id_trans'] ?>" class="download-btn">
                                <i class="fas fa-file-pdf"></i> Télécharger le reçu
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>