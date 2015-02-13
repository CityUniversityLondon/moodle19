# This file contains a complete database schema for all the 
# tables used by this module, written in SQL

# It may also contain INSERT statements for particular data 
# that may be used, especially new entries in the table log_display


#----------------------------
# Table structure for prefix_ipodcast
#----------------------------
CREATE TABLE `prefix_ipodcast` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ipodcastcourseid` int(10) NOT NULL default '0',
  `course` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `summary` varchar(255) NOT NULL default '',
  `notes` varchar(255) NOT NULL default '',
  `attachment` varchar(255) NOT NULL default '',
  `duration` varchar(50) NOT NULL default '',
  `explicit` tinyint(2) NOT NULL default '0',
  `subtitle` varchar(255) NOT NULL default '',
  `keywords` varchar(255) NOT NULL default '',
  `topcategory` int(10) NOT NULL default '0',
  `nestedcategory` int(10) NOT NULL default '0',
  `timecreated` int(10) unsigned NOT NULL default '0',
  `timemodified` int(10) unsigned NOT NULL default '0',
  `teacherentry` tinyint(2) unsigned NOT NULL default '0',
  `timestart` int(10) NOT NULL default '0',
  `timefinish` int(10) NOT NULL default '0',
  `approved` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='all podcasts';

#----------------------------
# Records for table prefix_ipodcast
#----------------------------

#----------------------------
# Table structure for prefix_ipodcast_comments
#----------------------------
CREATE TABLE `prefix_ipodcast_comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entryid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  `attachment` varchar(255) NOT NULL default '',
  `visibility` tinyint(2) NOT NULL default '0',
  `timemodified` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `entryid` (`entryid`)
)  TYPE=MyISAM COMMENT='comments on ipodcast  entries';

#----------------------------
# No records for table prefix_ipodcast_comments
#----------------------------

#----------------------------
# Table structure for prefix_ipodcast_views
#----------------------------
CREATE TABLE `prefix_ipodcast_views` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entryid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  `timemodified` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `entryid` (`entryid`)
)  TYPE=MyISAM COMMENT='views of podcasts';

#----------------------------
# Records for table prefix_ipodcast_views
#----------------------------

#----------------------------
# Table structure for prefix_ipodcast_courses
#----------------------------
CREATE TABLE `prefix_ipodcast_courses` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `course` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `studentcanpost` tinyint(2) NOT NULL default '0',
  `defaultapproval` tinyint(2) NOT NULL default '0',
  `attachwithcomment` tinyint(2) NOT NULL default '0',
  `enabletsseries` tinyint(2) NOT NULL default '0',
  `enabledarwin` tinyint(2) NOT NULL default '0',
  `authkey` varchar(255) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `summary` varchar(255) NOT NULL default '',
  `comment` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `imageheight` int(10) NOT NULL default '144',
  `imagewidth` int(10) NOT NULL default '144',
  `darwinurl` varchar(255) NOT NULL default '',
  `rssarticles` tinyint(2) NOT NULL default '0',
  `rsssorting` tinyint(2) NOT NULL default '1',
  `enablerssfeed` tinyint(2) NOT NULL default '0',
  `enablerssitunes` tinyint(2) NOT NULL default '0',
  `visible` tinyint(2) NOT NULL default '0',
  `explicit` tinyint(2) NOT NULL default '0',
  `subtitle` varchar(255) NOT NULL default '',
  `keywords` varchar(255) NOT NULL default '',
  `topcategory` int(10) NOT NULL default '0',
  `nestedcategory` int(10) NOT NULL default '0',
  `timecreated` int(10) NOT NULL default '0',
  `timemodified` int(10) NOT NULL default '0',
  `sortorder` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)  TYPE=MyISAM COMMENT='ipodcast course settings';

#----------------------------
# Records for table prefix_ipodcast_courses
#----------------------------

#----------------------------
# Table structure for prefix_ipodcast_itunes_categories
#----------------------------
CREATE TABLE `prefix_ipodcast_itunes_categories` (
  `id` int(10) NOT NULL auto_increment,
  `name` char(255) NOT NULL default '',
  `previousid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)  TYPE=MyISAM COMMENT='itunes top categories'; 
#----------------------------
# Records for table mdl_ipodcast_itunes_categories
#----------------------------


insert  into prefix_ipodcast_itunes_categories values 
(1, 'Arts',0), 
(2, 'Business',0), 
(3, 'Comedy',0), 
(4, 'Education',0), 
(5, 'Games & Hobbies',0), 
(6, 'Government & Organizations',0), 
(7, 'Health',0), 
(8, 'Kids & Family',0), 
(9, 'Music',0), 
(10, 'News & Politics',0), 
(11, 'Religion & Spirituality',0), 
(12, 'Science & Medicine',0), 
(13, 'Society & Culture',0), 
(14, 'Sports & Recreation',0), 
(15, 'Technology',0), 
(16, 'TV & Film',0);

#----------------------------
# Table structure for mdl_ipodcast_itunes_nested_categories
#----------------------------
CREATE TABLE `prefix_ipodcast_itunes_nested_categories` (
  `id` int(10) NOT NULL auto_increment,
  `name` char(255) NOT NULL default '',
  `topcategoryid` int(10) NOT NULL default '0',
  `previousid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)  TYPE=MyISAM COMMENT='itunes nested categories';
#----------------------------
# Records for table prefix_ipodcast_ipodcast_itunes_categories
#----------------------------


insert  into prefix_ipodcast_itunes_nested_categories values 
(1, 'Design', 1, 0), 
(2, 'Fashion & Beautiy', 1, 0), 
(3, 'Food', 1, 0), 
(4, 'Literature', 1, 0), 
(5, 'Performing Arts', 1, 0), 
(6, 'Visual Arts', 1, 0), 
(7, 'Business News', 2, 0), 
(8, 'Careers', 2, 0), 
(9, 'Investing', 2, 0), 
(10, 'Management & Marketing', 2, 0), 
(11, 'Shopping', 2, 0), 
(12, 'Education Technology', 4, 0), 
(13, 'Higher Education', 4, 0), 
(14, 'K-12', 4, 0), 
(15, 'Language Courses', 4, 0), 
(16, 'Training', 4, 0), 
(17, 'Automotive', 5, 0), 
(18, 'Aviation', 5, 0), 
(19, 'Hobbies', 5, 0), 
(20, 'Other Games', 5, 0), 
(21, 'Video Games', 5, 0), 
(22, 'Local', 6, 0), 
(23, 'National', 6, 0), 
(24, 'Non-Profit', 6, 0), 
(25, 'Regional', 6, 0), 
(26, 'Alternative Health', 7, 0), 
(27, 'Fitness & Nutrition', 7, 0), 
(28, 'Self-Help', 7, 0), 
(29, 'Sexuality', 7, 0), 
(30, 'Buddhism', 11, 0), 
(31, 'Christianity', 11, 0), 
(32, 'Hinduism', 11, 0), 
(33, 'Islam', 11, 0), 
(34, 'Judaism', 11, 0), 
(35, 'Other', 11, 0), 
(36, 'Medicine', 12, 0), 
(37, 'Natural Sciences', 12, 0), 
(38, 'Social Sciences', 12, 0), 
(39, 'History', 13, 0), 
(40, 'Personal Journals', 13, 0), 
(41, 'Philosophy', 13, 0), 
(42, 'Places & Travel', 13, 0), 
(43, 'Amateur', 14, 0), 
(44, 'College & High School', 14, 0), 
(45, 'Outdoor', 14, 0), 
(46, 'Professional', 14, 0), 
(47, 'Gadgets', 15, 0), 
(48, 'Tech News', 15, 0), 
(49, 'Podcasting', 15, 0), 
(50, 'Software How-To', 15, 0); 

#----------------------------
# Table structure for prefix_ipodcast_tsseries
#----------------------------
CREATE TABLE `prefix_ipodcast_tsseries` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ipodcastcourseid` int(10) NOT NULL default '0',
  `ipodcastid` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `summary` varchar(255) NOT NULL default '',
  `notes` varchar(255) NOT NULL default '',
  `duration` varchar(50) NOT NULL default '',
  `timecreated` int(10) unsigned NOT NULL default '0',
  `timemodified` int(10) unsigned NOT NULL default '0',
  `status` int(10) NOT NULL default '0',
  `attachment` varchar(255) NOT NULL default '',
  `roomname` varchar(255) NOT NULL default '',
  `section` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)  TYPE=MyISAM COMMENT='all tsseries generated podcasts';
#----------------------------
# Records for table prefix_ipodcast_tsseries
#----------------------------

#----------------------------
# Table structure for prefix_ipodcast_tsseries_config
#----------------------------
CREATE TABLE `prefix_ipodcast_tsseries_config` (
  `id` int(10) NOT NULL auto_increment,
  `roomname` char(255) NOT NULL default '',
  `StreamFilePath` char(255) NOT NULL default '',
  `StreamURLRoot` char(255) NOT NULL default '',
  `License` char(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
)  TYPE=MyISAM COMMENT='tsseries configs';
#----------------------------
# Records for table prefix_ipodcast_tsseries_config
#----------------------------


#
# Dumping data for table `log_display`
#



INSERT INTO prefix_log_display (module,action,mtable,field) VALUES ('ipodcast', 'view', 'ipodcast', 'name');
INSERT INTO prefix_log_display (module,action,mtable,field) VALUES ('ipodcast', 'add entry', 'ipodcast', 'name');
INSERT INTO prefix_log_display (module,action,mtable,field) VALUES ('ipodcast', 'update entry', 'ipodcast', 'name');

#These lines didnt work for Darrin Smith
#INSERT INTO prefix_config (id,name,value) VALUES (NULL,'ipodcast_enablerssfeeds', '1') ON DUPLICATE KEY UPDATE value=1;
#INSERT INTO prefix_config (id,name,value) VALUES (NULL,'ipodcast_enablerssitunes', '1') ON DUPLICATE KEY UPDATE value=1;

#INSERT INTO prefix_config VALUES (NULL,'ipodcast_enablerssfeeds', '1');
#INSERT INTO prefix_config VALUES (NULL,'ipodcast_enablerssitunes', '1');

