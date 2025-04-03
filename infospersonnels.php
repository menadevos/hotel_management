<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Informations Client</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            width: 50%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .info-text {
            font-size: 14px;
            color: #555;
        }
        button {
            background: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Réservation - Informations du Client</h2>

    <form action="traitement.php" method="POST">

        <!-- Partie 1: Informations du Client -->
        <div class="section">
            <h3>Informations Personnelles</h3>

            <label for="tel">Téléphone:</label>
            <input type="text" id="tel" name="tel" 
                   value="<?php echo isset($_GET['tel']) ? htmlspecialchars($_GET['tel']) : ''; ?>" required><br><br>
    
            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" 
                   value="<?php echo isset($_GET['prenom']) ? htmlspecialchars($_GET['prenom']) : ''; ?>" required><br><br>
    
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" 
                   value="<?php echo isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : ''; ?>" required><br><br>
    
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" required><br><br>
    
        </div>

        <!-- Partie 2: Connexion ou Création de compte -->
        <div class="section">
            <h3>Connexion ou Création de Compte</h3>
            <p class="info-text">
                Pour continuer votre réservation, vous devez vous connecter ou créer un compte.<br>
                🔹 **Créer un compte vous permet de consulter l’historique de vos réservations !**
            </p>
            <p class="info-text">
                Vous n'avez pas de compte ? creer le maintenant! <a href="signup_user.html">S'inscrire ici</a>
            </p>

            <p class="info-text">
                Déjà un compte ? <a href="login_user.html">Connectez-vous ici</a>
            </p>
        </div>

        <!-- Bouton de validation -->
        <button type="submit">Continuer la réservation</button>
    </form>
</div>

</body>
</html>
