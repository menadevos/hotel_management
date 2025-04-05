<?php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    die("<h1>Aucune réservation trouvée.</h1>");
}

$reservationId = $_SESSION['reservation_id'];

$conn = new mysqli('localhost', 'root', '', 'hotel');
if ($conn->connect_error) {
    die("<div class='error-container'><h1>Erreur : Connexion à la base de données échouée.</h1></div>");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Services Disponibles - TetraVilla</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #f5f7fa;
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
            color: var(--dark);
        }

        .container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        h1 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 2rem;
        }

        .service-item {
            border: 1px solid #ddd;
            border-left: 6px solid var(--secondary);
            padding: 1.2rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            background: var(--light);
            transition: var(--transition);
        }

        .service-item:hover {
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.1);
            transform: translateY(-4px);
        }

        .service-item h3 {
            margin: 0 0 0.5rem 0;
            color: var(--secondary);
        }

        .service-item p {
            margin: 0.3rem 0;
        }

        .service-item input[type="checkbox"] {
            transform: scale(1.2);
        }

        .paquet {
            margin-left: 20px;
            background: #fff;
            padding: 10px;
            border-radius: var(--radius);
            margin-top: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        button[type="submit"] {
            background: var(--secondary);
            color: white;
            padding: 12px 24px;
            font-size: 1rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            display: block;
            margin: 2rem auto 0;
            transition: var(--transition);
        }

        button[type="submit"]:hover {
            background: #2980b9;
            box-shadow: 0 5px 15px rgba(41, 128, 185, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        $sqlServices = "SELECT id_service, nom_service, description, type_service, prix FROM service";
        $resultServices = $conn->query($sqlServices);

        if ($resultServices && $resultServices->num_rows > 0) {
            echo "<h1>Liste des Services Disponibles</h1>";
            echo "<form action='gererService.php' method='POST'>";

            while ($service = $resultServices->fetch_assoc()) {
                echo "<div class='service-item'>";
                echo "<h3>{$service['nom_service']}</h3>";
                echo "<p>{$service['description']}</p>";

                if ($service['type_service'] !== 'restauration') {
                    echo "<p><strong>Prix :</strong> {$service['prix']} DH</p>";
                    echo "<label><input type='checkbox' name='services[]' value='{$service['id_service']}'> Sélectionner ce service</label>";
                } else {
                    $sqlPaquets = "SELECT id, nom_paquet, description, prix FROM paquet_restauration";
                    $resultPaquets = $conn->query($sqlPaquets);

                    if ($resultPaquets && $resultPaquets->num_rows > 0) {
                        echo "<div class='paquet'><strong>Paquets disponibles :</strong>";

                        while ($paquet = $resultPaquets->fetch_assoc()) {
                            echo "<label>";
                            echo "<input type='checkbox' name='paquets_restauration[]' value='{$paquet['id']}'>";
                            echo "<div><strong>{$paquet['nom_paquet']}</strong> - {$paquet['prix']} DH<br><small>{$paquet['description']}</small></div>";
                            echo "</label>";
                        }

                        echo "</div>";
                    }
                }

                echo "</div>";
            }

            echo "<button type='submit'>Valider mes sélections</button>";
            echo "</form>";
        } else {
            echo "<div class='error-container'><h1>Aucun service disponible pour le moment.</h1></div>";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
