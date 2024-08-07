-- MySQL Script generated by MySQL Workbench
-- Tue Jun 18 16:36:14 2024
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema pruzkumnikSVP
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema pruzkumnikSVP
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `pruzkumnikSVP` DEFAULT CHARACTER SET utf8 ;
USE `pruzkumnikSVP` ;

-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`skola`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`skola` (
  `skolaID` INT NOT NULL AUTO_INCREMENT,
  `jmenoSkoly` VARCHAR(200) NULL,
  PRIMARY KEY (`skolaID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`svp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`svp` (
  `svpID` INT NOT NULL AUTO_INCREMENT,
  `jmenoSVP` VARCHAR(200) NULL,
  `popisSVP` LONGTEXT NULL,
  `skola_skolaID` INT NOT NULL,
  PRIMARY KEY (`svpID`),
  CONSTRAINT `fk_svp_skola1`
    FOREIGN KEY (`skola_skolaID`)
    REFERENCES `pruzkumnikSVP`.`skola` (`skolaID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX IF NOT EXISTS `fk_svp_skola1_idx` ON `pruzkumnikSVP`.`svp` (`skola_skolaID` ASC);


-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`cil`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`cil` (
  `cilID` INT NOT NULL AUTO_INCREMENT,
  `jmenoCile` VARCHAR(200) NULL,
  `popisCile` LONGTEXT NULL,
  `svp_svpID` INT NOT NULL,
  PRIMARY KEY (`cilID`),
  CONSTRAINT `fk_cil_svp1`
    FOREIGN KEY (`svp_svpID`)
    REFERENCES `pruzkumnikSVP`.`svp` (`svpID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX IF NOT EXISTS `fk_cil_svp1_idx` ON `pruzkumnikSVP`.`cil` (`svp_svpID` ASC);


-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`vzdelavaciObor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`vzdelavaciObor` (
  `vzdelavaciOborID` INT NOT NULL AUTO_INCREMENT,
  `jmenoOboru` VARCHAR(200) NULL,
  `popisOboru` LONGTEXT NULL,
  `rodicovskyVzdelavaciOborID` INT NULL,
  `svp_svpID` INT NOT NULL,
  PRIMARY KEY (`vzdelavaciOborID`),
  CONSTRAINT `fk_vzdelavaciObor_vzdelavaciObor1`
    FOREIGN KEY (`rodicovskyVzdelavaciOborID`)
    REFERENCES `pruzkumnikSVP`.`vzdelavaciObor` (`vzdelavaciOborID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_vzdelavaciObor_svp1`
    FOREIGN KEY (`svp_svpID`)
    REFERENCES `pruzkumnikSVP`.`svp` (`svpID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX IF NOT EXISTS `fk_vzdelavaciObor_vzdelavaciObor1_idx` ON `pruzkumnikSVP`.`vzdelavaciObor` (`rodicovskyVzdelavaciOborID` ASC);

CREATE INDEX IF NOT EXISTS `fk_vzdelavaciObor_svp1_idx` ON `pruzkumnikSVP`.`vzdelavaciObor` (`svp_svpID` ASC);


-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`vzdelavaciObsah`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`vzdelavaciObsah` (
  `vzdelavaciObsahID` INT NOT NULL AUTO_INCREMENT,
  `jmenoObsahu` VARCHAR(200) NULL,
  `popisObsahu` LONGTEXT NULL,
  `rodicovskyVzdelavaciObsahID` INT NULL,
  `cil_cilID` INT NULL,
  `svp_svpID` INT NOT NULL,
  PRIMARY KEY (`vzdelavaciObsahID`),
  CONSTRAINT `fk_vzdelavaciObsah_vzdelavaciObsah1`
    FOREIGN KEY (`rodicovskyVzdelavaciObsahID`)
    REFERENCES `pruzkumnikSVP`.`vzdelavaciObsah` (`vzdelavaciObsahID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_vzdelavaciObsah_svp1`
    FOREIGN KEY (`svp_svpID`)
    REFERENCES `pruzkumnikSVP`.`svp` (`svpID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX IF NOT EXISTS `fk_vzdelavaciObsah_vzdelavaciObsah1_idx` ON `pruzkumnikSVP`.`vzdelavaciObsah` (`rodicovskyVzdelavaciObsahID` ASC);

CREATE INDEX IF NOT EXISTS `fk_vzdelavaciObsah_svp1_idx` ON `pruzkumnikSVP`.`vzdelavaciObsah` (`svp_svpID` ASC);


-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`typAktivity`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`typAktivity` (
  `typAktivityID` INT NOT NULL AUTO_INCREMENT,
  `jmenoTypu` VARCHAR(200) NULL,
  `popisTypu` LONGTEXT NULL,
  PRIMARY KEY (`typAktivityID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`vzdelavaciAktivita`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`vzdelavaciAktivita` (
  `vzdelavaciAktivitaID` INT NOT NULL AUTO_INCREMENT,
  `jmenoAktivity` VARCHAR(200) NULL,
  `popisAktivity` LONGTEXT NULL,
  `typAktivity_typAktivityID` INT NOT NULL,
  `svp_svpID` INT NOT NULL,
  PRIMARY KEY (`vzdelavaciAktivitaID`),
  CONSTRAINT `fk_vzdelavaciAktivita_typAktivity`
    FOREIGN KEY (`typAktivity_typAktivityID`)
    REFERENCES `pruzkumnikSVP`.`typAktivity` (`typAktivityID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_vzdelavaciAktivita_svp1`
    FOREIGN KEY (`svp_svpID`)
    REFERENCES `pruzkumnikSVP`.`svp` (`svpID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX IF NOT EXISTS `fk_vzdelavaciAktivita_typAktivity_idx` ON `pruzkumnikSVP`.`vzdelavaciAktivita` (`typAktivity_typAktivityID` ASC);

CREATE INDEX IF NOT EXISTS `fk_vzdelavaciAktivita_svp1_idx` ON `pruzkumnikSVP`.`vzdelavaciAktivita` (`svp_svpID` ASC);


-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`soucastAktivity`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`soucastAktivity` (
  `soucastAktivityID` INT NOT NULL AUTO_INCREMENT,
  `jmenoSoucasti` VARCHAR(200) NULL,
  `popisSoucasti` LONGTEXT NULL,
  `vzdelavaciAktivita_vzdelavaciAktivitaID` INT NOT NULL,
  `vzdelavaciObor_vzdelavaciOborID` INT NOT NULL,
  `vzdelavaciObsah_vzdelavaciObsahID` INT NOT NULL,
  PRIMARY KEY (`soucastAktivityID`),
  CONSTRAINT `fk_soucastAktivity_vzdelavaciAktivita1`
    FOREIGN KEY (`vzdelavaciAktivita_vzdelavaciAktivitaID`)
    REFERENCES `pruzkumnikSVP`.`vzdelavaciAktivita` (`vzdelavaciAktivitaID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_soucastAktivity_vzdelavaciObor1`
    FOREIGN KEY (`vzdelavaciObor_vzdelavaciOborID`)
    REFERENCES `pruzkumnikSVP`.`vzdelavaciObor` (`vzdelavaciOborID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_soucastAktivity_vzdelavaciObsah1`
    FOREIGN KEY (`vzdelavaciObsah_vzdelavaciObsahID`)
    REFERENCES `pruzkumnikSVP`.`vzdelavaciObsah` (`vzdelavaciObsahID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX IF NOT EXISTS `fk_soucastAktivity_vzdelavaciAktivita1_idx` ON `pruzkumnikSVP`.`soucastAktivity` (`vzdelavaciAktivita_vzdelavaciAktivitaID` ASC);

CREATE INDEX IF NOT EXISTS `fk_soucastAktivity_vzdelavaciObor1_idx` ON `pruzkumnikSVP`.`soucastAktivity` (`vzdelavaciObor_vzdelavaciOborID` ASC);

CREATE INDEX IF NOT EXISTS `fk_soucastAktivity_vzdelavaciObsah1_idx` ON `pruzkumnikSVP`.`soucastAktivity` (`vzdelavaciObsah_vzdelavaciObsahID` ASC);


-- -----------------------------------------------------
-- Table `pruzkumnikSVP`.`plneniCile`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pruzkumnikSVP`.`plneniCile` (
  `plneniCileID` INT NOT NULL AUTO_INCREMENT,
  `popisPlneniCile` LONGTEXT NULL,
  `cil_cilID` INT NOT NULL,
  `vzdelavaciObsah_vzdelavaciObsahID` INT NOT NULL,
  PRIMARY KEY (`plneniCileID`),
  CONSTRAINT `fk_plneniCile_cil1`
    FOREIGN KEY (`cil_cilID`)
    REFERENCES `pruzkumnikSVP`.`cil` (`cilID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_plneniCile_vzdelavaciObsah1`
    FOREIGN KEY (`vzdelavaciObsah_vzdelavaciObsahID`)
    REFERENCES `pruzkumnikSVP`.`vzdelavaciObsah` (`vzdelavaciObsahID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX IF NOT EXISTS `fk_plneniCile_cil1_idx` ON `pruzkumnikSVP`.`plneniCile` (`cil_cilID` ASC);

CREATE INDEX IF NOT EXISTS `fk_plneniCile_vzdelavaciObsah1_idx` ON `pruzkumnikSVP`.`plneniCile` (`vzdelavaciObsah_vzdelavaciObsahID` ASC);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
