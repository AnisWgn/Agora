-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20251104.8b43d270dd
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 07, 2025 at 01:30 PM
-- Server version: 9.4.0
-- PHP Version: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agora`
--

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE `genre` (
  `idGenre` int NOT NULL,
  `libGenre` varchar(24) NOT NULL,
  `idSpecialiste` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`idGenre`, `libGenre`, `idSpecialiste`) VALUES
(1, 'Action', 2),
(2, 'Aventureffff', 3),
(3, 'Combat', 1),
(4, 'Course', NULL),
(5, 'Gestion', NULL),
(6, 'Jeu de rôle', NULL),
(7, 'Ligue fantasy', NULL),
(8, 'Réflexion', NULL),
(9, 'Tactique', NULL),
(10, 'Sport', NULL),
(11, 'Simulation', NULL),
(13, 'Stratégie', NULL),
(14, 'Porte-monstre-trésor', NULL),
(24, 'Labyrinthe', 5),
(26, 'Objets cachés', 2),
(31, 'dddqdqs', 2),
(32, 'dddd', 1);

-- --------------------------------------------------------

--
-- Table structure for table `jeu_video`
--

CREATE TABLE `jeu_video` (
  `refJeu` varchar(16) NOT NULL,
  `idPlateforme` int DEFAULT NULL,
  `idPegi` int DEFAULT NULL,
  `idGenre` int DEFAULT NULL,
  `idMarque` int DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prix` decimal(6,2) NOT NULL,
  `dateParution` date NOT NULL DEFAULT '2018-03-16'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `jeu_video`
--

INSERT INTO `jeu_video` (`refJeu`, `idPlateforme`, `idPegi`, `idGenre`, `idMarque`, `nom`, `prix`, `dateParution`) VALUES
('BF8763098765', 2, 3, 10, 2, 'FIFA 18 - Edition essentielles', 59.99, '2017-09-29'),
('CF47563837YG', 3, 1, 13, 8, 'Paddington : escapades à Londres', 18.30, '2015-06-19'),
('EG763547598RF', 3, 2, 6, 13, 'Pokémon X', 39.90, '2013-10-12'),
('ER493746Y78', 8, 5, 1, 3, 'Rise of the Tomb Raider', 19.90, '2015-11-13'),
('ER6753FG987', 3, 3, 2, 1, 'Minecraft Story Mode - L\'aventure Complète -', 39.89, '2016-12-16'),
('ES47562098754', 4, 2, 2, 13, 'The Legend of Zelda - The Wind Waker HD ', 29.80, '2016-04-15'),
('ET86987453T5', 7, 5, 1, 10, 'La terre de milieu : L\'Ombre de la Guerre', 59.90, '2017-10-10'),
('ezfezf', 9, 1, 4, 5, 'fsefef', 343.00, '2025-11-05'),
('fseffezfe', 9, 1, 1, 5, 'fergre', 34.00, '2025-10-28'),
('RT4958673II2', 4, 2, 2, 13, 'New Super Mario Bros.', 18.90, '2016-04-15'),
('TF98653JU8', 15, 3, 2, 1, 'Minecraft Story Mode - L\'aventure Complète -', 39.89, '2016-12-16'),
('U174645475GT', 2, 3, 10, 1, 'Gran Turismo', 21.50, '2013-06-12'),
('YT65487BJI', 3, 1, 2, 13, 'Mario Kart 7 ', 39.90, '2012-11-28');

-- --------------------------------------------------------

--
-- Table structure for table `marque`
--

CREATE TABLE `marque` (
  `idMarque` int NOT NULL,
  `nomMarque` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Les marques des produits';

--
-- Dumping data for table `marque`
--

INSERT INTO `marque` (`idMarque`, `nomMarque`) VALUES
(1, 'Sony'),
(2, 'Electronic arts'),
(3, 'Square Enix'),
(4, 'Konami'),
(5, 'Bandai Namco Entertainment'),
(6, 'Rockstar Games'),
(7, 'Séga'),
(8, 'Techland'),
(9, 'Ubisoft'),
(10, 'Warner Bros'),
(11, 'Bensimon'),
(12, 'Hori'),
(13, 'Nintendo'),
(15, 'Kid\'s Mania');

-- --------------------------------------------------------

--
-- Table structure for table `membre`
--

CREATE TABLE `membre` (
  `idMembre` int NOT NULL,
  `nomMembre` varchar(32) NOT NULL,
  `prenomMembre` varchar(20) NOT NULL,
  `mailMembre` varchar(50) NOT NULL,
  `telMembre` varchar(10) NOT NULL,
  `rueMembre` varchar(36) NOT NULL,
  `cpMembre` varchar(5) NOT NULL,
  `villeMembre` varchar(30) NOT NULL,
  `loginMembre` varchar(20) NOT NULL,
  `mdpMembre` char(128) NOT NULL,
  `selMembre` char(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `membre`
--

INSERT INTO `membre` (`idMembre`, `nomMembre`, `prenomMembre`, `mailMembre`, `telMembre`, `rueMembre`, `cpMembre`, `villeMembre`, `loginMembre`, `mdpMembre`, `selMembre`) VALUES
(1, 'Dubois', 'Didier', 'dubois.didier@gmail.com', '0685451236', '48 rue des acacias', '57000', 'Metz', 'dubois', '7e3c5f890206b7cad6a01f51ba98db1713f37219bdf7668d35c6f71e3bf8f0ee73d3cce3b380234fd39628d57c33ff5baf7e7a258fdfcf85efaf32a5be77a87a', '9943e30b1efe68df39ac3734fa014bdd51cfc2f8eb93df70a04cfa070f19a9c9f71fc588161227ed76ef0a79a1ea6003f2cefe511fde90a1eb210824bb7d4f4d'),
(2, 'Celon', 'Elodie', 'elodie35@gmail.com', '0689451235', '18 rue des Tilleuls', '57000', 'Metz', 'celon', 'f332c43c4aed2f69497df8f52ce3ed9483737eb689fd86614a267a034bc0c2fc4bc1677b96767f286499bba623b87a8993d2825af203df34573f9435222222d1', '8e78f28b94066853c4f13017fa561e233b3ff48dc28455c61acf3590f7897deb82514ba38a51fe57b89bd220ebbb9bcf72af287463ae9395aa7d2e52121b745c'),
(3, 'Garance', 'Kevin', 'garance@gmail.com', '0678451236', '5 avenue Victor hugo', '57000', 'Metz', 'garance', '2ac84572232ba491a89635b19afb37217dbd8c9e5d79080981d6fc283f13addfed0607a006090fcce0fa21dcf18f770e6138532732c0449e1b7a5d9fa478b6b7', 'f53d9e6fe21b24ee613f5cb5303b6b0dab1619906e42721be3c004bcbe7f08f95977a4da1753a4b439402693cb76d79e068459b24406939bbb6b24bf4d6bff88'),
(4, 'Wagner', 'Anis', 'aniswagner6@gmail.com', '0749285128', 'non', '54000', 'Nancy', 'anis', 'Anis@171206', ''),
(5, 'Nom', 'Prénom', 'mail@example.com', '0123456789', 'rue', '00000', 'Ville', 'nouveauLogin', '72905e7b32d847468edcdbf99f7d218e466cd828300306f1d9f8c3e0512e44fe4394644b581ed52656a2870c9a67c592bc40ca322099aa52bf528c54f9cabde0', '');

-- --------------------------------------------------------

--
-- Table structure for table `pegi`
--

CREATE TABLE `pegi` (
  `idPegi` int NOT NULL,
  `ageLimite` int NOT NULL,
  `descPegi` varchar(400) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `pegi`
--

INSERT INTO `pegi` (`idPegi`, `ageLimite`, `descPegi`) VALUES
(1, 3, '« adapté à toutes les classes d’âge ». En effet, il ne comporte pas de sons ou d’images susceptibles d’effrayer ou de faire peur à de jeunes enfants. Les formes de violence très modérées dans un contexte comique ou enfantin sont toutefois acceptées, mais le langage grossier n\'est pas autoris.'),
(2, 7, 'Déconseillé aux moins de 7 ans. Il contient des scènes ou sons potentiellement effrayants. La violence très modérée (c\'est-à-dire implicite, non détaillée ou non réaliste) est accepté.'),
(3, 12, 'Déconseillé aux moins de 12 ans. Il peut montrer « de la violence sous une forme plus graphique par rapport à des personnages imaginaires et/ou une violence non graphique envers des personnages à figure humaine ». Il peut également présenter des insinuations à caractère sexuel ou des postures de type sexuelles dans un cadre léger. Enfin, il peut aussi proposer des jeux de hasard.'),
(4, 16, 'Déconseillé aux moins de 16 ans. Contenus possibles : la violence et/ou la sexualité sont représentés de manière semblable à ce que l\'on pourrait retrouver dans la réalité. Le jeu peut ainsi contenir de la violence explicite, un mauvais langage, des références ou contenus à caractères sexuels, mais aussi des jeux de hasard ou l\'utilisation d\'alcool, tabac et drogue (forme d\'incitation).'),
(5, 18, '« destinée aux adultes ». Il peut contenir un degré de violence extrême avec une représentation de violence crue, de meurtre sans motivation, de violence contre des personnages sans défense ou de la discrimination. Il peut aussi glorifier la prise des drogues illégales et les contacts sexuels explicites ainsi que des jeux de hasard.');

-- --------------------------------------------------------

--
-- Table structure for table `plateforme`
--

CREATE TABLE `plateforme` (
  `idPlateforme` int NOT NULL,
  `libPlateforme` varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `plateforme`
--

INSERT INTO `plateforme` (`idPlateforme`, `libPlateforme`) VALUES
(1, 'PlayStation 4'),
(2, 'PlayStation 3'),
(3, 'Nintendo 3DS'),
(4, 'Nintendo Wii'),
(5, 'PC ggg'),
(6, 'Sony PSP'),
(7, 'Xbox 360'),
(8, 'Xbox One'),
(9, 'Nintendo 2DS'),
(11, 'Nintendo DS'),
(13, 'Nintendo Switch'),
(15, 'Nintendo Wii U'),
(17, 'PlayStation Vita');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `genre`
--
ALTER TABLE `genre`
  ADD PRIMARY KEY (`idGenre`);

--
-- Indexes for table `jeu_video`
--
ALTER TABLE `jeu_video`
  ADD PRIMARY KEY (`refJeu`),
  ADD KEY `fk_jeu_video_genre` (`idGenre`) USING BTREE,
  ADD KEY `fk_jeu_video_pegi` (`idPegi`) USING BTREE,
  ADD KEY `fk_jeu_video_marque` (`idMarque`) USING BTREE,
  ADD KEY `fk_jeu_video_plateforme` (`idPlateforme`);

--
-- Indexes for table `marque`
--
ALTER TABLE `marque`
  ADD PRIMARY KEY (`idMarque`);

--
-- Indexes for table `membre`
--
ALTER TABLE `membre`
  ADD PRIMARY KEY (`idMembre`);

--
-- Indexes for table `pegi`
--
ALTER TABLE `pegi`
  ADD PRIMARY KEY (`idPegi`);

--
-- Indexes for table `plateforme`
--
ALTER TABLE `plateforme`
  ADD PRIMARY KEY (`idPlateforme`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `genre`
--
ALTER TABLE `genre`
  MODIFY `idGenre` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `marque`
--
ALTER TABLE `marque`
  MODIFY `idMarque` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `membre`
--
ALTER TABLE `membre`
  MODIFY `idMembre` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pegi`
--
ALTER TABLE `pegi`
  MODIFY `idPegi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `plateforme`
--
ALTER TABLE `plateforme`
  MODIFY `idPlateforme` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
