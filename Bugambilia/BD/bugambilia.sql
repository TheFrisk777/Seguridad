create database Bugambilia;
use Bugambilia;

create table users(
id_user int primary key,
name varchar(80),
email varchar (255),
rol boolean,
psswd varchar (255)
)engine = InnoDB;

create table Events(
id_event int primary key,
event varchar (255),
descriptione varchar (300),
dateevent date,
img varchar (255)
)engine = InnoDB;

create table donations(
id_donation int primary key,
quantity float,
date date,
id_user int,
FOREIGN KEY (id_user) REFERENCES users(id_user) on update cascade on delete no action
)engine = InnoDB;

create table categories(
id_cat int primary key,
ncategory varchar (100),
descriptionc varchar(255),
stock int
)engine = InnoDB;

create table plants(
id_plant int primary key,
descriptionp varchar (255),
nplant varchar(100),
quantityp int,
costp float,
img varchar(255),
id_cat int,
foreign key (id_cat) references categories(id_cat) on update cascade on delete no action
)engine = InnoDB;

create table sales(
id_sale int primary key,
dates date,
totals float,
id_plant int,
id_user int,
foreign key (id_plant) references plants(id_plant) on update cascade on delete no action,
FOREIGN KEY (id_user) REFERENCES users(id_user) on update cascade on delete no action
)engine = InnoDB;

