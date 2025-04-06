<?php
require_once "connect_base.php"; 

// Récupérer les paramètres
$type_stock = $_GET['type_stock'] ?? '';
if(empty($type_stock)) {
    die("Type de stock non spécifié");
}

$id_gest = $_GET['id'] ?? '';
if(empty($id_gest)) die("ID gestionnaire non spécifié");

// Requête SQL pour récupérer monnaiestock
$query = "SELECT monnaiestock 
          FROM gestionnaire_stock 
          WHERE id_gestionnaire = :id_gest AND type_stock = :type_stock";

$stmt = $conn->prepare($query);
$stmt->bindParam(':id_gest', $id_gest, PDO::PARAM_INT);
$stmt->bindParam(':type_stock', $type_stock, PDO::PARAM_STR);
$stmt->execute();
$monnaiestock = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget de Stock</title>
    <style>
      
      


        .dashboard-card {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width:400px;
            height:200px;
        }

        .dashboard-card h3 {
            font-size: 20px;
            color: #b68b8b;
            margin-bottom: 10px;
        }

        .dashboard-card p {
            font-size: 16px;
            color: #4a4a4a;
            margin-bottom: 15px;
        }


        .budget-display {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .budget-amount {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .budget-currency {
            font-size: 20px;
            color: #7f8c8d;
        }
        
        .budget-type {
            background-color: #f1dddd;
            color: #b68b8b;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            display: inline-block;
            margin-top: 10px;
        }
        
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <h3>Budget de Stock</h3>
            
            <div class="budget-display">
                <span class="budget-amount"><?php echo number_format($monnaiestock, 2); ?></span>
                <span class="budget-currency">DH</span>
            </div>
            
            <div class="budget-type">
                Type: <?php echo htmlspecialchars(ucfirst($type_stock)); ?>
            </div>
            
        
        </div>
    </div>
</body>
</html>