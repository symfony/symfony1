
-----------------------------------------------------------------------------
-- article
-----------------------------------------------------------------------------

DROP TABLE [article];


CREATE TABLE [article]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[title] VARCHAR(255)  NOT NULL,
	[body] MEDIUMTEXT,
	[Online] INTEGER,
	[excerpt] VARCHAR(255),
	[category_id] INTEGER  NOT NULL,
	[created_at] TIMESTAMP,
	[end_date] TIMESTAMP,
	[book_id] INTEGER,
	UNIQUE ([title],[category_id])
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
	[name] VARCHAR(255),
	UNIQUE ([name]),
	UNIQUE ([name])
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
	[article_id] INTEGER  NOT NULL,
	PRIMARY KEY ([author_id],[article_id])
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([author_id]) REFERENCES author ([id])

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([article_id]) REFERENCES article ([id])

-----------------------------------------------------------------------------
-- product
-----------------------------------------------------------------------------

DROP TABLE [product];


CREATE TABLE [product]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[price] FLOAT,
	[a_primary_string] VARCHAR(64)
);

-----------------------------------------------------------------------------
-- product_i18n
-----------------------------------------------------------------------------

DROP TABLE [product_i18n];


CREATE TABLE [product_i18n]
(
	[id] INTEGER  NOT NULL,
	[culture] VARCHAR(7)  NOT NULL,
	[name] VARCHAR(50),
	PRIMARY KEY ([id],[culture])
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([id]) REFERENCES product ([id])

-----------------------------------------------------------------------------
-- movie
-----------------------------------------------------------------------------

DROP TABLE [movie];


CREATE TABLE [movie]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[director] VARCHAR(255)
);

-----------------------------------------------------------------------------
-- movie_i18n
-----------------------------------------------------------------------------

DROP TABLE [movie_i18n];


CREATE TABLE [movie_i18n]
(
	[id] INTEGER  NOT NULL,
	[culture] VARCHAR(7)  NOT NULL,
	[title] VARCHAR(255),
	PRIMARY KEY ([id],[culture]),
	UNIQUE ([title])
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([id]) REFERENCES movie ([id])

-----------------------------------------------------------------------------
-- attachment
-----------------------------------------------------------------------------

DROP TABLE [attachment];


CREATE TABLE [attachment]
(
	[id] INTEGER  NOT NULL PRIMARY KEY,
	[article_id] INTEGER,
	[name] VARCHAR(255),
	[file] VARCHAR(255)
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([article_id]) REFERENCES article ([id])
