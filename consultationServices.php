<?php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    die("<h1>Aucune réservation trouvée.</h1>");
}

$reservationId = $_SESSION['reservation_id'];
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

// Requête pour récupérer les services disponibles
$sqlServices = "SELECT nom_service, description,type_service,prix FROM service";
$resultServices = $conn->query($sqlServices);

if ($resultServices && $resultServices->num_rows > 0) {
    echo "<h1>Liste des Services Disponibles</h1>";
    echo "<form action='gererService.php' method='POST'>";
    
    while ($service = $resultServices->fetch_assoc()) {
        echo "<div class='service-item' style='margin: 15px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h3>{$service['nom_service']}</h3>";
        echo "<p>{$service['description']}</p>";
        if($service['type_service'] !== 'restauration'){
            echo "<p> Prix :  {$service['prix']}</p>";
        }
       
        
        if ($service['type_service'] == 'restauration') {
            // Récupérer les paquets de restauration disponibles
            $sqlPaquets = "SELECT id, nom_paquet, description, prix FROM paquet_restauration";
            $resultPaquets = $conn->query($sqlPaquets);
            
            if ($resultPaquets && $resultPaquets->num_rows > 0) {
                echo "<div style='margin-left: 20px;'>";
                echo "<h4>Paquets disponibles :</h4>";
                
                while ($paquet = $resultPaquets->fetch_assoc()) {
                    echo "<div style='margin: 10px 0; padding: 8px; background: #f9f9f9; border-radius: 4px;'>";
                    echo "<label style='display: flex; align-items: center; gap: 10px;'>";
                    echo "<input type='checkbox' name='paquets_restauration[]' value='{$paquet['id']}'>";
                    echo "<div>";
                    echo "<strong>{$paquet['nom_paquet']}</strong> - {$paquet['prix']}DH";
                    echo "<br><small>{$paquet['description']}</small>";
                    echo "</div>";
                    echo "</label>";
                    echo "</div>";
                }
                echo "</div>";
            }
        } else {
            // Affichage standard pour les autres services
            echo "<label style='display: flex; align-items: center; gap: 10px;'>";
            echo "<input type='checkbox' name='services[]' value='{$service['nom_service']}'>";
            echo "Sélectionner ce service";
            echo "</label>";
        }
        echo "</div>";
    }
    
    echo "<div style='margin-top: 20px;'>";
    echo "<button type='submit' style='padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;'>";
    echo "Valider mes sélections";
    echo "</button>";
    echo "</div>";
    echo "</form>";
} else {
    echo "<h1>Aucun service disponible pour le moment.</h1>";
}

// Fermer la connexion
$conn->close();
?>

