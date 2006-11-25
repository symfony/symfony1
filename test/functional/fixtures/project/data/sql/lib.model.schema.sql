
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

-----------------------------------------------------------------------------
-- author
-----------------------------------------------------------------------------

DROP TABLE [author];


CREATE TABLE [author]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[name] VARCHAR(255)
);

-----------------------------------------------------------------------------
-- author_article
-----------------------------------------------------------------------------

DROP TABLE [author_article];


CREATE TABLE [author_article]
(
	[author_id] INTEGER,
	[article_id] INTEGER,
	[id] INTEGER  NOT NULL PRIMARY KEY
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([author_id]) REFERENCES author ([id])

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([article_id]) REFERENCES article ([id])
