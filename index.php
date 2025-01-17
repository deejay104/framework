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
	ini_set('default_charset', 'UTF-8');

	if (!isset($MyOpt))
	{
		$MyOpt=array();
	}

	list($usec, $sec) = explode(" ", microtime());
	$starttime=((float)$usec + (float)$sec);
	if ((isset($MyOpt["debug"])) && ($MyOpt["debug"]=="on"))
	{
		// $starttime=microtime();
		error_reporting(E_ALL); 
		ini_set("display_errors", 1); 
	}
	if ((isset($MyOpt["debugtime"])) && ($MyOpt["debugtime"]=="on"))
	{
		$debug=array();
	}
	
	$tabLang=array();

// ---- Ouverture de la session
	session_start();
	$gl_uid=0;
	if ((isset($_SESSION['uid'])) && ($_SESSION['uid']>0))
	{
		$gl_uid = $_SESSION['uid'];
	}

// ---- Charge les bibliothèques
	require ("lib/fonctions.inc.php");

// ---- Nettoyage des variables
	$tabPost=array();
	$fonc=checkVar("fonc","varchar");


// ---- Charge les objets
	require ("class/xtpl.inc.php");
	require ("class/objet.inc.php");
	require ("class/user.inc.php");
	require ("class/mysql.inc.php");
	require ("class/document.inc.php");


// ---- Se connecte à la base MySQL
	if (!isset($port))
	{
		$port=3306;
	}
	if (($mysqluser=="") || ($mysqlpassword=="") || ($hostname=="") || ($db=="") || ($gl_tbl==""))
	{
		echo "Configuration file is missing or with bad configuration";
		exit;
	}
	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db, $port);

// ---- Vérifie si la configuration initiale a été faite
	$MyOpt["tbl"]=$gl_tbl;
	if ($gl_uid==0)
	{
		// Vérifie si la table config existe
		$q="SHOW TABLES";
		$sql->Query($q);

		$ok=0;
		if ($sql->rows>0)
		{
			for($i=0; $i<$sql->rows; $i++)
			{
				$sql->GetRow($i);
				if ($sql->data["Tables_in_".$db]==$MyOpt["tbl"]."_config")
				{
					$ok=1;
				}
			}
		}

		if ($ok==0)
		{
			$module="modules";
			$tmpl_prg = LoadTemplate("init","default");
			$tmpl_prg->assign("corefolder", $MyOpt["host"]."/".$corefolder);
			$tmpl_prg->assign("rootfolder", $MyOpt["host"]);

			if (($mysqluser=="") || ($gl_tbl==""))
			{
				$tmpl_prg->parse("main.configdb");
			}
			else
			{
				$tmpl_prg->parse("main.createdb");
			}

			$tmpl_prg->parse("main");
			echo $tmpl_prg->text("main");
			exit;
		}

	}


// ---- Charge les variables
	if ( (isset($MyOpt["version"])) && ($MyOpt["version"]!="") )
	{
		$q="SELECT value FROM ".$MyOpt["tbl"]."_config WHERE param='variable' AND name1='version'";
		$res=$sql->QueryRow($q);
		$v=$res["value"];
	}
	else
	{
		$MyOpt["version"]=0;
		$v=1;
	}

	if ($v!=$MyOpt["version"])
	{
		$MyOpt=array();
		$MyOpt["tbl"]=$gl_tbl;
		$q="SELECT * FROM ".$MyOpt["tbl"]."_config WHERE param='variable'";
		$sql->Query($q);
		for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);
			if ($sql->data["name2"]=="")
			{
				$MyOpt[$sql->data["name1"]]=$sql->data["value"];
			}
			else
			{
				$MyOpt[$sql->data["name1"]][$sql->data["name2"]]=$sql->data["value"];
			}
		}

		$ret=GenereFichierVariables($MyOpt);
	}
	
	if ($MyOpt["debugtime"]=="on")
	{
		$debug["init"]=microtime();
	}

// ---- Gestion des thèmes

// Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_2 like Mac OS X; fr-fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8H7 Safari/6533.18.5
// Mozilla/5.0 (Linux; U; Android 2.2.1; fr-fr; HTC_Wildfire-orange-LS Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
// Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; XBLWP7; ZuneWP7)
// Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J3 Safari/6533.18.5
// Mozilla/5.0 (Android 11; Mobile; rv:90.0) Gecko/90.0 Firefox/90.0
// Mozilla/5.0 (Linux; Android 11; SM-A025G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Mobile Safari/537.36

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
	else if ((!isset($_SESSION['mytheme'])) || ($_SESSION['mytheme']==""))
	{
		if ((preg_match("/CPU iPhone OS/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/iPad; U; CPU OS/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/Linux; U; Android/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/Linux; Android/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/Android 11; Mobile;/",$_SERVER["HTTP_USER_AGENT"]))
		   )
		{
			$theme="phone";
			$_SESSION['mytheme']=$theme;
		}
		
	}
	
// ---- Gestion des droits
	if ($fonc=="logout")
	{
		include "login.php";
		exit;
	}
	if ($gl_uid==0)
	{
			include "login.php";
			exit;

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
	header('Content-type: text/html; charset=UTF-8');

	//error_reporting( E_ALL ^ E_NOTICE ^ E_DEPRECATED );

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

  


// ---- Charge les variables et fonctions
	$module="static/modules";

// ---- Charge le numéro de version
	require ("version.php");


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

	if (file_exists(MyRep("lang.".$lang.".php","default",false)))
	{
		require (MyRep("lang.".$lang.".php","default",false));
	}
	else
	{
		require (MyRep("lang.".$MyOpt["DefaultLanguage"].".php","default",false));
	}

// ---- Maintenance	
	if (($MyOpt["maintenance"]=="on") && (!GetDroit("SYS")))
	{
	  	echo $tabLang["core_maintenancetxt"];
	  	exit;
	}	  	


// ---- Template par default
	if ($fonc=="imprimer")
	{
		$tmpl_prg = LoadTemplate("print","default");
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
	$tmpl_prg->assign("rootfolder", $MyOpt["host"]);
	$tmpl_prg->assign("corefolder", $MyOpt["host"]."/".$corefolder);
	$tmpl_prg->assign("gl_uid", $gl_uid);

	if (file_exists($appfolder."/custom/".$MyOpt["site_logo"]))
	{
		$tmpl_prg->assign("site_logo", "/custom/".$MyOpt["site_logo"]);
	}
	else
	{
		$tmpl_prg->assign("site_logo", $MyOpt["host"]."/".$corefolder."/static/images/logo.png");
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

	if ($MyOpt["debugtime"]=="on")
	{
		$debug["load"]=microtime();
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
		else if (MyRep("lang.".$MyOpt["DefaultLanguage"].".php","default")!="")
		{
			require (MyRep("lang.".$MyOpt["DefaultLanguage"].".php","default"));
		}
		


		require($appfolder."/modules/default/menu.inc.php");
	}


	if ($MyOpt["module"]["ameliorations"]=="on")
	{
		$tabMenu["amelioration"]["icone"]=$MyOpt["host"]."/".$corefolder."/static/modules/ameliorations/img/icn32_titre.png";
		$tabMenu["amelioration"]["icone"]="mdi-bug";
		$tabMenu["amelioration"]["nom"]=$tabLang["core_improve"];
		$tabMenu["amelioration"]["droit"]="AccesAmeliorations";
		$tabMenu["amelioration"]["url"]=geturl("ameliorations","index","");

		$tabMenuPhone["amelioration"]["icone"]=$MyOpt["host"]."/".$corefolder."/static/modules/ameliorations/img/icn48_titre.png";
		$tabMenuPhone["amelioration"]["icone"]="mdi-bug";
		$tabMenuPhone["amelioration"]["nom"]="";
		$tabMenuPhone["amelioration"]["droit"]="AccesAmeliorations";
		$tabMenuPhone["amelioration"]["url"]=geturl("ameliorations","index","");
	}

	$tabMenu["configuration"]=array(
		"icone"=>$MyOpt["host"]."/".$corefolder."/static/modules/admin/img/icn32_titre.png",
		"icone"=>"mdi-settings",
		"nom"=>$tabLang["core_configure"],
		"droit"=>"AccesConfiguration",
		"url"=>geturl("admin","",""),
		"submenu"=>array(
			array("nom"=>$tabLang["lang_configuration"],"url"=>geturl("admin","config",""),"droit"=>"AccesConfigVar"),
			array("nom"=>$tabLang["lang_database"],"url"=>geturl("admin","base",""),"droit"=>"AccesConfigBase"),
			array("nom"=>$tabLang["lang_groups"],"url"=>geturl("admin","groupes",""),"droit"=>"AccesConfigGroupes"),
			array("nom"=>$tabLang["lang_deadline"],"url"=>geturl("admin","echeances",""),"droit"=>"AccesConfigEcheances"),
			array("nom"=>$tabLang["lang_userdata"],"url"=>geturl("admin","utildonnees",""),"droit"=>"AccesConfigDonneesUser"),
			array("nom"=>$tabLang["lang_crontab"],"url"=>geturl("admin","crontab",""),"droit"=>"AccesConfigCrontab"),
			array("nom"=>$tabLang["lang_emails"],"url"=>geturl("admin","emails",""),"droit"=>"AccesConfigEmails"),
		),
	);

	if (file_exists($appfolder."/modules/admin/mainmenu.inc.php"))
	{
		require($appfolder."/modules/admin/mainmenu.inc.php");
	}

	$tabMenu["logout"]["icone"]="mdi-logout";
	$tabMenu["logout"]["nom"]=$tabLang["core_logout"];
	$tabMenu["logout"]["droit"]="";
	$tabMenu["logout"]["url"]=geturl("","","fonc=logout");

	// if ($theme=="phone")
	// {
		// $tabMenu=$tabMenuPhone;
	// }

	foreach ($tabMenu as $m=>$d)
	{
		if (GetDroit($d["droit"]))
		{
			$tmpl_prg->assign("menu_id", $m);
			$tmpl_prg->assign("menu_icone", $d["icone"]);
			$tmpl_prg->assign("menu_nom", $d["nom"]);
	
			if (isset($d["submenu"]))
			{
				$tmpl_prg->assign("menu_url", $d["url"]."#ui-".$m);
				$tmpl_prg->assign("menu_collapse", 'data-bs-toggle="collapse" aria-expanded="false" aria-controls="ui-'.$m.'"');

				foreach ($d["submenu"] as $mm=>$dd)
				{
					if (GetDroit($dd["droit"]))
					{
						$tmpl_prg->assign("submenu_nom", $dd["nom"]);
						$tmpl_prg->assign("submenu_url", $dd["url"]);
						$tmpl_prg->parse("main.menu_lst.menu_lst_sub.menu_lst_sub_item");
					}
				}
				$tmpl_prg->parse("main.menu_lst.menu_lst_sub");
				$tmpl_prg->parse("main.menu_lst.menu_expand");
			}
			else
			{
				$tmpl_prg->assign("menu_url", $d["url"]);
				$tmpl_prg->assign("menu_collapse", "");
			}
// $tmpl_prg->assign("menu_url", $d["url"]);
			$tmpl_prg->parse("main.menu_lst");
		}
	}
	foreach ($tabMenuPhone as $m=>$d)
	{
		if (GetDroit($d["droit"]))
		{
			$tmpl_prg->assign("menu_icone", $d["icone"]);
			$tmpl_prg->assign("menu_url", $d["url"]);
			$tmpl_prg->parse("main.menu_lst_phone");
		}
	}
	

	if ($MyOpt["debugtime"]=="on")
	{
		$debug["menu"]=microtime();
	}

	$lstdoc=ListDocument($sql,$gl_uid,"avatar");
	if (count($lstdoc)>0)
	{
		$doc=new document_core($lstdoc[0],$sql);
		$tmpl_prg->assign("aff_avatar",$MyOpt["host"]."/".$doc->GenerePath(200,240));
	}
	else
	{
		$tmpl_prg->assign("aff_avatar",$MyOpt["host"]."/static/images/icn64_membre.png");
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
		if (MyRep("lang.".$lang.".php","",false)!="")
		{
			require (MyRep("lang.".$lang.".php","",false));
		}
		else if (MyRep("lang.".$MyOpt["DefaultLanguage"].".php","",false)!="")
		{
			require (MyRep("lang.".$MyOpt["DefaultLanguage"].".php","",false));
		}

		if (MyRep("lang.".$lang.".php")!="")
		{
			require (MyRep("lang.".$lang.".php"));
		}
		else if (MyRep("lang.".$MyOpt["DefaultLanguage"].".php")!="")
		{
			require (MyRep("lang.".$MyOpt["DefaultLanguage"].".php"));
		}

		// Charge le template
		$tmpl_x = LoadTemplate($affrub);
		$tmpl_x->assign("path_root",$MyOpt["host"]);
		$tmpl_x->assign("path_core",$corefolder);
		$tmpl_x->assign("path_module",$module."/".$mod);
		$tmpl_x->assign("form_checktime",$_SESSION['checkpost']);
	
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

	if ($MyOpt["debugtime"]=="on")
	{
		$debug["rub"]=microtime();
	}
	

// ---- Affecte les blocs
	if ($fonc=="imprimer")
	{
		$tmpl_prg->assign("style_url", $MyOpt["host"]."/".GenereStyle("print"));
	}
	else
	{
		$tmpl_prg->assign("style_url", $MyOpt["host"]."/".GenereStyle("default"));
	}

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
		list($usec, $sec) = explode(" ", microtime());
		$endtime=((float)$usec + (float)$sec);

		$time=round(($endtime-$starttime)*1000,1);
		$t=" (".$time."ms)";
	}
	if ($MyOpt["debugtime"]=="on")
	{
		$debug["end"]=microtime();
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
			echo "<div class='affDebug' style='border:1px solid #000000; background-color:#ffcccc; padding:10px; position:fixed; right:20px; top:20px; display: inline-block;'>".$txt."</div>";
		}
	}

	if ($MyOpt["debugtime"]=="on")
	{
		echo "<div class='affDebugTime'>";
		$o=$starttime;
		foreach($debug as $k=>$t)
		{
			list($usec, $sec) = explode(" ", $t);
			$t=((float)$usec + (float)$sec);

			$time=round(($t-$o)*1000,1);
			$o=$t;
			$total=round(($t-$starttime)*1000,1);
			echo "<p style='margin:0px; line-height:14px;'>".$k." : ".$time."ms : ".$total."ms</p>";
		}
		echo "</div>";
	}
?>
