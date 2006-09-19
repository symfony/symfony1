
-----------------------------------------------------------------------------
-- article
-----------------------------------------------------------------------------

DROP TABLE [article];


CREATE TABLE [article]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[title] VARCHAR(255),
	[body] MEDIUMTEXT,
	[created_at] TIMESTAMP
);
