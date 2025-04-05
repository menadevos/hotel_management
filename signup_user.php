<?php
session_start();

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'tetravilla');
if ($conn->connect_error) {
    die("<h1>Erreur : Connexion à la base de données échouée.</h1>");
}

// Récupérer les données du formulaire
if (isset($_POST['inscrire'])) {
    $nom = $_POST['nom'] ?? null;
    $prenom = $_POST['prenom'] ?? null;
    $email = $_POST['email'] ?? null;
    $telephone = $_POST['telephone'] ?? null;
    $motdepasse = $_POST['pass'] ?? null;

    // Insérer le client dans la base de données
    $sql = "INSERT INTO client (prenom, nom, tel, email, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $prenom, $nom, $telephone, $email, $motdepasse);

    if ($stmt->execute()) {
        // Récupérer l'ID du client inséré
        $clientId = $stmt->insert_id;
        // Stocker l'ID du client dans la session
        $_SESSION['client_id'] = $clientId;

        // Récupérer l'ID de réservation depuis l'URL
        $id_reservation = $_GET['id_reservation'] ?? null;
        // Redirection vers la page de connexion avec l'ID de réservation
        header("Location: login_user.php?id_reservation=$id_reservation");
        exit();
    } else {
        echo "Erreur lors de l'insertion : " . $conn->error;
    }
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TetraVilla - Inscription Employé</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f1dddd;
            padding: 20px;
        }
        
        .main-container {
            display: flex;
            width: 100%;
            max-width: 800px; /* Légèrement réduit */
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .image-section {
            flex: 1;
            background: url('signupperso.jpeg') no-repeat center center;
            background-size: cover;
            position: relative;
            min-height: 450px;
        }
        
        .image-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(139, 30, 63, 0.1);
        }
        
        .form-section {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
        }
        
        .logo {
            font-size: 22px; /* Réduit */
            font-weight: bold;
            color: #8b1e3f;
            margin-bottom: 8px; /* Réduit */
            text-align: center;
        }
        
        .subtitle {
            font-size: 12px; /* Réduit */
            color: #7f8c8d;
            margin-bottom: 20px; /* Réduit */
            text-align: center;
        }
        
        h2 {
            font-size: 18px; /* Réduit */
            color: #2c3e50;
            margin-bottom: 12px; /* Réduit */
            text-align: center;
        }
        
        .description {
            font-size: 12px; /* Réduit */
            color: #7f8c8d;
            margin-bottom: 20px; /* Réduit */
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 12px; /* Réduit */
        }
        
        .form-group label {
            display: block;
            margin-bottom: 4px; /* Réduit */
            font-size: 12px; /* Réduit */
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 12px; /* Réduit */
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 13px; /* Réduit */
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #8b1e3f;
            box-shadow: 0 0 0 2px rgba(139, 30, 63, 0.1);
        }
        
        .login-button {
            width: 100%;
            padding: 10px; /* Réduit */
            background-color: #8b1e3f;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px; /* Réduit */
            font-weight: 500; /* Légèrement réduit */
            cursor: pointer;
            margin-top: 12px; /* Réduit */
            transition: background-color 0.2s;
        }
        
        .login-button:hover {
            background-color: #b92989;
        }
        
        .note {
            font-size: 12px; /* Réduit */
            color: #7f8c8d;
            margin-top: 20px; /* Réduit */
            text-align: center;
        }
        
        .note a {
            color: #8b1e3f;
            text-decoration: none;
            font-weight: 500;
        }
        
        .note a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            
            .image-section {
                min-height: 180px; /* Réduit */
            }
            
            .form-section {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="image-section"></div>
        
        <div class="form-section">
            <div class="login-container">
                <div class="logo">TetraVilla</div>
             
                
                <h2>Créer votre compte</h2>
                <p class="description">Rejoignez-nous et profitez d'offres exclusives !</p>
                
                <form action="signup_user.php?id_reservation=<?php echo $_GET['id_reservation']; ?>" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                   
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                    <div class="form-group">
                        <label for="pass">Mot de passe</label>
                        <input type="password" id="pass" name="pass" required >
                    </div>
                    <button type="submit" class="login-button" name="inscrire">S'inscrire</button>
                </form>
                
                <div class="note">avez déjà un compte? <a href="login_user.php?id_reservation=<?php echo $_GET['id_reservation']; ?>">Connectez-vous ici</a></div>
            </div>
        </div>
    </div>
</body>
</html>