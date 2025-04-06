<?php
session_start();
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'hotel');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

if (isset($_POST['login']) && isset($_POST['email']) && isset($_POST['password'])) {
    // Récupérer les données du formulaire
    $email = $_POST['email'] ?? null;
    $motdepasse = $_POST['password'] ?? null;
    $id_reservation = $_GET['id_reservation'] ?? null;

    // Vérifier si l'utilisateur existe
    $sql = "SELECT id_client, nom, prenom, Tel FROM client WHERE email = ?";
    $sqlresult = $conn->prepare($sql);
    $sqlresult->bind_param("s", $email);
    $sqlresult->execute();
    $result = $sqlresult->get_result();

    if ($result->num_rows > 0) {
        // L'utilisateur existe, vérifier le mot de passe
        $row = $result->fetch_assoc();
        $clientId = $row['id_client'];
        $nom = $row['nom'];
        $prenom = $row['prenom'];
        $tel = $row['Tel'];

        // Vérifier le mot de passe
        $sqlPassword = "SELECT password FROM client WHERE id_client = ?";
        $stmtPassword = $conn->prepare($sqlPassword);
        $stmtPassword->bind_param("i", $clientId);
        $stmtPassword->execute();
        $resultPassword = $stmtPassword->get_result();

        if ($resultPassword->num_rows > 0) {
            $rowPassword = $resultPassword->fetch_assoc();
            if ($rowPassword['password'] === $motdepasse) {
                // Mot de passe correct, rediriger vers infospersonnels.php
                $_SESSION['client_id'] = $clientId;
                header("Location: infospersonnels.php?email=$email&prenom=$prenom&nom=$nom&tel=$tel&id_client=$clientId&id_reservation=$id_reservation");
                exit();
            } else {
                echo "<h1>Mot de passe incorrect.</h1>";
            }
        } else {
            echo "<h1>Erreur lors de la récupération du mot de passe.</h1>";
        }
    } else {
        echo "<h1>Aucun utilisateur trouvé avec cet email.</h1>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TetraVilla - Connexion</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
    
        <div class="illustration-section">
            <div class="illustration-placeholder">
                <img src="clientLogin.jpg"  class="illustration-img">
            </div>
        </div>

        <div class="login-section">
            <div class="logo">
                <img src="maqlog.jpg"  class="logo-img">
                <h1>TetraVilla</h1>
            </div>

            <h3>Se connecter à votre compte</h3>
            <p class="subtitle">Accédez à votre compte et profitez de nos services exclusifs</p>

            <form action="login_user.php?id_reservation=<?php echo $_GET['id_reservation']; ?>" method="POST" class="login-form">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Mot de passe" required>
                </div>
                <div class="form-options">
                    <label>
                        <input type="checkbox" name="remember"> Se souvenir de moi
                    </label>
                </div>
                <button type="submit" class="login-btn" name="login">Connexion</button>
            </form>

            <p class="register-link">Pas encore inscrit ? <a href="signup_user.php">Créer votre compte</a></p>
        </div>
    </div>
</body>
</html>
