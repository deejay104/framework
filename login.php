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

// ---- Charge les pr�requis
	require_once ("class/xtpl.inc.php");
	require_once ("class/mysql.inc.php");


// ---- Charge le num�ro de version
	require ("version.php");

// ---- Charge les templates
	$module="modules";
	$tmpl_prg = LoadTemplate("login","default");

// ---- Connection � la base de donn�es
	$sql   = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

// ---- Test si l'on a validé la page
	if ($fonc == "logout")
	{
		/*
		if ($_COOKIE['sessid']>0)
		{
			$query="UPDATE ".$MyOpt["tbl"]."_token SET active='non' WHERE id='".$_COOKIE['sessid']."'";
			$sql->Update($query);
		}
		*/

		//$_COOKIE['uid']=0;
		//$_COOKIE['sessid']=0;
		


		exit;
	}

// ---- 
	// if ($tmpl_prg->text("main.unsecure")=="")
	$tmpl_prg->parse("main.secure");


// ---- Affiche la page

	$tmpl_prg->assign("url", $url);
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
