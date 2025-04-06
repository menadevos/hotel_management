<?php
session_start();
if (!isset($_SESSION['client_id'])) {
    header("Location: login_user.php");
    exit;
}

$filename = $_GET['file'] ?? '';
$pdf_path = 'recus_reservations/' . basename($filename);

// Vérification de sécurité
if (!file_exists($pdf_path) || !preg_match('/^Reçu_Tetravilla_\d+\.pdf$/', $filename)) {
    die("Fichier non trouvé");
}

// Forcer le téléchargement
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: ' . filesize($pdf_path));
readfile($pdf_path);
exit;