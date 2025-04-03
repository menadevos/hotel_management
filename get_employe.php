<?php
require_once 'dbconfig.php';

header('Content-Type: application/json');

// Requête modifiée pour joindre la table département
$sql = "SELECT e.*, d.nom_dep 
        FROM employe e
        LEFT JOIN departement d ON e.id_dep = d.id_dep
        ORDER BY e.nom_emp";

$result = $conn->query($sql);

if (!$result) {
    // Gestion des erreurs SQL
    echo json_encode(['error' => $conn->error]);
    exit;
}

$employees = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

echo json_encode($employees);
$conn->close();
?>