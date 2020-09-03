-- :mode=transact-sql:

CREATE TABLE venues (
	id int(12) unsigned NOT NULL auto_increment,
	v_name varchar(255) NOT NULL DEFAULT '',
	v_addr varchar(255) NOT NULL DEFAULT '',
	v_phone varchar(31) NOT NULL DEFAULT '',
	PRIMARY KEY ( id )
);

CREATE TABLE attrs (
	id int(12) unsigned NOT NULL auto_increment,
	a_name varchar(255) NOT NULL DEFAULT '',
	a_hint varchar(255) NOT NULL DEFAULT '',
	a_type varchar(15) NOT NULL DEFAULT 'string',
	a_size smallint(3) NOT NULL DEFAULT 30,
	a_options text NOT NULL,
	a_flags int unsigned NOT NULL DEFAULT 0,
	a_sort_order int NOT NULL DEFAULT 0,
	PRIMARY KEY ( id )
);

CREATE TABLE reviews (
	id int(12) unsigned NOT NULL auto_increment,
	venue_id int(12) unsigned NOT NULL,
	username varchar(63) NOT NULL DEFAULT 'User',
	overall_rating float(5, 2) NOT NULL DEFAULT 1.0,
	submit_time int(12) unsigned NOT NULL DEFAULT 0,
	flags int unsigned NOT NULL DEFAULT 0,
	edit_key char(32) NOT NULL DEFAULT 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
	freetext longtext NOT NULL,
	PRIMARY KEY ( id )
);

CREATE TABLE review_data (
	id int(12) unsigned NOT NULL auto_increment,
	review_id int(12) unsigned NOT NULL,
	schema_id int(12) unsigned NOT NULL,
	d_value longtext,
	PRIMARY KEY ( id )
);

INSERT INTO attrs(a_name, a_hint, a_type, a_size, a_options, a_flags, a_sort_order) VALUES
	('Sketchiness','Did you feel that you were going to get shot, or were you in the safe part of town?','Rating',0,'{"scale":["We''re going to get mugged", "It''s all good"],"out_of":5}',3,1),
	('Time of trip','Certain times of day affect the plate experience','Radio',0,'{"options":[""Normal" dinnertime (4PM - 8PM)",""Late-Normal" dinnertime (8PM - 12AM)", "CSH dinnertime (12AM - 2AM)", "Marks O''Clock (2AM - 5AM)", "All-nighter (5AM - 8AM)", "Daytime (8AM - 4PM)"]}',1,0),
	('Size of group:','Some locations have difficulty with large groups; others handle them fine.','Radio',1,'{"options":["Forever alone (1)","Man-date (2)","Epic bros (3-4)","Full car (5-6)","Moderate group (7-15)","Bigger group (16-30)","Massive group (31-60)","CSH (61-130)"]}',0,2),
	('Meat selection:','What kind of meat did you have on your plate?','Radio',1,'{"options":["Cheeseburger","Hamburger","Red hot","White hot","Veggie burger","Scrambled eggs","Other"]}',0,8),
	('Fries:','','Radio',1,'{"options":["French fries (straight)","French fries (crinkle)","Homefries","None"]}',0,11),
	('Second base ingredient:','','Radio',1,'{"options":["Mac salad","Baked beans","Other","None"]}',0,12),
	('Did you get hot sauce?','','Radio',1,'{"options":["Yes","No"]}',0,14),
	('Rate the hot sauce:','','Rating',5,'{"scale":["Terrible","Amazing"]}',2,15),
	('Rate the meat:','','Rating',5,'{"scale":["Food poisoning","Perfect"]}',2,9),
	('Service quality:','','Rating',5,'{"scale":["",""]}',2,4),
	('Portion size:','','Radio',1,'{"options":["I left hungry","Kinda small","About perfect (I left satisfied)","Belt buster (finishing it took some work)","Couldn''t finish it"]}',0,7),
	('Price:','','Rating',5,'{"scale":["Unfair price","I got my money''s worth"]}',2,5),
	('Response time:','How long did it take to get your food?','Rating',5,'{"scale":["Way too long","I barely blinked"]}',2,6),
	('Comment on the meat:','','String',1,'',0,10),
	('Comments on base ingredients:','','String',1,'',0,13),
	('Comments on hot sauce:','','String',1,'',0,16),
	('Comments on the environment:','','String',1,'',0,3),
	('French bread included?','','Radio',1,'{"options":["Yes","No"]}',0,17);

