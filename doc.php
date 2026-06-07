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


// ---- Fonctions communes  
   	require ("lib/fonctions.inc.php");

	$id=checkVar("id","numeric");

// ---- Gestion des droits
	$gl_uid=0;
	if (isset($_COOKIE['t_session']))
	{
		$gl_auth=verifyJWT($_COOKIE['t_session']);
		$gl_uid=$gl_auth["uid"];
	}

	if ($gl_uid==0)
	{
		header("HTTP/1.0 302 Unauthorized"); 
		header("Location: ".$MyOpt["host"]."?redirect=/doc.php?id=".$id);
		exit;
	}


// ---- Variables
	$MyOpt["tbl"]=$gl_tbl;

	if ($id==0)
	{
		$ret=array();
		$ret["result"]="incorrect id";
		$ret["status"]=500;
		echo json_encode($ret);
		exit;
	}

	if ($mysqluser=="")
	{
		$ret=array();
		$ret["result"]="Fichier de configuration introuvable";
		$ret["status"]=500;
		echo json_encode($ret);
		exit;
	}

	$mode=checkVar("mode","varchar");
	

// ---- Se connecte à la base MySQL
	require ("class/mysql.inc.php");
	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

// ---- Charge les informations de l'utilisateur connecté
	require ("class/objet.inc.php");
	require ("class/user.inc.php");
	$myuser = new user_core($gl_uid,$sql,true);

// ---- Charge le document
	require ("class/document.inc.php");
	$doc = new document_core($id,$sql);


// ---- Force la timezone
	if ($MyOpt["timezone"]!="")
	  { date_default_timezone_set($MyOpt["timezone"]); }

// ---- Delete document
	$fonc=checkVar("fonc","varchar");
	if ($fonc=="delete")
	{
		$ret=array();
		$ret["result"]=$doc->delete();
		echo json_encode($ret);
		exit;		
	}

// ---- Renvoie le contenu du fichier
	$type=checkVar("type","varchar");

	if ( $type=="image")
	{
		$w=checkVar("width","numeric");
		$c=checkVar("height","numeric");
		$doc->ShowImage($w,$h);
	}
	else
	{
		$doc->Download($mode);
	}

?>
