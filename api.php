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

  	require ("lib/fonctions.inc.php");

// ---- Connexion à la base de données
	session_start();
	require ("class/mysql.inc.php");
	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);
	$sql->show=false;

// ---- Variables
	$MyOpt["tbl"]=$gl_tbl;

	$myid=checkVar("myid","numeric");
	$mykey=checkVar("mykey","varchar",500);
	$myusr=checkVar("myusr","varchar");
	$mypwd=checkVar("mypwd","varchar");
	$mod=checkVar("mod","varchar");
	$rub=checkVar("rub","varchar","index");
	$fonc=checkVar("fonc","varchar");

	$gl_uid = 0;
	$token=(!isset($token)) ? "" : $token;
	$token=($token=="sys") ? "" : $token;

	
// ---- Demande d'authentification --------------------------------------------------------------------
	if (($fonc=="login") && ($mykey!=""))
	{
		$data=array();
		$data["type"]="token";

		$ret=array();
		
		$ret["auth"]="NOK";
		$ret["status"]=401;

		$payload=checkToken($mykey);

		// Check if token is valid
		if ($payload["status"]=="ok")
		{
			$gl_uid=$payload["uid"];
			if (($gl_uid>0) && ($payload["expire"]-time()<24*3600))
			{
				$token=generateToken($gl_uid);
				$ret["token"]=$token;
			}
			$_SESSION['uid']=$gl_uid;
			$_SESSION['sessid']=$myid;

			$ret["auth"]="OK";
			$ret["status"]=200;
			$ret["uid"]=$gl_uid;

			$query = "SELECT prenom,nom FROM ".$MyOpt["tbl"]."_utilisateurs WHERE id='".$gl_uid."'";
			$res = $sql->QueryRow($query);
			
			$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".addslashes($res["prenom"])." ".addslashes($res["nom"])."', srcip='".getip()."', status='token',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($data)."'";
			$sql->Insert($query);

			$query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."' WHERE id='".$gl_uid."'";
			$sql->Update($query);
		}
		else
		{
			$_SESSION['sessid']=-1;

			// $query = "SELECT id,uid,token FROM ".$MyOpt["tbl"]."_token WHERE id='".$myid."'";
			// $res  = $sql->QueryRow($query);

			// $data["myid"]=$myid;
			// $data["mykey"]=$mykey;
			$data["result"]="rejected";

			if ($payload["uid"]>0)
			{
				$query = "SELECT prenom,nom FROM ".$MyOpt["tbl"]."_utilisateurs WHERE id='".$payload["uid"]."'";
				$res  = $sql->QueryRow($query);
				$payload["username"]=addslashes($res["prenom"])." ".addslashes($res["nom"]);
			}
			
			$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".$payload["username"]."',srcip='".getip()."',status='rejected',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($payload)."'";
			$sql->Insert($query);
		}

		$ret["payload"]=$payload;
		$ret["data"]=$data;
		echo json_encode($ret);
		exit;
	}
	else if (($fonc=="login") && ($mypwd!=""))
	{
		$myusr=strtolower($myusr);
		$myusr=preg_replace("/[\"'<>\\\;]/i","",$myusr);

		$data=array();
		$data["type"]="password";
		$data["myusr"]=$myusr;

		$ret=array();
		$ret["auth"]="NOK";

		$query = "SELECT id,prenom,nom,mail,password,creds FROM ".$MyOpt["tbl"]."_utilisateurs WHERE ((mail='".$myusr."' AND mail<>'') OR (initiales='".$myusr."' AND initiales<>'')) AND actif='oui' AND virtuel='non'";
		$res = $sql->QueryRow($query);

		if (($res["id"]>0) && (password_verify($mypwd,$res["creds"])))
		{
			$data["result"]="success";
			$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".addslashes($res["prenom"])." ".addslashes($res["nom"])."',srcip='".getip()."',status='password',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($data)."'";
			$sql->Insert($query);
			$_SESSION['uid']=$res["id"];
			$gl_uid=$res["id"];

			$myid=0;
			$token="";
			if ($MyOpt["tokenexpire"]>0)
			{
				$token=generateToken($gl_uid,$MyOpt["tokenexpire"]);

				// $token=bin2hex(random_bytes(32));
				
				// $query="INSERT INTO ".$MyOpt["tbl"]."_token SET uid=".$gl_uid.", token='".password_hash($token, PASSWORD_BCRYPT, array('cost' => 12))."', uid_creat='".$gl_uid."',uid_maj='".$gl_uid."',dte_creat='".now()."', dte_expire='".date("Y-m-d H:i:s",time()+$MyOpt["tokenexpire"]*3600*24)."'";
				// $myid=$sql->Insert($query);
				// $_SESSION['sessid']=$myid;

				// $ret["myid"]=$myid;
				$ret["token"]=$token;
			}
			
			$query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."' WHERE id='".$res["id"]."'";
			$sql->Update($query);

			$ret["auth"]="OK";
		}
		else if (($res["id"]>0) && ($res["creds"]=="") && ($mypwd==$res["password"]))  // For compatibility with older password
		{
			$data["result"]="success";
			$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".addslashes($res["prenom"])." ".addslashes($res["nom"])."',srcip='".getip()."',status='password',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($data)."'";
			$sql->Insert($query);
			$_SESSION['uid']=$res["id"];
			$gl_uid=$res["id"];

			$ret["myid"]=0;
			$ret["mytoken"]="";
			
			$query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."',creds='".password_hash($mypwd, PASSWORD_BCRYPT, array('cost' => 12))."',password='' WHERE id='".$res["id"]."'";
			$sql->Update($query);

			$ret["auth"]="OK";
		}
		else
		{
			$data["result"]="rejected";

			$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".addslashes($myusr)."',srcip='".getip()."',status='rejected',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($data)."'";
			$sql->Insert($query);
			$ret["error"]="Bad password";
		}

		$ret["data"]=$data;

		echo json_encode($ret);
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
		$gl_uid=0;

		$query = "SELECT id,creds FROM ".$MyOpt["tbl"]."_utilisateurs WHERE (mail='".$_SERVER["PHP_AUTH_USER"]."' OR initiales='".$_SERVER["PHP_AUTH_USER"]."')";
		$res = $sql->QueryRow($query);

		if ($res["id"]>0)
		{
			if (password_verify($_SERVER["PHP_AUTH_PW"],$res["creds"]))
			{
				$gl_uid=$res["id"];
			}
			else if (password_verify(md5($_SERVER["PHP_AUTH_PW"]),$res["creds"]))
			{
				$gl_uid=$res["id"];
			}
		}

		if ($gl_uid==0)
		{
			header("HTTP/1.0 401 Unauthorized"); exit;
		}		
	}
	else
	{
		header("HTTP/1.0 401 Unauthorized"); exit;
	}
	
// ---- Header ----------------------------------------------------------------------------------------
  	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	
	// HTTP/1.0
	header("Pragma: no-cache");

	// Charset
	// header('Content-type: text/html; charset=UTF-8');
	header('Content-type: application/json; charset=utf-8');

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