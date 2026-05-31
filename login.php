<?php
// ---------------------------------------------------------------------------------------------
//   Page de Login
//   
// ---------------------------------------------------------------------------------------------
//   Variables  : 
// ---------------------------------------------------------------------------------------------


// ---- Récupère les variables transmises
	$redirect=checkVar("redirect","varchar");
  	$url=$MyOpt["host"].(($redirect!="") ? $redirect : "/index.php?".$_SERVER['QUERY_STRING']);

// ---- Force la timezone
	if ($MyOpt["timezone"]!="")
	  { date_default_timezone_set($MyOpt["timezone"]); }

// ---- Charge les variables
	require_once("lib/fonctions.inc.php");

// ---- Charge le fichier de langue
	if ((isset($MyOpt["DefaultLanguage"])) && ($MyOpt["DefaultLanguage"]!=""))
	{
		$lang=$MyOpt["DefaultLanguage"];
	}
	else
	{
		$lang="fr";
	}
	$tabLang=array();
	//require (MyRep("lang.".$lang.".php","default",false));
 
	if (MyRep("lang.".$lang.".php","default",false)!="")
	{
		require (MyRep("lang.".$lang.".php","default",false));
	}
	else if (MyRep("lang.".$MyOpt["DefaultLanguage"].".php","default",false)!="")
	{
		require (MyRep("lang.".$MyOpt["DefaultLanguage"].".php","default",false));
	}

// ---- Charge les prérequis
	require_once ("class/xtpl.inc.php");
	require_once ("class/mysql.inc.php");


// ---- Charge le numéro de version
	require ("version.php");

// ---- Charge les templates
	$module="modules";
	$tmpl_prg = LoadTemplate("login","default");

// ---- Connection à la base de données
	$sql   = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

// ---- Test si l'on a validé la page
	if ($fonc == "logout")
	{
		// TODO : Efface le token de la base si on le trouve
		
		clearSessionCookie();
		clearRefreshCookie();

		header('Location: /', true, 303);
		exit;
	}


// ---- Affiche la page

	$tmpl_prg->assign("url", $url);
	if ($fonc=="resetpwd")
	{
		$tmpl_prg->assign("post_token", generateToken(0,5,$base="token_post"));
	}
	else
	{
		$tmpl_prg->assign("post_token", "");
	}

	$tmpl_prg->assign("version", $version."-".$core_version.(($MyOpt["maintenance"]=="on") ? " - ".ucwords($tabLang["core_maintenance"]) : ""));
	$tmpl_prg->assign("site_title", $MyOpt["site_title"]);
	$tmpl_prg->assign("corefolder", $corefolder);
	$tmpl_prg->assign("rootfolder", $MyOpt["host"]);

	$tmpl_prg->assign("style_url", $MyOpt["host"]."/".GenereStyle("default"));

	if (file_exists($appfolder."/custom/".$MyOpt["site_logo"]))
	{
		$tmpl_prg->assign("site_logo", "custom/".$MyOpt["site_logo"]);
	}
	else
	{
		$tmpl_prg->assign("site_logo", $corefolder."/static/images/logo.png");
	}

// ---- Affiche la page
	
	$tmpl_prg->parse("main");
	echo $tmpl_prg->text("main");


?>
