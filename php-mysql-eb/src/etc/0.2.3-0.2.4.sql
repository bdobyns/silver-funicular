# phpWebLog Update
# Jason Whittenburg
# 0.2.3 to 0.2.4
# --------------------------------------------------------

#
# Table updates for table 'T_Blocks'
#

ALTER TABLE T_Blocks CHANGE Content Content text;

#
# Table updates for table 'T_Comments'
#

ALTER TABLE T_Comments CHANGE Content Content text;
ALTER TABLE T_Comments ADD AuthorURL varchar(96);

#
# Table updates for table 'T_Config'
#

ALTER TABLE T_Config DROP BaseAddress;
ALTER TABLE T_Config DROP BasePath;
ALTER TABLE T_Config DROP Score;
ALTER TABLE T_Config DROP Newsletter char(1);
ALTER TABLE T_Config ADD Backend char(1);
ALTER TABLE T_Config ADD Views char(1);
ALTER TABLE T_Config ADD AllowHTML char(1);

UPDATE T_Config SET Backend = '0';
UPDATE T_Config SET Views = '0';
UPDATE T_Config SET AllowHTML = '0';

#
# Table updates for table 'T_Layout'
#

ALTER TABLE T_Layout ADD Curve char(1);

UPDATE T_Layout SET Curve = '0';

#
# Table updates for table 'T_Links'
#

ALTER TABLE T_Links CHANGE Hits Hits int(4);

#
# Table updates for table 'T_Stories'
#

ALTER TABLE T_Stories ADD AuthorURL varchar(96);
ALTER TABLE T_Stories CHANGE Hits Hits int(4);