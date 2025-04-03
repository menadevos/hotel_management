<?php
session_start();
// connexion a la base de donnee
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}
// recuperer les donnees du formulaire
if(isset($_POST['inscrire'])){

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$email = $_POST['email'] ?? null;
$telephone = $_POST['telephone'] ?? null;
$motdepasse = $_POST['motdepasse'] ?? null;
// Insérer le client dans la base de données
$sql = "INSERT INTO client (prenom, nom, tel, email, password) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $prenom, $nom, $tel, $email, $password);

if ($stmt->execute()) {
    // Récupérer l'ID du client inséré
    $clientId = $stmt->insert_id;
    // Stocker l'ID du client dans la session
    $_SESSION['client_id'] = $clientId;
     // Redirection vers infospersonnels.html avec les informations du client
     header("Location: login_user.html");
     exit();
} else {
    echo "Erreur lors de l'insertion : " . $conn->error;
}

// Fermer la connexion
$conn->close();

}



































?>