#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	old_password_list varchar(255) DEFAULT '' NOT NULL,
	lastlogin_attempt int(11) unsigned DEFAULT '0' NOT NULL,
	login_blocked_endtime int(11) unsigned DEFAULT '0' NOT NULL,
	login_attempt_failure int(11) DEFAULT '0' NOT NULL,
);