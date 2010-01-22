--
-- Table structure for table `ext_calendar_event`
--

CREATE TABLE `ext_calendar_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL DEFAULT '0',
  `date_update` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user_create` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `id_project` smallint(6) NOT NULL DEFAULT '0',
  `id_task` int(10) unsigned NOT NULL,
  `eventtype` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` text,
  `place` varchar(255) NOT NULL,
  `date_start` int(10) unsigned NOT NULL DEFAULT '0',
  `date_end` int(10) unsigned NOT NULL DEFAULT '0',
  `is_private` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_dayevent` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_public` tinyint(1) unsigned NOT NULL DEFAULT '0'
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ext_calendar_holiday`
--

CREATE TABLE `ext_calendar_holiday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL DEFAULT '0',
  `date_update` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user_create` smallint(5) unsigned NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `title` varchar(48) NOT NULL,
  `description` varchar(256) NOT NULL,
  `workingtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ext_calendar_holidayset`
--

CREATE TABLE `ext_calendar_holidayset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL DEFAULT '0',
  `date_update` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user_create` smallint(5) unsigned NOT NULL,
  `deleted` tinyint(2) NOT NULL,
  `title` varchar(32) NOT NULL,
  `description` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ext_calendar_mm_holiday_holidayset`
--

CREATE TABLE `ext_calendar_mm_holiday_holidayset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_holiday` int(10) unsigned NOT NULL DEFAULT '0',
  `id_holidayset` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `holiday` (`id_holiday`),
  KEY `holidayset` (`id_holidayset`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ext_calendar_mm_event_user`
--

CREATE TABLE `ext_calendar_mm_event_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_event` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user` int(10) unsigned NOT NULL DEFAULT '0',
  `is_acknowledged` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid_local` (`id_event`),
  KEY `uid_foreign` (`id_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
