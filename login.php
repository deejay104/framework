<?php
// ---------------------------------------------------------------------------------------------
//   Page de Login
//   
// ---------------------------------------------------------------------------------------------
//   Variables  : 
// ---------------------------------------------------------------------------------------------

	session_start();
	if (isset($_SESSION['uid']))
	  { $uid = $_SESSION['uid']; }

// ---- Récupère les variables transmises
	$rub=checkVar("rub","varchar");
	$fonc=checkVar("fonc","varchar");
	$username=checkVar("username","varchar");
	$password=checkVar("password","varchar");
	$myid=checkVar("myid","numeric");

	$var=checkVar("varlogin","varchar");
	if ($var=="")
	{
	  	$var=$_SERVER["REQUEST_URI"];
	}

	$var=preg_replace("/\/login.php/","",$var);


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
	require ("class/xtpl.inc.php");
	require ("class/mysql.inc.php");

// ---- Gestion des thèmes
	$theme="";
	if ( (isset($_REQUEST["settheme"])) && ($_REQUEST["settheme"]!="") )
	{	
	  	$theme=$themes[$_REQUEST["settheme"]];
		$_SESSION['mytheme']=$theme;
	}
	else if ((isset($_SESSION['mytheme'])) && ($_SESSION['mytheme']!=""))
	{	
		$theme=$_SESSION['mytheme'];
	}
	else if ((!isset($_SESSION['mytheme'])) || ($_SESSION['mytheme']==""))
	{
		if ((preg_match("/CPU iPhone OS/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/Linux; U; Android/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/iPad; U; CPU OS/",$_SERVER["HTTP_USER_AGENT"])) || 
			(preg_match("/Linux; Android/",$_SERVER["HTTP_USER_AGENT"])) 
			
		   )
		{
			$theme="phone";
			$_SESSION['mytheme']=$theme;
		}
		
	}

// ---- Charge le numéro de version
	require ("version.php");

// ---- Test si l'on a validé la page
	$ok=0;
	$errmsg="";

	if (($fonc == $tabLang["core_connect"]) && ($mysqluser!="") && ($MyOpt["tbl"]!=""))
	{
		if ($password=="") { $password="nok"; }
		$username=strtolower($username);
		$username=preg_replace("/[\"'<>\\\;]/i","",$username);

		//preg_match("/^([^ ]*) (.*?)$/",$username,$t);

		$sql   = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);
		$query = "SELECT id,prenom,nom,mail,password FROM ".$MyOpt["tbl"]."_utilisateurs WHERE ((mail='$username' AND mail<>'') OR (initiales='$username' AND initiales<>'')) AND actif='oui' AND virtuel='non'";

		$res   = $sql->QueryRow($query);

		if (($res["id"]>0) && (md5($res["password"].md5(session_id()))==$password))
		{
				$query="INSERT INTO ".$MyOpt["tbl"]."_login (username,dte_maj,header) VALUES ('".addslashes($res["prenom"])." ".addslashes($res["nom"])."','".now()."','".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."')";
				$sql->Insert($query);
				$_SESSION['uid']=$res["id"];

				$query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."' WHERE id='".$res["id"]."'";
				$sql->Update($query);

	
				echo "<HTML><HEAD><SCRIPT language=\"JavaScript\">function go() { document.location=\"$var\"; }</SCRIPT></HEAD><BODY onload=\"go();\"></BODY></HTML>";
				exit;

		}
		else
		{
			$errmsg="Votre mot de passe est incorrect.";
		}
	}
	else if ($fonc == "logout")
	{
		$_SESSION['uid']="";
		echo "<HTML><HEAD><SCRIPT language=\"JavaScript\">function go() { document.location=\"index.php\"; }</SCRIPT></HEAD><BODY onload=\"go();\"></BODY></HTML>";
		exit;
	}

// ---- Charge les templates
	$module="modules";
	$tmpl_prg = LoadTemplate("login","default");

	if ($tmpl_prg->text("main.unsecure")=="")
	  { $tmpl_prg->parse("main.secure"); }

// ---- Calcul de l'id
	$myid=md5(session_id());

// ---- Affiche la page
	$tmpl_prg->assign("myid", $myid);
	$tmpl_prg->assign("var", $var);
	$tmpl_prg->assign("errmsg", $errmsg);
	$tmpl_prg->assign("version", $version."-".$core_version.(($MyOpt["maintenance"]=="on") ? " - ".ucwords($tabLang["core_maintenance"]) : ""));
	$tmpl_prg->assign("site_title", $MyOpt["site_title"]);
	$tmpl_prg->assign("corefolder", $corefolder);

	$tmpl_prg->assign("style_url", GenereStyle(($theme=="phone") ? "phone" : "default"));

	if (file_exists($appfolder."/custom/".$MyOpt["site_logo"]))
	{
		$tmpl_prg->assign("site_logo", "custom/".$MyOpt["site_logo"]);
	}
	else
	{
		$tmpl_prg->assign("site_logo", $corefolder."/static/images/logo.png");
	}

// ---- Test si l'installation est faite

	if ($mysqluser=="")
	{
		$tmpl_prg->parse("main.configdb");
	}
	else
	{
		$sql   = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);
		$sql->show=false;
		$query = "SELECT * FROM ".$MyOpt["tbl"]."_config";
		$res  = $sql->QueryRow($query);
		
		if (!is_array($res))
		{
			$tmpl_prg->parse("main.createdb");
		}
		else
		{
			$tmpl_prg->parse("main.submit");
		}
	}
	
	$tmpl_prg->parse("main");
	echo $tmpl_prg->text("main");


?>
