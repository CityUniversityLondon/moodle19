CREATE TABLE prefix_ipodcast (
  id serial primary key,
  ipodcastcourseid integer NOT NULL default '0',
  course integer NOT NULL default '0',
  userid integer NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  summary varchar(255) NOT NULL default '',
  notes varchar(255) NOT NULL default '',
  attachment varchar(255) NOT NULL default '',
  duration varchar(50) NOT NULL default '',
  explicit smallint NOT NULL default '0',
  subtitle varchar(255) NOT NULL default '',
  keywords varchar(255) NOT NULL default '',
  topcategory integer NOT NULL default '0',
  nestedcategory integer NOT NULL default '0',
  timecreated integer  NOT NULL default '0',
  timemodified integer NOT NULL default '0',
  timestart integer NOT NULL default '0',
  timefinish integer NOT NULL default '0'
);


CREATE TABLE prefix_ipodcast_comments (
  id serial primary key,
  entryid integer NOT NULL default '0',
  userid integer NOT NULL default '0',
  comment text NOT NULL,
  timemodified integer NOT NULL default '0'
); 

CREATE INDEX prefix_ipodcast_comments_userid_idx ON prefix_ipodcast_comments (userid);
CREATE INDEX prefix_ipodcast_comments_entryid_idx ON prefix_ipodcast_comments (entryid);


CREATE TABLE prefix_ipodcast_views (
  id serial primary key,
  entryid integer NOT NULL default '0',
  userid integer NOT NULL default '0',
  views integer NOT NULL default '0',
  timemodified integer NOT NULL default '0'
);

CREATE INDEX prefix_ipodcast_views_userid_idx ON prefix_ipodcast_views (userid);
CREATE INDEX prefix_ipodcast_views_entryid_idx ON prefix_ipodcast_views (entryid);


CREATE TABLE prefix_ipodcast_courses (
  id serial primary key,
  course integer NOT NULL default '0',
  userid integer NOT NULL default '0',
  studentcanpost smallint NOT NULL default '0',
  enabletsseries smallint NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  summary varchar(255) NOT NULL default '',
  comment varchar(255) NOT NULL default '',
  StreamURL varchar(255) NOT NULL default '',
  rssarticles smallint NOT NULL default '0',
  enablerssfeed smallint NOT NULL default '0',
  enablerssitunes smallint NOT NULL default '0',
  visible smallint NOT NULL default '0',
  explicit smallint NOT NULL default '0',
  subtitle varchar(255) NOT NULL default '',
  keywords varchar(255) NOT NULL default '',
  topcategory integer NOT NULL default '0',
  nestedcategory integer NOT NULL default '0',
  timecreated integer NOT NULL default '0',
  timemodified integer NOT NULL default '0',
  sortorder integer NOT NULL default '0'
);

CREATE TABLE prefix_ipodcast_itunes_categories (
  id serial primary key,
  name text NOT NULL default ''
);

insert into prefix_ipodcast_itunes_categories values (1, 'Arts & Entertainment');
insert into prefix_ipodcast_itunes_categories values (2, 'Audio Blogs');
insert into prefix_ipodcast_itunes_categories values (3, 'Business');
insert into prefix_ipodcast_itunes_categories values (4, 'Comedy');
insert into prefix_ipodcast_itunes_categories values (5, 'Education');
insert into prefix_ipodcast_itunes_categories values (6, 'Family');
insert into prefix_ipodcast_itunes_categories values (7, 'Food');
insert into prefix_ipodcast_itunes_categories values (8, 'Health');
insert into prefix_ipodcast_itunes_categories values (9, 'International');
insert into prefix_ipodcast_itunes_categories values (10, 'Movies & Television');
insert into prefix_ipodcast_itunes_categories values (11, 'Music');
insert into prefix_ipodcast_itunes_categories values (12, 'News');
insert into prefix_ipodcast_itunes_categories values (13, 'Politics');
insert into prefix_ipodcast_itunes_categories values (14, 'Public Radio');
insert into prefix_ipodcast_itunes_categories values (15, 'Religion & Spirituality');
insert into prefix_ipodcast_itunes_categories values (16, 'Science');
insert into prefix_ipodcast_itunes_categories values (17, 'Talk Radio'); 
insert into prefix_ipodcast_itunes_categories values (18, 'Technology');
insert into prefix_ipodcast_itunes_categories values (19, 'Transportation');
insert into prefix_ipodcast_itunes_categories values (20, 'Travel');

-- reset the sequence now. ick, but we need those ids for later.
SELECT setval('prefix_ipodcast_itunes_categories_id_seq', (select max(id) from prefix_ipodcast_itunes_categories));



CREATE TABLE prefix_ipodcast_itunes_nested_categories (
  id serial primary key,
  name text NOT NULL default '',
  topcategoryid integer NOT NULL default '0'
);

insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Architecture', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Books', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Design', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Entertainment', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Games', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Performing Arts', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Photography', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Poetry', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Science Fiction', 1);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Careers', 3);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Finance', 3);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Investing', 3);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Management', 3);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Marketing', 3);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Higher Education', 5);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'K-12', 5);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Diet & Nutrition', 8);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Fitness', 8);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Relationships', 8);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Self-Help', 8);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Sexuality', 8);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Australian', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Belgian', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Brazilian', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Canadian', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Chinese', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Dutch', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'French', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'German', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Hebrew', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Italian', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Japanese', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Norwegian', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Polish', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Portuguese', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Spanish', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Swedish', 9);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Buddhish', 15);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Christianity', 15);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Islam', 15);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Judaism', 15);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'New Age', 15);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Philosophy', 15);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Spirituality', 15);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Computers', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Developers', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Gadgets', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Information Technology', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'News', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Operating Systems', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Podcasting', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Smart Phones', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Text/Speech', 18);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Automotive', 19);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Aviation', 19);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Bicycles', 19);
insert into prefix_ipodcast_itunes_nested_categories (name,topcategoryid) values ( 'Commuting', 19);

CREATE TABLE prefix_ipodcast_tsseries (
  id serial primary key,
  ipodcastcourseid integer NOT NULL default '0',
  ipodcastid integer NOT NULL default '0',
  userid integer NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  summary varchar(255) NOT NULL default '',
  notes varchar(255) NOT NULL default '',
  duration varchar(50) NOT NULL default '',
  timecreated integer NOT NULL default '0',
  timemodified integer NOT NULL default '0',
  status integer NOT NULL default '0',
  attachment varchar(255) NOT NULL default '',
  roomname varchar(255) NOT NULL default '',
  section integer NOT NULL default '0'
);

CREATE TABLE prefix_ipodcast_tsseries_config (
  id serial primary key,
  roomname char(255) NOT NULL default '',
  StreamFilePath char(255) NOT NULL default '',
  StreamURLRoot char(255) NOT NULL default '',
  License char(255) NOT NULL default ''
);



INSERT INTO prefix_log_display (module,action,mtable,field) VALUES ('ipodcast', 'view', 'ipodcast', 'name');
INSERT INTO prefix_log_display (module,action,mtable,field) VALUES ('ipodcast', 'add entry', 'ipodcast', 'name');
INSERT INTO prefix_log_display (module,action,mtable,field) VALUES ('ipodcast', 'update entry', 'ipodcast', 'name');

--These lines didnt work for Darrin Smith
--INSERT INTO prefix_config (id,name,value) VALUES (NULL,'ipodcast_enablerssfeeds', '1') ON DUPLICATE KEY UPDATE value=1;
--INSERT INTO prefix_config (id,name,value) VALUES (NULL,'ipodcast_enablerssitunes', '1') ON DUPLICATE KEY UPDATE value=1;

--INSERT INTO prefix_config VALUES (NULL,'ipodcast_enablerssfeeds', '1');
--INSERT INTO prefix_config VALUES (NULL,'ipodcast_enablerssitunes', '1');
