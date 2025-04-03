<?php
require_once 'dbconfig.php';

$employee = null;
$departements = [];
$error = null;

// Récupérer la liste des départements
$result = $conn->query("SELECT id_dep, nom_dep FROM departement");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departements[] = $row;
    }
}

// Récupérer l'employé à modifier
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT * FROM employe WHERE id_emp = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    
    if (!$employee) {
        die("Employé non trouvé");
    }
    
    $stmt->close();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
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

    $sql = "UPDATE employe SET 
            nom_emp = ?,
            prenom_emp = ?,
            salaire = ?,
            tel = ?,
            cin = ?,
            poste = ?,
            id_dep = ?,
            email_emp = ?,
            numCompteEmp = ?,
            code = ?,
            dateEmbauche = ?
            WHERE id_emp = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssissssi", 
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
        $date_embauche,
        $id
    );

    if ($stmt->execute()) {
        header('Location: gererEmp.html?success=1');
        exit;
    } else {
        $error = "Erreur de mise à jour: " . $conn->error;
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
    <title>Modifier Employé - TetraVilla</title>
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
    </style>
</head>
<body>
    <div class="form-container">
        <h1><i class="fas fa-user-edit"></i> Modifier Employé</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($employee['id_emp'] ?? ''); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($employee['nom_emp'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?php echo htmlspecialchars($employee['prenom_emp'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Salaire</label>
                    <input type="number" step="0.01" name="salaire" value="<?php echo htmlspecialchars($employee['salaire'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="tel" value="<?php echo htmlspecialchars($employee['tel'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>CIN</label>
                <input type="text" name="cin" value="<?php echo htmlspecialchars($employee['cin'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Poste</label>
                <input type="text" name="poste" value="<?php echo htmlspecialchars($employee['poste'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Département</label>
                <select name="departement" required>
                    <option value="">-- Sélectionnez un département --</option>
                    <?php foreach ($departements as $dep): ?>
                        <option value="<?= $dep['id_dep'] ?>"
                            <?= (isset($employee['id_dep']) && $employee['id_dep'] == $dep['id_dep']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dep['nom_dep']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($employee['email_emp'] ?? ''); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Numéro de Compte</label>
                    <input type="text" name="compte" value="<?php echo htmlspecialchars($employee['numCompteEmp'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Code Employé</label>
                    <input type="text" name="code" value="<?php echo htmlspecialchars($employee['code'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Date d'Embauche</label>
                <input type="date" name="date_embauche" value="<?php echo htmlspecialchars($employee['dateEmbauche'] ?? ''); ?>" required>
            </div>
            
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </form>
    </div>
</body>
</html>