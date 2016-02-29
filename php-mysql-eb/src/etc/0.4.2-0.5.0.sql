# phpWebLog Update
# Jason Hines
# 0.4.2 to 0.5.0
# --------------------------------------------------------

ALTER TABLE T_Blocks ADD COLUMN PageComments char(1);
ALTER TABLE T_Blocks ADD COLUMN ShowMain char(1);
ALTER TABLE T_Blocks ADD COLUMN Hits int(11);
ALTER TABLE T_Blocks ADD COLUMN OrderID tinyint(4);

ALTER TABLE T_Comments DROP COLUMN Heading; # was varchar(128)

ALTER TABLE T_PollQuestions ADD COLUMN ExpireDays int(11);

ALTER TABLE T_Stories CHANGE COLUMN Heading Heading varchar(96); # was varchar(48)
ALTER TABLE T_Stories DROP COLUMN StoryURL; # was varchar(128)
ALTER TABLE T_Stories ADD COLUMN Repostamp datetime;
UPDATE T_Stories SET Repostamp = Birthstamp;

ALTER TABLE T_Topics CHANGE COLUMN Topic Topic varchar(48) DEFAULT '' NOT NULL; # was varchar(48)
ALTER TABLE T_Topics ADD COLUMN NoPosting char(1);
ALTER TABLE T_Topics ADD COLUMN NoComments char(1);
ALTER TABLE T_Topics ADD INDEX Topic (Topic);

INSERT INTO T_Config (Name, Value) VALUES ('Top5','1');
UPDATE T_Config SET Name = 'Hot5' WHERE Name = 'Hotest';
INSERT INTO T_Config (Name, Value) VALUES ('MailFriend','0');
INSERT INTO T_Config (Name, Value) VALUES ('PrintStory','0');
INSERT INTO T_Config (Name, Value) VALUES ('Archive','0');
DELETE FROM T_Config WHERE Name = 'MoreLink';

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
);

CREATE TABLE T_IndexLinks (
  Rid int(11) NOT NULL auto_increment,
  ParentRid varchar(16),
  Name varchar(48),
  URL varchar(128),
  Hits int(11),
  PRIMARY KEY (Rid)
);

CREATE TABLE T_IndexNames (
  Rid int(11) NOT NULL auto_increment,
  Name varchar(48) DEFAULT '' NOT NULL,
  PRIMARY KEY (Rid),
  UNIQUE Name (Name)
);

CREATE TABLE T_LinkCats (
  Rid varchar(16) DEFAULT '' NOT NULL,
  Name varchar(50) DEFAULT '' NOT NULL,
  ParentRid varchar(16),
  Verified char(1),
  PRIMARY KEY (Rid)
);

