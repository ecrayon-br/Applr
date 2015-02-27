# Host: localhost  (Version: 5.5.32)
# Date: 2015-02-25 16:34:15
# Generator: MySQL-Front 5.3  (Build 4.198)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "client"
#

CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `city` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `state` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `country` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `contact` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `phone` bigint(15) DEFAULT NULL,
  `fax` bigint(15) DEFAULT NULL,
  `email` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `website` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "client"
#

INSERT INTO `client` VALUES (1,'ECRAYON','Rua Ibimirim, 404','São Paulo','São Paulo','Brazil','Diego',11992833477,NULL,'diego.flores@ecrayon.com.br','http://www.ecrayon.com.br','2013-01-05 00:00:00',1);

#
# Structure for table "ctn_translate"
#

CREATE TABLE `ctn_translate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `var` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "ctn_translate"
#


#
# Structure for table "project"
#

CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `description` text COLLATE utf8_bin,
  `logo_upload` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`,`client_id`),
  KEY `fk_project_client` (`client_id`),
  CONSTRAINT `fk_project_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "project"
#

INSERT INTO `project` VALUES (1,1,'Applr','Applr desc','site/sys/upload/20130211235414_e.jpg','2013-01-05 00:00:00',1);

#
# Structure for table "config"
#

CREATE TABLE `config` (
  `project_id` int(11) NOT NULL,
  `dir_upload` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `dir_image` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `dir_video` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `dir_template` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `dir_static` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `dir_dynamic` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `dir_xml` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `dir_rss` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `mail_auth` tinyint(1) DEFAULT '0',
  `mail_auth_host` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `mail_auth_user` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `mail_auth_password` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `mail_sys` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `mail_public` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `mail_contact` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `mail_user` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `paging_limit` int(3) DEFAULT NULL,
  `rss_group` tinyint(1) DEFAULT NULL,
  `rss_limit` int(3) DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  CONSTRAINT `fk_config_project` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "config"
#

INSERT INTO `config` VALUES (1,'site/sys/upload','site/sys/image','site/sys/video','site/sys/template','site/sys/static','site/sys/dynamic','site/sys/xml','site/sys/rss',1,'host','username','password','contato@ecrayon.com.br','contato@ecrayon.com.br','contato@ecrayon.com.br','contato@ecrayon.com.br',5,1,10);

#
# Structure for table "sec_struct"
#

CREATE TABLE `sec_struct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `html` text COLLATE utf8_bin,
  `suffix` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `fieldtype` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `length` int(11) DEFAULT NULL,
  `is_unsigned` tinyint(1) DEFAULT NULL,
  `notnull` tinyint(1) DEFAULT NULL,
  `default_value` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `extra_field` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sec_struct"
#

INSERT INTO `sec_struct` VALUES (1,'TEXT: Text Line','&lt;input type=&quot;text&quot; name=&quot;&quot; id=&quot;&quot; value=&quot;&quot; size=&quot;70&quot; maxlength=&quot;255&quot;&gt;',NULL,'text',255,1,0,NULL,0,1),(2,'TEXT: Text Field','&lt;textarea name=&quot;&quot; id=&quot;&quot; cols=&quot;100&quot; rows=&quot;25&quot;&gt;&lt;/textarea&gt;',NULL,'text',0,1,0,NULL,0,1),(3,'TEXT: Rich Text','&lt;textarea name=&quot;&quot; id=&quot;&quot; cols=&quot;70&quot; rows=&quot;14&quot;&gt;&lt;/textarea&gt;','richtext','text',0,1,0,NULL,0,1),(4,'NUMERIC: Integer','&lt;input type=&quot;text&quot; name=&quot;&quot; id=&quot;&quot; value=&quot;&quot; size=&quot;10&quot; maxlength=&quot;11&quot; onKeyPress=&quot;onlyNumber(this)&quot;&gt;','int','integer',11,1,0,'0',0,1),(5,'PERSONAL INFO: E-mail','&lt;input type=&quot;text&quot; name=&quot;&quot; id=&quot;&quot; value=&quot;&quot; size=&quot;70&quot; maxlength=&quot;255&quot; onBlur=&quot;&quot;&gt;','mail','text',255,1,0,NULL,0,1),(6,'PERSONAL INFO: Telephone','&lt;input type=&quot;text&quot; name=&quot;_ddd&quot; id=&quot;_ddd&quot; value=&quot;&quot; size=&quot;5&quot; maxlength=&quot;2&quot;&gt; &lt;input type=&quot;text&quot; name=&quot;&quot; id=&quot;&quot; value=&quot;&quot; size=&quot;10&quot; maxlength=&quot;9&quot;&gt;','phone','integer',10,1,0,NULL,0,1),(7,'PERSONAL INFO: Gender','&lt;input type=&quot;radio&quot; value=&quot;1&quot; name=&quot;&quot; id=&quot;_1&quot; /&gt; Male&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;0&quot; name=&quot;&quot; id=&quot;_0&quot; /&gt; Female','sex','integer',1,1,1,'0',0,1),(8,'MULTIMEDIA: Upload File','&lt;input type=&quot;file&quot; name=&quot;&quot; id=&quot;&quot; size=&quot;40&quot; maxlength=&quot;255&quot;&gt;\r\n&lt;input type=&quot;hidden&quot;name=&quot;_old&quot; id=&quot;_old&quot; value=&quot;&quot;&gt;\r\n&lt;div style=&quot;display: inline-block; cursor: pointer;&quot; class=&quot;upload-control-delete&quot;&gt;[ Excluir ]&lt;/div&gt;\r\n&lt;div style=&quot;display: inline-block; cursor: pointer;&quot; class=&quot;upload-control-undo&quot;&gt;[ Desfazer ]&lt;/div&gt;\r\n&lt;div class=&quot;upload-data&quot;&gt;\r\n&lt;img src=&quot;#content#&quot; style=&quot;max-width: 400px&quot; /&gt;&lt;/div&gt;','upload','text',255,1,0,NULL,0,1),(9,'CONDITIONAL: Yes / No','&lt;input type=&quot;radio&quot; value=&quot;1&quot; name=&quot;&quot; id=&quot;_1&quot; /&gt; Yes&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;0&quot; name=&quot;&quot; id=&quot;_0&quot; /&gt; No','bool','integer',1,1,1,'0',0,1),(10,'DATE: Date','{html_select_date prefix=&quot;&quot; start_year=&quot;-5&quot; end_year=&quot;+5&quot; day_value_format=&quot;%02d&quot; month_value_format=&quot;%m&quot; field_order=&quot;DMY&quot; year_empty=&quot;YEAR&quot; month_empty=&quot;MONTH&quot; day_empty=&quot;DAY&quot;} &lt;input type=&quot;button&quot; value=&quot;Set Empty&quot; onclick=&quot;setDateEmpty(&#039;#name#&#039;);&quot; /&gt;\n\n','date','date',0,1,0,NULL,0,1),(11,'DATE: Time','{html_select_time prefix=&quot;_&quot; field_order=&quot;HMS&quot; hour_empty=&quot;HOUR&quot; minute_empty=&quot;MIN&quot; second_empty=&quot;SEC&quot;} &lt;input type=&quot;button&quot; value=&quot;Set Empty&quot; onclick=&quot;setDateEmpty(&#039;#name#&#039;);&quot; /&gt;','time','time',0,1,0,NULL,0,1),(12,'TEXT: Password','&lt;input type=&quot;password&quot; name=&quot;&quot; id=&quot;&quot; value=&quot;&quot; size=&quot;32&quot; maxlength=&quot;32&quot;&gt; &lt;input type=&quot;checkbox&quot; id=&quot;_toggle&quot; onchange=&quot;togglePwd(&#039;#name#&#039;)&quot; /&gt; &lt;label for=&quot;_toggle&quot;&gt;Show characters&lt;/label&gt;','pwd','text',32,1,0,NULL,0,1),(13,'CONDITIONAL: Checkbox','&lt;input type=&quot;checkbox&quot; value=&quot;1&quot; name=&quot;&quot; id=&quot;_1&quot; /&gt;','check','boolean',0,1,0,NULL,0,1),(14,'PERSONAL INFO: Treatment Title','&lt;input type=&quot;radio&quot; value=&quot;0&quot; name=&quot;&quot; id=&quot;_0&quot; /&gt; Sr.&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;1&quot; name=&quot;&quot; id=&quot;_1&quot; /&gt; Mr.&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;2&quot; name=&quot;&quot; id=&quot;_2&quot; /&gt; Mrs.&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;3&quot; name=&quot;&quot; id=&quot;_3&quot; /&gt; Mss.','title','integer',1,1,0,NULL,0,1),(15,'PERSONAL INFO: Day Period','&lt;input type=&quot;radio&quot; value=&quot;0&quot; name=&quot;&quot; id=&quot;_0&quot; /&gt; Morning&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;1&quot; name=&quot;&quot; id=&quot;_1&quot; /&gt; Afternoon&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;2&quot; name=&quot;&quot; id=&quot;_2&quot; /&gt; Night&lt;br&gt;','period','integer',1,1,0,NULL,0,1),(16,'NUMERIC: Double','&lt;input type=&quot;text&quot; name=&quot;&quot; id=&quot;&quot; value=&quot;&quot; size=&quot;10&quot; maxlength=&quot;11&quot; onKeyPress=&quot;onlyNumber(this)&quot;&gt;',NULL,'float',0,1,0,NULL,0,1),(17,'NUMERIC: Float','&lt;input type=&quot;text&quot; name=&quot;&quot; id=&quot;&quot; value=&quot;&quot; size=&quot;10&quot; maxlength=&quot;11&quot; onKeyPress=&quot;onlyNumber(this)&quot;&gt;',NULL,'decimal',0,1,0,NULL,0,1),(18,'PERSONAL INFO: Currency','&lt;input type=&quot;radio&quot; value=&quot;0&quot; name=&quot;&quot; id=&quot;_0&quot; /&gt; R$&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;1&quot; name=&quot;&quot; id=&quot;_1&quot; /&gt; USD&lt;br&gt;\r\n&lt;input type=&quot;radio&quot; value=&quot;2&quot; name=&quot;&quot; id=&quot;_2&quot; /&gt; EUR&lt;br&gt;','currency','integer',1,1,0,NULL,0,1),(19,'PERSONAL INFO: Zip Code','&lt;input type=&quot;text&quot; name=&quot;&quot; id=&quot;&quot; value=&quot;&quot; size=&quot;9&quot; maxlength=&quot;9&quot; onKeyPress=&quot;onlyNumber(this)&quot;&gt;','zipcode','integer',9,0,0,NULL,0,1),(20,'MULTIMEDIA: Image From Gallery','&lt;input type=&quot;hidden&quot;name=&quot;&quot; id=&quot;&quot; value=&quot;&quot;&gt;\r\n&lt;div style=&quot;display: inline-block; cursor: pointer;&quot; class=&quot;media-control-select&quot;&gt;[ Selecionar ]&lt;/div&gt;\r\n&lt;div style=&quot;display: inline-block; cursor: pointer;&quot; class=&quot;media-control-delete&quot;&gt;[ Excluir ]&lt;/div&gt;\r\n&lt;div style=&quot;display: inline-block; cursor: pointer;&quot; class=&quot;media-control-undo&quot;&gt;[ Desfazer ]&lt;/div&gt;\r\n&lt;div class=&quot;media-data&quot;&gt;&lt;img src=&quot;#content#&quot; style=&quot;max-width: 400px&quot; /&gt;&lt;/div&gt;','img','text',255,1,0,NULL,1,1);

#
# Structure for table "sys_language"
#

CREATE TABLE `sys_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `acronym` varchar(5) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_language"
#

INSERT INTO `sys_language` VALUES (1,'English','en',1),(2,'Portugu&ecirc;s','pt_BR',1),(3,'Espa&ntilde;ol','es',1);

#
# Structure for table "rel_translate_language"
#

CREATE TABLE `rel_translate_language` (
  `ctn_translate_id` int(11) NOT NULL,
  `sys_language_id` int(11) NOT NULL,
  `content` text COLLATE utf8_bin,
  PRIMARY KEY (`ctn_translate_id`,`sys_language_id`),
  KEY `fk_rel_translate_language_ctn_translate` (`ctn_translate_id`),
  KEY `fk_rel_translate_language_sys_language` (`sys_language_id`),
  CONSTRAINT `fk_rel_translate_language_ctn_translate` FOREIGN KEY (`ctn_translate_id`) REFERENCES `ctn_translate` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_translate_language_sys_language` FOREIGN KEY (`sys_language_id`) REFERENCES `sys_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_translate_language"
#


#
# Structure for table "sys_mailing"
#

CREATE TABLE `sys_mailing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sys_language_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `email` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_sys_mailing_sys_language` (`sys_language_id`),
  CONSTRAINT `fk_sys_mailing_sys_language` FOREIGN KEY (`sys_language_id`) REFERENCES `sys_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_mailing"
#


#
# Structure for table "sys_search"
#

CREATE TABLE `sys_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sys_language_id` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `term` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `results` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sys_search_sys_language` (`sys_language_id`),
  CONSTRAINT `fk_sys_search_sys_language` FOREIGN KEY (`sys_language_id`) REFERENCES `sys_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_search"
#


#
# Structure for table "sys_sec_type"
#

CREATE TABLE `sys_sec_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `prefix` varchar(7) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

#
# Data for table "sys_sec_type"
#

INSERT INTO `sys_sec_type` VALUES (1,'Common Content','data',1),(2,'Admin Sys','adm_sys',1),(3,'AE Total : Lançamento','aet_fl',1);

#
# Structure for table "sys_template_type"
#

CREATE TABLE `sys_template_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_template_type"
#

INSERT INTO `sys_template_type` VALUES (1,'Content',1),(2,'List',1),(3,'Home',1);

#
# Structure for table "media_gallery"
#

CREATE TABLE `media_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_data_id` int(11) NOT NULL,
  `sec_config_id` int(11) DEFAULT '0',
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `mediatype` tinyint(3) NOT NULL DEFAULT '2',
  `description` text COLLATE utf8_bin,
  `dirpath` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '1',
  `autothumb` tinyint(1) DEFAULT '0',
  `autothumb_h` int(11) DEFAULT NULL,
  `autothumb_w` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_media_gallery_usr_data` (`usr_data_id`),
  KEY `fk_media_gallery_sec_config` (`sec_config_id`),
  CONSTRAINT `fk_media_gallery_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "media_gallery"
#

INSERT INTO `media_gallery` VALUES (1,1,NULL,0,'Common',2,NULL,'site/sys/image/common',1,0,NULL,NULL,1),(2,1,0,0,'Common',1,NULL,'site/sys/video/common',1,0,NULL,NULL,1),(3,1,0,0,'Common',0,NULL,'site/sys/upload/common',1,0,NULL,NULL,1);

#
# Structure for table "usr_profile"
#

CREATE TABLE `usr_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "usr_profile"
#


#
# Structure for table "usr_data"
#

CREATE TABLE `usr_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `usr_profile_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `email` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `authcode` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `authcode_true` tinyint(1) DEFAULT NULL,
  `username` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `password` char(32) COLLATE utf8_bin DEFAULT NULL,
  `expires` timestamp NULL DEFAULT NULL,
  `admin` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_usr_data_project` (`project_id`),
  KEY `fk_usr_data_usr_profile` (`usr_profile_id`),
  CONSTRAINT `fk_usr_data_project` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_usr_data_usr_profile` FOREIGN KEY (`usr_profile_id`) REFERENCES `usr_profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "usr_data"
#

INSERT INTO `usr_data` VALUES (1,1,1,'Diego','diego.flores@ecrayon.com.br',NULL,NULL,'ecrayon','ecrayon',NULL,1,0);

#
# Structure for table "media_data"
#

CREATE TABLE `media_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_gallery_id` int(11) NOT NULL,
  `usr_data_id` int(11) NOT NULL,
  `mediatype` tinyint(1) NOT NULL DEFAULT '2',
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `author` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `filepath` text COLLATE utf8_bin,
  `filepath_thumbnail` text COLLATE utf8_bin,
  `filepath_streaming` text COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `fk_img_data_img_gallery` (`media_gallery_id`),
  KEY `fk_img_data_usr_data` (`usr_data_id`),
  CONSTRAINT `fk_img_data_img_gallery` FOREIGN KEY (`media_gallery_id`) REFERENCES `media_gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_img_data_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "media_data"
#


#
# Structure for table "sys_folder"
#

CREATE TABLE `sys_folder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_data_id` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `icon_filepath` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `sys_filepath` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_sys_folder_usr_data` (`usr_data_id`),
  CONSTRAINT `fk_sys_folder_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_folder"
#

INSERT INTO `sys_folder` VALUES (1,1,'AE Total : Lan&ccedil;amento',NULL,NULL,1);

#
# Structure for table "sec_config"
#

CREATE TABLE `sec_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT '0',
  `sys_folder_id` int(11) DEFAULT '0',
  `sys_sec_type_id` int(11) DEFAULT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `permalink` varchar(60) COLLATE utf8_bin DEFAULT NULL,
  `table_name` varchar(60) COLLATE utf8_bin DEFAULT NULL,
  `website` tinyint(1) DEFAULT '1',
  `user_edit` tinyint(1) DEFAULT '0',
  `public` tinyint(1) DEFAULT '1',
  `home` tinyint(1) DEFAULT '1',
  `static` tinyint(1) DEFAULT '0',
  `static_filename` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `xml` tinyint(1) DEFAULT '0',
  `xml_items` tinyint(3) DEFAULT NULL,
  `rss` tinyint(1) DEFAULT '0',
  `rss_items` tinyint(3) DEFAULT NULL,
  `list_items` tinyint(3) DEFAULT NULL,
  `orderby` varchar(255) COLLATE utf8_bin DEFAULT 'id ASC',
  `search` tinyint(1) DEFAULT '1',
  `autothumb` tinyint(1) DEFAULT '0',
  `autothumb_h` tinyint(3) DEFAULT NULL,
  `autothumb_w` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sec_config_sec_config` (`parent`),
  KEY `fk_sec_config_sys_folder` (`sys_folder_id`),
  CONSTRAINT `fk_sec_config_sec_config` FOREIGN KEY (`parent`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sec_config_sys_folder` FOREIGN KEY (`sys_folder_id`) REFERENCES `sys_folder` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sec_config"
#


#
# Structure for table "perm_user"
#

CREATE TABLE `perm_user` (
  `usr_data_id` int(11) NOT NULL,
  `sec_config_id` int(11) NOT NULL,
  `view` tinyint(1) DEFAULT NULL,
  `insert` tinyint(1) DEFAULT NULL,
  `update` tinyint(1) DEFAULT NULL,
  `delete` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`usr_data_id`,`sec_config_id`),
  KEY `fk_perm_user_sec_config` (`sec_config_id`),
  KEY `fk_perm_user_usr_data` (`usr_data_id`),
  CONSTRAINT `fk_perm_user_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_perm_user_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "perm_user"
#


#
# Structure for table "perm_user_content"
#

CREATE TABLE `perm_user_content` (
  `usr_data_id` int(11) NOT NULL,
  `sec_config_id` int(11) NOT NULL,
  `sys_language_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  PRIMARY KEY (`usr_data_id`,`sec_config_id`,`sys_language_id`,`content_id`),
  KEY `fk_perm_user_content_perm_user` (`usr_data_id`,`sec_config_id`),
  KEY `fk_perm_user_content_sys_language` (`sys_language_id`),
  CONSTRAINT `fk_perm_user_content_perm_user` FOREIGN KEY (`usr_data_id`, `sec_config_id`) REFERENCES `perm_user` (`usr_data_id`, `sec_config_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_perm_user_content_sys_language` FOREIGN KEY (`sys_language_id`) REFERENCES `sys_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "perm_user_content"
#


#
# Structure for table "rel_sec_struct"
#

CREATE TABLE `rel_sec_struct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sec_config_id` int(11) NOT NULL,
  `sec_struct_id` int(11) NOT NULL,
  `field_name` varchar(65) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `tooltip` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `mandatory` tinyint(1) DEFAULT '0',
  `admin` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`,`sec_config_id`),
  KEY `fk_rel_sec_struct_sec_config` (`sec_config_id`),
  KEY `fk_rel_sec_struct_sec_struct` (`sec_struct_id`),
  CONSTRAINT `fk_rel_sec_struct_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_sec_struct_sec_struct` FOREIGN KEY (`sec_struct_id`) REFERENCES `sec_struct` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=200012 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_sec_struct"
#


#
# Structure for table "sec_config_order"
#

CREATE TABLE `sec_config_order` (
  `field_id` int(11) NOT NULL,
  `sec_config_id` int(11) NOT NULL DEFAULT '0',
  `field_order` tinyint(3) NOT NULL DEFAULT '0',
  `type` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`field_id`),
  KEY `fk_sec_config_order_sec_config` (`sec_config_id`),
  CONSTRAINT `fk_sec_config_order_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sec_config_order"
#


#
# Structure for table "rel_sec_sec"
#

CREATE TABLE `rel_sec_sec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sec_config_id` int(11) NOT NULL DEFAULT '0',
  `child_id` int(11) NOT NULL,
  `field_rel` varchar(65) COLLATE utf8_bin DEFAULT NULL,
  `fieldtype` tinyint(1) DEFAULT NULL,
  `field_name` varchar(65) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `tooltip` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `mandatory` tinyint(1) DEFAULT '0',
  `admin` tinyint(1) DEFAULT '0',
  `table_name` varchar(130) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`,`sec_config_id`,`child_id`),
  KEY `fk_rel_sec_sec_sec_config_child` (`child_id`),
  KEY `fk_rel_sec_sec_sec_config_parent` (`sec_config_id`),
  CONSTRAINT `fk_rel_sec_sec_sec_config_child` FOREIGN KEY (`child_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_sec_sec_sec_config_parent` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=200012 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_sec_sec"
#


#
# Structure for table "rel_sec_language"
#

CREATE TABLE `rel_sec_language` (
  `sys_language_id` int(11) NOT NULL,
  `sec_config_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`sys_language_id`,`sec_config_id`),
  KEY `fk_rel_sec_language_sys_language` (`sys_language_id`),
  KEY `fk_rel_sec_language_sec_config` (`sec_config_id`),
  CONSTRAINT `fk_rel_sec_language_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_sec_language_sys_language` FOREIGN KEY (`sys_language_id`) REFERENCES `sys_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_sec_language"
#


#
# Structure for table "sys_log"
#

CREATE TABLE `sys_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_data_id` int(11) NOT NULL,
  `sec_config_id` int(11) DEFAULT NULL,
  `action` enum('inseriu','alterou','deletou','deletou permanentemente') COLLATE utf8_bin DEFAULT NULL,
  `object` enum('o registro','a seção','o usuário') COLLATE utf8_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `content` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sys_log_sec_config` (`sec_config_id`),
  KEY `fk_sys_log_usr_data` (`usr_data_id`),
  CONSTRAINT `fk_sys_log_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sys_log_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_log"
#


#
# Structure for table "sys_share"
#

CREATE TABLE `sys_share` (
  `sec_config_id` int(11) NOT NULL,
  `sys_language_id` int(11) NOT NULL,
  `content` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `from_name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `from_email` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `to_name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `to_email` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `text` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`content`,`sec_config_id`,`sys_language_id`),
  KEY `fk_sys_share_sys_language` (`sys_language_id`),
  KEY `fk_sys_share_sec_config` (`sec_config_id`),
  CONSTRAINT `fk_sys_share_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sys_share_sys_language` FOREIGN KEY (`sys_language_id`) REFERENCES `sys_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_share"
#


#
# Structure for table "sys_poll"
#

CREATE TABLE `sys_poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_data_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `description` text COLLATE utf8_bin,
  `orderby` tinyint(2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_sys_poll_usr_data` (`usr_data_id`),
  CONSTRAINT `fk_sys_poll_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_poll"
#


#
# Structure for table "sys_poll_answer"
#

CREATE TABLE `sys_poll_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sys_poll_id` int(11) NOT NULL,
  `label` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `filepath` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `filepath_thumbnail` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `ordertag` tinyint(2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`,`sys_poll_id`),
  KEY `fk_sys_poll_answer_sys_poll` (`sys_poll_id`),
  CONSTRAINT `fk_sys_poll_answer_sys_poll` FOREIGN KEY (`sys_poll_id`) REFERENCES `sys_poll` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_poll_answer"
#


#
# Structure for table "sys_poll_result"
#

CREATE TABLE `sys_poll_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sys_poll_id` int(11) NOT NULL,
  `sys_poll_answer_id` int(11) NOT NULL,
  `user_ip` varchar(15) COLLATE utf8_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`sys_poll_id`,`sys_poll_answer_id`),
  KEY `fk_sys_poll_result_sys_poll_answer` (`sys_poll_answer_id`,`sys_poll_id`),
  CONSTRAINT `fk_sys_poll_result_sys_poll_answer` FOREIGN KEY (`sys_poll_answer_id`, `sys_poll_id`) REFERENCES `sys_poll_answer` (`id`, `sys_poll_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_poll_result"
#


#
# Structure for table "sys_quiz"
#

CREATE TABLE `sys_quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_data_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `description` text COLLATE utf8_bin,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_sys_quiz_usr_data` (`usr_data_id`),
  CONSTRAINT `fk_sys_quiz_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_quiz"
#


#
# Structure for table "sys_quiz_question"
#

CREATE TABLE `sys_quiz_question` (
  `sys_quiz_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `description` text COLLATE utf8_bin,
  `filepath` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `filepath_thumbnail` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `ordertag` tinyint(2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`,`sys_quiz_id`),
  KEY `fk_sys_quiz_question_sys_quiz` (`sys_quiz_id`),
  CONSTRAINT `fk_sys_quiz_question_sys_quiz` FOREIGN KEY (`sys_quiz_id`) REFERENCES `sys_quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_quiz_question"
#


#
# Structure for table "sys_quiz_answer"
#

CREATE TABLE `sys_quiz_answer` (
  `sys_quiz_question_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `filepath` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `filepath_thumbnail` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `ordertag` tinyint(2) DEFAULT NULL,
  `result_value` tinyint(3) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`,`sys_quiz_question_id`),
  KEY `fk_sys_quiz_answer_sys_quiz_question` (`sys_quiz_question_id`),
  CONSTRAINT `fk_sys_quiz_answer_sys_quiz_question` FOREIGN KEY (`sys_quiz_question_id`) REFERENCES `sys_quiz_question` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_quiz_answer"
#


#
# Structure for table "sys_quiz_result"
#

CREATE TABLE `sys_quiz_result` (
  `sys_quiz_answer_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_ip` varchar(15) COLLATE utf8_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`sys_quiz_answer_id`),
  KEY `fk_sys_quiz_answer_sys_quiz_answer` (`sys_quiz_answer_id`),
  CONSTRAINT `fk_sys_quiz_answer_sys_quiz_answer` FOREIGN KEY (`sys_quiz_answer_id`) REFERENCES `sys_quiz_answer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_quiz_result"
#


#
# Structure for table "sys_template"
#

CREATE TABLE `sys_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_data_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_sys_template_usr_data` (`usr_data_id`),
  CONSTRAINT `fk_sys_template_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_template"
#

INSERT INTO `sys_template` VALUES (1,1,'Home','index.html',1);

#
# Structure for table "rel_sec_template"
#

CREATE TABLE `rel_sec_template` (
  `sec_config_id` int(11) NOT NULL,
  `sys_template_id` int(11) NOT NULL,
  `sys_template_type_id` int(11) NOT NULL,
  PRIMARY KEY (`sec_config_id`,`sys_template_id`,`sys_template_type_id`),
  KEY `fk_rel_sec_tpl_sec_config` (`sec_config_id`),
  KEY `fk_rel_sec_tpl_sec_template` (`sys_template_id`),
  KEY `fk_rel_sec_tpl_sec_template_type` (`sys_template_type_id`),
  CONSTRAINT `fk_rel_sec_tpl_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_sec_tpl_sec_template` FOREIGN KEY (`sys_template_id`) REFERENCES `sys_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_sec_tpl_sec_template_type` FOREIGN KEY (`sys_template_type_id`) REFERENCES `sys_template_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_sec_template"
#


#
# Structure for table "sys_hotspot"
#

CREATE TABLE `sys_hotspot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_data_id` int(11) NOT NULL,
  `sys_template_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `description` text COLLATE utf8_bin,
  `var` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `orderby` tinyint(2) DEFAULT NULL,
  `limit_content` int(11) DEFAULT NULL,
  `limit_view` int(11) DEFAULT NULL,
  `autopost` tinyint(1) DEFAULT '1',
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_sys_hotspot_usr_data` (`usr_data_id`),
  KEY `fk_sys_hotspot_sys_template` (`sys_template_id`),
  CONSTRAINT `fk_sys_hotspot_sys_template` FOREIGN KEY (`sys_template_id`) REFERENCES `sys_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sys_hotspot_usr_data` FOREIGN KEY (`usr_data_id`) REFERENCES `usr_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "sys_hotspot"
#


#
# Structure for table "rel_hotspot_content"
#

CREATE TABLE `rel_hotspot_content` (
  `sys_hotspot_id` int(11) NOT NULL,
  `sec_config_id` int(11) NOT NULL,
  `content` int(11) NOT NULL,
  PRIMARY KEY (`sys_hotspot_id`,`sec_config_id`,`content`),
  KEY `fk_rel_hotspot_content_sec_config` (`sec_config_id`),
  KEY `fk_rel_hotspot_content_sys_hotspot` (`sys_hotspot_id`),
  CONSTRAINT `fk_rel_hotspot_content_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_hotspot_content_sys_hotspot` FOREIGN KEY (`sys_hotspot_id`) REFERENCES `sys_hotspot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_hotspot_content"
#


#
# Structure for table "rel_hotspot_sec"
#

CREATE TABLE `rel_hotspot_sec` (
  `sys_hotspot_id` int(11) NOT NULL,
  `sec_config_id` int(11) NOT NULL,
  `sys_language_id` int(11) NOT NULL,
  `type` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`sys_hotspot_id`,`sec_config_id`,`sys_language_id`),
  KEY `fk_rel_sys_hotspot_sys_hotspot` (`sys_hotspot_id`),
  KEY `fk_rel_sys_hotspot_sys_language` (`sys_language_id`),
  KEY `fk_rel_hotspot_sec_sec_config` (`sec_config_id`),
  CONSTRAINT `fk_rel_hotspot_sec_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_sys_hotspot_sys_hotspot` FOREIGN KEY (`sys_hotspot_id`) REFERENCES `sys_hotspot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_sys_hotspot_sys_language` FOREIGN KEY (`sys_language_id`) REFERENCES `sys_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_hotspot_sec"
#


#
# Structure for table "rel_poll_template"
#

CREATE TABLE `rel_poll_template` (
  `sys_poll_id` int(11) NOT NULL,
  `sys_template_id` int(11) NOT NULL,
  `sys_template_type_id` int(11) NOT NULL,
  PRIMARY KEY (`sys_poll_id`,`sys_template_id`,`sys_template_type_id`),
  KEY `fk_rel_poll_template_sys_poll` (`sys_poll_id`),
  KEY `fk_rel_poll_template_sys_template` (`sys_template_id`),
  KEY `fk_rel_poll_template_sys_template_type` (`sys_template_type_id`),
  CONSTRAINT `fk_rel_poll_template_sys_poll` FOREIGN KEY (`sys_poll_id`) REFERENCES `sys_poll` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_poll_template_sys_template` FOREIGN KEY (`sys_template_id`) REFERENCES `sys_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_poll_template_sys_template_type` FOREIGN KEY (`sys_template_type_id`) REFERENCES `sys_template_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_poll_template"
#


#
# Structure for table "rel_quiz_template"
#

CREATE TABLE `rel_quiz_template` (
  `sys_quiz_id` int(11) NOT NULL,
  `sys_template_id` int(11) NOT NULL,
  `sys_template_type_id` int(11) NOT NULL,
  PRIMARY KEY (`sys_quiz_id`,`sys_template_id`,`sys_template_type_id`),
  KEY `fk_rel_quiz_template_sys_quiz` (`sys_quiz_id`),
  KEY `fk_rel_quiz_template_sys_template` (`sys_template_id`),
  KEY `fk_rel_quiz_template_sys_template_type` (`sys_template_type_id`),
  CONSTRAINT `fk_rel_quiz_template_sys_quiz` FOREIGN KEY (`sys_quiz_id`) REFERENCES `sys_quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_quiz_template_sys_template` FOREIGN KEY (`sys_template_id`) REFERENCES `sys_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rel_quiz_template_sys_template_type` FOREIGN KEY (`sys_template_type_id`) REFERENCES `sys_template_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "rel_quiz_template"
#


#
# Structure for table "perm_profile"
#

CREATE TABLE `perm_profile` (
  `usr_profile_id` int(11) NOT NULL,
  `sec_config_id` int(11) NOT NULL,
  `view` tinyint(1) DEFAULT NULL,
  `insert` tinyint(1) DEFAULT NULL,
  `update` tinyint(1) DEFAULT NULL,
  `delete` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`usr_profile_id`,`sec_config_id`),
  KEY `fk_perm_profile_sec_config` (`sec_config_id`),
  KEY `fk_perm_profile_usr_profile` (`usr_profile_id`),
  CONSTRAINT `fk_perm_profile_sec_config` FOREIGN KEY (`sec_config_id`) REFERENCES `sec_config` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_perm_profile_usr_profile` FOREIGN KEY (`usr_profile_id`) REFERENCES `usr_profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Data for table "perm_profile"
#

