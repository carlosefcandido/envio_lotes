-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 14/02/2025 às 14:21
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
-- Estrutura para tabela `banco`
--

DROP TABLE IF EXISTS `banco`;
CREATE TABLE IF NOT EXISTS `banco` (
  `id_banco` int NOT NULL AUTO_INCREMENT,
  `nome_banco` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id_banco`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimento`
--

DROP TABLE IF EXISTS `movimento`;
CREATE TABLE IF NOT EXISTS `movimento` (
  `id_movimento` int NOT NULL AUTO_INCREMENT,
  `id_tipo` int DEFAULT NULL,
  `lote` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `data_salvo` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `enviado` tinyint(1) DEFAULT '0',
  `id_banco` int DEFAULT NULL,
  PRIMARY KEY (`id_movimento`),
  KEY `id_tipo` (`id_tipo`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_banco` (`id_banco`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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
  `tipo_provisao` enum('Pagamento do Dia','DDA','Folha') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `data_folha` date DEFAULT NULL,
  `data_salvo` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_provisao`),
  KEY `id_banco` (`id_banco`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_pagamento`
--

DROP TABLE IF EXISTS `tipo_pagamento`;
CREATE TABLE IF NOT EXISTS `tipo_pagamento` (
  `id_tipo` int NOT NULL AUTO_INCREMENT,
  `nome_tipo` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id_tipo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `nivel_usuario` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `codigo_recuperacao` varchar(6) COLLATE utf8mb4_bin DEFAULT NULL,
  `expiracao_codigo` datetime DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
