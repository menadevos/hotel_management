<?php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    die("<h1>Aucune réservation trouvée.</h1>");
}

$reservationId = $_SESSION['reservation_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceChoisi = $_POST['service'] ?? null;

    if (!$serviceChoisi) {
        die("<h1>Erreur : Aucun service sélectionné.</h1>");
    }

    echo "<h1>Formulaire pour : $serviceChoisi</h1>";

    // Afficher des champs spécifiques selon le service sélectionné
    if ($serviceChoisi === 'Spa') {
        echo "<form action='AjouterServiceRes.php' method='POST'>
                <label for='date'>Choisissez une date :</label>
                <input type='date' id='date' name='date' required><br><br>
                <label for='prix'>Prix :</label>
                <input type='text' id='prix' name='prix' value='200' readonly><br><br>
                <input type='hidden' name='service' value='Spa'>
                <button type='submit'>Inclure dans la reservation</button>
              </form>";
    } elseif ($serviceChoisi === 'Gym') {
        echo "<form action='AjouterServiceRes.php' method='POST'>
                <label for='prix'>Prix :</label>
                <input type='text' id='prix' name='prix' value='100' readonly><br><br>
                <input type='hidden' name='service' value='Gym'>
                <button type='submit'>Inclure dans la reservation</button>
              </form>";
    } elseif ($serviceChoisi === 'Restauration') {
        // Connexion à la base de données
        $conn = new mysqli('localhost', 'root', '', 'tetravilla');
        if ($conn->connect_error) {
            die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
        }

        // Récupérer les paquets disponibles pour la restauration
        $sqlPaquets = "SELECT nomPaquet, prixPaquet, description FROM restauration";
        $resultPaquets = $conn->query($sqlPaquets);

        echo "<form action='AjouterServiceRes.php' method='POST'>
        <label for='paquet'>Choisissez un paquet :</label>
        <select id='paquet' name='paquet' required>";
while ($paquet = $resultPaquets->fetch_assoc()) {
    echo "<option value='{$paquet['nomPaquet']}'>{$paquet['nomPaquet']} - {$paquet['prixPaquet']} DH</option>";
}
echo "</select><br><br>";

// Repositionner le curseur pour relire les données
$resultPaquets->data_seek(0); 

// Afficher les descriptions de tous les paquets
echo "<h2>Descriptions des Paquets Disponibles :</h2>";
while ($paquet = $resultPaquets->fetch_assoc()) {
    echo "<p><strong>{$paquet['nomPaquet']} :</strong> {$paquet['description']}</p>";
}

echo "<input type='hidden' name='service' value='Restauration'>
      <button type='submit'>Inclure dans la reservation</button>
      </form>";


        $conn->close();
    } else {
        echo "<h1>Service non reconnu.</h1>";
    }
} else {
    die("<h1>Méthode non autorisée.</h1>");
}
?>
