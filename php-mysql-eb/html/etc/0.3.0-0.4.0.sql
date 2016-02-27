# phpWebLog Update
# Jason Hines
# 0.3.0 to 0.4.0
# --------------------------------------------------------

ALTER TABLE T_Blocks CHANGE COLUMN Rid Rid varchar(16) DEFAULT '' NOT NULL; # was int(11) DEFAULT '0' NOT NULL auto_increment
ALTER TABLE T_Blocks ADD COLUMN URL varchar(96);
ALTER TABLE T_Blocks ADD COLUMN Birthstamp datetime;

ALTER TABLE T_Comments DROP COLUMN CommentRid; # was int(11)
ALTER TABLE T_Comments CHANGE COLUMN NewsRid TopRid varchar(16); # was int(11)
ALTER TABLE T_Comments CHANGE COLUMN Rid Rid varchar(16) DEFAULT '' NOT NULL; # was int(11) DEFAULT '0' NOT NULL auto_increment
ALTER TABLE T_Comments ADD COLUMN ParentRid varchar(16);
UPDATE T_Comments SET ParentRid = TopRid;
ALTER TABLE T_Comments ADD COLUMN Heading varchar(128);

ALTER TABLE T_Config DROP COLUMN SiteOwner; # was char(24)
ALTER TABLE T_Config DROP COLUMN Links; # was char(1)
ALTER TABLE T_Config DROP COLUMN Comments; # was char(1)
ALTER TABLE T_Config DROP COLUMN TopicSort; # was char(8)
ALTER TABLE T_Config DROP COLUMN Backend; # was char(1)
ALTER TABLE T_Config DROP COLUMN Timezone; # was char(6)
ALTER TABLE T_Config DROP COLUMN AllowAnon; # was char(1)
ALTER TABLE T_Config DROP COLUMN Views; # was char(1)
ALTER TABLE T_Config DROP COLUMN EmailAddress; # was char(96)
ALTER TABLE T_Config DROP COLUMN EmailComments; # was char(1)
ALTER TABLE T_Config DROP COLUMN Moderation; # was char(1)
ALTER TABLE T_Config DROP COLUMN Topics; # was char(1)
ALTER TABLE T_Config DROP COLUMN Passwd; # was char(128)
ALTER TABLE T_Config DROP COLUMN Timestamp; # was timestamp(14)
ALTER TABLE T_Config DROP COLUMN AllowHTML; # was char(1)
ALTER TABLE T_Config DROP COLUMN SiteName; # was char(64)
ALTER TABLE T_Config DROP COLUMN Older; # was char(1)
ALTER TABLE T_Config DROP COLUMN SiteSlogan; # was char(64)
ALTER TABLE T_Config DROP COLUMN ShowIP; # was char(1)
ALTER TABLE T_Config DROP COLUMN CommentSort; # was char(8)
ALTER TABLE T_Config DROP COLUMN SaveInfo; # was char(1)
ALTER TABLE T_Config DROP COLUMN LimitNews; # was char(2)
ALTER TABLE T_Config DROP COLUMN SiteKey; # was char(10)
ALTER TABLE T_Config DROP COLUMN Layout; # was char(24)
ALTER TABLE T_Config ADD COLUMN Name varchar(16);
ALTER TABLE T_Config ADD COLUMN Value varchar(128);

DELETE FROM T_Config;
INSERT INTO T_Config (Rid, Name, Value) VALUES (1,'Moderation','2');
INSERT INTO T_Config (Rid, Name, Value) VALUES (2,'SiteName','phpWebLog');
INSERT INTO T_Config (Rid, Name, Value) VALUES (3,'SiteSlogan','web news management with tits');
INSERT INTO T_Config (Rid, Name, Value) VALUES (4,'SiteOwner','Foo Mun Choo');
INSERT INTO T_Config (Rid, Name, Value) VALUES (5,'EmailAddress','jason@greenhell.com');
INSERT INTO T_Config (Rid, Name, Value) VALUES (6,'Backend','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (7,'BackendFile','weblog.rdf');
INSERT INTO T_Config (Rid, Name, Value) VALUES (8,'MailingList','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (9,'MailingAddress','');
INSERT INTO T_Config (Rid, Name, Value) VALUES (10,'SummaryLength','255');
INSERT INTO T_Config (Rid, Name, Value) VALUES (11,'Comments','2');
INSERT INTO T_Config (Rid, Name, Value) VALUES (12,'CommentSort','asc');
INSERT INTO T_Config (Rid, Name, Value) VALUES (13,'Topics','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (14,'TopicSort','id');
INSERT INTO T_Config (Rid, Name, Value) VALUES (15,'Links','2');
INSERT INTO T_Config (Rid, Name, Value) VALUES (16,'Older','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (17,'Hotest','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (18,'LimitNews','10');
INSERT INTO T_Config (Rid, Name, Value) VALUES (19,'LimitType','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (20,'Passwd','5f4dcc3b5aa765d61d8327deb882cf99');
INSERT INTO T_Config (Rid, Name, Value) VALUES (21,'SiteKey','phpWebLog');
INSERT INTO T_Config (Rid, Name, Value) VALUES (22,'SiteStats','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (23,'AllowAnon','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (24,'AllowHTML','3');
INSERT INTO T_Config (Rid, Name, Value) VALUES (25,'SaveInfo','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (26,'MoreLink','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (27,'EmailComments','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (28,'AllowContrib','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (29,'Layout','default');
INSERT INTO T_Config (Rid, Name, Value) VALUES (30,'Language','english');

ALTER TABLE T_Links CHANGE COLUMN Title Title varchar(96); # was char(96)
ALTER TABLE T_Links CHANGE COLUMN Description Description varchar(255); # was char(255)
ALTER TABLE T_Links CHANGE COLUMN URL URL varchar(96); # was char(96)
ALTER TABLE T_Links CHANGE COLUMN Cat Cat varchar(32); # was char(32)
ALTER TABLE T_Links CHANGE COLUMN Host Host varchar(96); # was char(96)
ALTER TABLE T_Links CHANGE COLUMN Rid Rid varchar(16) DEFAULT '' NOT NULL; # was int(11) DEFAULT '0' NOT NULL auto_increment

ALTER TABLE T_Pages CHANGE COLUMN Rid Rid varchar(16) DEFAULT '' NOT NULL; # was int(11) DEFAULT '0' NOT NULL auto_increment

ALTER TABLE T_PollAnswers CHANGE COLUMN Answer Answer varchar(255); # was char(255)
ALTER TABLE T_PollAnswers CHANGE COLUMN Rid Rid varchar(16) DEFAULT '' NOT NULL; # was char(20) DEFAULT '' NOT NULL

ALTER TABLE T_PollQuestions CHANGE COLUMN Display Display char(1) DEFAULT '0' NOT NULL; # was tinyint(4) DEFAULT '0' NOT NULL
ALTER TABLE T_PollQuestions CHANGE COLUMN Question Question varchar(255) DEFAULT '' NOT NULL; # was char(255) DEFAULT '' NOT NULL
ALTER TABLE T_PollQuestions CHANGE COLUMN Rid Rid varchar(16) DEFAULT '' NOT NULL; # was char(64) DEFAULT '' NOT NULL

ALTER TABLE T_Stories CHANGE COLUMN Topic Topic varchar(16); # was int(11)
ALTER TABLE T_Stories CHANGE COLUMN Rid Rid varchar(16) DEFAULT '' NOT NULL; # was int(11) DEFAULT '0' NOT NULL auto_increment
ALTER TABLE T_Stories ADD COLUMN StoryURL varchar(128);

