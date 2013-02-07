-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tempo de Geração: 
-- Versão do Servidor: 5.5.27
-- Versão do PHP: 5.4.7

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de Dados: 'applr'
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'client'
--

DROP TABLE IF EXISTS client;
CREATE TABLE IF NOT EXISTS `client` (
  id int(11) NOT NULL AUTO_INCREMENT,
  company varchar(80) COLLATE utf8_bin DEFAULT NULL,
  address varchar(255) COLLATE utf8_bin DEFAULT NULL,
  city varchar(80) COLLATE utf8_bin DEFAULT NULL,
  state varchar(80) COLLATE utf8_bin DEFAULT NULL,
  country varchar(80) COLLATE utf8_bin DEFAULT NULL,
  contact varchar(80) COLLATE utf8_bin DEFAULT NULL,
  phone bigint(15) DEFAULT NULL,
  fax bigint(15) DEFAULT NULL,
  email varchar(80) COLLATE utf8_bin DEFAULT NULL,
  website varchar(80) COLLATE utf8_bin DEFAULT NULL,
  start_date timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela 'config'
--

DROP TABLE IF EXISTS config;
CREATE TABLE IF NOT EXISTS config (
  project_client_id int(11) NOT NULL,
  project_id int(11) NOT NULL,
  id int(11) NOT NULL AUTO_INCREMENT,
  dir_upload varchar(255) COLLATE utf8_bin DEFAULT NULL,
  dir_image varchar(255) COLLATE utf8_bin DEFAULT NULL,
  dir_video varchar(255) COLLATE utf8_bin DEFAULT NULL,
  dir_template varchar(255) COLLATE utf8_bin DEFAULT NULL,
  dir_static varchar(255) COLLATE utf8_bin DEFAULT NULL,
  dir_dynamic varchar(255) COLLATE utf8_bin DEFAULT NULL,
  dir_xml varchar(255) COLLATE utf8_bin DEFAULT NULL,
  dir_rss varchar(255) COLLATE utf8_bin DEFAULT NULL,
  mail_auth tinyint(1) DEFAULT '0',
  mail_auth_host varchar(80) COLLATE utf8_bin DEFAULT NULL,
  mail_auth_user varchar(80) COLLATE utf8_bin DEFAULT NULL,
  mail_auth_password varchar(80) COLLATE utf8_bin DEFAULT NULL,
  mail_sys varchar(80) COLLATE utf8_bin DEFAULT NULL,
  mail_public varchar(80) COLLATE utf8_bin DEFAULT NULL,
  mail_contact varchar(80) COLLATE utf8_bin DEFAULT NULL,
  mail_user varchar(80) COLLATE utf8_bin DEFAULT NULL,
  paging_limit int(3) DEFAULT NULL,
  rss_group tinyint(1) DEFAULT NULL,
  rss_limit int(3) DEFAULT NULL,
  PRIMARY KEY (id,project_client_id,project_id),
  KEY fk_config_project (project_id,project_client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela 'ctn_translate'
--

DROP TABLE IF EXISTS ctn_translate;
CREATE TABLE IF NOT EXISTS ctn_translate (
  id int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  var varchar(45) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela 'media_data'
--

DROP TABLE IF EXISTS media_data;
CREATE TABLE IF NOT EXISTS media_data (
  id int(11) NOT NULL AUTO_INCREMENT,
  media_gallery_id int(11) NOT NULL,
  usr_data_id int(11) NOT NULL,
  `type` tinyint(1) DEFAULT '1',
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  author varchar(80) COLLATE utf8_bin DEFAULT NULL,
  label varchar(255) COLLATE utf8_bin DEFAULT NULL,
  filepath varchar(255) COLLATE utf8_bin DEFAULT NULL,
  filepath_thumbnail varchar(255) COLLATE utf8_bin DEFAULT NULL,
  filepath_streaming varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_img_data_img_gallery (media_gallery_id),
  KEY fk_img_data_usr_data (usr_data_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA media_data:
--   media_gallery_id
--       media_gallery -> id
--   usr_data_id
--       usr_data -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'media_gallery'
--

DROP TABLE IF EXISTS media_gallery;
CREATE TABLE IF NOT EXISTS media_gallery (
  id int(11) NOT NULL AUTO_INCREMENT,
  usr_data_id int(11) NOT NULL,
  sec_config_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  description text COLLATE utf8_bin,
  dirpath varchar(255) COLLATE utf8_bin DEFAULT NULL,
  public tinyint(1) DEFAULT '1',
  `default` tinyint(1) DEFAULT '1',
  authothumb tinyint(1) DEFAULT '0',
  autothumb_h int(11) DEFAULT NULL,
  autothumb_w int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY fk_media_gallery_usr_data (usr_data_id),
  KEY fk_media_gallery_sec_config (sec_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA media_gallery:
--   usr_data_id
--       usr_data -> id
--   sec_config_id
--       sec_config -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'perm_profile'
--

DROP TABLE IF EXISTS perm_profile;
CREATE TABLE IF NOT EXISTS perm_profile (
  usr_profile_id int(11) NOT NULL,
  sec_config_id int(11) NOT NULL,
  `view` tinyint(1) DEFAULT NULL,
  `insert` tinyint(1) DEFAULT NULL,
  `update` tinyint(1) DEFAULT NULL,
  `delete` tinyint(1) DEFAULT NULL,
  publish tinyint(1) DEFAULT NULL,
  PRIMARY KEY (usr_profile_id,sec_config_id),
  KEY fk_perm_profile_sec_config (sec_config_id),
  KEY fk_perm_profile_usr_profile (usr_profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA perm_profile:
--   usr_profile_id
--       usr_profile -> id
--   sec_config_id
--       sec_config -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'perm_user'
--

DROP TABLE IF EXISTS perm_user;
CREATE TABLE IF NOT EXISTS perm_user (
  usr_data_id int(11) NOT NULL,
  sec_config_id int(11) NOT NULL,
  `view` tinyint(1) DEFAULT NULL,
  `insert` tinyint(1) DEFAULT NULL,
  `update` tinyint(1) DEFAULT NULL,
  `delete` tinyint(1) DEFAULT NULL,
  publish tinyint(1) DEFAULT NULL,
  PRIMARY KEY (usr_data_id,sec_config_id),
  KEY fk_perm_user_sec_config (sec_config_id),
  KEY fk_perm_user_usr_data (usr_data_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA perm_user:
--   usr_data_id
--       usr_data -> id
--   sec_config_id
--       sec_config -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'perm_user_content'
--

DROP TABLE IF EXISTS perm_user_content;
CREATE TABLE IF NOT EXISTS perm_user_content (
  usr_data_id int(11) NOT NULL,
  sec_config_id int(11) NOT NULL,
  sys_language_id int(11) NOT NULL,
  content_id int(11) NOT NULL,
  PRIMARY KEY (usr_data_id,sec_config_id,sys_language_id,content_id),
  KEY fk_perm_user_content_perm_user (usr_data_id,sec_config_id),
  KEY fk_perm_user_content_sys_language (sys_language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA perm_user_content:
--   sys_language_id
--       sys_language -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'project'
--

DROP TABLE IF EXISTS project;
CREATE TABLE IF NOT EXISTS project (
  id int(11) NOT NULL AUTO_INCREMENT,
  client_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  description text COLLATE utf8_bin,
  logo_upload varchar(255) COLLATE utf8_bin DEFAULT NULL,
  start_date timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id,client_id),
  KEY fk_project_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA project:
--   client_id
--       client -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_hotspot_content'
--

DROP TABLE IF EXISTS rel_hotspot_content;
CREATE TABLE IF NOT EXISTS rel_hotspot_content (
  sys_hotspot_id int(11) NOT NULL,
  sec_config_id int(11) NOT NULL,
  content int(11) NOT NULL,
  PRIMARY KEY (sys_hotspot_id,sec_config_id,content),
  KEY fk_rel_hotspot_content_sec_config (sec_config_id),
  KEY fk_rel_hotspot_content_sys_hotspot (sys_hotspot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA rel_hotspot_content:
--   sys_hotspot_id
--       sys_hotspot -> id
--   sec_config_id
--       sec_config -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_hotspot_sec'
--

DROP TABLE IF EXISTS rel_hotspot_sec;
CREATE TABLE IF NOT EXISTS rel_hotspot_sec (
  sys_hotspot_id int(11) NOT NULL,
  sec_config_id int(11) NOT NULL,
  sys_language_id int(11) NOT NULL,
  `type` tinyint(1) DEFAULT '1',
  PRIMARY KEY (sys_hotspot_id,sec_config_id,sys_language_id),
  KEY fk_rel_sys_hotspot_sys_hotspot (sys_hotspot_id),
  KEY fk_rel_sys_hotspot_sys_language (sys_language_id),
  KEY fk_rel_hotspot_sec_sec_config (sec_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA rel_hotspot_sec:
--   sys_hotspot_id
--       sys_hotspot -> id
--   sys_language_id
--       sys_language -> id
--   sec_config_id
--       sec_config -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_poll_template'
--

DROP TABLE IF EXISTS rel_poll_template;
CREATE TABLE IF NOT EXISTS rel_poll_template (
  sys_poll_id int(11) NOT NULL,
  sys_template_id int(11) NOT NULL,
  sys_template_type_id int(11) NOT NULL,
  PRIMARY KEY (sys_poll_id,sys_template_id,sys_template_type_id),
  KEY fk_rel_poll_template_sys_poll (sys_poll_id),
  KEY fk_rel_poll_template_sys_template (sys_template_id),
  KEY fk_rel_poll_template_sys_template_type (sys_template_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA rel_poll_template:
--   sys_poll_id
--       sys_poll -> id
--   sys_template_id
--       sys_template -> id
--   sys_template_type_id
--       sys_template_type -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_quiz_template'
--

DROP TABLE IF EXISTS rel_quiz_template;
CREATE TABLE IF NOT EXISTS rel_quiz_template (
  sys_quiz_id int(11) NOT NULL,
  sys_template_id int(11) NOT NULL,
  sys_template_type_id int(11) NOT NULL,
  PRIMARY KEY (sys_quiz_id,sys_template_id,sys_template_type_id),
  KEY fk_rel_quiz_template_sys_quiz (sys_quiz_id),
  KEY fk_rel_quiz_template_sys_template (sys_template_id),
  KEY fk_rel_quiz_template_sys_template_type (sys_template_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA rel_quiz_template:
--   sys_quiz_id
--       sys_quiz -> id
--   sys_template_id
--       sys_template -> id
--   sys_template_type_id
--       sys_template_type -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_sec_language'
--

DROP TABLE IF EXISTS rel_sec_language;
CREATE TABLE IF NOT EXISTS rel_sec_language (
  sys_language_id int(11) NOT NULL,
  sec_config_id int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (sys_language_id,sec_config_id),
  KEY fk_rel_sec_language_sys_language (sys_language_id),
  KEY fk_rel_sec_language_sec_config (sec_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA rel_sec_language:
--   sys_language_id
--       sys_language -> id
--   sec_config_id
--       sec_config -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_sec_sec'
--

DROP TABLE IF EXISTS rel_sec_sec;
CREATE TABLE IF NOT EXISTS rel_sec_sec (
  id int(11) NOT NULL AUTO_INCREMENT,
  parent_id int(11) NOT NULL,
  child_id int(11) NOT NULL,
  field_rel varchar(65) COLLATE utf8_bin DEFAULT NULL,
  field_type tinyint(1) DEFAULT NULL,
  field_name varchar(65) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  tooltip varchar(255) COLLATE utf8_bin DEFAULT NULL,
  mandatory tinyint(1) DEFAULT '0',
  admin tinyint(1) DEFAULT '0',
  `table` varchar(130) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (id,parent_id,child_id),
  KEY fk_rel_sec_sec_sec_config_parent (parent_id),
  KEY fk_rel_sec_sec_sec_config_child (child_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA rel_sec_sec:
--   parent_id
--       sec_config -> id
--   child_id
--       sec_config -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_sec_struct'
--

DROP TABLE IF EXISTS rel_sec_struct;
CREATE TABLE IF NOT EXISTS rel_sec_struct (
  id int(11) NOT NULL AUTO_INCREMENT,
  sec_config_id int(11) NOT NULL,
  sec_struct_id int(11) NOT NULL,
  field_name varchar(65) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  tooltip varchar(255) COLLATE utf8_bin DEFAULT NULL,
  mandatory tinyint(1) DEFAULT '0',
  admin tinyint(1) DEFAULT '0',
  PRIMARY KEY (id,sec_config_id),
  KEY fk_rel_sec_struct_sec_config (sec_config_id),
  KEY fk_rel_sec_struct_sec_struct (sec_struct_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA rel_sec_struct:
--   sec_config_id
--       sec_config -> id
--   sec_struct_id
--       sec_struct -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_sec_template'
--

DROP TABLE IF EXISTS rel_sec_template;
CREATE TABLE IF NOT EXISTS rel_sec_template (
  sec_config_id int(11) NOT NULL,
  sys_template_id int(11) NOT NULL,
  sys_template_type_id int(11) NOT NULL,
  PRIMARY KEY (sec_config_id,sys_template_id,sys_template_type_id),
  KEY fk_rel_sec_tpl_sec_config (sec_config_id),
  KEY fk_rel_sec_tpl_sec_template (sys_template_id),
  KEY fk_rel_sec_tpl_sec_template_type (sys_template_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA rel_sec_template:
--   sec_config_id
--       sec_config -> id
--   sys_template_id
--       sys_template -> id
--   sys_template_type_id
--       sys_template_type -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'rel_translate_language'
--

DROP TABLE IF EXISTS rel_translate_language;
CREATE TABLE IF NOT EXISTS rel_translate_language (
  ctn_translate_id int(11) NOT NULL,
  sys_language_id int(11) NOT NULL,
  content text COLLATE utf8_bin,
  PRIMARY KEY (ctn_translate_id,sys_language_id),
  KEY fk_rel_translate_language_ctn_translate (ctn_translate_id),
  KEY fk_rel_translate_language_sys_language (sys_language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA rel_translate_language:
--   ctn_translate_id
--       ctn_translate -> id
--   sys_language_id
--       sys_language -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sec_config'
--

DROP TABLE IF EXISTS sec_config;
CREATE TABLE IF NOT EXISTS sec_config (
  id int(11) NOT NULL AUTO_INCREMENT,
  parent int(11) NOT NULL,
  sys_folder_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `table` varchar(60) COLLATE utf8_bin DEFAULT NULL,
  website tinyint(1) DEFAULT '1',
  user_edit tinyint(1) DEFAULT '0',
  public tinyint(1) DEFAULT '1',
  home tinyint(1) DEFAULT '1',
  static tinyint(1) DEFAULT '0',
  static_filename varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `xml` tinyint(1) DEFAULT '0',
  xml_items tinyint(3) DEFAULT NULL,
  rss tinyint(1) DEFAULT '0',
  rss_items tinyint(3) DEFAULT NULL,
  list_items tinyint(3) DEFAULT NULL,
  orderby varchar(255) COLLATE utf8_bin DEFAULT 'id ASC',
  search tinyint(1) DEFAULT '1',
  autothumb tinyint(1) DEFAULT '0',
  autothumb_h tinyint(3) DEFAULT NULL,
  autothumb_w tinyint(3) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_sec_config_sec_config (parent),
  KEY fk_sec_config_sys_folder (sys_folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sec_config:
--   parent
--       sec_config -> id
--   sys_folder_id
--       sys_folder -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sec_config_order'
--

DROP TABLE IF EXISTS sec_config_order;
CREATE TABLE IF NOT EXISTS sec_config_order (
  field_id int(11) NOT NULL,
  `order` tinyint(4) DEFAULT NULL,
  `type` tinyint(1) DEFAULT '1',
  PRIMARY KEY (field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sec_struct'
--

DROP TABLE IF EXISTS sec_struct;
CREATE TABLE IF NOT EXISTS sec_struct (
  id int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  html text COLLATE utf8_bin,
  sufix varchar(45) COLLATE utf8_bin DEFAULT NULL,
  fieldtype varchar(45) COLLATE utf8_bin DEFAULT NULL,
  length int(11) DEFAULT NULL,
  `fixed` tinyint(1) DEFAULT NULL,
  `unsigned` tinyint(1) DEFAULT NULL,
  notnull tinyint(1) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_folder'
--

DROP TABLE IF EXISTS sys_folder;
CREATE TABLE IF NOT EXISTS sys_folder (
  id int(11) NOT NULL AUTO_INCREMENT,
  usr_data_id int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  icon_filepath varchar(255) COLLATE utf8_bin DEFAULT NULL,
  sys_filepath varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY fk_sys_folder_usr_data (usr_data_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_folder:
--   usr_data_id
--       usr_data -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_hotspot'
--

DROP TABLE IF EXISTS sys_hotspot;
CREATE TABLE IF NOT EXISTS sys_hotspot (
  id int(11) NOT NULL AUTO_INCREMENT,
  usr_data_id int(11) NOT NULL,
  sys_template_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  description text COLLATE utf8_bin,
  var varchar(45) COLLATE utf8_bin DEFAULT NULL,
  orderby tinyint(2) DEFAULT NULL,
  limit_content int(11) DEFAULT NULL,
  limit_view int(11) DEFAULT NULL,
  autopost tinyint(1) DEFAULT '1',
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY fk_sys_hotspot_usr_data (usr_data_id),
  KEY fk_sys_hotspot_sys_template (sys_template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_hotspot:
--   usr_data_id
--       usr_data -> id
--   sys_template_id
--       sys_template -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_language'
--

DROP TABLE IF EXISTS sys_language;
CREATE TABLE IF NOT EXISTS sys_language (
  id int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  acronym varchar(5) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_log'
--

DROP TABLE IF EXISTS sys_log;
CREATE TABLE IF NOT EXISTS sys_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  usr_data_id int(11) NOT NULL,
  sec_config_id int(11) DEFAULT NULL,
  `action` enum('inseriu','alterou','deletou','deletou permanentemente') COLLATE utf8_bin DEFAULT NULL,
  object enum('o registro','a seção','o usuário') COLLATE utf8_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  content varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_sys_log_sec_config (sec_config_id),
  KEY fk_sys_log_usr_data (usr_data_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_log:
--   sec_config_id
--       sec_config -> id
--   usr_data_id
--       usr_data -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_mailing'
--

DROP TABLE IF EXISTS sys_mailing;
CREATE TABLE IF NOT EXISTS sys_mailing (
  id int(11) NOT NULL AUTO_INCREMENT,
  sys_language_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  email varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY fk_sys_mailing_sys_language (sys_language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_mailing:
--   sys_language_id
--       sys_language -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_poll'
--

DROP TABLE IF EXISTS sys_poll;
CREATE TABLE IF NOT EXISTS sys_poll (
  id int(11) NOT NULL AUTO_INCREMENT,
  usr_data_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  description text COLLATE utf8_bin,
  orderby tinyint(2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY fk_sys_poll_usr_data (usr_data_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_poll:
--   usr_data_id
--       usr_data -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_poll_answer'
--

DROP TABLE IF EXISTS sys_poll_answer;
CREATE TABLE IF NOT EXISTS sys_poll_answer (
  id int(11) NOT NULL AUTO_INCREMENT,
  sys_poll_id int(11) NOT NULL,
  label varchar(255) COLLATE utf8_bin DEFAULT NULL,
  filepath varchar(255) COLLATE utf8_bin DEFAULT NULL,
  filepath_thumbnail varchar(255) COLLATE utf8_bin DEFAULT NULL,
  ordertag tinyint(2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id,sys_poll_id),
  KEY fk_sys_poll_answer_sys_poll (sys_poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_poll_answer:
--   sys_poll_id
--       sys_poll -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_poll_result'
--

DROP TABLE IF EXISTS sys_poll_result;
CREATE TABLE IF NOT EXISTS sys_poll_result (
  id int(11) NOT NULL AUTO_INCREMENT,
  sys_poll_id int(11) NOT NULL,
  sys_poll_answer_id int(11) NOT NULL,
  user_ip varchar(15) COLLATE utf8_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id,sys_poll_id,sys_poll_answer_id),
  KEY fk_sys_poll_result_sys_poll_answer (sys_poll_answer_id,sys_poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_quiz'
--

DROP TABLE IF EXISTS sys_quiz;
CREATE TABLE IF NOT EXISTS sys_quiz (
  id int(11) NOT NULL AUTO_INCREMENT,
  usr_data_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  description text COLLATE utf8_bin,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY fk_sys_quiz_usr_data (usr_data_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_quiz:
--   usr_data_id
--       usr_data -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_quiz_answer'
--

DROP TABLE IF EXISTS sys_quiz_answer;
CREATE TABLE IF NOT EXISTS sys_quiz_answer (
  sys_quiz_question_id int(11) NOT NULL,
  id int(11) NOT NULL AUTO_INCREMENT,
  label varchar(80) COLLATE utf8_bin DEFAULT NULL,
  filepath varchar(255) COLLATE utf8_bin DEFAULT NULL,
  filepath_thumbnail varchar(255) COLLATE utf8_bin DEFAULT NULL,
  ordertag tinyint(2) DEFAULT NULL,
  result_value tinyint(3) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id,sys_quiz_question_id),
  KEY fk_sys_quiz_answer_sys_quiz_question (sys_quiz_question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_quiz_answer:
--   sys_quiz_question_id
--       sys_quiz_question -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_quiz_question'
--

DROP TABLE IF EXISTS sys_quiz_question;
CREATE TABLE IF NOT EXISTS sys_quiz_question (
  sys_quiz_id int(11) NOT NULL,
  id int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  description text COLLATE utf8_bin,
  filepath varchar(255) COLLATE utf8_bin DEFAULT NULL,
  filepath_thumbnail varchar(255) COLLATE utf8_bin DEFAULT NULL,
  ordertag tinyint(2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id,sys_quiz_id),
  KEY fk_sys_quiz_question_sys_quiz (sys_quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_quiz_question:
--   sys_quiz_id
--       sys_quiz -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_quiz_result'
--

DROP TABLE IF EXISTS sys_quiz_result;
CREATE TABLE IF NOT EXISTS sys_quiz_result (
  sys_quiz_answer_id int(11) NOT NULL,
  id int(11) NOT NULL AUTO_INCREMENT,
  user_ip varchar(15) COLLATE utf8_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id,sys_quiz_answer_id),
  KEY fk_sys_quiz_answer_sys_quiz_answer (sys_quiz_answer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_quiz_result:
--   sys_quiz_answer_id
--       sys_quiz_answer -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_search'
--

DROP TABLE IF EXISTS sys_search;
CREATE TABLE IF NOT EXISTS sys_search (
  id int(11) NOT NULL AUTO_INCREMENT,
  sys_language_id int(11) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  term varchar(255) COLLATE utf8_bin DEFAULT NULL,
  results int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_sys_search_sys_language (sys_language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_search:
--   sys_language_id
--       sys_language -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_share'
--

DROP TABLE IF EXISTS sys_share;
CREATE TABLE IF NOT EXISTS sys_share (
  sec_config_id int(11) NOT NULL,
  sys_language_id int(11) NOT NULL,
  content int(11) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  from_name varchar(80) COLLATE utf8_bin DEFAULT NULL,
  from_email varchar(80) COLLATE utf8_bin DEFAULT NULL,
  to_name varchar(80) COLLATE utf8_bin DEFAULT NULL,
  to_email varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `text` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (content,sec_config_id,sys_language_id),
  KEY fk_sys_share_sys_language (sys_language_id),
  KEY fk_sys_share_sec_config (sec_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- RELAÇÕES PARA A TABELA sys_share:
--   sys_language_id
--       sys_language -> id
--   sec_config_id
--       sec_config -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_template'
--

DROP TABLE IF EXISTS sys_template;
CREATE TABLE IF NOT EXISTS sys_template (
  id int(11) NOT NULL AUTO_INCREMENT,
  usr_data_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  path varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY fk_sys_template_usr_data (usr_data_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA sys_template:
--   usr_data_id
--       usr_data -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'sys_template_type'
--

DROP TABLE IF EXISTS sys_template_type;
CREATE TABLE IF NOT EXISTS sys_template_type (
  id int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela 'usr_data'
--

DROP TABLE IF EXISTS usr_data;
CREATE TABLE IF NOT EXISTS usr_data (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) NOT NULL,
  usr_profile_id int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  email varchar(80) COLLATE utf8_bin DEFAULT NULL,
  authcode varchar(255) COLLATE utf8_bin DEFAULT NULL,
  authcode_true tinyint(1) DEFAULT NULL,
  username varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `password` char(32) COLLATE utf8_bin DEFAULT NULL,
  expires timestamp NULL DEFAULT NULL,
  admin tinyint(1) DEFAULT '0',
  deleted tinyint(1) DEFAULT '0',
  PRIMARY KEY (id),
  KEY fk_usr_data_project (project_id),
  KEY fk_usr_data_usr_profile (usr_profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- RELAÇÕES PARA A TABELA usr_data:
--   usr_profile_id
--       usr_profile -> id
--   project_id
--       project -> id
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'usr_profile'
--

DROP TABLE IF EXISTS usr_profile;
CREATE TABLE IF NOT EXISTS usr_profile (
  id int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Restrições para as tabelas dumpadas
--

--
-- Restrições para a tabela `config`
--
ALTER TABLE `config`
  ADD CONSTRAINT fk_config_project FOREIGN KEY (project_id, project_client_id) REFERENCES project (id, client_id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `media_data`
--
ALTER TABLE `media_data`
  ADD CONSTRAINT fk_img_data_img_gallery FOREIGN KEY (media_gallery_id) REFERENCES media_gallery (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_img_data_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `media_gallery`
--
ALTER TABLE `media_gallery`
  ADD CONSTRAINT fk_media_gallery_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_media_gallery_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `perm_profile`
--
ALTER TABLE `perm_profile`
  ADD CONSTRAINT fk_perm_profile_usr_profile FOREIGN KEY (usr_profile_id) REFERENCES usr_profile (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_perm_profile_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para a tabela `perm_user`
--
ALTER TABLE `perm_user`
  ADD CONSTRAINT fk_perm_user_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_perm_user_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `perm_user_content`
--
ALTER TABLE `perm_user_content`
  ADD CONSTRAINT fk_perm_user_content_perm_user FOREIGN KEY (usr_data_id, sec_config_id) REFERENCES perm_user (usr_data_id, sec_config_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_perm_user_content_sys_language FOREIGN KEY (sys_language_id) REFERENCES sys_language (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT fk_project_client FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_hotspot_content`
--
ALTER TABLE `rel_hotspot_content`
  ADD CONSTRAINT fk_rel_hotspot_content_sys_hotspot FOREIGN KEY (sys_hotspot_id) REFERENCES sys_hotspot (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_hotspot_content_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_hotspot_sec`
--
ALTER TABLE `rel_hotspot_sec`
  ADD CONSTRAINT fk_rel_sys_hotspot_sys_hotspot FOREIGN KEY (sys_hotspot_id) REFERENCES sys_hotspot (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_sys_hotspot_sys_language FOREIGN KEY (sys_language_id) REFERENCES sys_language (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_hotspot_sec_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_poll_template`
--
ALTER TABLE `rel_poll_template`
  ADD CONSTRAINT fk_rel_poll_template_sys_poll FOREIGN KEY (sys_poll_id) REFERENCES sys_poll (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_poll_template_sys_template FOREIGN KEY (sys_template_id) REFERENCES sys_template (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_poll_template_sys_template_type FOREIGN KEY (sys_template_type_id) REFERENCES sys_template_type (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_quiz_template`
--
ALTER TABLE `rel_quiz_template`
  ADD CONSTRAINT fk_rel_quiz_template_sys_quiz FOREIGN KEY (sys_quiz_id) REFERENCES sys_quiz (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_quiz_template_sys_template FOREIGN KEY (sys_template_id) REFERENCES sys_template (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_quiz_template_sys_template_type FOREIGN KEY (sys_template_type_id) REFERENCES sys_template_type (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_sec_language`
--
ALTER TABLE `rel_sec_language`
  ADD CONSTRAINT fk_rel_sec_language_sys_language FOREIGN KEY (sys_language_id) REFERENCES sys_language (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_sec_language_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_sec_sec`
--
ALTER TABLE `rel_sec_sec`
  ADD CONSTRAINT fk_rel_sec_sec_sec_config_parent FOREIGN KEY (parent_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_sec_sec_sec_config_child FOREIGN KEY (child_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_sec_struct`
--
ALTER TABLE `rel_sec_struct`
  ADD CONSTRAINT fk_rel_sec_struct_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_sec_struct_sec_struct FOREIGN KEY (sec_struct_id) REFERENCES sec_struct (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_sec_template`
--
ALTER TABLE `rel_sec_template`
  ADD CONSTRAINT fk_rel_sec_tpl_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_sec_tpl_sec_template FOREIGN KEY (sys_template_id) REFERENCES sys_template (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_sec_tpl_sec_template_type FOREIGN KEY (sys_template_type_id) REFERENCES sys_template_type (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `rel_translate_language`
--
ALTER TABLE `rel_translate_language`
  ADD CONSTRAINT fk_rel_translate_language_ctn_translate FOREIGN KEY (ctn_translate_id) REFERENCES ctn_translate (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rel_translate_language_sys_language FOREIGN KEY (sys_language_id) REFERENCES sys_language (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sec_config`
--
ALTER TABLE `sec_config`
  ADD CONSTRAINT fk_sec_config_sec_config FOREIGN KEY (parent) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_sec_config_sys_folder FOREIGN KEY (sys_folder_id) REFERENCES sys_folder (id) ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_folder`
--
ALTER TABLE `sys_folder`
  ADD CONSTRAINT fk_sys_folder_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_hotspot`
--
ALTER TABLE `sys_hotspot`
  ADD CONSTRAINT fk_sys_hotspot_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_sys_hotspot_sys_template FOREIGN KEY (sys_template_id) REFERENCES sys_template (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_log`
--
ALTER TABLE `sys_log`
  ADD CONSTRAINT fk_sys_log_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_sys_log_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_mailing`
--
ALTER TABLE `sys_mailing`
  ADD CONSTRAINT fk_sys_mailing_sys_language FOREIGN KEY (sys_language_id) REFERENCES sys_language (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_poll`
--
ALTER TABLE `sys_poll`
  ADD CONSTRAINT fk_sys_poll_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_poll_answer`
--
ALTER TABLE `sys_poll_answer`
  ADD CONSTRAINT fk_sys_poll_answer_sys_poll FOREIGN KEY (sys_poll_id) REFERENCES sys_poll (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_poll_result`
--
ALTER TABLE `sys_poll_result`
  ADD CONSTRAINT fk_sys_poll_result_sys_poll_answer FOREIGN KEY (sys_poll_answer_id, sys_poll_id) REFERENCES sys_poll_answer (id, sys_poll_id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_quiz`
--
ALTER TABLE `sys_quiz`
  ADD CONSTRAINT fk_sys_quiz_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_quiz_answer`
--
ALTER TABLE `sys_quiz_answer`
  ADD CONSTRAINT fk_sys_quiz_answer_sys_quiz_question FOREIGN KEY (sys_quiz_question_id) REFERENCES sys_quiz_question (id);

--
-- Restrições para a tabela `sys_quiz_question`
--
ALTER TABLE `sys_quiz_question`
  ADD CONSTRAINT fk_sys_quiz_question_sys_quiz FOREIGN KEY (sys_quiz_id) REFERENCES sys_quiz (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_quiz_result`
--
ALTER TABLE `sys_quiz_result`
  ADD CONSTRAINT fk_sys_quiz_answer_sys_quiz_answer FOREIGN KEY (sys_quiz_answer_id) REFERENCES sys_quiz_answer (id);

--
-- Restrições para a tabela `sys_search`
--
ALTER TABLE `sys_search`
  ADD CONSTRAINT fk_sys_search_sys_language FOREIGN KEY (sys_language_id) REFERENCES sys_language (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_share`
--
ALTER TABLE `sys_share`
  ADD CONSTRAINT fk_sys_share_sys_language FOREIGN KEY (sys_language_id) REFERENCES sys_language (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_sys_share_sec_config FOREIGN KEY (sec_config_id) REFERENCES sec_config (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `sys_template`
--
ALTER TABLE `sys_template`
  ADD CONSTRAINT fk_sys_template_usr_data FOREIGN KEY (usr_data_id) REFERENCES usr_data (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `usr_data`
--
ALTER TABLE `usr_data`
  ADD CONSTRAINT fk_usr_data_usr_profile FOREIGN KEY (usr_profile_id) REFERENCES usr_profile (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_usr_data_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
