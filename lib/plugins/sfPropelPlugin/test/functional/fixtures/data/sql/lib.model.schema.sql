
-----------------------------------------------------------------------------
-- article
-----------------------------------------------------------------------------

DROP TABLE [article];


CREATE TABLE [article]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[title] VARCHAR(255)  NOT NULL,
	[body] MEDIUMTEXT,
	[online] INTEGER,
	[category_id] INTEGER  NOT NULL,
	[created_at] TIMESTAMP,
	[end_date] TIMESTAMP,
	[book_id] INTEGER
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([category_id]) REFERENCES category ([id])

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([book_id]) REFERENCES book ([id])

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
-- book
-----------------------------------------------------------------------------

DROP TABLE [book];


CREATE TABLE [book]
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
	[author_id] INTEGER  NOT NULL,
	[article_id] INTEGER  NOT NULL
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([author_id]) REFERENCES author ([id])

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([article_id]) REFERENCES article ([id])
