<?php
session_start();

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

$messageErreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $motdepasse = $_POST['password'] ?? '';

    // Vérifier si l'utilisateur existe avec l'email
    $sql = "SELECT id_client, password FROM client WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $utilisateur = $result->fetch_assoc();

        if ($motdepasse === $utilisateur['password']) {
            $_SESSION['client_id'] = $utilisateur['id_client'];
            header("Location: historique_reservations.php");
            exit();
        } else {
            $messageErreur = "Mot de passe incorrect.";
        }
    } else {
        $messageErreur = "Aucun compte trouvé avec cet email.";
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
                <img src="clientLogin.jpg" class="illustration-img" alt="Illustration client">
            </div>
        </div>

        <div class="login-section">
            <div class="logo">
                <img src="maqlog.jpg" class="logo-img" alt="Logo">
                <h1>TetraVilla</h1>
            </div>

            <h3>Se connecter à votre compte</h3>
            <p class="subtitle">Accédez à votre compte et profitez de nos services exclusifs</p>

            <?php if (!empty($messageErreur)) : ?>
                <p style="color:red; font-weight: bold;"><?php echo $messageErreur; ?></p>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
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
        </div>
    </div>
</body>
</html>
