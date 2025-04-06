<?php
session_start();

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'hotel');
if ($conn->connect_error) {
    die("<div class='error-container'><h1>Erreur : Connexion à la base de données échouée.</h1></div>");
}

// Récupérer l'ID de la réservation depuis l'URL
$idReservation = $_GET['id_reservation'] ?? null;
$_SESSION['reservation_id'] = $idReservation;

if (!$idReservation) {
    echo "<div class='error-container'><h1>Aucune réservation spécifiée.</h1></div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Chambre - Tetravilla</title>
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
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark);
        }

        .container {
            max-width: 800px;
            width: 90%;
            margin: 2rem auto;
        }

        .chambre-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            padding: 2.5rem;
        }

        .chambre-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .title {
            color: var(--primary);
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .detail-value {
            font-size: 1.1rem;
            color: var(--dark);
            background: var(--light);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
        }

        .status {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-confirme {
            background: rgba(39, 174, 96, 0.2);
            color: var(--success);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--secondary);
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            margin-top: 2rem;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            text-align: center;
        }

        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 128, 185, 0.3);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            max-width: 600px;
            margin: 2rem auto;
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 1rem;
            }
            
            .chambre-card {
                padding: 1.5rem;
            }
            
            .detail-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .detail-value {
                margin-top: 0.5rem;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Requête pour récupérer les détails de la chambre via la réservation
        $sql = "SELECT c.type_chambre, c.capacite, c.statut, c.tarif
                FROM chambre c
                INNER JOIN reservation r ON c.id_chambre = r.id_chambre
                WHERE r.id_reservation = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idReservation);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $chambre = $result->fetch_assoc();
        ?>
            <div class="chambre-card">
                <h1 class="title">Détails de la Chambre</h1>
                
                <div class="detail-item">
                    <span class="detail-label">Type de chambre</span>
                    <span class="detail-value"><?= htmlspecialchars($chambre['type_chambre']) ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Capacité</span>
                    <span class="detail-value"><?= htmlspecialchars($chambre['capacite']) ?> personnes</span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value status status-confirme"><?= htmlspecialchars($chambre['statut']) ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Tarif par nuit</span>
                    <span class="detail-value"><?= number_format($chambre['tarif'], 2) ?> DH</span>
                </div>
                
                <a href="consultationServices.php" class="btn">
                    <i class="fas fa-concierge-bell"></i> Consulter les Services
                </a>
            </div>
        <?php
        } else {
            echo "<div class='error-container'><h1>Aucune chambre correspondante trouvée pour cette réservation.</h1></div>";
        }

        // Fermer la connexion
        $conn->close();
        ?>
    </div>
</body>
</html>