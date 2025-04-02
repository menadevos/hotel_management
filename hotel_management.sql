-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2025 at 11:53 PM
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
-- Database: `hotel_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `agent_departement`
--

CREATE TABLE `agent_departement` (
  `id_agentd` int(11) NOT NULL,
  `nom_agentd` char(1) DEFAULT NULL,
  `prenom_agentd` char(1) DEFAULT NULL,
  `password_agentd` char(1) DEFAULT NULL,
  `email_agentd` char(1) DEFAULT NULL,
  `numCompteDep` int(11) DEFAULT NULL,
  `monnaieDep` float DEFAULT NULL,
  `id_dep` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_financier`
--

CREATE TABLE `agent_financier` (
  `id_agentf` int(11) NOT NULL,
  `nom_agentf` char(1) DEFAULT NULL,
  `prenom_agentf` char(1) DEFAULT NULL,
  `password_agentf` char(1) DEFAULT NULL,
  `email_agentf` char(1) DEFAULT NULL,
  `numCompteFinance` int(11) DEFAULT NULL,
  `monnaieFinance` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chambre`
--

CREATE TABLE `chambre` (
  `id_chambre` int(11) NOT NULL,
  `type_chambre` char(1) DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL,
  `statut` char(1) DEFAULT NULL,
  `tarif` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id_client` int(11) NOT NULL,
  `Tel` char(1) DEFAULT NULL,
  `prenom` char(1) DEFAULT NULL,
  `nom` char(1) DEFAULT NULL,
  `email` char(1) DEFAULT NULL,
  `password` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commande`
--

CREATE TABLE `commande` (
  `id_comm` int(11) NOT NULL,
  `etat` char(1) DEFAULT NULL,
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
  `statut_dem` char(1) DEFAULT NULL,
  `description_dem` char(1) DEFAULT NULL,
  `date_dem` date DEFAULT NULL,
  `type` char(1) DEFAULT NULL,
  `idRH` int(11) DEFAULT NULL,
  `id_emp` int(11) DEFAULT NULL,
  `id_agentd` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departement`
--

CREATE TABLE `departement` (
  `id_dep` int(11) NOT NULL,
  `nom_dep` char(1) DEFAULT NULL,
  `revenu_dep` double DEFAULT NULL,
  `depenses_dep` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employe`
--

CREATE TABLE `employe` (
  `id_emp` int(11) NOT NULL,
  `prenom_emp` char(1) DEFAULT NULL,
  `nom_emp` char(1) DEFAULT NULL,
  `salaire` double DEFAULT NULL,
  `tel` char(1) DEFAULT NULL,
  `cin` char(1) DEFAULT NULL,
  `poste` char(1) DEFAULT NULL,
  `email_emp` char(1) DEFAULT NULL,
  `numCompteEmp` int(11) DEFAULT NULL,
  `code` char(1) DEFAULT NULL,
  `dateEmbauche` date DEFAULT NULL,
  `id_agentd` int(11) DEFAULT NULL,
  `idRH` int(11) DEFAULT NULL,
  `id_dep` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facture`
--

CREATE TABLE `facture` (
  `id_fac` int(11) NOT NULL,
  `description` char(1) DEFAULT NULL,
  `montant` double DEFAULT NULL,
  `statut` char(1) DEFAULT NULL,
  `type` char(1) DEFAULT NULL,
  `id_transaction` int(11) DEFAULT NULL,
  `id_agent_financier` int(11) DEFAULT NULL,
  `id_agent_departement` int(11) DEFAULT NULL,
  `id_comm` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fournisseur`
--

CREATE TABLE `fournisseur` (
  `id_fournisseur` int(11) NOT NULL,
  `nom_fournisseur` char(1) DEFAULT NULL,
  `prenom_fournisseur` char(1) DEFAULT NULL,
  `adresse` char(1) DEFAULT NULL,
  `email` char(1) DEFAULT NULL,
  `teleF` char(1) DEFAULT NULL,
  `numCompte` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gestionnaire_stock`
--

CREATE TABLE `gestionnaire_stock` (
  `id_gestionnaire` int(11) NOT NULL,
  `nom_gestionnaire` char(1) DEFAULT NULL,
  `email_gestionnaire` char(1) DEFAULT NULL,
  `prenom_gestionnaire` char(1) DEFAULT NULL,
  `telephone` char(1) DEFAULT NULL,
  `numCompte` int(11) DEFAULT NULL,
  `type_stock` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gym`
--

CREATE TABLE `gym` (
  `prix` double DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ligne_commande`
--

CREATE TABLE `ligne_commande` (
  `id_commande` int(11) DEFAULT NULL,
  `id_produit` int(11) DEFAULT NULL,
  `qte_comm` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produit`
--

CREATE TABLE `produit` (
  `id_produit` int(11) NOT NULL,
  `nom_produit` char(1) DEFAULT NULL,
  `Description_produit` text DEFAULT NULL,
  `categorie_produit` char(1) DEFAULT NULL,
  `prix_produit` double DEFAULT NULL,
  `id_fournisseur` int(11) DEFAULT NULL,
  `id_stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rapport`
--

CREATE TABLE `rapport` (
  `id_rapp` int(11) NOT NULL,
  `date_rapp` date DEFAULT NULL,
  `description` char(1) DEFAULT NULL,
  `revenu_total` double DEFAULT NULL,
  `depenses_total` double DEFAULT NULL,
  `id_agentd` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recu`
--

CREATE TABLE `recu` (
  `id_recu` int(11) NOT NULL,
  `details` char(1) DEFAULT NULL,
  `type` char(1) DEFAULT NULL,
  `DateEmission` date DEFAULT NULL,
  `id_transaction` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `id_reservation` int(11) NOT NULL,
  `date_arrivee` date DEFAULT NULL,
  `date_depart` date DEFAULT NULL,
  `etat_reservation` char(1) DEFAULT NULL,
  `nbre_personnes` int(11) DEFAULT NULL,
  `id_client` int(11) DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL,
  `id_chambre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restauration`
--

CREATE TABLE `restauration` (
  `id_Paquet` int(11) NOT NULL,
  `nomPaquet` char(1) DEFAULT NULL,
  `prixPaquet` double DEFAULT NULL,
  `description` char(1) DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rh`
--

CREATE TABLE `rh` (
  `idRH` int(11) NOT NULL,
  `nomRH` char(1) DEFAULT NULL,
  `prenomRH` char(1) DEFAULT NULL,
  `emailRH` char(1) DEFAULT NULL,
  `motDePasse` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `id_service` int(11) NOT NULL,
  `nom_service` char(1) DEFAULT NULL,
  `description` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spa`
--

CREATE TABLE `spa` (
  `date` date DEFAULT NULL,
  `prix` double DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id_stock` int(11) NOT NULL,
  `qte_stock` int(11) DEFAULT NULL,
  `id_gestionnaire_stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `id_trans` int(11) NOT NULL,
  `montant_trans` double DEFAULT NULL,
  `date_trans` date DEFAULT NULL,
  `typeTrans` char(1) DEFAULT NULL,
  `id_reservation` int(11) DEFAULT NULL,
  `id_agent_financier` int(11) DEFAULT NULL,
  `id_emp` int(11) DEFAULT NULL,
  `id_agent_departement` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agent_departement`
--
ALTER TABLE `agent_departement`
  ADD PRIMARY KEY (`id_agentd`);

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
  ADD PRIMARY KEY (`id_comm`);

--
-- Indexes for table `demande`
--
ALTER TABLE `demande`
  ADD PRIMARY KEY (`id_dem`);

--
-- Indexes for table `departement`
--
ALTER TABLE `departement`
  ADD PRIMARY KEY (`id_dep`);

--
-- Indexes for table `employe`
--
ALTER TABLE `employe`
  ADD PRIMARY KEY (`id_emp`);

--
-- Indexes for table `facture`
--
ALTER TABLE `facture`
  ADD PRIMARY KEY (`id_fac`),
  ADD KEY `id_transaction` (`id_transaction`),
  ADD KEY `id_agent_financier` (`id_agent_financier`),
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
-- Indexes for table `gym`
--
ALTER TABLE `gym`
  ADD KEY `id_service` (`id_service`);

--
-- Indexes for table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id_produit`);

--
-- Indexes for table `rapport`
--
ALTER TABLE `rapport`
  ADD PRIMARY KEY (`id_rapp`);

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
  ADD KEY `id_service` (`id_service`),
  ADD KEY `id_chambre` (`id_chambre`);

--
-- Indexes for table `restauration`
--
ALTER TABLE `restauration`
  ADD PRIMARY KEY (`id_Paquet`),
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
-- Indexes for table `spa`
--
ALTER TABLE `spa`
  ADD KEY `id_service` (`id_service`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id_stock`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id_trans`),
  ADD KEY `id_reservation` (`id_reservation`),
  ADD KEY `id_agent_financier` (`id_agent_financier`),
  ADD KEY `id_emp` (`id_emp`),
  ADD KEY `id_agent_departement` (`id_agent_departement`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `facture`
--
ALTER TABLE `facture`
  ADD CONSTRAINT `facture_ibfk_1` FOREIGN KEY (`id_transaction`) REFERENCES `transaction` (`id_trans`),
  ADD CONSTRAINT `facture_ibfk_2` FOREIGN KEY (`id_agent_financier`) REFERENCES `agent_financier` (`id_agentf`),
  ADD CONSTRAINT `facture_ibfk_3` FOREIGN KEY (`id_agent_departement`) REFERENCES `agent_departement` (`id_agentd`),
  ADD CONSTRAINT `facture_ibfk_4` FOREIGN KEY (`id_comm`) REFERENCES `commande` (`id_comm`);

--
-- Constraints for table `gym`
--
ALTER TABLE `gym`
  ADD CONSTRAINT `gym_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`);

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
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`),
  ADD CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`id_chambre`) REFERENCES `chambre` (`id_chambre`);

--
-- Constraints for table `restauration`
--
ALTER TABLE `restauration`
  ADD CONSTRAINT `restauration_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`);

--
-- Constraints for table `spa`
--
ALTER TABLE `spa`
  ADD CONSTRAINT `spa_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`id_reservation`) REFERENCES `reservation` (`id_reservation`),
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`id_agent_financier`) REFERENCES `agent_financier` (`id_agentf`),
  ADD CONSTRAINT `transaction_ibfk_3` FOREIGN KEY (`id_emp`) REFERENCES `employe` (`id_emp`),
  ADD CONSTRAINT `transaction_ibfk_4` FOREIGN KEY (`id_agent_departement`) REFERENCES `agent_departement` (`id_agentd`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
