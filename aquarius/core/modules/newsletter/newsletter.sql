DROP TABLE IF EXISTS `newsletter_subscription`;
CREATE TABLE `newsletter_subscription` (
    `newsletter_id` int(11) NOT NULL,
    `address_id` int(11) NOT NULL,
    `subscription_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
    `active` tinyint(1) NOT NULL default '0',
    `activation_code` varchar(32) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `newsletter_addresses`;
CREATE TABLE `newsletter_addresses` (
    `id` int(11) NOT NULL auto_increment,
    `address` varchar(255) NOT NULL default '',
    `language` char(3) NOT NULL default '',
    PRIMARY KEY  (`id`),
    UNIQUE KEY `address` (`address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `newsletter_sent`;
CREATE TABLE `newsletter_sent` (
    `id` int(11) NOT NULL auto_increment,
    `address_id` int(11) NOT NULL,
    `edition_id` int(11) NOT NULL,
    `sent` boolean NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;