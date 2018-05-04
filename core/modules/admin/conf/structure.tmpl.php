<?
$tabTmpl=Array
(
	"actualites" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"titre" => Array("Type" => "varchar(150)", "Default" => "Titre" ),
		"message" => Array("Type" => "text", ),
		"dte_mail" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"mail" => Array("Type" => "enum('oui','non')", "Default" => "non", ),
		"actif" => Array("Type" => "enum('oui','non')", "Default" => "oui", "Index"=>1),
		"uid_creat" => Array("Type" => "int(10) unsigned", "Index" => "1", ),
		"dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"uid_modif" => Array("Type" => "int(11)", ),
		"dte_modif" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
	),
	"config" => Array
	(
		"param" => Array("Type" => "varchar(20)", ),
		"value" => Array("Type" => "varchar(20)", ),
		"dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00" ),
	),
	"cron" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"description" => Array("Type" => "varchar(40)", ),
		"module" => Array("Type" => "varchar(20)", ),
		"script" => Array("Type" => "varchar(20)", ),
		"schedule" => Array("Type" => "int(10) unsigned", ),
		"lastrun" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"nextrun" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"txtretour" => Array("Type" => "varchar(20)", ),
		"txtlog" => Array("Type" => "text", ),
		"actif" => Array("Type" => "enum('oui','non')", "Default" => "oui", ),
	),
	"disponibilite" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"uid" => Array("Type" => "int(10) unsigned", "Index" => "1", ),
		"dte_deb" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"dte_fin" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"uid_maj" => Array("Type" => "int(10) unsigned", "Default" => 0, ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
	),
	"document" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"name" => Array("Type" => "varchar(100)", ),
		"filename" => Array("Type" => "varchar(20)", ),
		"uid" => Array("Type" => "int(10) unsigned", "Default" => 0, "Index" => "1", ),
		"type" => Array("Type" => "varchar(10)", "Index" => "1", ),
		"dossier" => Array("Type" => "tinytext", ),
		"droit" => Array("Type" => "varchar(3)", ),
		"actif" => Array("Type" => "enum('oui','non')", "Default" => "oui", "Index"=>1),
		"uid_creat" => Array("Type" => "int(10) unsigned","Default" => 0, ),
		"dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
	),
	"droits" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"groupe" => Array("Type" => "varchar(5)", "Index" => "1", ),
		"uid" => Array("Type" => "int(10) unsigned", "Default" => 0, "Index" => "1", ),
		"uid_creat" => Array("Type" => "int(10) unsigned", "Default" => 0, ),
		"dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
	),
	"echeance" => Array
	(
		"id" => Array("Type" => "bigint(20) unsigned", "Index" => "PRIMARY", ),
		"typeid" => Array("Type" => "int(10) unsigned", "Index" => "1", ),
		"uid" => Array("Type" => "int(10) unsigned", "Index" => "1", ),
		"dte_echeance" => Array("Type" => "date", ),
		"paye" => Array("Type" => "enum('oui','non')", "Default" => "non", ),
		"actif" => Array("Type" => "enum('oui','non')", "Default" => "oui", "Index"=>1 ),
		"dte_create" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"uid_create" => Array("Type" => "int(10) unsigned", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"uid_maj" => Array("Type" => "int(10) unsigned", ),
	),
	"echeancetype" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"description" => Array("Type" => "varchar(100)", ),
		"poste" => Array("Type" => "int(11)", "Index" => "1", ),
		"cout" => Array("Type" => "decimal(10,2)", "Default" => "0.00", ),
		"resa" => Array("Type" => "enum('obligatoire','instructeur','facultatif')", ),
		"droit" => Array("Type" => "varchar(3)", ),
		"multi" => Array("Type" => "enum('oui','non')", "Default" => "non", ),
		"notif" => Array("Type" => "enum('oui','non')", "Default" => "non", ),
		"delai" => Array("Type" => "tinyint(3) unsigned", "Default" => "30", ),
	),
	"export" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"nom" => Array("Type" => "varchar(50)", ),
		"description" => Array("Type" => "text", ),
		"requete" => Array("Type" => "text", ),
		"param" => Array("Type" => "varchar(50)", ),
		"droit_r" => Array("Type" => "char(3)", ),
	),
	"flight" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"uid" => Array("Type" => "int(10) unsigned", "Index"=>"1"),
		"dte_flight" => Array("Type" => "date", "Default" => "0000-00-00"),
		"callsign" => Array("Type" => "varchar(8)", "Index"=>"1"),
		"type" => Array("Type" => "varchar(4)"),
		"comment" => Array("Type" => "varchar(50)"),
		"time_dc_day" => Array("Type" => "int(10) unsigned"),
		"time_cdb_day" => Array("Type" => "int(10) unsigned"),
		"time_dc_night" => Array("Type" => "int(10) unsigned"),
		"time_cdb_night" => Array("Type" => "int(10) unsigned"),
		"time_simu" => Array("Type" => "int(10) unsigned"),
		"nb_ifr" => Array("Type" => "int(10) unsigned"),
		"nb_att" => Array("Type" => "int(10) unsigned"),
		"nb_amerr" => Array("Type" => "int(10) unsigned")
	),
	"forums" => Array
	(
		"id" => Array("Type" => "mediumint(8) unsigned", "Index" => "PRIMARY", ),
		"fid" => Array("Type" => "mediumint(8) unsigned", "Default" => "0", "Index" => "1", ),
		"fil" => Array("Type" => "mediumint(8) unsigned", "Default" => "0", "Index" => "1", ),
		"titre" => Array("Type" => "varchar(104)", ),
		"message" => Array("Type" => "text", ),
		"pseudo" => Array("Type" => "varchar(104)", ),
		"mail_diff" => Array("Type" => "varchar(104)", ),
		"actif" => Array("Type" => "enum('oui','non')", "Default" => "oui", "Index" => "1", ),
		"droit_r" => Array("Type" => "char(3)", ),
		"droit_w" => Array("Type" => "char(3)", ),
		"uid_creat" => Array("Type" => "int(10) unsigned", "Default" => "0", "Index" => "1", ),
		"dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00", ),
		"uid_maj" => Array("Type" => "int(10) unsigned", "Default" => "0", "Index" => "1", ),
		"mailing" => Array("Type" => "int(11)", "Default" => "0", ),
	),
	"forums_lus" => Array
	(
		"forum_id" => Array("Type" => "mediumint(8) unsigned", "Index" => "PRIMARY", ),
		"forum_msg" => Array("Type" => "mediumint(8) unsigned", "Index" => "1", ),
		"forum_usr" => Array("Type" => "int(10) unsigned", "Index" => "1", ),
		"forum_date" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
	),
	"groupe" => Array
	(
		"groupe" => Array("Type" => "varchar(5)", "Index" => "PRIMARY", ),
		"description" => Array("Type" => "varchar(200)", ),
	),
	"historique" => Array
	(
		"id" => Array("Type" => "bigint(20) unsigned", "Index" => "PRIMARY", ),
		"class" => Array("Type" => "varchar(20)", ),
		"table" => Array("Type" => "varchar(20)", "Index" => "1", ),
		"idtable" => Array("Type" => "bigint(20) unsigned", "Index" => "1", ),
		"uid_maj" => Array("Type" => "int(10) unsigned", "Default" => "0", "Index" => "1", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"type" => Array("Type" => "varchar(3)", ),
		"comment" => Array("Type" => "text", ),
	),
	"login" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"username" => Array("Type" => "varchar(100)", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00", ),
		"header" => Array("Type" => "varchar(200)", ),
	),
	"roles" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"groupe" => Array("Type" => "varchar(5)", "Index" => "1", ),
		"role" => Array("Type" => "varchar(40)", "Index" => "1", ),
	),
	"utildonnees" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"did" => Array("Type" => "int(10) unsigned", "Index" => "1", ),
		"uid" => Array("Type" => "int(11)", "Index" => "1", ),
		"valeur" => Array("Type" => "varchar(255)", ),
	),
	"utildonneesdef" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"ordre" => Array("Type" => "tinyint(3) unsigned", ),
		"nom" => Array("Type" => "varchar(20)", ),
		"type" => Array("Type" => "varchar(10)", ),
		"actif" => Array("Type" => "enum('oui','non')", "Default" => "oui", "Index" => "1"),
	),
	"utilisateurs" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"nom" => Array("Type" => "varchar(40)", ),
		"prenom" => Array("Type" => "varchar(40)", ),
		"initiales" => Array("Type" => "char(3)", ),
		"password" => Array("Type" => "varchar(50)", ),
		"mail" => Array("Type" => "varchar(104)", ),
		"notification" => Array("Type" => "enum('oui','non')", "Default" => "oui", ),
		"commentaire" => Array("Type" => "text", ),
		"time_dc_day" => Array("Type" => "int(10) unsigned"),
		"time_cdb_day" => Array("Type" => "int(10) unsigned"),
		"time_dc_night" => Array("Type" => "int(10) unsigned"),
		"time_cdb_night" => Array("Type" => "int(10) unsigned"),
		"time_simu" => Array("Type" => "int(10) unsigned"),
		"droits" => Array("Type" => "varchar(100)", ),
		"actif" => Array("Type" => "enum('oui','non','off')", "Default" => "oui", "Index" => "1", ),
		"virtuel" => Array("Type" => "enum('oui','non')", "Default" => "non", "Index" => "1", ),
		"dte_creat" => Array("Type" => "date", "Default" => "0000-00-00"),
		"dte_login" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"aff_msg" => Array("Type" => "tinyint(3) unsigned", "Default" => "0", ),
		"uid_maj" => Array("Type" => "smallint(5) unsigned", "Default" => "0", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00", ),
	)

);



?>