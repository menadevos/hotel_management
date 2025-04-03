<?php
require_once 'dbconfig.php';

$error = null;

// Récupérer la liste des départements pour le menu déroulant
$departements = [];
$result = $conn->query("SELECT id_dep, nom_dep FROM departement");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departements[] = $row;
    }
}

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $conn->real_escape_string($_POST['nom']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $salaire = floatval($_POST['salaire']);
    $tel = $conn->real_escape_string($_POST['tel']);
    $cin = $conn->real_escape_string($_POST['cin']);
    $poste = $conn->real_escape_string($_POST['poste']);
    $id_dep = intval($_POST['departement']);
    $email = $conn->real_escape_string($_POST['email']);
    $compte = $conn->real_escape_string($_POST['compte']);
    $code = $conn->real_escape_string($_POST['code']);
    $date_embauche = $conn->real_escape_string($_POST['date_embauche']);

    // Correction 1: Suppression du doublon id_dep dans la liste des colonnes
    // Correction 2: Ajustement du nombre de paramètres (11 au lieu de 12)
    $sql = "INSERT INTO employe (nom_emp, prenom_emp, salaire, tel, cin, poste, id_dep, email_emp, numCompteEmp, code, dateEmbauche)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    // Correction 3: Ajustement du format des paramètres (11 "s" au lieu de 12)
    $stmt->bind_param("ssdsssissss",
        $nom,
        $prenom,
        $salaire,
        $tel,
        $cin,
        $poste,
        $id_dep,
        $email,
        $compte,
        $code,
        $date_embauche
    );

    if ($stmt->execute()) {
        header('Location: gererEmp.html?success=1');
        exit;
    } else {
        $error = "Erreur d'ajout: " . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Employé - TetraVilla</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #8b1e3f;
            --secondary-color: #b92989;
            --light-gray: #f5f5f5;
            --dark-gray: #333;
            --white: #fff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f1dddd;
            padding: 20px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-gray);
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: var(--secondary-color);
        }
        
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .success-message {
            color: #27ae60;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><i class="fas fa-user-plus"></i> Ajouter un Employé</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="success-message">Employé ajouté avec succès!</div>
        <?php endif; ?>
        
        <form method="POST" action="ajouter_employe.php">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Salaire</label>
                    <input type="number" step="0.01" name="salaire" required>
                </div>
                
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="tel" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>CIN</label>
                <input type="text" name="cin" required>
            </div>
            
            <div class="form-group">
                <label>Poste</label>
                <input type="text" name="poste" required>
            </div>
            <div class="form-group">
                    <label>Département</label>
                    <select name="departement" required>
                        <option value="">-- Sélectionnez un département --</option>
                        <?php foreach ($departements as $dep): ?>
                            <option value="<?= $dep['id_dep'] ?>">
                                <?= htmlspecialchars($dep['nom_dep']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Numéro de Compte</label>
                    <input type="text" name="compte" required>
                </div>
                
                <div class="form-group">
                    <label>Code Employé</label>
                    <input type="text" name="code" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Date d'Embauche</label>
                    <input type="date" name="date_embauche" required>
                </div>
                
            
            </div>
            
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Ajouter l'Employé
            </button>
        </form>
    </div>
</body>
</html>