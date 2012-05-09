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
	freetext longtext NOT NULL,
	PRIMARY KEY ( id )
);

CREATE TABLE review_data (
	id int(12) unsigned NOT NULL auto_increment,
	review_id int(12) unsigned NOT NULL,
	attr_id int(12) unsigned NOT NULL,
	d_value longtext,
	PRIMARY KEY ( id )
);
