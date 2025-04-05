<?php
session_start();
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// recuperer les infos du client 

$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$email = $_POST['email'] ?? '';
$tel = $_POST['tel'] ?? '';
$reservation_id = $_GET['id_reservation'] ?? null;
if (!$reservation_id) {
    die("<h1>Erreur : Aucune réservation trouvée.</h1>");
}

// recuperer id du client 
$client_id = $_SESSION['client_id'] ?? null;
if (!$client_id) {
    die("<h1>Erreur : Aucune réservation trouvée.</h1>");
}

// Récupérer les détails de la réservation
$reservation_id = $_GET['id_reservation'];
$reservation_query = "SELECT r.*, c.type_chambre, c.tarif
                      FROM reservation r
                      JOIN chambre c ON r.id_chambre = c.id_chambre
                      WHERE r.id_reservation = ?";
$stmt = $conn->prepare($reservation_query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$reservation_result = $stmt->get_result();
$reservation_data = $reservation_result->fetch_assoc();

// Récupérer les services associés
$services_query = "SELECT s.nom_service, type_service, s.prix 
                   FROM reservation_service rs
                   JOIN service s ON rs.id_service = s.id_service
                   WHERE rs.id_reservation = ?";
$stmt = $conn->prepare($services_query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$services_result = $stmt->get_result();
$services = $services_result->fetch_all(MYSQLI_ASSOC);

// Récupérer les paquets de restauration
$paquets_query = "SELECT pr.nom_paquet, pr.prix 
                  FROM reservation_paquet_restauration rpr
                  JOIN paquet_restauration pr ON rpr.paquet_restauration_id = pr.id
                  WHERE rpr.reservation_id = ?";
$stmt = $conn->prepare($paquets_query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$paquets_result = $stmt->get_result();
$paquets = $paquets_result->fetch_all(MYSQLI_ASSOC);

// Calcul du nombre de nuits
$date_arrivee = new DateTime($reservation_data['date_arrivee']);
$date_depart = new DateTime($reservation_data['date_depart']);
$nuits = $date_arrivee->diff($date_depart)->days;

// Calcul du total
$total_chambre = $reservation_data['tarif'] * $nuits;
$total_services = array_sum(array_column($services, 'prix'));
$total_paquets = array_sum(array_column($paquets, 'prix'));
$total = $total_chambre + $total_services + $total_paquets;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif de Réservation - Tetravilla</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ===== VARIABLES & BASE ===== */
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --success: #27ae60;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --radius: 10px;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: #f9f9f9;
            padding: 20px;
            margin: 0;
        }

        /* ===== LAYOUT ===== */
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .section {
            padding: 25px;
            border-bottom: 1px solid #eee;
            animation: fadeIn 0.5s ease forwards;
        }

        /* ===== TYPOGRAPHY ===== */
        h2, h3 {
            color: var(--primary);
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary);
        }
        
        h2 { font-size: 1.8rem; }
        h3 { font-size: 1.5rem; }

        /* ===== COMPONENTS ===== */
        /* Info items */
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 12px 15px;
            background: var(--light);
            border-radius: var(--radius);
            transition: var(--transition);
        }
        
        .info-item:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary);
            min-width: 200px;
        }
        
        .info-value {
            text-align: right;
            color: var(--dark);
        }

        /* Lists */
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        li {
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
        }
        
        li:last-child { border-bottom: none; }

        /* Total section */
        .total {
            background: var(--primary);
            color: white;
            font-size: 1.2rem;
            padding: 15px;
            margin-top: 20px;
        }
        
        .total .info-label,
        .total .info-value {
            color: white;
        }
        
        .total .info-value {
            font-weight: bold;
        }

        /* Payment form */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
        }
        
        .card-icons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .card-icon {
            height: 25px;
            filter: grayscale(30%);
            transition: var(--transition);
        }
        
        .card-icon:hover {
            filter: grayscale(0);
        }

        /* Buttons */
        .payment-btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--success);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 16px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            margin-top: 20px;
            text-align: center;
        }
        
        .payment-btn:hover {
            background: #2ecc71;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }

        /* Flex utilities */
        .flex-group {
            display: flex;
            gap: 15px;
        }
        
        .flex-1 { flex: 1; }

        /* Text utilities */
        .text-center { text-align: center; }
        .mt-20 { margin-top: 20px; }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            
            .info-item {
                flex-direction: column;
            }
            
            .info-label,
            .info-value {
                text-align: left;
                width: 100%;
            }
            
            .info-value {
                margin-top: 5px;
            }
            
            .flex-group {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section">
            <h3>Récapitulatif de la réservation</h3>
            
            <!-- Informations client -->
            <div class="info-item">
                <span class="info-label">Nom:</span>
                <span class="info-value"><?= htmlspecialchars($nom) ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Prénom:</span>
                <span class="info-value"><?= htmlspecialchars($prenom) ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Téléphone:</span>
                <span class="info-value"><?= htmlspecialchars($tel) ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($email) ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Numéro de réservation:</span>
                <span class="info-value"><?= htmlspecialchars($reservation_id) ?></span>
            </div>
            
            <!-- Informations sur la chambre -->
            <div class="info-item">
                <span class="info-label">Chambre réservée:</span>
                <span class="info-value">
                    id_chambre : <?= htmlspecialchars($reservation_data['id_chambre']) ?>
                    (<?= htmlspecialchars($reservation_data['type_chambre'] ?? '') ?>)
                    - <?= htmlspecialchars($reservation_data['tarif']) ?> MAD/nuit
                </span>
            </div>
            
            <!-- Dates de séjour -->
            <div class="info-item">
                <span class="info-label">Période:</span>
                <span class="info-value">
                    Du <?= htmlspecialchars(date('d/m/Y', strtotime($reservation_data['date_arrivee']))) ?>
                    au <?= htmlspecialchars(date('d/m/Y', strtotime($reservation_data['date_depart']))) ?>
                    (<?= $nuits ?> nuits)
                </span>
            </div>
            
            <!-- Détails des prix -->
            <div class="info-item">
                <span class="info-label">Coût chambre:</span>
                <span class="info-value">
                    <?= $nuits ?> nuits × <?= $reservation_data['tarif'] ?> MAD = 
                    <strong><?= $total_chambre ?> MAD</strong>
                </span>
            </div>
            
            <!-- Services choisis -->
            <?php if (!empty($services)): ?>
            <div class="info-item" style="align-items: flex-start;">
                <span class="info-label">Services:</span>
                <span class="info-value">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($services as $service): ?>
                            <li>
                                <?= htmlspecialchars($service['nom_service']) ?>
                                <?php if ($service['type_service'] !== 'restauration' && $service['prix'] > 0): ?>
                                    - <?= htmlspecialchars($service['prix']) ?> MAD
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($total_services > 0): ?>
                        <strong>Total services: <?= $total_services ?> MAD</strong>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Paquets de restauration -->
            <?php if (!empty($paquets)): ?>
            <div class="info-item" style="align-items: flex-start;">
                <span class="info-label">Options restauration:</span>
                <span class="info-value">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($paquets as $paquet): ?>
                            <li><?= htmlspecialchars($paquet['nom_paquet']) ?> - <?= htmlspecialchars($paquet['prix']) ?> MAD</li>
                        <?php endforeach; ?>
                    </ul>
                    <strong>Total restauration: <?= $total_paquets ?> MAD</strong>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Total général -->
            <div class="info-item total">
                <span class="info-label">Total à payer:</span>
                <span class="info-value"><?= $total ?> MAD</span>
            </div>

            
        <div class="payment-section">
            <h2><i class="fas fa-credit-card"></i> Informations de paiement</h2>
            
            <form id="payment-form" action="traitement_paiement.php?id_reservation=<?= $reservation_id ?>&total=<?= $total ?>" method="POST">
                <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                <input type="hidden" name="montant_total" value="<?= $total ?>">
                
                <div class="form-group">
                    <label for="card-number">Numéro de carte</label>
                    <input type="text" id="card-number" name="card_number" placeholder="1234 5678 9012 3456" required>
                    <div class="card-icons">
                        <img src="https://cdn-icons-png.flaticon.com/512/825/825470.png" alt="Visa" class="card-icon">
                        <img src="https://cdn-icons-png.flaticon.com/512/825/825463.png" alt="Mastercard" class="card-icon">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="card-name">Nom sur la carte</label>
                    <input type="text" id="card-name" name="card_name" placeholder="Nom Prénom" required>
                </div>
                
                <div class="form-group" style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label for="expiry-date">Date d'expiration</label>
                        <input type="text" id="expiry-date" name="expiry_date" placeholder="MM/AA" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="cvv">Code de sécurité</label>
                        <input type="number" id="cvv" name="cvv" placeholder="CVV" required>
                    </div>
                </div>

            <!-- Bouton de redirection vers la page de paiement -->
            <div class="text-center">
                <button type="submit" class="payment-btn" name="payer">Payer maintenant</button>
            
        </div>
    </div>
</body>
</html>

<?php
// Fermer la connexion
$conn->close();
?>
