# phpWebLog Update
# Jason Hines
# 0.2.4 to 0.3.0
# --------------------------------------------------------

#
# Table updates for table 'T_Blocks'
#

ALTER TABLE T_Blocks ADD Display char(1);
ALTER TABLE T_Blocks ADD Type tinyint(4);
UPDATE T_Blocks SET Display = 'Y';

#
# Table updates for table 'T_Comments'
#

ALTER TABLE T_Comments ADD CommentRid int(11);
ALTER TABLE T_Comments ADD Host char(96);

#
# Table updates for table 'T_Config'
#

ALTER TABLE T_Config ADD SiteSlogan char(64);
ALTER TABLE T_Config ADD SiteOwner char(24);
ALTER TABLE T_Config ADD Comments char(1);
ALTER TABLE T_Config ADD CommentSort char(8);
ALTER TABLE T_Config ADD TopicSort char(8);
ALTER TABLE T_Config ADD Older char(1);
ALTER TABLE T_Config ADD EmailComments char(1);
ALTER TABLE T_Config ADD ShowIP char(1);
ALTER TABLE T_Config CHANGE Layout Layout char(24);
UPDATE T_Config SET Layout = 'default';
UPDATE T_Config SET EmailComments = '0';
UPDATE T_Config SET Older = '1';
UPDATE T_Config SET TopicSort = 'id';
UPDATE T_Config SET Comments = '1';
UPDATE T_Config SET CommentSort = 'desc';

#
# Table updates for table 'T_Layout'
#

DROP TABLE T_Layout;

#
# Table updates for table 'T_Links'
#

ALTER TABLE T_Links CHANGE Hits Hits int(4);
ALTER TABLE T_Links ADD Host char(96);

#
# Table structure for table 'T_Pages'
#
CREATE TABLE T_Pages (
  Rid int(11) DEFAULT '0' NOT NULL auto_increment,
  Name varchar(32),
  Heading varchar(64),
  Content text,
  Display char(1),
  Type tinyint(4),
  Timestamp timestamp(14),
  PRIMARY KEY (Rid)
);

#
# Table updates for table 'T_Stories'
#

ALTER TABLE T_Stories ADD Summary text;
ALTER TABLE T_Stories ADD EmailComments tinyint(4);
ALTER TABLE T_Stories ADD Host char(96);
ALTER TABLE T_Stories CHANGE Hits Hits int(4);
UPDATE T_Stories SET Summary = Content;

#
# Table structure for table 'T_PollAnswers'
#
CREATE TABLE T_PollAnswers (
  Rid char(20) DEFAULT '' NOT NULL,
  Aid int(11) DEFAULT '0' NOT NULL,
  Answer char(255),
  Votes int(11),
  PRIMARY KEY (Rid,Aid)
);

#
# Table structure for table 'T_PollQuestions'
#
CREATE TABLE T_PollQuestions (
  Rid char(64) DEFAULT '' NOT NULL,
  Question char(255) DEFAULT '' NOT NULL,
  Voters int(11),
  BirthStamp datetime,
  Display tinyint(4) DEFAULT '0' NOT NULL,
  PRIMARY KEY (Rid)
);
