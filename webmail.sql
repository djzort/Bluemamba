# BlueMamba Version 1.0.9

#CREATE DATABASE webmail;
#USE webmail;




CREATE TABLE users 
 (
    id mediumint(9) NOT NULL auto_increment,
    login varchar(50) NOT NULL,
    host varchar(50) NOT NULL,
    dateCreated int(12),
    lastLogin int(12),
    userLevel int(3),
    PRIMARY KEY (id),
    KEY id (id),
    UNIQUE id_2 (id),
    KEY login (login),
    KEY host (host)
 );




CREATE TABLE contacts 
 (
    id mediumint(9) NOT NULL auto_increment,
    owner mediumint(9) NOT NULL,
    name text,
    email text,
    email2 text,
    grp text,
    aim text,
    icq text,
    msn text,
    yahoo text,
    jabber text,
    phone text,
    work text,
    cell text,
    fax text,
    address text,
    url text,
    comments text,
    PRIMARY KEY (id),
    KEY id (id),
    KEY owner (owner)
 );




CREATE TABLE bookmarks
 (
    id int NOT NULL auto_increment,
    owner int NOT NULL,
    name text,
    url text,
    grp text,
    is_private char,
    comments text,
    PRIMARY KEY (id),
    KEY id (id),
    KEY owner (owner)
 );




CREATE TABLE sessions
 (
    sid varchar(64) NOT NULL,
    login text,
    password text,
    host text,
    path text,
    dataID int,
    port int,
    userLevel int(3),
    inTime int(12) NOT NULL,
    lastSend int,
    numSent int,
    PRIMARY KEY sid (sid),
    KEY inTime (inTime)
 );




CREATE TABLE colors
 (
    id int NOT NULL,
    tool_bg varchar(15),
    tool_link varchar(15),
    folder_bg varchar(15),
    folder_link varchar(15),
    main_bg varchar(15),
    main_hilite varchar(15),
    main_text varchar(15),
    main_link varchar(15),
    main_head_bg varchar(15),
    main_head_txt varchar(15),
    quotes varchar(15),
    font_family varchar(255),
    font_boxfamily varchar(255),
    font_size int(2),
    small_font_size int(2),
    menu_font_size int(2),
    folderlist_font_size int(2),
    main_darkbg varchar(15),
    main_light_txt varchar(15),
    PRIMARY KEY id (id),
    KEY id (id)
 );




CREATE TABLE prefs
 (
    id int NOT NULL,
    colorize_quotes char,
    detect_links char,
    view_max int,
    show_size char,
    save_sent char,
    delete_trash char,
    user_name text,
    email_address text,
    signature1 text,
    show_sig1 char,
    sort_field text,
    sort_order text,
    list_folders char,
    view_inside char,
    preview_window char,
    timezone int,
    html_in_frame char,
    show_images_inline char,
    subject_edit char,
    advanced_controls char,
    showContacts char,
    showCC char,
    closeAfterSend char,
    showNav char,
    compose_inside char,
    showNumUnread char,
    refresh_folderlist char,
    folderlist_interval int(3),
    radar_interval int(3),
    theme varchar(50),
    notify varchar(50),
    alt_identities text,
    main_cols varchar(10),
    main_toolbar varchar(5),
    nav_no_flag char,
    filters char,
    tmda char,
    tmda_clear char,
    PRIMARY KEY (id),
    KEY id (id)
 );




CREATE TABLE log
 (
    logTime datetime,
    logTimeStamp int(12),
    userID int,
    account text,
    action text,
    comment text,
    ip varchar(15)
 );




CREATE TABLE calendar
 (
    id int NOT NULL auto_increment,
    userID int,
    title text,
    place text,
    description text,
    participants text,
    beginTime int,
    endTime int,
    beginDate int(11),
    endDate int (11),
    pattern_day text,
    pattern_week text,
    pattern_month text,
    pattern_year text,
    isPrivate char,
    color varchar(20),
    PRIMARY KEY (id),
    KEY id (id),
    KEY userID (userID),
    KEY beginDate (beginDate),
    KEY endDate (endDate)
 );




CREATE TABLE identities
 (
    id int NOT NULL auto_increment,
    owner mediumint(9) NOT NULL,
    name varchar(128),
    email varchar(128),
    replyto varchar(128),
    sig text,
    PRIMARY KEY (id),
    KEY id (id),
    KEY owner (owner)
 );




CREATE TABLE cache
 (
    id int NOT NULL auto_increment,
    owner int NOT NULL,
    cache_key varchar(64),
    cache_data text,
    PRIMARY KEY(id),
    KEY id (id),
    KEY owner (owner),
    KEY cache_key (cache_key)
 );




CREATE TABLE filters
 (
    id mediumint(9) NOT NULL auto_increment,
    owner mediumint(9) NOT NULL,
    type text,
    syntax text,
    moveto text,
    PRIMARY KEY(id),
    KEY id (id),
    KEY owner (owner)
 );




CREATE TABLE folders
 (
    id mediumint(9) NOT NULL auto_increment,
    owner mediumint(9) NOT NULL,
    name text,
    type text,
    PRIMARY KEY(id),
    KEY id (id),
    KEY owner (owner)
 );

