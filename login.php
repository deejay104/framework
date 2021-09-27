<?php
// ---------------------------------------------------------------------------------------------
//   Page de Login
//   
// ---------------------------------------------------------------------------------------------
//   Variables  : 
// ---------------------------------------------------------------------------------------------

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}


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
	require (MyRep("lang.".$lang.".php","default",false));
 
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
		if ($_SESSION['sessid']>0)
		{
			$query="UPDATE ".$MyOpt["tbl"]."_token SET active='non' WHERE id='".$_SESSION['sessid']."'";
			$sql->Update($query);
		}

		$_SESSION['uid']=0;
		$_SESSION['sessid']=0;
		
		echo "<html><body>";
		echo "<script>";
		echo "if (localStorage) { localStorage.setItem(\"token\",\"\"); }";
		echo "document.location=\"index.php\";";
		echo "</script>";
		echo "</body></html>";
		exit;

	}

// ---- 
	// if ($tmpl_prg->text("main.unsecure")=="")
	$tmpl_prg->parse("main.secure");

// ---- Calcul de l'id
	$myid=md5(session_id());

// ---- Affiche la page

	// $tmpl_prg->assign("myid", $myid);
	$tmpl_prg->assign("url", $url);
	// $tmpl_prg->assign("errmsg", $errmsg);
	$tmpl_prg->assign("version", $version."-".$core_version.(($MyOpt["maintenance"]=="on") ? " - ".ucwords($tabLang["core_maintenance"]) : ""));
	$tmpl_prg->assign("site_title", $MyOpt["site_title"]);
	$tmpl_prg->assign("corefolder", $corefolder);
	$tmpl_prg->assign("rootfolder", $MyOpt["host"]);

	$tmpl_prg->assign("style_url", GenereStyle(($theme=="phone") ? "phone" : "default"));

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
