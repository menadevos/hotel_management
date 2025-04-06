<?php
// Fichier: get_products.php
require_once "connect_base.php";

// Vérifier si l'ID du fournisseur est fourni
if (isset($_GET['fournisseur_id'])) {
    $fournisseurId = $_GET['fournisseur_id'];
    
    // Préparer la requête pour récupérer les produits du fournisseur
    $sql = "SELECT id_produit, nom_produit, prix_produit 
            FROM produit 
            WHERE id_fournisseur = :fournisseur_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fournisseur_id', $fournisseurId);
    $stmt->execute();
    
    // Récupérer tous les produits
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retourner les produits au format JSON
    header('Content-Type: application/json');
    echo json_encode($products);
} else {
    // Si aucun ID de fournisseur n'est fourni, retourner un tableau vide
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>