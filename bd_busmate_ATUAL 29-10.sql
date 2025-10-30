-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 30/10/2025 às 01:55
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `bd_busmate`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `aviso`
--

CREATE TABLE `aviso` (
  `avis_id` int(11) NOT NULL,
  `avis_titulo` varchar(120) DEFAULT NULL,
  `avis_texto` text NOT NULL,
  `usua_id` int(11) NOT NULL,
  `avis_situacao` enum('ATIVO','ARQUIVADO') NOT NULL DEFAULT 'ATIVO',
  `avis_publica_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `avis_expira_em` timestamp NULL DEFAULT NULL,
  `avis_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aviso_leitura`
--

CREATE TABLE `aviso_leitura` (
  `avle_id` int(11) NOT NULL,
  `avis_id` int(11) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `avle_leitura` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contrato`
--

CREATE TABLE `contrato` (
  `cont_id` int(11) NOT NULL,
  `empr_id` int(11) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `cont_situacao` enum('RASCUNHO','ATIVO','ENCERRADO','CANCELADO') NOT NULL DEFAULT 'RASCUNHO',
  `cont_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `cont_dth_assinatura` timestamp NULL DEFAULT NULL,
  `cont_inicio_vigencia` date DEFAULT NULL,
  `cont_fim_vigencia` date DEFAULT NULL,
  `cont_valor_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `documento`
--

CREATE TABLE `documento` (
  `docu_id` int(11) NOT NULL,
  `docu_tipo` varchar(45) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `docu_numero` varchar(60) DEFAULT NULL,
  `docu_url` varchar(255) DEFAULT NULL,
  `docu_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `email`
--

CREATE TABLE `email` (
  `emai_id` int(11) NOT NULL,
  `emai_endereco` varchar(200) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `is_principal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `email`
--

INSERT INTO `email` (`emai_id`, `emai_endereco`, `usua_id`, `is_principal`, `created_at`) VALUES
(1, 'ana.mellany@busmate.com', 1, 1, '2025-10-28 04:50:48'),
(2, 'juliany.gabriela@busmate.com', 2, 1, '2025-10-28 04:50:48'),
(3, 'marcos.vinicius@busmate.com', 3, 1, '2025-10-28 04:50:48'),
(4, 'maria.vitoria@busmate.com', 4, 1, '2025-10-28 04:50:48'),
(5, 'nicolle.matias@busmate.com', 5, 1, '2025-10-28 04:50:48'),
(6, 'vitoria.alvis@busmate.com', 6, 1, '2025-10-28 04:50:48'),
(7, 'blabla@gmail.com', 31, 1, '2025-10-29 16:37:40'),
(8, 'carlinhosmaia@gmail.com', 25, 1, '2025-10-29 18:18:30'),
(9, 'virginia@gmail.com', 26, 1, '2025-10-29 18:19:05'),
(10, 'joao@gmail.com', 27, 1, '2025-10-29 18:48:42'),
(11, 'enaldinho@gmail.com', 28, 1, '2025-10-29 18:49:59'),
(12, 'nsei@gmail.com', 29, 1, '2025-10-29 19:10:36'),
(13, 'cucabeludinho@gmail.com', 30, 1, '2025-10-29 19:17:28'),
(14, 'baca@gmail.com', 32, 1, '2025-10-29 17:04:55'),
(15, 'sas@gmail.com', 33, 1, '2025-10-30 00:10:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresa`
--

CREATE TABLE `empresa` (
  `empr_id` int(11) NOT NULL,
  `empr_razao` varchar(200) NOT NULL,
  `empr_cnpj` char(25) NOT NULL,
  `empr_logo` varchar(255) DEFAULT NULL,
  `empr_criado_por` int(11) DEFAULT NULL,
  `empr_qtd_admin` int(11) NOT NULL DEFAULT 1,
  `empr_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `empr_nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`empr_id`, `empr_razao`, `empr_cnpj`, `empr_logo`, `empr_criado_por`, `empr_qtd_admin`, `empr_dth_criacao`, `empr_nome`) VALUES
(1, 'Juma Transportes Filial LTDA', '12.111.111/1111-11', 'public/uploads/empresas_logo/1761747478_98b4fcbf09e3.png', 1, 2, '2025-10-21 18:17:58', 'Juma Transportes Filial'),
(2, 'Juma Transportes LTDA', '00000000000000', 'public/uploads/empresas_logo/1761747478_98b4fcbf09e3.png', 1, 2, '2025-10-21 18:17:58', 'Juma Transportes'),
(3, 'Amatur LTDA', '11.111.111/1111-11', 'public/uploads/empresas_logo/1761749268_d5b3a7693e06.png', 2, 2, '2025-10-22 18:47:48', 'Amatur'),
(4, 'PussyBus LTDA', '55.555.555/5555-55', 'public/uploads/empresas_logo/1761750895_609b1aaf2039.jpg', 3, 1, '2025-10-23 19:14:55', 'PussyBus'),
(5, 'FuckItAll LTDA', '66.666.666/6666-66', 'public/uploads/empresas_logo/1761751026_ec7a2b00167e.jpg', 4, 1, '2025-10-24 19:17:06', 'FuckItAll'),
(6, 'IDKBus LTDA', '77.777.777/7777-77', 'public/uploads/empresas_logo/1761751026_ec7a2b00167e.jpg', 5, 1, '2025-10-25 19:17:06', 'IDKBUS'),
(7, 'DickBus LTDA', '88.888.888/8888-88', 'public/uploads/empresas_logo/1761751026_ec7a2b00167e.jpg', 6, 1, '2025-10-26 19:17:06', 'DickBus'),
(8, 'a', '78.787.878/7878-78', 'public/uploads/empresas_logo/1761783017_b612932b5cef.png', 6, 1, '2025-10-30 00:10:17', 'aa');

-- --------------------------------------------------------

--
-- Estrutura para tabela `forum`
--

CREATE TABLE `forum` (
  `foru_id` int(11) NOT NULL,
  `foru_titulo` varchar(100) NOT NULL,
  `foru_texto` text NOT NULL,
  `foru_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `foru_dth_atualizacao` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `foru_situacao` enum('ABERTO','FECHADO','ARQUIVADO') NOT NULL DEFAULT 'ABERTO',
  `usua_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `forum_resposta`
--

CREATE TABLE `forum_resposta` (
  `fore_id` int(11) NOT NULL,
  `fore_texto` text NOT NULL,
  `fore_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `fore_dth_atualizacao` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `foru_id` int(11) NOT NULL,
  `usua_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `manutencao`
--

CREATE TABLE `manutencao` (
  `manu_id` int(11) NOT NULL,
  `manu_data` date NOT NULL,
  `onib_id` int(11) NOT NULL,
  `manu_descricao` varchar(200) DEFAULT NULL,
  `manu_valor` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `motorista`
--

CREATE TABLE `motorista` (
  `moto_id` int(11) NOT NULL,
  `moto_nome` varchar(120) NOT NULL,
  `moto_cpf` char(11) NOT NULL,
  `moto_cnh` varchar(20) NOT NULL,
  `moto_telefone` varchar(20) DEFAULT NULL,
  `moto_situacao` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `onibus`
--

CREATE TABLE `onibus` (
  `onib_id` int(11) NOT NULL,
  `onib_placa` varchar(10) NOT NULL,
  `empr_id` int(11) NOT NULL,
  `onib_capacidade` int(11) NOT NULL DEFAULT 40,
  `onib_modelo` varchar(80) DEFAULT NULL,
  `onib_situacao` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento`
--

CREATE TABLE `pagamento` (
  `paga_id` int(11) NOT NULL,
  `paga_valor` decimal(10,2) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `paga_competencia` char(7) DEFAULT NULL,
  `paga_metodo` enum('PIX','CARTAO','BOLETO') DEFAULT 'PIX',
  `paga_situacao` enum('PENDENTE','PAGO','FALHOU','ESTORNADO') NOT NULL DEFAULT 'PENDENTE',
  `paga_dth` timestamp NULL DEFAULT NULL,
  `paga_comprovante_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ponto`
--

CREATE TABLE `ponto` (
  `pont_id` int(11) NOT NULL,
  `pont_local` varchar(100) NOT NULL,
  `viag_id` int(11) NOT NULL,
  `pont_ordem` int(11) DEFAULT NULL,
  `pont_latitude` decimal(9,6) DEFAULT NULL,
  `pont_longitude` decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao_aluno`
--

CREATE TABLE `solicitacao_aluno` (
  `soli_id` int(11) NOT NULL,
  `soli_nome` varchar(100) NOT NULL,
  `soli_matricula` varchar(100) DEFAULT NULL,
  `soli_email` varchar(200) NOT NULL,
  `soli_telefone` varchar(20) DEFAULT NULL,
  `soli_escola` varchar(100) DEFAULT NULL,
  `soli_turno` varchar(50) DEFAULT NULL,
  `soli_endereco` varchar(200) DEFAULT NULL,
  `soli_curso` varchar(100) DEFAULT NULL,
  `soli_obs` varchar(300) DEFAULT NULL,
  `empr_id` int(11) NOT NULL,
  `status` enum('PENDENTE','ACEITA','RECUSADA') NOT NULL DEFAULT 'PENDENTE',
  `motivo_recusa` varchar(300) DEFAULT NULL,
  `soli_token` char(36) NOT NULL,
  `soli_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `soli_dth_decisao` timestamp NULL DEFAULT NULL,
  `soli_foto_url` varchar(255) DEFAULT NULL,
  `soli_comprovante_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `solicitacao_aluno`
--

INSERT INTO `solicitacao_aluno` (`soli_id`, `soli_nome`, `soli_matricula`, `soli_email`, `soli_telefone`, `soli_escola`, `soli_turno`, `soli_endereco`, `soli_curso`, `soli_obs`, `empr_id`, `status`, `motivo_recusa`, `soli_token`, `soli_dth_criacao`, `soli_dth_decisao`, `soli_foto_url`, `soli_comprovante_url`) VALUES
(1, 'a', 'a', 'aluno@gmail.com', '(88) 00000-0000', 'IFRO - Campus Guajrá-Mirim', 'Tarde', 'Av. Raimundo brasileiro', 'a', NULL, 2, 'PENDENTE', NULL, '14a74759-c732-4fa5-ac54-e13f6e596775', '2025-10-29 23:52:15', NULL, 'public/uploads/solicitacoes_tmp/f262e45f-808c-4a2e-939f-57c9fe1455e4/1761781915_41779287.jpg', 'public/uploads/solicitacoes_tmp/f262e45f-808c-4a2e-939f-57c9fe1455e4/1761781915_e514d0c7.jpg'),
(2, 'b', 'dada', 'b@gmail.com', '(99) 00000-0000', 'IFRO - Campus Guajrá-Mirim', 'Tarde', 'Av. Raimundo brasileiro', 'adasda', NULL, 4, 'PENDENTE', NULL, 'a3ba0a26-4845-40cd-94d0-c348bc5bd00d', '2025-10-29 23:54:45', NULL, 'public/uploads/solicitacoes_tmp/e71af1a4-23ce-406c-83b8-ba25fefe3f15/1761782071_42a64e44.jpg', 'public/uploads/solicitacoes_tmp/e71af1a4-23ce-406c-83b8-ba25fefe3f15/1761782071_12878fa6.jpg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `telefone`
--

CREATE TABLE `telefone` (
  `tele_id` int(11) NOT NULL,
  `tele_numero` varchar(20) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `tipo` enum('CEL','FIXO','COM') DEFAULT 'CEL',
  `is_principal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `telefone`
--

INSERT INTO `telefone` (`tele_id`, `tele_numero`, `usua_id`, `tipo`, `is_principal`, `created_at`) VALUES
(7, '(77) 00000-0000', 31, 'CEL', 1, '2025-10-29 16:37:40'),
(8, '(11) 00000-0000', 25, 'CEL', 1, '2025-10-29 18:18:30'),
(9, '(22) 00000-0000', 26, 'CEL', 1, '2025-10-29 18:19:05'),
(10, '(33) 00000-0000', 27, 'CEL', 1, '2025-10-29 18:48:42'),
(11, '(44) 00000-0000', 28, 'CEL', 1, '2025-10-29 18:49:59'),
(12, '(55) 00000-0000', 29, 'CEL', 1, '2025-10-29 19:10:36'),
(13, '(66) 00000-0000', 30, 'CEL', 1, '2025-10-29 19:17:28'),
(14, '(99) 99999-9999', 32, 'CEL', 1, '2025-10-29 17:04:55'),
(15, '(74) 12589-6325', 33, 'CEL', 1, '2025-10-30 00:10:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `tius_id` int(11) NOT NULL,
  `tius_descricao` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`tius_id`, `tius_descricao`) VALUES
(2, 'ADMINISTRADOR'),
(3, 'ALUNO'),
(1, 'ROOT');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `usua_id` int(11) NOT NULL,
  `usua_nome` varchar(100) NOT NULL,
  `usua_turma` varchar(100) DEFAULT NULL,
  `usua_curso` varchar(100) DEFAULT NULL,
  `usua_escola` varchar(100) DEFAULT NULL,
  `usua_turno` varchar(50) DEFAULT NULL,
  `usua_endereco` varchar(200) DEFAULT NULL,
  `usua_cpf` char(15) DEFAULT NULL,
  `usua_foto` varchar(100) DEFAULT NULL,
  `tius_id` int(11) NOT NULL,
  `usua_senha_hash` varchar(255) DEFAULT NULL,
  `usua_matricula` varchar(100) DEFAULT NULL,
  `usua_situacao` enum('A','I','B') NOT NULL DEFAULT 'A',
  `usua_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `usua_dth_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `empr_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`usua_id`, `usua_nome`, `usua_turma`, `usua_curso`, `usua_escola`, `usua_turno`, `usua_endereco`, `usua_cpf`, `usua_foto`, `tius_id`, `usua_senha_hash`, `usua_matricula`, `usua_situacao`, `usua_dth_criacao`, `usua_dth_update`, `empr_id`) VALUES
(1, 'Ana Méllany Sales Nascimento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$pnh9nmDXu3uSoXbLljiuuuCFJW4NnU7sCHQ2bvDuMiypOaZpxxwPy', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 05:58:50', NULL),
(2, 'Juliany Gabriela Abiorana Souza de Carvalho', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$yIRiGRNXXn7dWUpWGlBzHuGtbGtJjbsicxJvIhvB0s169d5GjuCA.', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 05:59:33', NULL),
(3, 'Marcos Vinícius Araújo Monteiro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$SSu9X3/HuK4t8kvUnOH3jeyM8z//IC3iQd0R5e5fCutPkKQSfgnzW', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 07:06:28', NULL),
(4, 'Maria Vitória Dias Nunes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$hPTaKgNi3aiqLiaoEptUZO6YCXXhNxLPjAFMNrX850y0g/LtooPJy', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 07:07:00', NULL),
(5, 'Nicolle Matias Gomes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$9uI9XUQcD0H.KOTwm0tF0OFbN0jEgBPEek2Y6MXAZQ.XcQcytl6xS', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 07:07:13', NULL),
(6, 'Vitória Alvis Oliveira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$itJBOgDygoFn2zv2lPAnouMz/6GQvlXXSqFgxXG5gaYAZpUqnX1b.', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 06:23:50', NULL),
(25, 'Carlinhos Maia', NULL, NULL, NULL, NULL, NULL, '000.000.000-00', 'public/uploads/usuarios_img/1761747510_9d2f77021000.webp', 2, '$2y$10$NuHdmq8EB2W9IeLSzMB0heetA12OTv1bOZN9TbgEHp7h./n2HX.nS', NULL, 'A', '2025-10-29 18:18:30', '2025-10-29 18:18:30', 2),
(26, 'Virginia Fonseca', NULL, NULL, NULL, NULL, NULL, '111.111.111-11', 'public/uploads/usuarios_img/1761747545_6f46ab8883c4.jpg', 2, '$2y$10$kPcOSqzwPMeoz/RAmn2s6uqfpaYshPBOz7.w.r2p.N6hVLJ1ZKfY.', NULL, 'A', '2025-10-29 18:19:05', '2025-10-29 18:19:05', 3),
(27, 'João Carlos', NULL, NULL, NULL, NULL, NULL, '222.222.222-22', 'public/uploads/usuarios_img/1761749322_cdd294c63cb6.png', 2, '$2y$10$Z0eboSU4LXR2KSi9pMhXCuCRGVh9xGutCBsdTAXUZv2pfG1rUZUsi', NULL, 'A', '2025-10-29 18:48:42', '2025-10-29 18:48:42', 4),
(28, 'Enaldinho', NULL, NULL, NULL, NULL, NULL, '333.333.333-33', 'public/uploads/usuarios_img/1761749399_466160493809.webp', 2, '$2y$10$4Lnuzx6/73am1pVQg1x97usp5ET2L5Nd2IdoozD6P96U8Jy5dC6ee', NULL, 'A', '2025-10-29 18:49:59', '2025-10-29 18:49:59', 5),
(29, 'ygugo', NULL, NULL, NULL, NULL, NULL, '444.444.444-44', 'public/uploads/usuarios_img/1761750636_728178546c80.webp', 2, '$2y$10$G77r1VSK1fJ5fFPfYpwSGe3D3DjuHXgg77rZQF8f/MHmddSVMJCwG', NULL, 'A', '2025-10-29 19:10:36', '2025-10-29 19:10:36', 6),
(30, 'Carlinhos Maia', NULL, NULL, NULL, NULL, NULL, '555.555.555-55', 'public/uploads/usuarios_img/1761751048_97803b355d5c.webp', 2, '$2y$10$3T5ONgSkHXXDRp1gmsBc/OumPV/oCe0eb7GJHH1E5psOlHpi6CxNa', NULL, 'A', '2025-10-29 19:17:28', '2025-10-29 19:17:28', 7),
(31, 'Virginia Fonseca', NULL, NULL, NULL, NULL, NULL, '999.999.999-99', 'public/uploads/usuarios_img/1761755860_a9a245e5019a.png', 2, '$2y$10$XHb4TFmwkN6kwCm7oBfpz.Qv41OLI8hLhfSMw8no9c8mAB0p7FCQ6', NULL, 'A', '2025-10-29 16:37:40', '2025-10-29 16:37:40', 7),
(32, 'Bailarina Cappuccina', NULL, NULL, NULL, NULL, NULL, '666.666.666-66', 'public/uploads/usuarios_img/1761757495_8696b88453a0.webp', 2, '$2y$10$otGV82Qr10ykYMarSLZB..rIscm9z/1.CSFxFEcEs2OUgGpPnsDMK', NULL, 'A', '2025-10-29 17:04:55', '2025-10-29 17:04:55', 1),
(33, 'sas', NULL, NULL, NULL, NULL, NULL, '984.519.865-16', 'public/uploads/usuarios_img/1761783050_13f826fe9027.jpg', 2, '$2y$10$BLekKjIxlDTz8XzSBb9ItOMKB/uI2MNPDF2YVVRoUGsyYf8dHuXxy', NULL, 'A', '2025-10-30 00:10:50', '2025-10-30 00:10:50', 8);

-- --------------------------------------------------------

--
-- Estrutura para tabela `viagem`
--

CREATE TABLE `viagem` (
  `viag_id` int(11) NOT NULL,
  `viag_turno` enum('MANHA','TARDE','NOITE') NOT NULL,
  `onib_id` int(11) NOT NULL,
  `moto_id` int(11) DEFAULT NULL,
  `viag_partida_prev` datetime DEFAULT NULL,
  `viag_chegada_prev` datetime DEFAULT NULL,
  `viag_status` enum('AGENDADA','EM_ANDAMENTO','CONCLUIDA','CANCELADA') NOT NULL DEFAULT 'AGENDADA'
) ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `viagem_usuario`
--

CREATE TABLE `viagem_usuario` (
  `vius_id` int(11) NOT NULL,
  `vius_dth` timestamp NOT NULL DEFAULT current_timestamp(),
  `usua_id` int(11) NOT NULL,
  `viag_id` int(11) NOT NULL,
  `vius_status` enum('PRESENTE','FALTA','JUSTIFICADA') DEFAULT 'PRESENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `aviso`
--
ALTER TABLE `aviso`
  ADD PRIMARY KEY (`avis_id`),
  ADD KEY `idx_aviso_usua_status` (`usua_id`,`avis_situacao`),
  ADD KEY `idx_aviso_publicacao` (`avis_publica_em`),
  ADD KEY `idx_aviso_expira` (`avis_expira_em`);

--
-- Índices de tabela `aviso_leitura`
--
ALTER TABLE `aviso_leitura`
  ADD PRIMARY KEY (`avle_id`),
  ADD UNIQUE KEY `uq_avle_avis_user` (`avis_id`,`usua_id`),
  ADD KEY `idx_avle_user` (`usua_id`);

--
-- Índices de tabela `contrato`
--
ALTER TABLE `contrato`
  ADD PRIMARY KEY (`cont_id`),
  ADD KEY `idx_contrato_situacao` (`cont_situacao`),
  ADD KEY `fk_contrato_empresa` (`empr_id`),
  ADD KEY `fk_contrato_usuario` (`usua_id`);

--
-- Índices de tabela `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`docu_id`),
  ADD KEY `idx_documento_usuario` (`usua_id`);

--
-- Índices de tabela `email`
--
ALTER TABLE `email`
  ADD PRIMARY KEY (`emai_id`),
  ADD UNIQUE KEY `uq_email` (`emai_endereco`),
  ADD KEY `idx_email_usuario` (`usua_id`,`is_principal`);

--
-- Índices de tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`empr_id`),
  ADD UNIQUE KEY `uq_empresa_cnpj` (`empr_cnpj`),
  ADD KEY `fk_empresa_criado_por` (`empr_criado_por`);

--
-- Índices de tabela `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`foru_id`),
  ADD KEY `idx_forum_usuario_status` (`usua_id`,`foru_situacao`);

--
-- Índices de tabela `forum_resposta`
--
ALTER TABLE `forum_resposta`
  ADD PRIMARY KEY (`fore_id`),
  ADD KEY `idx_fore_foru` (`foru_id`),
  ADD KEY `idx_fore_usua` (`usua_id`);

--
-- Índices de tabela `manutencao`
--
ALTER TABLE `manutencao`
  ADD PRIMARY KEY (`manu_id`),
  ADD KEY `idx_manutencao_onib_data` (`onib_id`,`manu_data`);

--
-- Índices de tabela `motorista`
--
ALTER TABLE `motorista`
  ADD PRIMARY KEY (`moto_id`),
  ADD UNIQUE KEY `uq_motorista_cpf` (`moto_cpf`),
  ADD UNIQUE KEY `uq_motorista_cnh` (`moto_cnh`);

--
-- Índices de tabela `onibus`
--
ALTER TABLE `onibus`
  ADD PRIMARY KEY (`onib_id`),
  ADD UNIQUE KEY `uq_onibus_placa` (`onib_placa`),
  ADD KEY `idx_onibus_empr_sit` (`empr_id`,`onib_situacao`);

--
-- Índices de tabela `pagamento`
--
ALTER TABLE `pagamento`
  ADD PRIMARY KEY (`paga_id`),
  ADD UNIQUE KEY `uq_pag_user_comp` (`usua_id`,`paga_competencia`),
  ADD KEY `idx_pag_user_status` (`usua_id`,`paga_situacao`);

--
-- Índices de tabela `ponto`
--
ALTER TABLE `ponto`
  ADD PRIMARY KEY (`pont_id`),
  ADD KEY `idx_ponto_viag_ordem` (`viag_id`,`pont_ordem`);

--
-- Índices de tabela `solicitacao_aluno`
--
ALTER TABLE `solicitacao_aluno`
  ADD PRIMARY KEY (`soli_id`),
  ADD UNIQUE KEY `uq_solicitacao_token` (`soli_token`),
  ADD KEY `fk_solicitacao_empresa` (`empr_id`);

--
-- Índices de tabela `telefone`
--
ALTER TABLE `telefone`
  ADD PRIMARY KEY (`tele_id`),
  ADD KEY `idx_telefone_usuario` (`usua_id`,`is_principal`);

--
-- Índices de tabela `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`tius_id`),
  ADD UNIQUE KEY `uq_tius_descricao` (`tius_descricao`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usua_id`),
  ADD UNIQUE KEY `uq_usuario_cpf` (`usua_cpf`),
  ADD KEY `idx_usuario_tius` (`tius_id`,`usua_situacao`),
  ADD KEY `fk_usuario_empresa` (`empr_id`);

--
-- Índices de tabela `viagem`
--
ALTER TABLE `viagem`
  ADD PRIMARY KEY (`viag_id`),
  ADD KEY `fk_viagem_motorista` (`moto_id`),
  ADD KEY `idx_viagem_onibus_status` (`onib_id`,`viag_status`),
  ADD KEY `idx_viagem_turno` (`viag_turno`);

--
-- Índices de tabela `viagem_usuario`
--
ALTER TABLE `viagem_usuario`
  ADD PRIMARY KEY (`vius_id`),
  ADD UNIQUE KEY `uq_viagem_usuario` (`viag_id`,`usua_id`),
  ADD KEY `idx_viagem_usuario_usua_status` (`usua_id`,`vius_status`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aviso`
--
ALTER TABLE `aviso`
  MODIFY `avis_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aviso_leitura`
--
ALTER TABLE `aviso_leitura`
  MODIFY `avle_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contrato`
--
ALTER TABLE `contrato`
  MODIFY `cont_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `documento`
--
ALTER TABLE `documento`
  MODIFY `docu_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `email`
--
ALTER TABLE `email`
  MODIFY `emai_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `empr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `forum`
--
ALTER TABLE `forum`
  MODIFY `foru_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `forum_resposta`
--
ALTER TABLE `forum_resposta`
  MODIFY `fore_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `manutencao`
--
ALTER TABLE `manutencao`
  MODIFY `manu_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `motorista`
--
ALTER TABLE `motorista`
  MODIFY `moto_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `onibus`
--
ALTER TABLE `onibus`
  MODIFY `onib_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento`
--
ALTER TABLE `pagamento`
  MODIFY `paga_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ponto`
--
ALTER TABLE `ponto`
  MODIFY `pont_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `solicitacao_aluno`
--
ALTER TABLE `solicitacao_aluno`
  MODIFY `soli_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `telefone`
--
ALTER TABLE `telefone`
  MODIFY `tele_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `tius_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usua_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `viagem`
--
ALTER TABLE `viagem`
  MODIFY `viag_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `viagem_usuario`
--
ALTER TABLE `viagem_usuario`
  MODIFY `vius_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `aviso`
--
ALTER TABLE `aviso`
  ADD CONSTRAINT `fk_aviso_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `aviso_leitura`
--
ALTER TABLE `aviso_leitura`
  ADD CONSTRAINT `fk_avle_aviso` FOREIGN KEY (`avis_id`) REFERENCES `aviso` (`avis_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avle_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `contrato`
--
ALTER TABLE `contrato`
  ADD CONSTRAINT `fk_contrato_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contrato_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `documento`
--
ALTER TABLE `documento`
  ADD CONSTRAINT `fk_documento_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `email`
--
ALTER TABLE `email`
  ADD CONSTRAINT `fk_email_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `fk_empresa_criado_por` FOREIGN KEY (`empr_criado_por`) REFERENCES `usuario` (`usua_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `forum`
--
ALTER TABLE `forum`
  ADD CONSTRAINT `fk_forum_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `forum_resposta`
--
ALTER TABLE `forum_resposta`
  ADD CONSTRAINT `fk_forum_resposta_forum` FOREIGN KEY (`foru_id`) REFERENCES `forum` (`foru_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_forum_resposta_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `manutencao`
--
ALTER TABLE `manutencao`
  ADD CONSTRAINT `fk_manutencao_onibus` FOREIGN KEY (`onib_id`) REFERENCES `onibus` (`onib_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `onibus`
--
ALTER TABLE `onibus`
  ADD CONSTRAINT `fk_onibus_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `pagamento`
--
ALTER TABLE `pagamento`
  ADD CONSTRAINT `fk_pagamento_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `ponto`
--
ALTER TABLE `ponto`
  ADD CONSTRAINT `fk_ponto_viagem` FOREIGN KEY (`viag_id`) REFERENCES `viagem` (`viag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `solicitacao_aluno`
--
ALTER TABLE `solicitacao_aluno`
  ADD CONSTRAINT `fk_solicitacao_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `telefone`
--
ALTER TABLE `telefone`
  ADD CONSTRAINT `fk_telefone_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`),
  ADD CONSTRAINT `fk_usuario_tipo_usuario` FOREIGN KEY (`tius_id`) REFERENCES `tipo_usuario` (`tius_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `viagem`
--
ALTER TABLE `viagem`
  ADD CONSTRAINT `fk_viagem_motorista` FOREIGN KEY (`moto_id`) REFERENCES `motorista` (`moto_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_viagem_onibus` FOREIGN KEY (`onib_id`) REFERENCES `onibus` (`onib_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `viagem_usuario`
--
ALTER TABLE `viagem_usuario`
  ADD CONSTRAINT `fk_viagem_usuario_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_viagem_usuario_viagem` FOREIGN KEY (`viag_id`) REFERENCES `viagem` (`viag_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
