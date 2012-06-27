-- --------------------------------------------------------

-- 
-- Structure de la table `module_newsletter`
-- 

CREATE TABLE `module_newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter_name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `newsletter_from` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `newsletter_from_email` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `newsletter_description` mediumtext collate utf8_unicode_ci NOT NULL,
  `subscribe_warning` mediumtext collate utf8_unicode_ci NOT NULL,
  `subscribe_error` mediumtext collate utf8_unicode_ci NOT NULL,
  `subscribe_thanx` mediumtext collate utf8_unicode_ci NOT NULL,
  `subscribe_subject` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `subscribe_text` mediumtext collate utf8_unicode_ci NOT NULL,
  `unsubscribe_warning` mediumtext collate utf8_unicode_ci NOT NULL,
  `unsubscribe_error` mediumtext collate utf8_unicode_ci NOT NULL,
  `unsubscribe_thanx` mediumtext collate utf8_unicode_ci NOT NULL,
  `unsubscribe_subject` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `unsubscribe_text` mediumtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `module_newsletter`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `newsletter_addresses`
-- 

CREATE TABLE `newsletter_addresses` (
  `id` int(11) NOT NULL auto_increment,
  `address` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `language` char(3) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `address` (`address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `newsletter_addresses`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `newsletter_sent`
-- 

CREATE TABLE `newsletter_sent` (
  `id` int(11) NOT NULL auto_increment,
  `address_id` int(11) NOT NULL,
  `edition_id` int(11) NOT NULL,
  `sent` tinyint(1) NOT NULL,
  `lang` varchar(2) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ad_ed_lg` (`address_id`,`edition_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `newsletter_sent`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `newsletter_subscription`
-- 

CREATE TABLE `newsletter_subscription` (
  `newsletter_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `subscription_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL default '0',
  `activation_code` varchar(32) collate utf8_unicode_ci default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
