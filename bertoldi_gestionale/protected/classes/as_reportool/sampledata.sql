CREATE TABLE animal_categories (
      categoryid INT(10) not null auto_increment,
      categoryname VARCHAR(32) default '',
      primary key(categoryid));

INSERT INTO animal_categories VALUES(1,'Sea animals');
INSERT INTO animal_categories VALUES(2,'Jungle animals');

CREATE TABLE animals (
      animalid INT(10) not null auto_increment,
      category INT(10) not null default 0,
      animalname VARCHAR(32) default '',
      primary key(animalid));

INSERT INTO animals VALUES(1,1,'whales');
INSERT INTO animals VALUES(2,1,'sharks');
INSERT INTO animals VALUES(3,1,'dolphins');

INSERT INTO animals VALUES(4,2,'elephants');
INSERT INTO animals VALUES(5,2,'gorillas');

CREATE TABLE big_zoo (
      itemid INT(10) not null auto_increment,
      animalid INT(10) not null default 0,
      nickname VARCHAR(32) default '',
      birth    DATE,
      gender CHAR(1) default 'm',
      weight INT(6) default 0,
      primary key(itemid));

/*whales */
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(1,'Whale-Boy','1997-06-22','m',7000);
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(1,'Whale-Girl','2000-08-01','f',6500);
/* sharks */
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(2,'BloodyJaws','2001-09-15','m',1500);
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(2,'HungryMary','2002-11-10','f',1200);

/* dolphins */
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(3,'BigBob','1996-01-12','m',3500);
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(3,'BigMartha','1998-05-20','f',3000);

/* elephants */
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(4,'LazyTom','2004-10-23','m',5000);
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(4,'MightyJack','2003-06-16','m',5300);
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(4,'Samantha-Shy','2004-04-24','f',4200);

/* gorillas */
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(5,'Gorilla-Dad','2000-02-05','m',180);
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(5,'Gorilla-Mom','2001-08-23','f',150);
INSERT INTO big_zoo (animalid,nickname,birth,gender,weight) VALUES(5,'Gorilla-Son','2008-04-15','m',40);
