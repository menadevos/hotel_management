<?php
session_start();

$id_client = $_GET['id_client'] ?? null;
$isLoggedIn = $id_client !== null;
$_SESSION['client_id'] = $id_client;

$id_reservation = $_GET['id_reservation'] ?? null;
if (!$id_reservation) {
    die("<h1>Erreur : Aucune réservation trouvée.</h1>");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Informations Client</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 40px;
            background-color: #f4f4f4;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .section {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 8px;
            background: #f9f9f9;
        }

        h3 {
            margin-top: 0;
            color: #333;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .info-text {
            font-size: 14px;
            color: #555;
            margin: 8px 0;
        }

        .info-text a {
            color: #007BFF;
            text-decoration: none;
        }

        .info-text a:hover {
            text-decoration: underline;
        }

        .warning {
            color: #e74c3c;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #218838;
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Réservation - Informations du Client</h2>

    <form action="recap.php?id_reservation=<?php echo $id_reservation ?>&client_id=<?php echo $id_client ?>" method="POST">

        <!-- Partie 1: Informations du Client -->
        <div class="section">
            <h3>Informations Personnelles</h3>

            <label for="tel">Téléphone:</label>
            <input type="text" id="tel" name="tel" 
                   value="<?php echo isset($_GET['tel']) ? htmlspecialchars($_GET['tel']) : ''; ?>" required>

            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" 
                   value="<?php echo isset($_GET['prenom']) ? htmlspecialchars($_GET['prenom']) : ''; ?>" required>

            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" 
                   value="<?php echo isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : ''; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" required>
        </div>

        <!-- Partie 2: Connexion ou Création de compte -->
        <div class="section">
            <h3>Connexion ou Création de Compte</h3>

            <p class="info-text">
                Pour continuer votre réservation, vous devez vous connecter ou créer un compte.<br>
                🔹 <strong>Créer un compte vous permet de consulter l’historique de vos réservations !</strong>
            </p>

            <p class="info-text">
                Vous n'avez pas de compte ? <a href="signup_user.php?id_reservation=<?php echo $id_reservation ?>">S'inscrire ici</a>
            </p>

            <p class="info-text">
                Déjà un compte ? <a href="login_user.html?id_reservation=<?php echo $id_reservation ?>">Connectez-vous ici</a>
            </p>
        </div>

        <!-- Message d'avertissement -->
        <?php if (!$isLoggedIn): ?>
            <p class="warning">⚠️ Vous devez être connecté pour continuer la réservation.</p>
        <?php endif; ?>

        <!-- Bouton de validation -->
        <button type="submit" <?php echo !$isLoggedIn ? 'disabled' : ''; ?>>Continuer la réservation</button>

    </form>
</div>

</body>
</html>
