-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : jeu. 22 avr. 2021 à 05:52
-- Version du serveur :  10.3.16-MariaDB
-- Version de PHP : 7.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `id16407667_vroumbdd`
--
CREATE DATABASE IF NOT EXISTS `id16407667_vroumbdd` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `id16407667_vroumbdd`;

-- --------------------------------------------------------

--
-- Structure de la table `appartientAListe`
--

CREATE TABLE `appartientAListe` (
  `idListe` int(11) NOT NULL,
  `idUser` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `InterCity`
--

CREATE TABLE `InterCity` (
  `idTrip` int(11) NOT NULL,
  `city` varchar(150) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ListeUtilisateur`
--

CREATE TABLE `ListeUtilisateur` (
  `idListe` int(11) NOT NULL,
  `nom` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `createur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Note`
--

CREATE TABLE `Note` (
  `idNote` int(11) NOT NULL,
  `note` int(11) NOT NULL,
  `notant` int(11) NOT NULL,
  `idTrajet` int(11) NOT NULL,
  `notation` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Notification`
--

CREATE TABLE `Notification` (
  `idNotif` int(11) NOT NULL,
  `destinataire` int(11) NOT NULL,
  `expediteur` int(11) NOT NULL,
  `trajet` int(11) NOT NULL,
  `lue` tinyint(1) NOT NULL DEFAULT 0,
  `type` enum('ASK_PARTICIPATION_TRIP','DELETE_TRIP_OFFER','ACCEPT_PARTICIPATION_TRIP','REFUSE_PARTICIPATION_TRIP','DELETE_PARTICIPATION_TRIP','CREATE_PRIVATE_USER_LIST') COLLATE utf8_unicode_ci NOT NULL,
  `texte` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participeATrajet`
--

CREATE TABLE `participeATrajet` (
  `participant` int(11) NOT NULL,
  `idTrajet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Trajet`
--

CREATE TABLE `Trajet` (
  `idTrajet` int(11) NOT NULL,
  `dateD` datetime DEFAULT NULL,
  `dateA` datetime DEFAULT NULL,
  `conducteur` int(100) DEFAULT NULL,
  `villeD` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `villeA` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prix` int(11) DEFAULT NULL,
  `cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `placeMax` int(3) DEFAULT NULL,
  `precisionsRDV` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `precisionsContraintes` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `listePrivee` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `User`
--

CREATE TABLE `User` (
  `idUser` int(11) NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `pwd_hash` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prenom` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sexe` tinyint(1) DEFAULT NULL,
  `voiture` tinyint(1) DEFAULT NULL,
  `tel` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `photo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recoit_email` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `appartientAListe`
--
ALTER TABLE `appartientAListe`
  ADD PRIMARY KEY (`idListe`,`idUser`),
  ADD KEY `idUser` (`idUser`);

--
-- Index pour la table `InterCity`
--
ALTER TABLE `InterCity`
  ADD PRIMARY KEY (`idTrip`,`city`);

--
-- Index pour la table `ListeUtilisateur`
--
ALTER TABLE `ListeUtilisateur`
  ADD PRIMARY KEY (`idListe`),
  ADD KEY `createur` (`createur`);

--
-- Index pour la table `Note`
--
ALTER TABLE `Note`
  ADD PRIMARY KEY (`idNote`),
  ADD KEY `notant` (`notant`),
  ADD KEY `trajet` (`idTrajet`),
  ADD KEY `note` (`note`) USING BTREE;

--
-- Index pour la table `Notification`
--
ALTER TABLE `Notification`
  ADD PRIMARY KEY (`idNotif`),
  ADD KEY `destinataire` (`destinataire`),
  ADD KEY `expéditeur` (`expediteur`),
  ADD KEY `trajet` (`trajet`);

--
-- Index pour la table `participeATrajet`
--
ALTER TABLE `participeATrajet`
  ADD PRIMARY KEY (`idTrajet`,`participant`),
  ADD KEY `participeATrajet_ibfk_1` (`participant`);

--
-- Index pour la table `Trajet`
--
ALTER TABLE `Trajet`
  ADD PRIMARY KEY (`idTrajet`),
  ADD KEY `conducteur` (`conducteur`),
  ADD KEY `listePrivee` (`listePrivee`);

--
-- Index pour la table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`idUser`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ListeUtilisateur`
--
ALTER TABLE `ListeUtilisateur`
  MODIFY `idListe` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Note`
--
ALTER TABLE `Note`
  MODIFY `idNote` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Notification`
--
ALTER TABLE `Notification`
  MODIFY `idNotif` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Trajet`
--
ALTER TABLE `Trajet`
  MODIFY `idTrajet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `User`
--
ALTER TABLE `User`
  MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `appartientAListe`
--
ALTER TABLE `appartientAListe`
  ADD CONSTRAINT `appartientAListe_ibfk_1` FOREIGN KEY (`idListe`) REFERENCES `ListeUtilisateur` (`idListe`),
  ADD CONSTRAINT `appartientAListe_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `User` (`idUser`);

--
-- Contraintes pour la table `InterCity`
--
ALTER TABLE `InterCity`
  ADD CONSTRAINT `InterCity_ibfk_1` FOREIGN KEY (`idTrip`) REFERENCES `Trajet` (`idTrajet`);

--
-- Contraintes pour la table `ListeUtilisateur`
--
ALTER TABLE `ListeUtilisateur`
  ADD CONSTRAINT `ListeUtilisateur_ibfk_1` FOREIGN KEY (`createur`) REFERENCES `User` (`idUser`);

--
-- Contraintes pour la table `Note`
--
ALTER TABLE `Note`
  ADD CONSTRAINT `Note_ibfk_1` FOREIGN KEY (`note`) REFERENCES `User` (`idUser`),
  ADD CONSTRAINT `Note_ibfk_2` FOREIGN KEY (`notant`) REFERENCES `User` (`idUser`),
  ADD CONSTRAINT `Note_ibfk_3` FOREIGN KEY (`idTrajet`) REFERENCES `Trajet` (`idTrajet`);

--
-- Contraintes pour la table `Notification`
--
ALTER TABLE `Notification`
  ADD CONSTRAINT `Notification_ibfk_1` FOREIGN KEY (`destinataire`) REFERENCES `User` (`idUser`),
  ADD CONSTRAINT `Notification_ibfk_2` FOREIGN KEY (`expediteur`) REFERENCES `User` (`idUser`),
  ADD CONSTRAINT `Notification_ibfk_3` FOREIGN KEY (`trajet`) REFERENCES `Trajet` (`idTrajet`);

--
-- Contraintes pour la table `participeATrajet`
--
ALTER TABLE `participeATrajet`
  ADD CONSTRAINT `participeATrajet_ibfk_1` FOREIGN KEY (`participant`) REFERENCES `User` (`idUser`),
  ADD CONSTRAINT `participeATrajet_ibfk_2` FOREIGN KEY (`idTrajet`) REFERENCES `Trajet` (`idTrajet`);

--
-- Contraintes pour la table `Trajet`
--
ALTER TABLE `Trajet`
  ADD CONSTRAINT `Trajet_ibfk_1` FOREIGN KEY (`conducteur`) REFERENCES `User` (`idUser`),
  ADD CONSTRAINT `Trajet_ibfk_2` FOREIGN KEY (`listePrivee`) REFERENCES `ListeUtilisateur` (`idListe`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
