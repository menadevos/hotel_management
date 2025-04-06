<?php
session_start();
if (!isset($_SESSION['client_id'])) {
    header("Location: login_user.php");
    exit;
}

$filename = $_GET['file'] ?? '';
$client_id = $_GET['id_client'] ?? '';
$pdf_path = 'recus_reservations/' . basename($filename);

// Vérification de sécurité
if (!file_exists($pdf_path) || !preg_match('/^Reçu_Tetravilla_\d+\.pdf$/', $filename)) {
    die("Fichier non trouvé");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de paiement - Tetravilla</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .confirmation-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        p {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .download-btn {
            display: inline-block;
            background: var(--secondary);
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .download-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 128, 185, 0.3);
        }
        
        .download-btn i {
            margin-right: 8px;
        }
        
        .redirect-message {
            font-size: 0.9rem;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h1>Paiement confirmé avec succès!</h1>
        <p>Votre réservation a été confirmée et votre reçu est prêt à être téléchargé.</p>
        
        <a href="telechargement_recu_reser.php?file=<?= urlencode($filename) ?>" class="download-btn" id="downloadBtn">
            <i class="fas fa-file-pdf"></i> Télécharger le reçu
        </a>
       
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>