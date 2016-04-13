CREATE TABLE IF NOT EXISTS `itf_accsta` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_accsta` (`id`, `code`, `description`) VALUES
('','C','Current accession in the living collection'),
('','D','Non-current accession of the living collection due to death'),
('','T','Non-current accession due to transfer to another record system, normally of another garden'),
('','S','Stored in a dormant state'),
('','O','Other accession status  - different from those above.');

CREATE TABLE IF NOT EXISTS `itf_acct` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_acct` (`id`, `code`, `description`) VALUES
('','P','Whole plant'),
('','S','Seed or Spore'),
('','V','Vegetative part'),
('','T','Tissue culture'),
('','O','Other');

CREATE TABLE IF NOT EXISTS `itf_brs` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_brs` (`id`, `code`, `description`) VALUES
('','M','Male'),
('','F','Female'),
('','B','includes both \'male\' and \'female\' individuals'),
('','Q','Dioecious plant of unknown sex'),
('','H','hermaphrodite or is monoecious'),
('','H1','hermaphrodite or is monoecious, but self-incompatible.'),
('','A','reproduces by agamospermy'),
('','U','Insufficient information to dedescriptionine breeding system.');

CREATE TABLE IF NOT EXISTS `itf_dont` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_dont` (`id`, `code`, `description`) VALUES
('','E','Expedition'),
('','G','Gene bank'),
('','B','Botanic Garden or Arboretum'),
('','R','Other research, field or experimental station'),
('','S','Staff of the botanic garden to which record system applies'),
('','U','University Department'),
('','H','Horticultural Association or Garden Club'),
('','M','Municipal department'),
('','N','Nursery or other commercial establishment'),
('','I','Individual'),
('','O','Other'),
('','U2','Unknown');
      
CREATE TABLE IF NOT EXISTS `itf_hyb` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_hyb` (`id`, `code`, `description`) VALUES
('','H','A hybrid formula'),
('','x','A hybrid'),
('','+','A graft hybrid or graft chimaera');
      
CREATE TABLE IF NOT EXISTS `itf_idql` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_idql` (`id`, `code`, `description`) VALUES
('','aff.','Akin to or bordering'),
('','cf.','Compare with'),
('','Incorrect','Incorrect'),
('','forsan','Perhaps'),
('','near','Close to'),
('','?','Questionable');
      
CREATE TABLE IF NOT EXISTS `itf_per` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_per` (`id`, `code`, `description`) VALUES
('','M','Monocarpic plants'),
('','MA','Annuals'),
('','MB','Biennials and short-lived perennials'),
('','ML','Long-lived monocarpic plants'),
('','P','Polycarpic plants'),
('','PD','Deciduous polycarpic plants'),
('','PE','Evergreen polycarpic plants'),
('','U','Uncertain which of the above applies.');
      
CREATE TABLE IF NOT EXISTS `itf_prohis` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_prohis` (`id`, `code`, `description`) VALUES
('','I','Individual wild plant(s)'),
('','S','Seeds from sexual reproduction (excluding apomixis)'),
('','SA','Seeds from open breeding'),
('','SB','Seeds from controlled breeding'),
('','SC','Seeds from self-pollinated isolated plants'),
('','V','Plant material derived asexually'),
('','VA','Vegetative reproduction'),
('','VB','Vegetative from apomictic cloning (agamospermy)'),
('','U','Uncertain, or no information.');
      
CREATE TABLE IF NOT EXISTS `itf_prot` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_prot` (`id`, `code`, `description`) VALUES
('','W','Accession of wild source'),
('','Z','From a wild source plant in cultivation'),
('','G','Not of wild source');
      
CREATE TABLE IF NOT EXISTS `itf_rk` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_rk` (`id`, `code`, `description`) VALUES
('','regnum','Regnum'),
('','subregnum','Subregnum'),
('','division','Division'),
('','subdivision','Subdivision'),
('','class','Class'),
('','subclass','Subclass'),
('','order','Order'),
('','family','Family'),
('','subfamily','Subfamily'),
('','tribe','Tribe'),
('','subtribe','Subtribe'),
('','genus','Genus'),
('','subgenus','Subgenus'),
('','section','Section'),
('','subsect.','Subsection'),
('','series','Series'),
('','subser.','Subseries'),
('','species','Species'),
('','nss','Nothosubspecies'),
('','subsp.','Subspecies'),
('','convar.','Convariety'),
('','var.','Variety'),
('','nva','Nothovariety'),
('','subvar.','Subvariety'),
('','f.','Form'),
('','subf.','Subform'),
('','nfo','Nothoform'),
('','sxss','Hybrid between subsp.'),
('','ssxva','Hybrid subsp. x var.'),
('','ssxfo','Hybrid subsp. x f.'),
('','vaxsf','Hybrid var. x subf.');
      
CREATE TABLE IF NOT EXISTS `itf_rkql` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_rkql` (`id`, `code`, `description`) VALUES
('','B','Below Family'),
('','F','Family'),
('','G','Genus'),
('','S','Species'),
('','I','first Infraspecific Epithet'),
('','J','second Infraspecific Epithet (new for ITF2)'),
('','C','Cultivar');
      
CREATE TABLE IF NOT EXISTS `itf_spql` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_spql` (`id`, `code`, `description`) VALUES
('','agg.','An aggregate species'),
('','s.lat.','aggregrate species (sensu lato)'),
('','s.str.','segregate species (sensu stricto)');
      
CREATE TABLE IF NOT EXISTS `itf_vlev` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_vlev` (`id`, `code`, `description`) VALUES
('','U','unknown if the name has been checked'),
('','0','not been dedescriptionined by any authority'),
('','1','dedescriptionined by comparison with other named plants'),
('','2','dedescriptionined by a taxonomist or other competent person'),
('','3','dedescriptionined by a specialist (taxonomist)'),
('','4','plant represents all or part of the type material (wrong position in ITF)');
      
CREATE TABLE IF NOT EXISTS `itf_wpst` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(25) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_wpst` (`id`, `code`, `description`) VALUES
('','Wild native','Endemic found within its indigenous range'),
('','Wild non-native','Plant found outside its indigenous range'),
('','Cultivated native','Endemic known to have been cultivated and reintroduced or translocated within its indigenous range.'),
('','Cultivated non-native','Plant known to be cultivated and found outside its indigenous range.');
      
CREATE TABLE IF NOT EXISTS `itf_accuse` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `itf_accuse` (`id`, `code`, `description`) VALUES
('','01','fruit trees and their wild relatives'),
('','02','crop plants from several regions of the world, such as cereals'),
('','03','species of value for amenity horticulture or as ornamentals'),
('','04','textile plants'),
('','05','oil plants'),
('','06','timbers'),
('','07','cork'),
('','08','resin yielding plants'),
('','09','plants used for industrial production'),
('','10','grasses, and forage plants'),
('','11','wild relatives of crops'),
('','12','underutilized or neglected crops'),
('','13','local and traditional economic plant varieties and land races'),
('','14','medicinal plants'),
('','15','plants that are important for local uses (e.g. for basket making, for tools used in cooking, fishing and agriculture)'),
('','16','perfume, essential oil and cosmetic plants'),
('','17','spice and flavouring plants'),
('','18','melifluous species'),
('','19','dyeing and tanning'),
('','20','plants for bonsai'),
('','21','systematic collections of important eceonomic plant groups such as conifers or legumes'),
('','22','temperate and tropical timber trees'),
('','23','ornamental trees');


