CREATE TABLE IF NOT EXISTS `f_memory` (
     `ID` INT(10) NOT NULL AUTO_INCREMENT,
     `USER_ID` INT(10),
     `FULL_NAME` VARCHAR(128),
     `MEMORY_SIZE` VARCHAR(128) DEFAULT ' ',
     PRIMARY KEY (ID)
);