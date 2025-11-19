--
-- Banco de dados: `bd_busmate`
--

DROP DATABASE IF EXISTS `bd_busmate`;
CREATE DATABASE `bd_busmate` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bd_busmate`;

CREATE TABLE `tipo_usuario` (
  `tius_id` int(11) NOT NULL AUTO_INCREMENT,
  `tius_descricao` varchar(50) NOT NULL,
  PRIMARY KEY (`tius_id`),
  UNIQUE KEY `uq_tius_descricao` (`tius_descricao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipo_usuario` (`tius_id`, `tius_descricao`) VALUES
(2, 'ADMINISTRADOR'),
(3, 'ALUNO'),
(1, 'ROOT');

CREATE TABLE `empresa` (
  `empr_id` int(11) NOT NULL AUTO_INCREMENT,
  `empr_razao` varchar(200) NOT NULL,
  `empr_cnpj` char(25) NOT NULL,
  `empr_logo` varchar(255) DEFAULT NULL,
  `empr_criado_por` int(11) DEFAULT NULL,
  `empr_qtd_admin` int(11) NOT NULL DEFAULT 1,
  `empr_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `empr_nome` varchar(100) NOT NULL,
  `empr_contrato_url` varchar(255) NOT NULL,
  `empr_pix_url` varchar(255) NOT NULL DEFAULT 'public/uploads/empresas_pix/pix_juma.jpeg',
  `empr_chave_pix` varchar(100) NOT NULL DEFAULT '067.439.362-78',
  `empr_vlr_mensalidade` decimal(10,2) NOT NULL,
  PRIMARY KEY (`empr_id`),
  UNIQUE KEY `uq_empresa_cnpj` (`empr_cnpj`),
  KEY `fk_empresa_criado_por` (`empr_criado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuario` (
  `usua_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `empr_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`usua_id`),
  UNIQUE KEY `uq_usuario_cpf` (`usua_cpf`),
  KEY `idx_usuario_tius` (`tius_id`,`usua_situacao`),
  KEY `fk_usuario_empresa` (`empr_id`),
  CONSTRAINT `fk_usuario_tipo_usuario` FOREIGN KEY (`tius_id`) REFERENCES `tipo_usuario` (`tius_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SENHA = 123456
INSERT INTO `usuario` (`usua_id`, `usua_nome`, `usua_turma`, `usua_curso`, `usua_escola`, `usua_turno`, `usua_endereco`, `usua_cpf`, `usua_foto`, `tius_id`, `usua_senha_hash`, `usua_matricula`, `usua_situacao`, `usua_dth_criacao`, `usua_dth_update`, `empr_id`) VALUES
(1, 'Ana Méllany Sales Nascimento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$pnh9nmDXu3uSoXbLljiuuuCFJW4NnU7sCHQ2bvDuMiypOaZpxxwPy', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 05:58:50', NULL),
(2, 'Juliany Gabriela Abiorana Souza de Carvalho', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$yIRiGRNXXn7dWUpWGlBzHuGtbGtJjbsicxJvIhvB0s169d5GjuCA.', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 05:59:33', NULL),
(3, 'Marcos Vinícius Araújo Monteiro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$SSu9X3/HuK4t8kvUnOH3jeyM8z//IC3iQd0R5e5fCutPkKQSfgnzW', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 07:06:28', NULL),
(4, 'Maria Vitória Dias Nunes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$QT963f8djcRm34ZhI5wNIu.zBVDhqagmHXx09LmpCQAF7iZrT8Y8W', NULL, 'A', '2025-10-28 04:46:19', '2025-11-18 21:28:45', NULL),
(5, 'Nicolle Matias Gomes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$9uI9XUQcD0H.KOTwm0tF0OFbN0jEgBPEek2Y6MXAZQ.XcQcytl6xS', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 07:07:13', NULL),
(6, 'Vitória Alvis Oliveira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '$2y$10$itJBOgDygoFn2zv2lPAnouMz/6GQvlXXSqFgxXG5gaYAZpUqnX1b.', NULL, 'A', '2025-10-28 04:46:19', '2025-10-28 06:23:50', NULL);

CREATE TABLE `onibus` (
  `onib_id` int(11) NOT NULL AUTO_INCREMENT,
  `empr_id` int(11) NOT NULL,
  `onib_modelo` varchar(120) NOT NULL,
  `onib_placa` varchar(10) NOT NULL,
  `onib_foto` varchar(255) DEFAULT NULL,
  `onib_situacao` enum('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  PRIMARY KEY (`onib_id`),
  KEY `idx_onibus_empresa` (`empr_id`,`onib_situacao`),
  CONSTRAINT `fk_onibus_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `horario` (
  `hori_id` int(11) NOT NULL AUTO_INCREMENT,
  `empr_id` int(11) NOT NULL,
  `onib_id` int(11) DEFAULT NULL,
  `hori_titulo` varchar(120) NOT NULL,
  `hori_turno` enum('MANHA','TARDE','NOITE') NOT NULL DEFAULT 'MANHA',
  `hori_hora_ida` time NOT NULL,
  `hori_hora_volta` time DEFAULT NULL,
  `hori_ponto` varchar(255) DEFAULT NULL,
  `hori_dias` varchar(80) DEFAULT NULL,
  `hori_situacao` enum('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  `hori_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`hori_id`),
  KEY `idx_horario_empresa` (`empr_id`,`hori_turno`,`hori_situacao`),
  KEY `fk_horario_onibus` (`onib_id`),
  CONSTRAINT `fk_horario_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_horario_onibus` FOREIGN KEY (`onib_id`) REFERENCES `onibus` (`onib_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `forum` (
  `foru_id` int(11) NOT NULL AUTO_INCREMENT,
  `foru_titulo` varchar(100) NOT NULL,
  `foru_texto` text NOT NULL,
  `foru_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `foru_dth_atualizacao` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `foru_situacao` enum('ABERTO','FECHADO','ARQUIVADO') NOT NULL DEFAULT 'ABERTO',
  `usua_id` int(11) NOT NULL,
  PRIMARY KEY (`foru_id`),
  KEY `idx_forum_usuario_status` (`usua_id`,`foru_situacao`),
  CONSTRAINT `fk_forum_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `forum_resposta` (
  `fore_id` int(11) NOT NULL AUTO_INCREMENT,
  `fore_texto` text NOT NULL,
  `fore_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `fore_dth_atualizacao` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `foru_id` int(11) NOT NULL,
  `usua_id` int(11) NOT NULL,
  PRIMARY KEY (`fore_id`),
  KEY `idx_fore_foru` (`foru_id`),
  KEY `idx_fore_usua` (`usua_id`),
  CONSTRAINT `fk_forum_resposta_forum` FOREIGN KEY (`foru_id`) REFERENCES `forum` (`foru_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_forum_resposta_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `aviso` (
  `avis_id` int(11) NOT NULL AUTO_INCREMENT,
  `avis_titulo` varchar(120) DEFAULT NULL,
  `avis_texto` text NOT NULL,
  `usua_id` int(11) NOT NULL,
  `avis_situacao` enum('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  `avis_publica_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `avis_expira_em` timestamp NULL DEFAULT NULL,
  `avis_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `empr_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`avis_id`),
  KEY `idx_aviso_usua_status` (`usua_id`,`avis_situacao`),
  KEY `idx_aviso_publicacao` (`avis_publica_em`),
  KEY `idx_aviso_expira` (`avis_expira_em`),
  KEY `idx_aviso_empr` (`empr_id`),
  CONSTRAINT `fk_aviso_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_aviso_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contrato` (
  `cont_id` int(11) NOT NULL AUTO_INCREMENT,
  `empr_id` int(11) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `cont_situacao` enum('RASCUNHO','ANALISE','ATIVO','ENCERRADO','CANCELADO') NOT NULL DEFAULT 'RASCUNHO',
  `cont_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `cont_dth_assinatura` timestamp NULL DEFAULT NULL,
  `cont_inicio_vigencia` date DEFAULT NULL,
  `cont_fim_vigencia` date DEFAULT NULL,
  `cont_valor_total` decimal(10,2) DEFAULT NULL,
  `cont_assinado_url` varchar(255) DEFAULT NULL,
  `cont_motivo_recusa` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cont_id`),
  KEY `idx_contrato_situacao` (`cont_situacao`),
  KEY `fk_contrato_empresa` (`empr_id`),
  KEY `fk_contrato_usuario` (`usua_id`),
  CONSTRAINT `fk_contrato_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_contrato_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `documento` (
  `docu_id` int(11) NOT NULL AUTO_INCREMENT,
  `docu_tipo` varchar(45) NOT NULL,
  `docu_nome_original` varchar(255) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `docu_url` varchar(255) DEFAULT NULL,
  `docu_dth_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`docu_id`),
  KEY `idx_documento_usuario` (`usua_id`),
  CONSTRAINT `fk_documento_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email` (
  `emai_id` int(11) NOT NULL AUTO_INCREMENT,
  `emai_endereco` varchar(200) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `is_principal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`emai_id`),
  UNIQUE KEY `uq_email` (`emai_endereco`),
  KEY `idx_email_usuario` (`usua_id`,`is_principal`),
  CONSTRAINT `fk_email_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `email` (`emai_id`, `emai_endereco`, `usua_id`, `is_principal`, `created_at`) VALUES
(1, 'ana.mellany@busmate.com', 1, 1, '2025-10-28 04:50:48'),
(2, 'juliany.gabriela@busmate.com', 2, 1, '2025-10-28 04:50:48'),
(3, 'marcos.vinicius@busmate.com', 3, 1, '2025-10-28 04:50:48'),
(4, 'maria.vitoria@busmate.com', 4, 1, '2025-10-28 04:50:48'),
(5, 'nicolle.matias@busmate.com', 5, 1, '2025-10-28 04:50:48'),
(6, 'vitoria.alvis@busmate.com', 6, 1, '2025-10-28 04:50:48');

CREATE TABLE pagamento (
  paga_id int(11) NOT NULL AUTO_INCREMENT,
  paga_valor decimal(10,2) NOT NULL,
  usua_id int(11) NOT NULL,
  cont_id int(11) DEFAULT NULL,
  paga_competencia char(7) DEFAULT NULL,
  paga_metodo enum('PIX','CARTAO','BOLETO') DEFAULT 'PIX',
  paga_situacao enum('PENDENTE','PAGO','FALHOU','ESTORNADO') NOT NULL DEFAULT 'PENDENTE',
  paga_dth timestamp NULL DEFAULT NULL,
  paga_comprovante_url varchar(255) DEFAULT NULL,
  paga_motivo varchar(255) DEFAULT NULL,
  PRIMARY KEY (paga_id),
  UNIQUE KEY uq_pag_user_comp (usua_id,paga_competencia),
  KEY idx_pag_user_status (usua_id,paga_situacao),
  KEY fk_pagamento_contrato (cont_id),
  CONSTRAINT fk_pagamento_contrato_ref FOREIGN KEY (cont_id) REFERENCES contrato (cont_id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pagamento_usuario FOREIGN KEY (usua_id) REFERENCES usuario (usua_id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `solicitacao_aluno` (
  `soli_id` int(11) NOT NULL AUTO_INCREMENT,
  `soli_nome` varchar(100) NOT NULL,
  `soli_email` varchar(200) NOT NULL,
  `soli_email_recuperacao` varchar(200) NOT NULL,
  `soli_tel` varchar(20) DEFAULT NULL,
  `soli_responsavel_tel` varchar(20) DEFAULT NULL,
  `soli_escola` varchar(100) DEFAULT NULL,
  `soli_turno` varchar(50) DEFAULT NULL,
  `soli_endereco` varchar(200) DEFAULT NULL,
  `soli_curso` varchar(100) DEFAULT NULL,
  `soli_turma` varchar(50) DEFAULT NULL,
  `empr_id` int(11) NOT NULL,
  `soli_status` enum('PENDENTE','ACEITA','RECUSADA') NOT NULL DEFAULT 'PENDENTE',
  `motivo_recusa` varchar(300) DEFAULT NULL,
  `soli_token` char(36) NOT NULL,
  `soli_senha_hash` varchar(255) DEFAULT NULL,
  `soli_dth_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `soli_dth_decisao` timestamp NULL DEFAULT NULL,
  `soli_foto_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`soli_id`),
  UNIQUE KEY `uq_solicitacao_token` (`soli_token`),
  KEY `fk_solicitacao_empresa` (`empr_id`),
  CONSTRAINT `fk_solicitacao_empresa` FOREIGN KEY (`empr_id`) REFERENCES `empresa` (`empr_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `solicitacao_documento` (
  `sodo_id` int(11) NOT NULL AUTO_INCREMENT,
  `soli_id` int(11) NOT NULL,
  `sodo_tipo` enum('COMPROVANTE_MATRICULA','COMPROVANTE_RESIDENCIA','RG_ALUNO','CPF_ALUNO','DOC_RESPONSAVEL') NOT NULL,
  `sodo_url_tmp` varchar(255) NOT NULL,
  `sodo_dth_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sodo_id`),
  KEY `idx_sodo_soli` (`soli_id`,`sodo_tipo`),
  CONSTRAINT `fk_sodo_soli` FOREIGN KEY (`soli_id`) REFERENCES `solicitacao_aluno` (`soli_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `telefone` (
  `tele_id` int(11) NOT NULL AUTO_INCREMENT,
  `tele_numero` varchar(20) NOT NULL,
  `usua_id` int(11) NOT NULL,
  `tipo` enum('CEL','FIXO','COM') DEFAULT 'CEL',
  `is_principal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tele_id`),
  KEY `idx_telefone_usuario` (`usua_id`,`is_principal`),
  CONSTRAINT `fk_telefone_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;