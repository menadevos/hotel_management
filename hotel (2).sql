-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2025 at 11:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel`
--

-- --------------------------------------------------------

--
-- Table structure for table `agent_departement`
--

CREATE TABLE `agent_departement` (
  `id_agentd` int(11) NOT NULL,
  `nom_agentd` varchar(50) DEFAULT NULL,
  `prenom_agentd` varchar(50) DEFAULT NULL,
  `password_agentd` varchar(255) DEFAULT NULL,
  `email_agentd` varchar(100) DEFAULT NULL,
  `numCompteDep` int(11) DEFAULT NULL,
  `monnaieDep` float DEFAULT NULL,
  `id_dep` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_departement`
--

INSERT INTO `agent_departement` (`id_agentd`, `nom_agentd`, `prenom_agentd`, `password_agentd`, `email_agentd`, `numCompteDep`, `monnaieDep`, `id_dep`) VALUES
(1, 'Youssef', 'Amrani', 'youssef123', 'youssef.amrani@dep.com', 111222, 60393, 1),
(2, 'Amina', 'Khalid', 'amina456', 'amina.khalid@dep.com', 333444, 60100, 2),
(3, 'Nadia', 'El Fassi', 'nadia789', 'nadia.elfassi@dep.com', 555666, 60450, 3),
(4, 'Omar', 'Bennani', 'omar321', 'omar.bennani@dep.com', 777888, 60275, 4);

-- --------------------------------------------------------

--
-- Table structure for table `agent_financier`
--

CREATE TABLE `agent_financier` (
  `id_agentf` int(11) NOT NULL,
  `nom_agentf` varchar(50) DEFAULT NULL,
  `prenom_agentf` varchar(50) DEFAULT NULL,
  `password_agentf` varchar(255) DEFAULT NULL,
  `email_agentf` varchar(100) DEFAULT NULL,
  `numCompteFinance` int(11) DEFAULT NULL,
  `monnaieFinance` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_financier`
--

INSERT INTO `agent_financier` (`id_agentf`, `nom_agentf`, `prenom_agentf`, `password_agentf`, `email_agentf`, `numCompteFinance`, `monnaieFinance`) VALUES
(1, 'Omar', 'Bennani', 'omar789', 'omar.bennani@finance.com', 123456, 299800);

-- --------------------------------------------------------

--
-- Table structure for table `chambre`
--

CREATE TABLE `chambre` (
  `id_chambre` int(11) NOT NULL,
  `type_chambre` varchar(30) DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL,
  `statut` varchar(30) DEFAULT NULL,
  `tarif` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chambre`
--

INSERT INTO `chambre` (`id_chambre`, `type_chambre`, `capacite`, `statut`, `tarif`) VALUES
(1, 'simple', 1, 'disponible', 100),
(2, 'double', 2, 'reservée', 200),
(3, 'suite', 5, 'disponible', 500),
(4, 'simple', 1, 'disponible', 100),
(5, 'double', 2, 'disponible', 230),
(6, 'suite', 4, 'disponible', 600),
(7, 'simple', 1, 'disponible', 100),
(8, 'double', 2, 'disponible', 298),
(9, 'suite', 4, 'disponible', 7800),
(10, 'simple', 1, 'disponible', 100),
(11, 'double', 2, 'disponible', 240),
(12, 'suite', 4, 'disponible', 780),
(13, 'simple', 1, 'disponible', 100),
(14, 'double', 2, 'disponible', 250),
(15, 'suite', 4, 'disponible', 760);

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id_client` int(11) NOT NULL,
  `Tel` varchar(15) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`id_client`, `Tel`, `prenom`, `nom`, `email`, `password`) VALUES
(1, '062782902', 'mina', 'mina ', 'bouzid@gmail.com', '123');

-- --------------------------------------------------------

--
-- Table structure for table `commande`
--

CREATE TABLE `commande` (
  `id_comm` int(11) NOT NULL,
  `etat` varchar(50) DEFAULT NULL,
  `date_commande` date DEFAULT NULL,
  `date_livraison` date DEFAULT NULL,
  `id_fact` int(11) DEFAULT NULL,
  `id_gestionnaire_stock` int(11) DEFAULT NULL,
  `id_fournisseur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `demande`
--

CREATE TABLE `demande` (
  `id_dem` int(11) NOT NULL,
  `statut_dem` varchar(50) DEFAULT NULL,
  `description_dem` varchar(255) DEFAULT NULL,
  `date_dem` date DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `idRH` int(11) DEFAULT NULL,
  `id_emp` int(11) DEFAULT NULL,
  `id_agentd` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demande`
--

INSERT INTO `demande` (`id_dem`, `statut_dem`, `description_dem`, `date_dem`, `type`, `idRH`, `id_emp`, `id_agentd`) VALUES
(1, 'Approuvée', 'Demande de congé', '2025-04-03', 'Congé', 1, 1, 1),
(2, 'Approuvée', 'Demande de congé de 5 jours', '2025-04-02', 'Congé', 1, 1, 2),
(3, 'Rejetée', 'Demande congé urgent', '2025-04-01', 'Congé', 1, 2, 3),
(4, 'Approuvée', 'Demande d\'augmentation de salaire', '2025-04-04', 'Salaire', 1, 3, 1),
(5, 'En attente', 'Demande de prime de performance', '2025-04-05', 'Salaire', 1, 4, 2),
(6, 'Rejetée', 'Demande de réajustement salarial', '2025-04-06', 'Salaire', 1, 5, 3),
(7, 'Approuvée', 'Demande de bonus annuel', '2025-04-07', 'Salaire', 1, 6, 1),
(8, 'En attente', 'Demande de révision salariale', '2025-04-08', 'Salaire', 1, 7, 2);

-- --------------------------------------------------------

--
-- Table structure for table `departement`
--

CREATE TABLE `departement` (
  `id_dep` int(11) NOT NULL,
  `nom_dep` varchar(100) DEFAULT NULL,
  `revenu_dep` double DEFAULT NULL,
  `depenses_dep` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departement`
--

INSERT INTO `departement` (`id_dep`, `nom_dep`, `revenu_dep`, `depenses_dep`) VALUES
(1, 'service', 50000, 20000),
(2, 'restauration', 100000, 40000),
(3, 'menage', 75000, 30000),
(4, 'securite', 60000, 25000);

-- --------------------------------------------------------

--
-- Table structure for table `employe`
--

CREATE TABLE `employe` (
  `id_emp` int(11) NOT NULL,
  `prenom_emp` varchar(50) DEFAULT NULL,
  `nom_emp` varchar(50) DEFAULT NULL,
  `salaire` double DEFAULT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `cin` varchar(20) DEFAULT NULL,
  `poste` varchar(50) DEFAULT NULL,
  `email_emp` varchar(100) DEFAULT NULL,
  `numCompteEmp` int(11) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `dateEmbauche` date DEFAULT NULL,
  `id_agentd` int(11) DEFAULT NULL,
  `idRH` int(11) DEFAULT NULL,
  `id_dep` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employe`
--

INSERT INTO `employe` (`id_emp`, `prenom_emp`, `nom_emp`, `salaire`, `tel`, `cin`, `poste`, `email_emp`, `numCompteEmp`, `code`, `dateEmbauche`, `id_agentd`, `idRH`, `id_dep`) VALUES
(1, 'Karim', 'Saidi', 6000, '0656789012', 'AB123456', 'Réceptionniste', 'karim.saidi@hotel.com', 987654, 'K123', '2024-01-15', 1, 1, 1),
(2, 'Leila', 'Mansouri', 7000, '0697800123', 'CD789012', 'Manager', 'leila.mansouri@hotel.com', 469780, '1458', '2022-09-01', 2, 1, 2),
(3, 'Ilyas', 'Malki', 7800, '0706070601', 'T456752', 'Serveur', 'ilyas.malki@hotel.com', 567890, 'S012', '2020-12-12', 1, 1, 2),
(4, 'Salma', 'Azhich', 2500, '0803221110', 'E34597', 'Femme de ménage', 'salma.azhich@hotel.com', 234690, 'S073', '2024-10-14', 2, 1, 3),
(5, 'Yassine', 'El Idrissi', 4200, '0611223344', 'X123789', 'Sécurité', 'yassine.idrissi@hotel.com', 111222, 'SEC01', '2023-05-10', 4, 1, 4),
(6, 'Meriem', 'Benchekroun', 5500, '0622334455', 'M445566', 'Réceptionniste', 'meriem.benchekroun@hotel.com', 222333, 'REC45', '2021-03-22', 1, 1, 1),
(7, 'Anas', 'Touimi', 3900, '0677889900', 'AN998877', 'Cuisinier', 'anas.touimi@hotel.com', 333444, 'CUI22', '2023-08-17', 2, 1, 2),
(8, 'Nawal', 'Ziani', 2600, '0633445566', 'NA112233', 'Femme de ménage', 'nawal.ziani@hotel.com', 444555, 'MEN88', '2022-07-01', 2, 1, 3),
(9, 'Rachid', 'Oulhaj', 4600, '0600112233', 'RA223344', 'Agent de sécurité', 'rachid.oulhaj@hotel.com', 555666, 'SEC02', '2020-11-11', 4, 1, 4),
(10, 'Sofia', 'Amrani', 5200, '0688990011', 'SO334455', 'Chargée clientèle', 'sofia.amrani@hotel.com', 666777, 'CLT90', '2021-06-30', 1, 1, 1),
(11, 'mina ', 'MINA', 7999.99, '0478384832', 'BH71819', 'agent', 'mina26bouzid@gmail.com', 2147483647, '1234', '2025-04-08', NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `facture`
--

CREATE TABLE `facture` (
  `id_fac` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `montant` double DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `id_transaction` int(11) DEFAULT NULL,
  `id_agent_departement` int(11) DEFAULT NULL,
  `id_comm` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facture`
--

INSERT INTO `facture` (`id_fac`, `description`, `montant`, `statut`, `type`, `id_transaction`, `id_agent_departement`, `id_comm`) VALUES
(3, 'facture test ', 100, 'Rejetée', 'Fourniture', NULL, 1, NULL),
(6, 'facture testoooo', 50, 'Payée', 'Fourniture', 8, 1, NULL),
(7, 'OOO', 200, 'Rejetée', 'Service', NULL, 1, NULL),
(8, 'OOO', 200, 'Payée', 'Service', 14, 1, NULL),
(13, 'exemple de description', 100.5, 'En attente', 'stock', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fournisseur`
--

CREATE TABLE `fournisseur` (
  `id_fournisseur` int(11) NOT NULL,
  `nom_fournisseur` varchar(50) DEFAULT NULL,
  `prenom_fournisseur` varchar(50) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `teleF` varchar(15) DEFAULT NULL,
  `numCompte` int(11) DEFAULT NULL,
  `categorie_fournit` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fournisseur`
--

INSERT INTO `fournisseur` (`id_fournisseur`, `nom_fournisseur`, `prenom_fournisseur`, `adresse`, `email`, `teleF`, `numCompte`, `categorie_fournit`) VALUES
(1, 'Leblanc', 'Jacques', '12 Rue des Marchands, Paris', 'j.leblanc@fournisseur.com', '0145678910', 2001, 'alimentation'),
(2, 'Petit', 'Marie', '34 Avenue des Textiles, Lyon', 'm.petit@textiles.com', '0423456789', 2002, 'linges'),
(3, 'Moreau', 'Luc', '56 Boulevard Proprete, Marseille', 'l.moreau@clean.com', '0498765432', 2003, 'ménage'),
(4, 'Roux', 'François', '78 Rue Gourmande, Bordeaux', 'f.roux@linges.com', '0556789123', 2004, 'linges'),
(5, 'salim', 'François', '78 Rue Gourmande, Bordeaux', 'salimx@menage.com', '0556789123', 2004, 'ménage'),
(6, 'Ahmed', 'Amrani', '78 Rue Gourmande, Bordeaux', 'amrani21@linges.com', '0556789123', 2004, 'linges');

-- --------------------------------------------------------

--
-- Table structure for table `gestionnaire_stock`
--

CREATE TABLE `gestionnaire_stock` (
  `id_gestionnaire` int(11) NOT NULL,
  `nom_gestionnaire` varchar(50) DEFAULT NULL,
  `email_gestionnaire` varchar(100) DEFAULT NULL,
  `prenom_gestionnaire` varchar(50) DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `numCompte` int(11) DEFAULT NULL,
  `type_stock` varchar(50) DEFAULT NULL,
  `monnaiestock` float DEFAULT NULL,
  `password_gest` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gestionnaire_stock`
--

INSERT INTO `gestionnaire_stock` (`id_gestionnaire`, `nom_gestionnaire`, `email_gestionnaire`, `prenom_gestionnaire`, `telephone`, `numCompte`, `type_stock`, `monnaiestock`, `password_gest`) VALUES
(1, 'Dupont', 'dupont@hotel.com', 'Jean', '0612345678', 1001, 'alimentation', 5000, 'stock1'),
(2, 'Martin', 'martin@hotel.com', 'Sophie', '0623456789', 1002, 'linges', 3000, 'stock2'),
(3, 'Bernard', 'bernard@hotel.com', 'Pierre', '0634567890', 1003, 'ménage', 4000, 'stock3');

-- --------------------------------------------------------

--
-- Table structure for table `ligne_commande`
--

CREATE TABLE `ligne_commande` (
  `id_commande` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `qte_comm` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paquet_restauration`
--

CREATE TABLE `paquet_restauration` (
  `id` int(11) NOT NULL,
  `nomPaquet` varchar(100) DEFAULT NULL,
  `prix` double DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paquet_restauration`
--

INSERT INTO `paquet_restauration` (`id`, `nomPaquet`, `prix`, `description`, `service_id`) VALUES
(1, 'Petit Déjeuner Continental', 350, 'Café, jus d orange, viennoiseries, fruits frais', 3),
(2, 'Déjeuner Affaire', 500, 'Entrée + plat principal + dessert + boisson', 3),
(3, 'Dîner Gastronomique', 250, 'Menu 3 plats avec vin inclus', 3),
(4, 'Formule Enfant', 99, 'Plat simple + dessert + boisson', 3),
(5, 'Buffet à Volonté', 120, 'Accès illimité au buffet du restaurant', 3),
(6, 'Service en Chambre', 700, 'Menu complet servi dans votre chambre', 3);

-- --------------------------------------------------------

--
-- Table structure for table `produit`
--

CREATE TABLE `produit` (
  `id_produit` int(11) NOT NULL,
  `nom_produit` varchar(100) DEFAULT NULL,
  `Description_produit` text DEFAULT NULL,
  `categorie_produit` varchar(100) DEFAULT NULL,
  `prix_produit` double DEFAULT NULL,
  `id_fournisseur` int(11) DEFAULT NULL,
  `id_gestionnaire` int(11) DEFAULT NULL,
  `qte_stock` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produit`
--

INSERT INTO `produit` (`id_produit`, `nom_produit`, `Description_produit`, `categorie_produit`, `prix_produit`, `id_fournisseur`, `id_gestionnaire`, `qte_stock`) VALUES
(1, 'Farine', 'Farine de blé T45 1kg', 'alimentation', 1.2, 1, 1, 50),
(2, 'Sucre', 'Sucre blanc 1kg', 'alimentation', 1.5, 1, 1, 40),
(3, 'Café', 'Café arabica 250g', 'alimentation', 4.5, 4, 1, 30),
(4, 'Thé', 'Thé vert 100 sachets', 'alimentation', 5.2, 4, 1, 25),
(5, 'Serviette', 'Serviette de bain 100% coton', 'linge', 12, 2, 2, 100),
(6, 'Drap', 'Drap housse 180x200 cm', 'linge', 25, 2, 2, 60),
(7, 'Oreiller', 'Oreiller 50x70 cm', 'linge', 18, 2, 2, 40),
(8, 'Liquide vaisselle', '1L', 'ménage', 3.5, 3, 3, 30),
(9, 'Désinfectant', 'Spray 750ml', 'ménage', 4.2, 3, 3, 25),
(10, 'Sac poubelle', 'Rouleau 30 sacs 50L', 'ménage', 5.8, 3, 3, 20),
(11, 'Chiffon microfibre', 'Lot de 5', 'ménage', 7.5, 3, 3, 50);

-- --------------------------------------------------------

--
-- Table structure for table `rapport`
--

CREATE TABLE `rapport` (
  `id_rapp` int(11) NOT NULL,
  `date_rapp` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `revenu_total` double DEFAULT NULL,
  `depenses_total` double DEFAULT NULL,
  `id_agentd` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rapport`
--

INSERT INTO `rapport` (`id_rapp`, `date_rapp`, `description`, `revenu_total`, `depenses_total`, `id_agentd`) VALUES
(1, '2025-04-02', 'Rapport mensuel avril', 15000, 5000, 1),
(2, '2025-04-03', 'Rapport financier du département restauration :  \r\nRevenus générés par les ventes des repas en salle, room service et événements privés. Augmentation des commandes de menus gastronomiques et des forfaits petit-déjeuner.  \r\nDépenses principales : approvisi', 20000, 8000, 2),
(3, '2025-04-03', 'Rapport financier du département service – 03 avril 2025\r\n\r\nLe département service a joué un rôle clé dans l\'amélioration de l\'expérience client, en répondant efficacement aux demandes et en offrant des prestations de haute qualité.\r\n\r\nRevenus :\r\nLes prin', 50000, 30000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `recu`
--

CREATE TABLE `recu` (
  `id_recu` int(11) NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `DateEmission` date DEFAULT NULL,
  `id_transaction` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recu`
--

INSERT INTO `recu` (`id_recu`, `details`, `type`, `DateEmission`, `id_transaction`) VALUES
(3, 'Paiement de la facture ID 4', 'Paiement Facture', '2025-04-03', 5),
(5, 'Paiement de la facture ID 5', 'Paiement Facture', '2025-04-03', 7),
(6, 'Paiement de la facture ID 6 - Description: facture testoooo - Type: Fourniture', 'Paiement Facture', '2025-04-03', 8),
(7, 'Paiement salaire pour Leila Mansouri', 'Paiement Salaire', '2025-04-03', 9),
(8, 'Paiement salaire pour Karim Saidi', 'Paiement Salaire', '2025-04-03', 10),
(9, 'Paiement salaire pour Karim Saidi', 'Paiement Salaire', '2025-04-03', 11),
(10, 'Paiement de la facture ID 8 - Description: OOO - Type: Service', 'Paiement Facture', '2025-04-05', 14),
(11, 'Paiement de la facture ID 9 - Description: OOO - Type: Service', 'Paiement Facture', '2025-04-05', 15),
(12, 'Réservation #1 - Chambre: double - Du 14/04/2025 au 15/04/2025 - Total: 1,499.00 DH', 'réservation', '2025-04-07', 16);

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `id_reservation` int(11) NOT NULL,
  `date_arrivee` date DEFAULT NULL,
  `date_depart` date DEFAULT NULL,
  `etat_reservation` varchar(30) DEFAULT NULL,
  `nbre_personnes` int(11) DEFAULT NULL,
  `id_client` int(11) DEFAULT NULL,
  `id_chambre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`id_reservation`, `date_arrivee`, `date_depart`, `etat_reservation`, `nbre_personnes`, `id_client`, `id_chambre`) VALUES
(1, '2025-04-14', '2025-04-15', 'confirmée', 2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `reservation_paquet_restauration`
--

CREATE TABLE `reservation_paquet_restauration` (
  `reservation_id` int(11) NOT NULL,
  `paquet_restauration_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_paquet_restauration`
--

INSERT INTO `reservation_paquet_restauration` (`reservation_id`, `paquet_restauration_id`) VALUES
(1, 1),
(1, 2),
(1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `reservation_service`
--

CREATE TABLE `reservation_service` (
  `id_reservation` int(11) NOT NULL,
  `id_service` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_service`
--

INSERT INTO `reservation_service` (`id_reservation`, `id_service`) VALUES
(1, 1),
(1, 2),
(1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `rh`
--

CREATE TABLE `rh` (
  `idRH` int(11) NOT NULL,
  `nomRH` varchar(50) DEFAULT NULL,
  `prenomRH` varchar(50) DEFAULT NULL,
  `emailRH` varchar(100) DEFAULT NULL,
  `motDePasse` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rh`
--

INSERT INTO `rh` (`idRH`, `nomRH`, `prenomRH`, `emailRH`, `motDePasse`) VALUES
(1, 'Hassan', 'El Amrani', 'hassan.elamrani@rh.com', 'rh123');

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `id_service` int(11) NOT NULL,
  `nom_service` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type_service` enum('restauration','gym','spa') DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`id_service`, `nom_service`, `description`, `type_service`, `prix`) VALUES
(1, 'Spa', 'Espace de détente avec massages, bains thérapeutiques et soins bien-être', 'spa', 200.00),
(2, 'Gym', 'Salle de sport équipée avec appareils cardio et musculation, cours collectifs', 'gym', 150.00),
(3, 'Service de restauration', 'Contient les paquets de restauration', 'restauration', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `id_trans` int(11) NOT NULL,
  `montant_trans` double DEFAULT NULL,
  `date_trans` date DEFAULT NULL,
  `typeTrans` varchar(50) DEFAULT NULL,
  `id_agent_financier` int(11) DEFAULT NULL,
  `id_emp` int(11) DEFAULT NULL,
  `id_agent_departement` int(11) DEFAULT NULL,
  `id_reservation` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id_trans`, `montant_trans`, `date_trans`, `typeTrans`, `id_agent_financier`, `id_emp`, `id_agent_departement`, `id_reservation`) VALUES
(3, 100, '2025-04-03', 'Distribution', 1, NULL, 1, NULL),
(5, 100, '2025-04-03', 'Paiement Facture', NULL, NULL, 1, NULL),
(7, 257, '2025-04-03', 'Paiement Facture', NULL, NULL, 1, NULL),
(8, 50, '2025-04-03', 'Paiement Facture', NULL, NULL, 1, NULL),
(9, 7000, '2025-04-03', 'Paiement Salaire', NULL, NULL, 1, NULL),
(10, 6000, '2025-04-03', 'Paiement Salaire', NULL, NULL, 1, NULL),
(11, 6000, '2025-04-03', 'Paiement Salaire', NULL, 1, 1, NULL),
(12, 100, '2025-04-04', 'Distribution', 1, NULL, 1, NULL),
(13, 100, '2025-04-04', 'Distribution', 1, NULL, 2, NULL),
(14, 200, '2025-04-05', 'Paiement Facture', NULL, NULL, 1, NULL),
(15, 200, '2025-04-05', 'Paiement Facture', NULL, NULL, 1, NULL),
(16, 1499, '2025-04-07', 'réservation', NULL, NULL, NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agent_departement`
--
ALTER TABLE `agent_departement`
  ADD PRIMARY KEY (`id_agentd`),
  ADD KEY `id_dep` (`id_dep`);

--
-- Indexes for table `agent_financier`
--
ALTER TABLE `agent_financier`
  ADD PRIMARY KEY (`id_agentf`);

--
-- Indexes for table `chambre`
--
ALTER TABLE `chambre`
  ADD PRIMARY KEY (`id_chambre`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_client`);

--
-- Indexes for table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id_comm`),
  ADD KEY `id_gestionnaire_stock` (`id_gestionnaire_stock`),
  ADD KEY `id_fournisseur` (`id_fournisseur`),
  ADD KEY `id_fact` (`id_fact`);

--
-- Indexes for table `demande`
--
ALTER TABLE `demande`
  ADD PRIMARY KEY (`id_dem`),
  ADD KEY `idRH` (`idRH`),
  ADD KEY `id_emp` (`id_emp`),
  ADD KEY `id_agentd` (`id_agentd`);

--
-- Indexes for table `departement`
--
ALTER TABLE `departement`
  ADD PRIMARY KEY (`id_dep`);

--
-- Indexes for table `employe`
--
ALTER TABLE `employe`
  ADD PRIMARY KEY (`id_emp`),
  ADD KEY `id_agentd` (`id_agentd`),
  ADD KEY `idRH` (`idRH`),
  ADD KEY `id_dep` (`id_dep`);

--
-- Indexes for table `facture`
--
ALTER TABLE `facture`
  ADD PRIMARY KEY (`id_fac`),
  ADD KEY `id_transaction` (`id_transaction`),
  ADD KEY `id_agent_departement` (`id_agent_departement`),
  ADD KEY `id_comm` (`id_comm`);

--
-- Indexes for table `fournisseur`
--
ALTER TABLE `fournisseur`
  ADD PRIMARY KEY (`id_fournisseur`);

--
-- Indexes for table `gestionnaire_stock`
--
ALTER TABLE `gestionnaire_stock`
  ADD PRIMARY KEY (`id_gestionnaire`);

--
-- Indexes for table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD PRIMARY KEY (`id_commande`,`id_produit`),
  ADD KEY `id_produit` (`id_produit`);

--
-- Indexes for table `paquet_restauration`
--
ALTER TABLE `paquet_restauration`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `id_fournisseur` (`id_fournisseur`),
  ADD KEY `id_gestionnaire` (`id_gestionnaire`);

--
-- Indexes for table `rapport`
--
ALTER TABLE `rapport`
  ADD PRIMARY KEY (`id_rapp`),
  ADD KEY `id_agentd` (`id_agentd`);

--
-- Indexes for table `recu`
--
ALTER TABLE `recu`
  ADD PRIMARY KEY (`id_recu`),
  ADD KEY `id_transaction` (`id_transaction`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id_reservation`),
  ADD KEY `id_client` (`id_client`),
  ADD KEY `id_chambre` (`id_chambre`);

--
-- Indexes for table `reservation_paquet_restauration`
--
ALTER TABLE `reservation_paquet_restauration`
  ADD PRIMARY KEY (`reservation_id`,`paquet_restauration_id`),
  ADD KEY `paquet_restauration_id` (`paquet_restauration_id`);

--
-- Indexes for table `reservation_service`
--
ALTER TABLE `reservation_service`
  ADD PRIMARY KEY (`id_reservation`,`id_service`),
  ADD KEY `id_service` (`id_service`);

--
-- Indexes for table `rh`
--
ALTER TABLE `rh`
  ADD PRIMARY KEY (`idRH`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id_service`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id_trans`),
  ADD KEY `id_agent_financier` (`id_agent_financier`),
  ADD KEY `id_emp` (`id_emp`),
  ADD KEY `id_agent_departement` (`id_agent_departement`),
  ADD KEY `id_reservation` (`id_reservation`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agent_departement`
--
ALTER TABLE `agent_departement`
  MODIFY `id_agentd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `agent_financier`
--
ALTER TABLE `agent_financier`
  MODIFY `id_agentf` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chambre`
--
ALTER TABLE `chambre`
  MODIFY `id_chambre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `id_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `commande`
--
ALTER TABLE `commande`
  MODIFY `id_comm` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `demande`
--
ALTER TABLE `demande`
  MODIFY `id_dem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `departement`
--
ALTER TABLE `departement`
  MODIFY `id_dep` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employe`
--
ALTER TABLE `employe`
  MODIFY `id_emp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `facture`
--
ALTER TABLE `facture`
  MODIFY `id_fac` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `fournisseur`
--
ALTER TABLE `fournisseur`
  MODIFY `id_fournisseur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `gestionnaire_stock`
--
ALTER TABLE `gestionnaire_stock`
  MODIFY `id_gestionnaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `paquet_restauration`
--
ALTER TABLE `paquet_restauration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `produit`
--
ALTER TABLE `produit`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rapport`
--
ALTER TABLE `rapport`
  MODIFY `id_rapp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `recu`
--
ALTER TABLE `recu`
  MODIFY `id_recu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id_reservation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rh`
--
ALTER TABLE `rh`
  MODIFY `idRH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `id_service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `id_trans` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agent_departement`
--
ALTER TABLE `agent_departement`
  ADD CONSTRAINT `agent_departement_ibfk_1` FOREIGN KEY (`id_dep`) REFERENCES `departement` (`id_dep`);

--
-- Constraints for table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`id_gestionnaire_stock`) REFERENCES `gestionnaire_stock` (`id_gestionnaire`),
  ADD CONSTRAINT `commande_ibfk_2` FOREIGN KEY (`id_fournisseur`) REFERENCES `fournisseur` (`id_fournisseur`),
  ADD CONSTRAINT `commande_ibfk_3` FOREIGN KEY (`id_fact`) REFERENCES `facture` (`id_fac`);

--
-- Constraints for table `demande`
--
ALTER TABLE `demande`
  ADD CONSTRAINT `demande_ibfk_1` FOREIGN KEY (`idRH`) REFERENCES `rh` (`idRH`),
  ADD CONSTRAINT `demande_ibfk_2` FOREIGN KEY (`id_emp`) REFERENCES `employe` (`id_emp`),
  ADD CONSTRAINT `demande_ibfk_3` FOREIGN KEY (`id_agentd`) REFERENCES `agent_departement` (`id_agentd`);

--
-- Constraints for table `employe`
--
ALTER TABLE `employe`
  ADD CONSTRAINT `employe_ibfk_1` FOREIGN KEY (`id_agentd`) REFERENCES `agent_departement` (`id_agentd`),
  ADD CONSTRAINT `employe_ibfk_2` FOREIGN KEY (`idRH`) REFERENCES `rh` (`idRH`),
  ADD CONSTRAINT `employe_ibfk_3` FOREIGN KEY (`id_dep`) REFERENCES `departement` (`id_dep`);

--
-- Constraints for table `facture`
--
ALTER TABLE `facture`
  ADD CONSTRAINT `facture_ibfk_1` FOREIGN KEY (`id_transaction`) REFERENCES `transaction` (`id_trans`),
  ADD CONSTRAINT `facture_ibfk_2` FOREIGN KEY (`id_agent_departement`) REFERENCES `agent_departement` (`id_agentd`),
  ADD CONSTRAINT `facture_ibfk_3` FOREIGN KEY (`id_comm`) REFERENCES `commande` (`id_comm`);

--
-- Constraints for table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD CONSTRAINT `ligne_commande_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id_comm`),
  ADD CONSTRAINT `ligne_commande_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produit` (`id_produit`);

--
-- Constraints for table `paquet_restauration`
--
ALTER TABLE `paquet_restauration`
  ADD CONSTRAINT `paquet_restauration_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service` (`id_service`);

--
-- Constraints for table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1` FOREIGN KEY (`id_fournisseur`) REFERENCES `fournisseur` (`id_fournisseur`),
  ADD CONSTRAINT `produit_ibfk_2` FOREIGN KEY (`id_gestionnaire`) REFERENCES `gestionnaire_stock` (`id_gestionnaire`);

--
-- Constraints for table `rapport`
--
ALTER TABLE `rapport`
  ADD CONSTRAINT `rapport_ibfk_1` FOREIGN KEY (`id_agentd`) REFERENCES `agent_departement` (`id_agentd`);

--
-- Constraints for table `recu`
--
ALTER TABLE `recu`
  ADD CONSTRAINT `recu_ibfk_1` FOREIGN KEY (`id_transaction`) REFERENCES `transaction` (`id_trans`);

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`),
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`id_chambre`) REFERENCES `chambre` (`id_chambre`);

--
-- Constraints for table `reservation_paquet_restauration`
--
ALTER TABLE `reservation_paquet_restauration`
  ADD CONSTRAINT `reservation_paquet_restauration_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id_reservation`),
  ADD CONSTRAINT `reservation_paquet_restauration_ibfk_2` FOREIGN KEY (`paquet_restauration_id`) REFERENCES `paquet_restauration` (`id`);

--
-- Constraints for table `reservation_service`
--
ALTER TABLE `reservation_service`
  ADD CONSTRAINT `reservation_service_ibfk_1` FOREIGN KEY (`id_reservation`) REFERENCES `reservation` (`id_reservation`),
  ADD CONSTRAINT `reservation_service_ibfk_2` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`id_agent_financier`) REFERENCES `agent_financier` (`id_agentf`),
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`id_emp`) REFERENCES `employe` (`id_emp`),
  ADD CONSTRAINT `transaction_ibfk_3` FOREIGN KEY (`id_agent_departement`) REFERENCES `agent_departement` (`id_agentd`),
  ADD CONSTRAINT `transaction_ibfk_4` FOREIGN KEY (`id_reservation`) REFERENCES `reservation` (`id_reservation`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
