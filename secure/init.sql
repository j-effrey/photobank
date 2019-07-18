BEGIN TRANSACTION;

-- *************************** USERS DB ******************************
-- creating users table for login/logout, initialize with 2 seed data
CREATE TABLE users (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	username TEXT NOT NULL UNIQUE,
	password TEXT NOT NULL
);

INSERT INTO users (id, username, password) VALUES (1, 'jeffrey', '$2y$10$mRsD5tdgHRKC7LIZEmtTfO5P9wtVuiz8jeTOfYJOdHCykpRKgeTQO'); -- username: jeffrey, password: password1
INSERT INTO users (id, username, password) VALUES (2, 'victoria', '$2y$10$G7cyOufx5zOwKkNvdNv27OKGwVbeU8JCQl4nGd.v8g7q6/EcGUhjK'); -- username: victoria, password: password2

-- creating sessions table for recurrent login (cookies)
CREATE TABLE sessions (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id INTEGER NOT NULL,
	session TEXT NOT NULL UNIQUE
);
-- ******************************************************************

-- *********************** IMAGE GALLERY DB *************************
CREATE TABLE images (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id INTEGER NOT NULL,
    usr_name TEXT NOT NULL,
    img_name TEXT NOT NULL,
    img_ext TEXT NOT NULL,
    img_src TEXT
);
-- sources for seed images. Unsplash is a website that allows individuals
-- to use their photos freely (permission not even required, but sources below)
-- 1: https://unsplash.com/photos/8Fft7EHjqUo
-- 2: https://unsplash.com/photos/iTtLN5oTbck
-- 3: https://unsplash.com/photos/D9unuKpH-cw
-- 4: https://unsplash.com/photos/XAxEp-NKBiQ
-- 5: https://unsplash.com/photos/hztya2tQqB8
-- 6: https://unsplash.com/photos/c11_sMCmlgg
-- 7: https://unsplash.com/photos/Ns216N3sCAA
-- 8: https://unsplash.com/photos/Im7lZjxeLhg
-- 9: https://unsplash.com/photos/OLlj17tUZnU
-- 10: https://unsplash.com/photos/-Bq3TeSBRdE
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (1, 1, 'jeffrey', '1', 'jpg', 'https://unsplash.com/photos/8Fft7EHjqUo');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (2, 1, 'jeffrey', '2', 'jpg', 'https://unsplash.com/photos/iTtLN5oTbck');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (3, 1, 'jeffrey', '3', 'jpg', 'https://unsplash.com/photos/D9unuKpH-cw');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (4, 1, 'jeffrey', '4', 'jpg', 'https://unsplash.com/photos/XAxEp-NKBiQ');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (5, 1, 'jeffrey', '5', 'jpg', 'https://unsplash.com/photos/hztya2tQqB8');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (6, 2, 'victoria', '6', 'jpg', 'https://unsplash.com/photos/c11_sMCmlgg');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (7, 2, 'victoria', '7', 'jpg', 'https://unsplash.com/photos/Ns216N3sCAA');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (8, 2, 'victoria', '8', 'jpg', 'https://unsplash.com/photos/Im7lZjxeLhg');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (9, 2, 'victoria', '9', 'jpg', 'https://unsplash.com/photos/OLlj17tUZnU');
INSERT INTO images (id, user_id, usr_name, img_name, img_ext, img_src) VALUES (10, 2, 'victoria', '10', 'jpg', 'https://unsplash.com/photos/-Bq3TeSBRdE');

-- the purpose of this table is to enforce the many-many relationship
-- that is present between images and tags.
CREATE TABLE tag_assignment (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    image_id INTEGER NOT NULL,
    tag_id INTEGER
);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (1, 1, 3);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (2, 2, 3);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (3, 3, 1);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (4, 3, 3);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (5, 4, 2);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (6, 4, 3);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (7, 5, 1);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (8, 6, 1);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (9, 7, 2);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (10, 8, 5);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (11, 9, 4);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (12, 9, 5);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (13, 9, 2);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (14, 10, 3);
INSERT INTO tag_assignment (id, image_id, tag_id) VALUES (15, 10, 4);

CREATE TABLE tags (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    tag TEXT NOT NULL UNIQUE
);
INSERT INTO tags (id, tag) VALUES (1, 'Animals');
INSERT INTO tags (id, tag) VALUES (2, 'Artwork');
INSERT INTO tags (id, tag) VALUES (3, 'Landscape');
INSERT INTO tags (id, tag) VALUES (4, 'Space');
INSERT INTO tags (id, tag) VALUES (5, 'Technology');
-- ******************************************************************


COMMIT;
