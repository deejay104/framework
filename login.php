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
	// $rub=checkVar("rub","varchar");
	// $fonc=checkVar("fonc","varchar");
	// $username=checkVar("username","varchar");
	// $password=checkVar("password","varchar");
	// $myid=checkVar("myid","numeric");

  	$url=$MyOpt["host"]."/index.php?".$_SERVER['QUERY_STRING'];
	// $url=preg_replace("/\/login.php/","",$url);
	// $url=preg_replace("/\/index.php/","",$url);

// phpinfo();

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

// ---- Charge les templates
	$module="modules";
	$tmpl_prg = LoadTemplate("login","default");

// ---- Connection à la base de données
	$sql   = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

// ---- Test si l'on a validé la page
	// $ok=0;
	// $errmsg="";

	// if (($fonc == $tabLang["core_connect"]) && ($mysqluser!="") && ($MyOpt["tbl"]!=""))
	// {
		// if ($password=="") { $password="nok"; }
		// $username=strtolower($username);
		// $username=preg_replace("/[\"'<>\\\;]/i","",$username);

		// preg_match("/^([^ ]*) (.*?)$/",$username,$t);

		// $query = "SELECT id,prenom,nom,mail,password FROM ".$MyOpt["tbl"]."_utilisateurs WHERE ((mail='$username' AND mail<>'') OR (initiales='$username' AND initiales<>'')) AND actif='oui' AND virtuel='non'";

		// $res   = $sql->QueryRow($query);

		// if (($res["id"]>0) && (md5($res["password"].md5(session_id()))==$password))
		// {
				// $query="INSERT INTO ".$MyOpt["tbl"]."_login (username,dte_maj,header,type) VALUES ('".addslashes($res["prenom"])." ".addslashes($res["nom"])."','".now()."','".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."','password')";
				// $sql->Insert($query);
				// $_SESSION['uid']=$res["id"];
				// $gl_uid=$res["id"];

				// $myid=0;
				// $token="";
				// if ($MyOpt["tokenexpire"]>0)
				// {
					// $token=bin2hex(random_bytes(32));
					// $token=bin2hex(openssl_random_pseudo_bytes(32));

					// $query="INSERT INTO ".$MyOpt["tbl"]."_token SET uid=".$gl_uid.", token='".$token."', uid_creat='".$gl_uid."',uid_maj='".$gl_uid."',dte_creat='".now()."', dte_expire='".date("Y-m-d H:i:s",time()+$MyOpt["tokenexpire"]*3600*24)."'";
					// $myid=$sql->Insert($query);
					// $_SESSION['sessid']=$myid;
				// }
				
				// $query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."' WHERE id='".$res["id"]."'";
				// $sql->Update($query);
	
				// echo "<html><body>";
				// echo "<script>";
				// echo "if (localStorage) { localStorage.setItem(\"myid\",\"".$myid."\"); localStorage.setItem(\"mytoken\",\"".$token."\"); }";
				// echo "document.location=\"$var\";";
				// echo "</script>";
				// echo "</body></html>";
				// exit;

		// }
		// else
		// {
			// $errmsg="Votre mot de passe est incorrect.";
		// }
	// }
	// else 
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
		echo "if (localStorage) { localStorage.setItem(\"myid\",\"\"); localStorage.setItem(\"mytoken\",\"\"); }";
		echo "document.location=\"index.php\";";
		echo "</script>";
		echo "</body></html>";
		exit;

		// $tmpl_prg->parse("main.logout");
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
