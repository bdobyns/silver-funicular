# MySQL dump 8.12
#
# Host: localhost    Database: PHPWEBLOG
#--------------------------------------------------------
# Server version	3.23.31-log

#
# Dumping data for table 'T_Blocks'
#

INSERT INTO T_Blocks (Rid, Display, ShowMain, PageComments, Heading, Content, Type, OrderID, URL, Hits, Timestamp, Birthstamp, Cache) VALUES ('01/02/02/3090381','l','0','0','Welcome to phpWebLog','You have successfully installed phpWebLog.  The first thing you will want to do is log in as administrator and configure the site settings.  The default password is [password].  Click <a href=\"admin/\">here</a> to login.\r\n<br><br>\r\nIf you need any questions answered, join us at <a href=\" http://www.phpweblog.org\">phpweblog.org</a>.',0,1,'',5,20010202173830,'2001-02-02 17:38:30',60);

#
# Dumping data for table 'T_Comments'
#


#
# Dumping data for table 'T_Config'
#

INSERT INTO T_Config (Rid, Name, Value) VALUES (1,'Moderation','2');
INSERT INTO T_Config (Rid, Name, Value) VALUES (2,'SiteName','phpWebLog');
INSERT INTO T_Config (Rid, Name, Value) VALUES (3,'SiteSlogan','web news management');
INSERT INTO T_Config (Rid, Name, Value) VALUES (4,'SiteOwner','Your Name Goes Here');
INSERT INTO T_Config (Rid, Name, Value) VALUES (5,'EmailAddress','jason@greenhell.com');
INSERT INTO T_Config (Rid, Name, Value) VALUES (6,'Backend','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (7,'BackendFile','weblog.rdf');
INSERT INTO T_Config (Rid, Name, Value) VALUES (8,'MailingList','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (9,'MailingAddress','');
INSERT INTO T_Config (Rid, Name, Value) VALUES (10,'SummaryLength','300');
INSERT INTO T_Config (Rid, Name, Value) VALUES (11,'Comments','2');
INSERT INTO T_Config (Rid, Name, Value) VALUES (12,'CommentSort','asc');
INSERT INTO T_Config (Rid, Name, Value) VALUES (13,'Topics','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (14,'TopicSort','asc');
INSERT INTO T_Config (Rid, Name, Value) VALUES (15,'Links','2');
INSERT INTO T_Config (Rid, Name, Value) VALUES (16,'Older','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (17,'Top5','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (18,'Hot5','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (19,'LimitNews','10');
INSERT INTO T_Config (Rid, Name, Value) VALUES (20,'LimitType','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (21,'Passwd','5f4dcc3b5aa765d61d8327deb882cf99');
INSERT INTO T_Config (Rid, Name, Value) VALUES (22,'SiteKey','phpWebLog');
INSERT INTO T_Config (Rid, Name, Value) VALUES (23,'SiteStats','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (24,'AllowAnon','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (25,'ParseLevel','2');
INSERT INTO T_Config (Rid, Name, Value) VALUES (26,'SaveInfo','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (27,'EmailComments','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (28,'AllowContrib','1');
INSERT INTO T_Config (Rid, Name, Value) VALUES (29,'Layout','shanked');
INSERT INTO T_Config (Rid, Name, Value) VALUES (30,'Language','english');
INSERT INTO T_Config (Rid, Name, Value) VALUES (31,'Archive','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (32,'MailFriend','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (33,'PrintStory','0');
INSERT INTO T_Config (Rid, Name, Value) VALUES (34,'ParseLevelCmt','2');

#
# Dumping data for table 'T_IndexLinks'
#


#
# Dumping data for table 'T_IndexNames'
#


#
# Dumping data for table 'T_LinkCats'
#


#
# Dumping data for table 'T_Links'
#


#
# Dumping data for table 'T_Pages'
#


#
# Dumping data for table 'T_PollAnswers'
#


#
# Dumping data for table 'T_PollQuestions'
#


#
# Dumping data for table 'T_Stories'
#


#
# Dumping data for table 'T_Topics'
#

INSERT INTO T_Topics (Rid, Topic, ImgURL, AltTag, NoPosting, NoComments, Timestamp) VALUES (1,'General','','General',NULL,NULL,20000531182757);

