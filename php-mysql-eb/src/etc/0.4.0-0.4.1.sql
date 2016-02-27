# phpWebLog Update
# Jason Hines
# 0.4.0 to 0.4.1
# --------------------------------------------------------

UPDATE T_Config SET Name = 'ParseLevel' WHERE Name = 'AllowHTML';
ALTER TABLE T_Blocks ADD COLUMN Cache int;
