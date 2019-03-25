<?
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

  	require ("lib/fonctions.inc.php");

// ---- Connexion à la base de données
	session_start();
	require ("class/mysql.inc.php");
	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);
	$sql->show=false;


// ---- Variables
	$myid=checkVar("myid","numeric");
	$mykey=checkVar("mykey","varchar");
	$mod=checkVar("mod","varchar");
	$rub=checkVar("rub","varchar");

	$gl_uid = 0;
	$token=(!isset($token)) ? "" : $token;
	$token=($token=="sys") ? "" : $token;

	
// ---- Demande d'authentification
	if ($mykey!="")
	{
		$res=array();
		$res["mykey"]=md5("NOK");

		$query = "SELECT id,uid FROM ".$MyOpt["tbl"]."_token WHERE id='".$myid."' AND active='oui' AND MD5(CONCAT('".md5(session_id())."','-',token))='".$mykey."' AND dte_expire<>'0000-00-00' AND dte_expire>NOW()";
		$res  = $sql->QueryRow($query);
		if ($res["id"]>0)
		{
			$query="UPDATE ".$MyOpt["tbl"]."_token SET dte_expire='".date("Y-m-d H:i:s",time()+$MyOpt["tokenexpire"]*3600*24)."'";
			$sql->Update($query);

			$gl_uid=$res["uid"];
			$_SESSION['uid']=$gl_uid;
			$_SESSION['sessid']=$myid;

			$res["auth"]="OK";
			$res["myid"]=$res["id"];
			$res["mykey"]=md5("OK");
		}

		echo json_encode($res);
		exit;
	}
	else if ((isset($_SESSION['uid'])) && ($_SESSION['uid']>0))
	{
		$gl_uid = $_SESSION['uid'];
	}
	else if (($mod=="admin") && ($rub=="update"))
	{
		$query = "SELECT * FROM ".$MyOpt["tbl"]."_config";
		$res  = $sql->QueryRow($query);
		
		if (!is_array($res))
		{
			$gl_uid=0;
			$token="sys";
		}
		else
		{
			header("HTTP/1.0 401 Unauthorized"); exit;
		}
	}
	else if ( (isset($_SERVER["PHP_AUTH_USER"])) && (isset($_SERVER["PHP_AUTH_PW"])) )
	{
		$query = "SELECT id FROM ".$MyOpt["tbl"]."_utilisateurs WHERE (mail='".$_SERVER["PHP_AUTH_USER"]."' OR initiales='".$_SERVER["PHP_AUTH_USER"]."') AND password='".$_SERVER["PHP_AUTH_PW"]."'";
		$res  = $sql->QueryRow($query);
		if ($res["id"]>0)
		{
			$gl_uid=$res["id"];
		}
	}
	else
	{
		header("HTTP/1.0 401 Unauthorized"); exit;
	}
	
// ---- Header
  	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	
	// HTTP/1.0
	header("Pragma: no-cache");

	// Charset
	header('Content-type: text/html; charset=ISO-8859-1');

// ---- Charge les informations standards

	if ($MyOpt["timezone"]!="")
	  { date_default_timezone_set($MyOpt["timezone"]); }

// ---- Se connecte à  la base MySQL
	if ($mysqluser=="")
	{
		$res=array();
		$res["result"]="Fichier de configuration introuvable";
	  	echo json_encode($res);
		exit;
	}

	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

// ---- Charge les informations de l'utilisateur connecté
	require ("class/objet.inc.php");
	require ("class/user.inc.php");
	if ($gl_uid>0)
	{
		$myuser = new user_core($gl_uid,$sql,true);
		$token=$gl_uid;
	}
	else if (($gl_uid==0) && ($token=="sys"))
	{
		$myuser = new user_core(0,$sql,true);
	}

	$module="modules";
	$gl_mode="api";

// ---- Charge le fichier de langue
	if ($myuser->val("language")!="")
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
	require ("modules/default/lang/lang.".$lang.".php");

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

// ---- Charge la page
	if (($mod!="") && ($rub!=""))
	{
		// Charge le fichier de langue du module
		if (file_exists("modules/".$mod."/lang/lang.".$lang.".php"))
		{
			require ("modules/".$mod."/lang/lang.".$lang.".php");
		}
		if (file_exists($appfolder."/modules/".$mod."/lang/lang.".$lang.".php"))
		{
			require ($appfolder."/modules/".$mod."/lang/lang.".$lang.".php");
		}

		// Charge le script
		if (file_exists($appfolder."/modules/".$mod."/".$rub.".api.php"))
		{
			require($appfolder."/modules/".$mod."/".$rub.".api.php");
		}
		else if (file_exists("modules/".$mod."/".$rub.".api.php"))
		{
			require("modules/".$mod."/".$rub.".api.php");
		}
		else if (file_exists("modules/".$mod."/".$rub.".inc.php"))
		{
			require("modules/".$mod."/".$rub.".inc.php");
		}
		else
		{
			echo "{  \"result\": \"File not found\" }\n";
		}
	}
	else
	{
		echo "{  \"result\": \"\" }\n";
	}
 ?>