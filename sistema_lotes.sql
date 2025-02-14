-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 14/02/2025 às 14:42
-- Versão do servidor: 9.1.0
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_lotes`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `provisao`
--

DROP TABLE IF EXISTS `provisao`;
CREATE TABLE IF NOT EXISTS `provisao` (
  `id_provisao` int NOT NULL AUTO_INCREMENT,
  `id_banco` int NOT NULL,
  `valor` decimal(65,2) NOT NULL,
  `id_usuario` int NOT NULL,
  `id_tipo_provisao` int NOT NULL,
  `data_folha` date DEFAULT NULL,
  `data_salvo` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_provisao`),
  KEY `id_banco` (`id_banco`),
  KEY `id_usuario` (`id_usuario`),
  KEY `fk_tipo_provisao` (`id_tipo_provisao`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_provisao`
--

DROP TABLE IF EXISTS `tipo_provisao`;
CREATE TABLE IF NOT EXISTS `tipo_provisao` (
  `id_tipo_provisao` int NOT NULL AUTO_INCREMENT,
  `nome_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id_tipo_provisao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
