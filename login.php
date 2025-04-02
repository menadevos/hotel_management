<?php
// Start the session
session_start();

// Database connection
$host = "localhost"; // Replace with your host if different
$username = "root";  // Replace with your MySQL username
$password = "";      // Replace with your MySQL password
$dbname = "hotel";   // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Prepare SQL query based on role
    $sql = "";
    $table = "";
    $id_column = ""; // To store the correct ID column name
    
    switch ($role) {
        case "employe":
            $table = "Employe";
            $sql = "SELECT * FROM Employe WHERE email_emp = ?";
            $id_column = "id_emp";
            break;
        case "agent_departement":
            $table = "Agent_Departement";
            $sql = "SELECT * FROM Agent_Departement WHERE email_agentd = ? AND password_agentd = ?";
            $id_column = "id_agentd";
            break;
        case "agent_financier":
            $table = "Agent_Financier";
            $sql = "SELECT * FROM Agent_Financier WHERE email_agentf = ? AND password_agentf = ?";
            $id_column = "id_agentf";
            break;
        case "gestionnaire_stock":
            $table = "Gestionnaire_Stock";
            $sql = "SELECT * FROM Gestionnaire_Stock WHERE email_gestionnaire = ?";
            $id_column = "id_gestionnaire";
            break;
        case "rh":
            $table = "RH";
            $sql = "SELECT * FROM RH WHERE emailRH = ? AND motDePasse = ?";
            $id_column = "idRH";
            break;
        default:
            echo "Rôle invalide.";
            exit;
    }

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Bind parameters (email and password, except where password is not applicable)
        if ($role == "employe" || $role == "gestionnaire_stock") {
            $stmt->bind_param("s", $email); // Only email for tables without password
        } else {
            $stmt->bind_param("ss", $email, $password); // Email and password
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a matching record exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user[$id_column]; // Use the specific ID column for the table
            $_SESSION['role'] = $role;

            // Redirect based on role
            switch ($role) {
                case "agent_financier":
                    header("Location: admin_finance.php");
                    break;
                case "agent_departement":
                    header("Location: agent_departement.php");
                    break;
                case "employe":
                    header("Location: employe.php");
                    break;
                case "gestionnaire_stock":
                    header("Location: gestionnaire_stock.php");
                    break;
                case "rh":
                    header("Location: rh.php");
                    break;
            }
            exit; // Ensure no further code is executed after redirection
        } else {
            echo "Email ou mot de passe incorrect pour le rôle sélectionné.";
        }

        $stmt->close();
    } else {
        echo "Erreur de préparation de la requête: " . $conn->error;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TetraVilla Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="illustration-section">
            <div class="illustration-placeholder">
                <img src="maqphoto.jpg" class="illustration-img">
            </div>
        </div>

        <div class="login-section">
            <div class="logo">
                <img src="maqlog.jpg" class="logo-img">
                <h1>TetraVilla</h1>
            </div>

            <h2>Espace Admin/Personnel</h2>
            <h3>Se connecter à votre compte</h3>
            <p class="subtitle">Voyez ce qui se passe avec votre business</p>

            <form action="login.php" method="POST" class="login-form">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <select name="role" required>
                        <option value="" disabled selected>Sélectionner un rôle</option>
                        <option value="agent_departement">Agent Departement</option>
                        <option value="agent_financier">Agent Financier</option>
                        <option value="employe">Employe</option>
                        <option value="gestionnaire_stock">Gestionnaire Stock</option>
                        <option value="rh">RH</option>
                    </select>
                </div>
                <div class="form-options">
                    <label>
                        <input type="checkbox" name="remember"> Remember Me
                    </label>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>

            <p class="register-link">Not Registered Yet? <a href="signup.html">Create an account</a></p>
        </div>
    </div>
</body>
</html>