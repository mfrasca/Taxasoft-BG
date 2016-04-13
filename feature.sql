CREATE TABLE IF NOT EXISTS `features` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `features` (`id`, `code`, `description`) VALUES
('','F','Monocarpic (DTF)'),
('','O','invasive weed (DTF)'),
('','R','Climber (DTF)'),
('','S','Succulent (DTF)'),
('','T','Epiphytic (DTF)'),
('','W','Water Plant (DTF)'),
('','X','Parasitic (DTF)'),
('','Z','Carnivorous plant (DTF)');

CREATE TABLE IF NOT EXISTS `tolerance` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `description` varchar(150) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `tolerance` (`id`, `code`, `description`) VALUES

('','C','Cover against moisture/frost (DTF)'),
('','F','Frost damage possible (DTF)'),
('','G','Greenhouse (DTF)'),
('','H','Completely hardy (DTF)');

