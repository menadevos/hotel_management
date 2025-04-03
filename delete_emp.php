<?php
require_once 'dbconfig.php';

header('Content-Type: application/json');



try {
    // Méthode 1: Récupération standard POST
    if(isset($_POST['id'])) {
        $id = intval($_POST['id']);
    } 
    // Méthode 2: Récupération pour données JSON
    else {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $id = isset($data['id']) ? intval($data['id']) : 0;
    }

    if($id <= 0) throw new Exception('ID invalide');

    // Vérifiez le nom exact de votre table (Employe ou employe)
    $sql = "DELETE FROM employe WHERE id_emp = ?";
    $stmt = $conn->prepare($sql);
    
    if(!$stmt) throw new Exception("Erreur préparation: ".$conn->error);
    
    $stmt->bind_param("i", $id);
    
    if(!$stmt->execute()) {
        throw new Exception("Erreur exécution: ".$stmt->error);
    }

    if($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Aucune ligne affectée - ID peut-être inexistant");
    }
    
    $stmt->close();
} catch(Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'server' => $_SERVER['REQUEST_METHOD'],
        'received' => ['post' => $_POST, 'input' => file_get_contents('php://input')]
    ]);
} finally {
    $conn->close();
}
?>