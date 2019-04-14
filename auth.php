<?php
// ---------------------------------------------------------------------------------------------
//   Page de Login
//   
// ---------------------------------------------------------------------------------------------
//   Variables  : 
// ---------------------------------------------------------------------------------------------
	
// ---- Récupère les variables transmises
	$rub=checkVar("rub","varchar");
	$fonc=checkVar("fonc","varchar");

	$var=checkVar("varlogin","varchar");
	if ($var=="")
	{
	  	$var=$_SERVER["REQUEST_URI"];
	}

	$var=preg_replace("/\/[a-z]*\.php/","",$var);

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
	require (MyRep("lang.".$lang.".php","default",false));
 
// ---- Charge les prérequis
	require_once ("class/xtpl.inc.php");
	require_once ("class/mysql.inc.php");

	
// ---- Charge les templates
	$module="modules";
	$tmpl_prg = LoadTemplate("auth","default");

// ---- Teste le client
	if ((preg_match("/CPU iPhone OS/",$_SERVER["HTTP_USER_AGENT"])) ||
		(preg_match("/Linux; U; Android/",$_SERVER["HTTP_USER_AGENT"])) ||
		(preg_match("/iPad; U; CPU OS/",$_SERVER["HTTP_USER_AGENT"])) || 
		(preg_match("/Linux; Android/",$_SERVER["HTTP_USER_AGENT"])) 
		
	   )
	{
		$theme="phone";
		$_SESSION['mytheme']=$theme;
	}

	
// ---- Affiche la page
	$tmpl_prg->assign("var", $var);
	$tmpl_prg->assign("corefolder", $corefolder);
	$tmpl_prg->assign("mysess", md5(session_id()));
	$tmpl_prg->assign("site_title", $MyOpt["site_title"]);
	$tmpl_prg->assign("style_url", GenereStyle(($theme=="phone") ? "phone" : "default"));

	$tmpl_prg->parse("main");
	echo $tmpl_prg->text("main");
	
?>