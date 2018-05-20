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

// ---- Autorisation d'accшs
	session_start();
	require ("class/mysql.inc.php");

	if ((isset($_SESSION['uid'])) && ($_SESSION['uid']>0))
	{
		$uid = $_SESSION['uid'];
	}
	else if (($_REQUEST["mod"]=="admin") && ($_REQUEST["rub"]=="update"))
	{
 		$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);
		$sql->show=false;
		$query = "SELECT * FROM ".$MyOpt["tbl"]."_config";
		$res  = $sql->QueryRow($query);
		
		if (!is_array($res))
		{
			$uid=0;
			$token="sys";
		}
		else
		{
			header("HTTP/1.0 401 Unauthorized"); exit;
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
  	require ("lib/fonctions.inc.php");

	if ($MyOpt["timezone"]!="")
	  { date_default_timezone_set($MyOpt["timezone"]); }

// ---- Se connecte ра la base MySQL
	if ($mysqluser=="")
	{
		$res=array();
		$res["result"]="Fichier de configuration introuvable";
	  	echo json_encode($res);
		exit;
	}

	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

// ---- Charge les informations de l'utilisateur connectщ
	require ("class/objet.inc.php");
	require ("class/user.inc.php");
	if ($uid>0)
	{
		$myuser = new user_core($uid,$sql,true);
		$token=$uid;
	}

	$module="modules";
	$gl_mode="api";

// ---- Vщrifie la variable $mod
	$mod=$_REQUEST["mod"];
	if (!preg_match("/^[a-z0-9_]*$/",$mod))
	  { $mod = ""; }
	if (trim($mod)=="")
	  { $mod = ""; }

// ---- Vщrifie la variable $rub
	$rub=$_REQUEST["rub"];
	if (!preg_match("/^[a-z0-9_]*$/",$rub))
	  { $rub = ""; }
	if (trim($rub)=="")
	  { $rub = ""; }

// ---- Charge la page
	if (($mod!="") && ($rub!=""))
	{
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