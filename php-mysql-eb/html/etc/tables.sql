# MySQL dump 8.12
#
# Host: localhost    Database: PHPWEBLOG
#--------------------------------------------------------
# Server version	3.23.31-log

#
# Table structure for table 'T_Blocks'
#

DROP TABLE IF EXISTS T_Blocks;
CREATE TABLE T_Blocks (
  Rid varchar(16) NOT NULL default '',
  Display char(1) default NULL,
  ShowMain char(1) default NULL,
  PageComments char(1) default NULL,
  Heading varchar(48) default NULL,
  Content text,
  Type tinyint(4) default NULL,
  OrderID tinyint(4) default NULL,
  URL varchar(96) default NULL,
  Hits int(11) default NULL,
  Timestamp timestamp(14) NOT NULL,
  Birthstamp datetime default NULL,
  Cache int(11) default NULL,
  PRIMARY KEY (Rid)
) TYPE=MyISAM;

#
# Table structure for table 'T_Comments'
#

DROP TABLE IF EXISTS T_Comments;
CREATE TABLE T_Comments (
  Rid varchar(16) NOT NULL default '',
  TopRid varchar(16) default NULL,
  Author varchar(32) default NULL,
  AuthorEmail varchar(96) default NULL,
  AuthorURL varchar(96) default NULL,
  Content text,
  Host varchar(96) default NULL,
  Birthstamp datetime default NULL,
  Timestamp timestamp(14) NOT NULL,
  ParentRid varchar(16) default NULL,
  PRIMARY KEY (Rid)
) TYPE=MyISAM;

#
# Table structure for table 'T_Config'
#

DROP TABLE IF EXISTS T_Config;
CREATE TABLE T_Config (
  Rid int(11) NOT NULL auto_increment,
  Name varchar(16) default NULL,
  Value varchar(128) default NULL,
  PRIMARY KEY (Rid)
) TYPE=MyISAM;

#
# Table structure for table 'T_IndexLinks'
#

DROP TABLE IF EXISTS T_IndexLinks;
CREATE TABLE T_IndexLinks (
  Rid int(11) NOT NULL auto_increment,
  ParentRid varchar(16) default NULL,
  Name varchar(48) default NULL,
  URL varchar(128) default NULL,
  Hits int(11) default NULL,
  PRIMARY KEY (Rid)
) TYPE=MyISAM;

#
# Table structure for table 'T_IndexNames'
#

DROP TABLE IF EXISTS T_IndexNames;
CREATE TABLE T_IndexNames (
  Rid int(11) NOT NULL auto_increment,
  Name varchar(48) NOT NULL default '',
  PRIMARY KEY (Rid),
  UNIQUE KEY Name(Name)
) TYPE=MyISAM;

#
# Table structure for table 'T_LinkCats'
#

DROP TABLE IF EXISTS T_LinkCats;
CREATE TABLE T_LinkCats (
  Rid varchar(16) NOT NULL default '',
  Name varchar(50) NOT NULL default '',
  ParentRid varchar(16) default NULL,
  Verified char(1) default NULL,
  PRIMARY KEY (Rid)
) TYPE=MyISAM;

#
# Table structure for table 'T_Links'
#

DROP TABLE IF EXISTS T_Links;
CREATE TABLE T_Links (
  Rid varchar(16) NOT NULL default '',
  CatRid varchar(16) NOT NULL default '',
  Url varchar(255) NOT NULL default '',
  Name varchar(64) default NULL,
  Description varchar(255) default NULL,
  Verified char(1) default NULL,
  SubmitName varchar(64) default NULL,
  SubmitEmail varchar(96) default NULL,
  SubmitDate datetime default NULL,
  Hits int(11) default NULL,
  PRIMARY KEY (Rid),
  UNIQUE KEY Url(Url)
) TYPE=MyISAM;

#
# Table structure for table 'T_Pages'
#

DROP TABLE IF EXISTS T_Pages;
CREATE TABLE T_Pages (
  Rid varchar(16) NOT NULL default '',
  Name varchar(32) default NULL,
  Heading varchar(64) default NULL,
  Content text,
  Display char(1) default NULL,
  Type tinyint(4) default NULL,
  Timestamp timestamp(14) NOT NULL,
  PRIMARY KEY (Rid)
) TYPE=MyISAM;

#
# Table structure for table 'T_PollAnswers'
#

DROP TABLE IF EXISTS T_PollAnswers;
CREATE TABLE T_PollAnswers (
  Rid varchar(16) NOT NULL default '',
  Aid int(11) NOT NULL default '0',
  Answer varchar(255) default NULL,
  Votes int(11) default NULL,
  PRIMARY KEY (Rid,Aid)
) TYPE=MyISAM;

#
# Table structure for table 'T_PollQuestions'
#

DROP TABLE IF EXISTS T_PollQuestions;
CREATE TABLE T_PollQuestions (
  Rid varchar(16) NOT NULL default '',
  Question varchar(255) NOT NULL default '',
  Voters int(11) default NULL,
  ExpireDays int(11) default NULL,
  BirthStamp datetime default NULL,
  Display char(1) NOT NULL default '0',
  PRIMARY KEY (Rid)
) TYPE=MyISAM;

#
# Table structure for table 'T_Stories'
#

DROP TABLE IF EXISTS T_Stories;
CREATE TABLE T_Stories (
  Rid varchar(16) NOT NULL default '',
  Verified char(1) default NULL,
  Score int(11) default NULL,
  Host varchar(96) default NULL,
  Topic varchar(16) default NULL,
  Heading varchar(96) default NULL,
  Summary text,
  Content text,
  Author varchar(32) default NULL,
  AuthorEmail varchar(96) default NULL,
  AuthorURL varchar(96) default NULL,
  EmailComments tinyint(4) default NULL,
  Hits int(11) default NULL,
  Repostamp datetime default NULL,
  Timestamp timestamp(14) NOT NULL,
  Birthstamp datetime default NULL,
  PRIMARY KEY (Rid)
) TYPE=MyISAM;

#
# Table structure for table 'T_Topics'
#

DROP TABLE IF EXISTS T_Topics;
CREATE TABLE T_Topics (
  Rid int(11) NOT NULL auto_increment,
  Topic varchar(48) NOT NULL default '',
  ImgURL varchar(96) default NULL,
  AltTag varchar(64) default NULL,
  NoPosting char(1) default NULL,
  NoComments char(1) default NULL,
  Timestamp timestamp(14) NOT NULL,
  PRIMARY KEY (Rid),
  UNIQUE KEY Topic(Topic)
) TYPE=MyISAM;

