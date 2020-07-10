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

// ---- Gestion des droits
	session_start();

	if ((isset($_SESSION['uid'])) && ($_SESSION['uid']>0))
	  { $gl_uid = $_SESSION['uid']; }
	else
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Fonctions communes  
   	require ("lib/fonctions.inc.php");

// ---- Variables
	$MyOpt["tbl"]=$gl_tbl;

	if ((is_numeric($_REQUEST["id"])) && ($_REQUEST["id"]>0))
	{
		$id=$_REQUEST["id"];
	}
	else
	{
		$ret=array();
		$ret["result"]="id incorrect";
		echo json_encode($ret);
		exit;
	}

	if ($mysqluser=="")
	{
		$ret=array();
		$ret["result"]="Fichier de configuration introuvable";
		echo json_encode($ret);
		exit;
	}

	$mode=checkVar("mode","varchar");
	

// ---- Se connecte à  la base MySQL
	require ("class/mysql.inc.php");
	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

// ---- Charge les informations de l'utilisateur connecté
	require ("class/objet.inc.php");
	require ("class/user.inc.php");
	$myuser = new user_core($gl_uid,$sql,true);

// ---- Charge le document
	require ("class/document.inc.php");
	$doc = new document_core($id,$sql);

// ---- Delete document
	if ( (isset($_REQUEST["fonc"])) && ($_REQUEST["fonc"]=="delete") )
	{
		$ret=array();
		$ret["result"]=$doc->delete();
		echo json_encode($ret);
		// echo "<script>opener.location.reload(); window.close();</script>";
		exit;		
	}


//	header("Content-Type: image/jpeg");
//	header('Content-Disposition: inline; filename="'.substr($name,strrpos($name,"/")+1,strlen($name)-strrpos($name,"/")).'";');

// ---- Renvoie le contenu du fichier
	if ( (isset($_REQUEST["type"])) && ($_REQUEST["type"]=="image") )
	{
		if ((isset($_REQUEST["width"])) && (is_numeric($_REQUEST["width"])))
		{
			$w=$_REQUEST["width"];
		}
		else
		{
			$w=0;
		}
		if ((isset($_REQUEST["height"])) && (is_numeric($_REQUEST["height"])))
		{
			$h=$_REQUEST["height"];
		}
		else
		{
			$h=0;
		}
		$doc->ShowImage($w,$h);
	}
	else
	{
		$doc->Download($mode);
	}

?>
