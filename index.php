<?php
/*
    MnMs Framework
    Copyright (C) 2018 Matthieu Isorez

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
	if (!isset($MyOpt))
	{
		$MyOpt=array();
	}

	if ($MyOpt["debug"]=="on")
	{
		$starttime=microtime();
		error_reporting(E_ALL); 
		ini_set("display_errors", 1); 
	}

// ---- Charge les bibliothèques
	require ("lib/fonctions.inc.php");

// ---- Nettoyage des variables
	$tabPost=array();
	$fonc=checkVar("fonc","varchar");

// ---- Gestion des droits
	session_start();
 
	if ($fonc=="logout")
	{
		include "login.php";
		exit;
	}
	else if ((isset($_SESSION['uid'])) && ($_SESSION['uid']>0))
	{
		$gl_uid = $_SESSION['uid'];
	}
	else
	{
		if ( ((isset($_SESSION['sessid'])) && ($_SESSION['sessid']==-1)) || ($MyOpt["tokenexpire"]==0) )
		{
			include "login.php";
			exit;
		}
		else
		{
			$_SESSION['sessid']=-1;
			include "auth.php";
			exit;
		}
	}

// ---- Header de la page
	// Date du passé
	header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	// toujours modifié
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	
	// HTTP/1.0
	header("Pragma: no-cache");

	// Charset
	header('Content-type: text/html; charset=ISO-8859-1');

	//error_reporting( E_ALL ^ E_NOTICE ^ E_DEPRECATED );

// ---- Récupère les paramètres
	$e ="foreach( \$_REQUEST as \$key=>\$value) {"."\n";
	$e.="if (!isset(\$_SESSION[\"\$key\"])) {"."\n";

	$e.="  if (is_array(\$value)) {"."\n";
	$e.="      foreach(\$value as \$k=>\$v) { if (!is_array(\$v)) { \$value[\$k]=stripslashes(\$v); } } \$\$key=\$value;"."\n";
	$e.="  } else if (get_magic_quotes_gpc()) {"."\n";
	$e.="      \$\$key = stripslashes(\$value);"."\n";
	$e.="  } else {"."\n";
	$e.="    \$\$key = \$value;"."\n";
	$e.="} } }"."\n";

	eval($e);


// ---- Force la timezone
	if ($MyOpt["timezone"]!="")
	  { date_default_timezone_set($MyOpt["timezone"]); }
	
// ---- Vérifie la variable $mod
	$mod=checkVar("mod","varchar");
	if (trim($mod)=="")
	  { $mod = "default"; }
	if (!preg_match("/^[a-z0-9_]*$/",$mod))
	  { $mod = "default"; }

// ---- Vérifie la variable $rub
	$rub=checkVar("rub","varchar");
	if (trim($rub)=="")
	  { $rub = "index"; }
	if (!preg_match("/^[a-z0-9_]*$/",$rub))
	  { $rub = "index"; }
  


// ---- Défini les variables globales
	$prof="";
	$gl_mode="html";
	$uid=$gl_uid;

	if (!isset($appfolder))
	{
		$appfolder="..";
	}

  
// ---- Gestion des thèmes

// Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_2 like Mac OS X; fr-fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8H7 Safari/6533.18.5
// Mozilla/5.0 (Linux; U; Android 2.2.1; fr-fr; HTC_Wildfire-orange-LS Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
// Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; XBLWP7; ZuneWP7)
// Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J3 Safari/6533.18.5

	$theme=checkVar("theme","varchar",10);
	$settheme=checkVar("settheme","varchar");
	if ($settheme!="")
	{	
		$themes["default"]="";
		$themes["phone"]="phone";
		
		$theme=$themes[$_REQUEST["settheme"]];
		$_SESSION['mytheme']=$theme;
	}
	else if ($theme!="")
	{
	}
	else if (isset($_SESSION['mytheme']))
	{
		$theme=$_SESSION['mytheme'];
	}
	else if ($_SESSION['mytheme']="")
	{
		if ((preg_match("/CPU iPhone OS/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/Linux; U; Android/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/iPad; U; CPU OS/",$_SERVER["HTTP_USER_AGENT"]))
		   )
		{
			$theme="phone";
			$_SESSION['mytheme']=$theme;
		}
		
	}

// ---- Charge les variables et fonctions
	$module="static/modules";

// ---- Charge le numéro de version
	require ("version.php");

// ---- Charge les templates
	require ("class/xtpl.inc.php");

// ---- Charge les class
	require ("class/objet.inc.php");
	require ("class/user.inc.php");
	require ("class/mysql.inc.php");
	require ("class/document.inc.php");

// ---- Se connecte à la base MySQL
	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db, $port);

// ---- Fonction des informations de l'utilisateur
	$myuser = new user_core($gl_uid,$sql,true);
	$res_user=$myuser->data;
	$token=$uid;

// ---- Charge le fichier de langue
	$lang=checkVar("lang","varchar");
	if (($lang=="") && ($myuser->val("language")!=""))
	{
		$lang=$myuser->val("language");
	}
	else if ((isset($MyOpt["DefaultLanguage"])) && ($MyOpt["DefaultLanguage"]!=""))
	{
		$lang=$MyOpt["DefaultLanguage"];
	}
	else
	{
		$lang="fr";
	}
	$tabLang=array();
	require (MyRep("lang.".$lang.".php","default",false));

// ---- Maintenance	
	if (($MyOpt["maintenance"]=="on") && (!GetDroit("SYS")))
	{
	  	echo $tabLang["core_maintenancetxt"];
	  	exit;
	}	  	


// ---- Template par default
	if ($fonc=="imprimer")
	{
		$tmpl_prg = new XTemplate (MyRep("print.htm","default"));
		$tmpl_prg->assign("style_url", GenereStyle("print"));
	}
	else
	{
		$tmpl="default";
		$tmpl_prg = LoadTemplate($tmpl,"default");
	}


// ---- Maj du template
	$tmpl_prg->assign("uid", $uid);
	$tmpl_prg->assign("username", $myuser->fullname);
	$tmpl_prg->assign("site_title", $MyOpt["site_title"]);
	$tmpl_prg->assign("corefolder", $corefolder);
	$tmpl_prg->assign("gl_uid", $gl_uid);

	if (file_exists($appfolder."/custom/".$MyOpt["site_logo"]))
	{
		$tmpl_prg->assign("site_logo", "custom/".$MyOpt["site_logo"]);
	}
	else
	{
		$tmpl_prg->assign("site_logo", $corefolder."/static/images/logo.png");
	}

// ---- Flag pour ne pouvoir poster qu'une seule fois les mêmes infos
	$checktime=checkVar("checktime","numeric");
	if (!isset($_SESSION["checkpost"]))
	{
		$_SESSION["checkpost"]=1;
	}
	else
	{	
	  	$_SESSION["checkpost"]=$_SESSION["checkpost"]+1;
	}
	$checkpost=$_SESSION["checkpost"];

	if (!isset($_SESSION["tab_checkpost"]))
	  { 
		$tab_checkpost[""]="ok";
		$_SESSION["tab_checkpost"][""]="ok";
	  }


// ---- Définition des variables
	$gl_myprint_txt="";

// ---- Initialisation des variables
	$tmpl_prg->assign("rub", ucwords($rub));
	$tmpl_prg->assign("module", ucwords($mod));

	$tmpl_prg->assign("date_expire", date("r"));
	//Mon, 22 Jul 2002 11:12:01 GMT
	
// ---- Affichages du menu
	$tabMenu=array();
	$tabMenuPhone=array();
	require("modules/default/menu.inc.php");
	if (file_exists($appfolder."/modules/default/menu.inc.php"))
	{
		if (MyRep("lang.".$lang.".php","default")!="")
		{
			require (MyRep("lang.".$lang.".php","default"));
		}
		require($appfolder."/modules/default/menu.inc.php");
	}


	if ($MyOpt["module"]["ameliorations"]=="on")
	{
		$tabMenu["amelioration"]["icone"]=$corefolder."/static/modules/ameliorations/img/icn32_titre.png";
		$tabMenu["amelioration"]["nom"]=$tabLang["core_improve"];
		$tabMenu["amelioration"]["droit"]="AccesAmeliorations";
		$tabMenu["amelioration"]["url"]="mod=ameliorations";
		$tabMenuPhone["amelioration"]["icone"]=$corefolder."/static/modules/ameliorations/img/icn48_titre.png";
		$tabMenuPhone["amelioration"]["nom"]="";
		$tabMenuPhone["amelioration"]["droit"]="AccesAmeliorations";
		$tabMenuPhone["amelioration"]["url"]="mod=ameliorations";
	}

	$tabMenu["configuration"]["icone"]=$corefolder."/static/modules/admin/img/icn32_titre.png";
	$tabMenu["configuration"]["nom"]=$tabLang["core_configure"];
	$tabMenu["configuration"]["droit"]="AccesConfiguration";
	$tabMenu["configuration"]["url"]="mod=admin";


	if ($theme=="phone")
	{
		$tabMenu=$tabMenuPhone;
	}

	foreach ($tabMenu as $m=>$d)
	{
		if (GetDroit($d["droit"]))
		{
			$tmpl_prg->assign("menu_icone", $d["icone"]);
			$tmpl_prg->assign("menu_nom", $d["nom"]);
			$tmpl_prg->assign("menu_url", $d["url"]);
			$tmpl_prg->parse("main.menu_lst");
			$tmpl_prg->parse("main.menu_lst_sm");
		}
	}
	

	
// ---- Charge la rubrique
	$affrub=$rub;
	while ($affrub!="")
	{
		$oldrub=$affrub;
		$oldmod=$mod;

		// Initialise les variables
		$infos="";
		$icone="";
		$corps="";
		
		// Charge le fichier de langue du module
		$l=MyRep("lang.".$lang.".php","",false);
		if ($l!="")
		{
			require ($l);
		}
		$l=MyRep("lang.".$lang.".php");
		if ($l!="")
		{
			require ($l);
		}

		// Charge le template
		$tmpl_x = LoadTemplate($affrub);
		
		// Charge la rubrique
		$r=MyRep($affrub.".inc.php");
		if ($r!="")
		{
			$rub=$affrub;
			require($r);
		}
		else
		{
			FatalError($tabLang["core_filenotfound"],ucwords($tabLang["core_file"])." : $affrub.inc.php");
		}
		
		if (($affrub==$oldrub) && ($mod==$oldmod))
		{
			$affrub="";
		}
	}

	

// ---- Affecte les blocs
	$tmpl_prg->assign("style_url", GenereStyle(($theme=="phone") ? "phone" : "default"));

	$tmpl_prg->assign("icone", $icone);
	$tmpl_prg->assign("infos", $infos);
	$tmpl_prg->assign("corps", $corps);

	if ($gl_myprint_txt!="")
	{
		affInformation(nl2br(htmlentities(utf8_decode($gl_myprint_txt),ENT_COMPAT,'ISO-8859-1')),"warning");
	}

// ---- Calcul du temps d'affichage
	$t="";
	if ($MyOpt["debug"]=="on")
	{
		$time=round((microtime()-$starttime)*1000,1);
		$t=" (".$time."ms)";
	}
	$tmpl_prg->assign("version", $version."-".$core_version.(($MyOpt["maintenance"]=="on") ? " - ".ucwords($tabLang["core_maintenance"]) : "").$t);

// ---- Affiche la page
	$tmpl_prg->parse("main");
	echo $tmpl_prg->text("main");

// ---- Ferme la connexion à la base de données	  
    $sql->closedb();

// ---- Debug: Show unitialized variables
	if ($MyOpt["debug"]=="on")
	{
		$txt="";
		foreach($_REQUEST as $k=>$v)
		{
			if (!isset($tabPost[$k]))
			{
				$txt.=$k."<br/>";
			}
		}
		if ($txt!="")
		{
			echo "<div style='border:1px solid #000000; background-color:#ffcccc; padding:10px; position:fixed; right:20px; top:20px; display: inline-block;'>".$txt."</div>";
		}
	}
?>
