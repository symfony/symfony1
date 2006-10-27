
-----------------------------------------------------------------------------
-- article
-----------------------------------------------------------------------------

DROP TABLE [article];


CREATE TABLE [article]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[title] VARCHAR(255),
	[body] MEDIUMTEXT,
	[online] INTEGER,
	[category_id] INTEGER,
	[created_at] TIMESTAMP
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([category_id]) REFERENCES category ([id])

-----------------------------------------------------------------------------
-- category
-----------------------------------------------------------------------------

DROP TABLE [category];


CREATE TABLE [category]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[name] VARCHAR(255)
);
