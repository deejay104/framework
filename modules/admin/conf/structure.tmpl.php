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
		"uid_maj" => Array("Type" => "int(11)", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
	),
	"ameliore_com" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"fid" => Array("Type" => "int(10) unsigned", "Index" => "1"),
		"description"=>Array("Type"=>"text"),
		"actif"=>Array("Type"=>"enum('oui','non')", "Default" => "oui", "Index" => "1",),
		"uid_dist" => Array("Type" => "int(10) unsigned", "Default" => "0"),
		"uid_creat" => Array("Type" => "int(10) unsigned", "Default" => "0"),
		"dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"uid_maj" => Array("Type" => "int(10) unsigned", "Default" => "0", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00", ),
	),
	"config" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
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
		"uid_maj" => Array("Type" => "int(10) unsigned","Default" => 0, ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
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
		"dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"uid_creat" => Array("Type" => "int(10) unsigned", ),
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
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"forum_id" => Array("Type" => "mediumint(8) unsigned"),
		"forum_msg" => Array("Type" => "mediumint(8) unsigned", "Index" => "1", ),
		"forum_usr" => Array("Type" => "int(10) unsigned", "Index" => "1", ),
		"forum_date" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
	),
	"groupe" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"groupe" => Array("Type" => "varchar(5)", "Index" => "PRIMARY", ),
		"description" => Array("Type" => "varchar(200)", ),
		"principale" => Array("Type" => "enum('oui','non')", "Default" => "non", "Index" => "1", ),
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
	"mailtmpl" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"nom" => Array("Type" => "varchar(12)","Index"=>"1"),
		"titre" => Array("Type" => "varchar(50)"),
		"corps" => Array("Type" => "text"),
		"balise" => Array("Type" => "varchar(100)"),
		"uid_creat" => Array("Type" => "int(10) unsigned", "Default" => "0"),
		"dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00"),
		"uid_maj" => Array("Type" => "int(10) unsigned", "Default" => "0", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00", ),
	),
	"roles" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"groupe" => Array("Type" => "varchar(5)", "Index" => "1", ),
		"role" => Array("Type" => "varchar(40)", "Index" => "1", ),
		"autorise" => Array("Type" => "enum('oui','non')", "Default" => "oui", "Index" => "1")
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
		"droits" => Array("Type" => "varchar(100)", ),
		"groupe" => Array("Type" => "varchar(5)", "Default"=>"ALL"),
		"actif" => Array("Type" => "enum('oui','non','off')", "Default" => "oui", "Index" => "1", ),
		"virtuel" => Array("Type" => "enum('oui','non')", "Default" => "non", "Index" => "1", ),
		"language" => Array("Type" => "enum('fr','en')", "Default" => "fr" ),
		"aff_msg" => Array("Type" => "tinyint(3) unsigned", "Default" => "0", ),
		"dte_login" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		"uid_creat" => Array("Type" => "int(10) unsigned", "Default" => "0"),
		"dte_creat" => Array("Type" => "date", "Default" => "0000-00-00"),
		"uid_maj" => Array("Type" => "int(10) unsigned", "Default" => "0", ),
		"dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00", ),
	),
	"token" => Array
	(
		"id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		"uid" => Array("Type" => "int(10) unsigned", "Index" => "1", ),
		"token" => Array("Type" => "varchar(64)", ),
		"active" => Array("Type" => "enum('oui','non')", "Default"=>"oui"),
		"dte_creat" => Array("Type" => "datetime", "Default"=>"0000-00-00 00:00:00"),
		"dte_expire" => Array("Type" => "datetime", "Default"=>"0000-00-00 00:00:00"),
	),

);

	// "ameliorations" => Array
	// (
		// "id" => Array("Type" => "int(10) unsigned", "Index" => "PRIMARY", ),
		// "titre"=>Array("Type"=>"varchar(100)"),
		// "description"=>Array("Type"=>"text"),
		// "version"=>Array("Type"=>"varchar(10)"),
		// "status"=>Array("Type"=>"varchar(10)","Index" => "1"),
		// "module"=>Array("Type"=>"varchar(10)","Index" => "1"),
		// "actif"=>Array("Type"=>"enum('oui','non')", "Default" => "oui", "Index" => "1",),
		// "uid_dist" => Array("Type" => "int(10) unsigned", "Default" => "0"),
		// "mail_dist" => Array("Type" => "varchar(104)"),
		// "uid_creat" => Array("Type" => "int(10) unsigned", "Default" => "0"),
		// "dte_creat" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00"),
		// "uid_maj" => Array("Type" => "int(10) unsigned", "Default" => "0", ),
		// "dte_maj" => Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00", ),
	// ),
	
	require_once ("class/amelioration.inc.php");
	$obj=new amelioration_core(0,$sql);
	$obj->genSqlTab($tabTmpl);


?>