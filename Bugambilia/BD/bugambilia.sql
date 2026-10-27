CREATE DATABASE IF NOT EXISTS bugambilia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bugambilia;

create table users(
id_user int primary key AUTO_INCREMENT,
name varchar(80) NOT NULL,
email varchar(254) NOT NULL UNIQUE,
rol boolean NOT NULL DEFAULT 0,
psswd varchar(255) NOT NULL
)engine = InnoDB;

create table Events(
id_event int primary key AUTO_INCREMENT,
event varchar (255),
descriptione varchar (300),
dateevent date,
img varchar (255)
)engine = InnoDB;

create table donations(
id_donation int primary key AUTO_INCREMENT,
quantity DECIMAL(12,2),
date date,
id_user int,
FOREIGN KEY (id_user) REFERENCES users(id_user) on update cascade on delete no action
)engine = InnoDB;

create table categories(
id_cat int primary key AUTO_INCREMENT,
ncategory varchar (100),
descriptionc varchar(255),
stock int
)engine = InnoDB;

create table plants(
id_plant int primary key AUTO_INCREMENT,
descriptionp varchar (255),
nplant varchar(100),
quantityp int,
costp DECIMAL(12,2),
img varchar(255),
id_cat int,
foreign key (id_cat) references categories(id_cat) on update cascade on delete no action
)engine = InnoDB;

create table sales(
id_sale int primary key AUTO_INCREMENT,
dates date,
totals DECIMAL(12,2),
id_plant int,
id_user int,
foreign key (id_plant) references plants(id_plant) on update cascade on delete no action,
FOREIGN KEY (id_user) REFERENCES users(id_user) on update cascade on delete no action
)engine = InnoDB;


CREATE TABLE auth_attempts (key_hash CHAR(64) PRIMARY KEY, attempts INT NOT NULL DEFAULT 0, expires_at BIGINT NOT NULL, INDEX(expires_at)) ENGINE=InnoDB;
