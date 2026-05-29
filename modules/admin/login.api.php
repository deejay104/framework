<?php
	function verifyCredentials($myusr,$mypwd)
	{
		global $sql,$MyOpt;


		$ret["type"]="password";
		$ret["myusr"]=$myusr;
		$ret["status"]=500;
		$ret["uid"]=0;
		$ret["code"]="none";

		$query = "SELECT id,prenom,nom,mail,password,creds FROM ".$MyOpt["tbl"]."_utilisateurs WHERE ((mail='".$myusr."' AND mail<>'') OR (initiales='".$myusr."' AND initiales<>'')) AND actif='oui' AND virtuel='non'";
		$res = $sql->QueryRow($query);

		if ((isset($res["id"])) && ($res["id"]>0) && (password_verify($mypwd,$res["creds"])))
		{
			$ret["status"]=200;
			$ret["code"]="password";
			$ret["uid"]=$res["id"];

			$myid=0;
			if ($MyOpt["tokenexpire"]>0)
			{
				$t_expire=((isset($MyOpt["tokenexpire"])) && ($MyOpt["tokenexpire"]>0)) ? $MyOpt["tokenexpire"] : 7;
				generateRefreshToken($res["id"],$t_expire);
				$ret["token"]=generateJWT($res["id"]);

				$s_expire=((isset($MyOpt["sessionexpire"])) && ($MyOpt["sessionexpire"]>0)) ? $MyOpt["sessionexpire"] : 600;

				setcookie('t_session', $ret["token"], [
					'expires'  => time() + $s_expire,
					'path'     => '/',
					'httponly'  => true,      // JS n'en a pas besoin non plus
					'secure'   => true,
					'samesite' => 'Lax',     // ← Lax et non Strict, sinon le cookie
				]);                           //   ne part pas quand on arrive d'un
											//   lien externe (ex: email, Google)
			}
			
			$query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."' WHERE id='".$res["id"]."'";
			$sql->Update($query);

			$ret["message"]="Access granted";
		}

		else
		{
			$ret["status"]=401;
			$ret["code"]="rejected";
			$ret["message"]="Bad password";
		}

		$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".addslashes($res["prenom"])." ".addslashes($res["nom"])."',srcip='".getip()."',status='".$ret["code"]."',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($ret)."'";
		$sql->Insert($query);

		return $ret;
	}

	function verifyRefreshToken($token)
	{
		global $sql,$MyOpt;


		$ret["type"]="token";
		$ret["status"]=500;
		$ret["uid"]=0;
		$ret["code"]="none";

		$query="SELECT id,uid,token,dte_expire FROM ".$MyOpt["tbl"]."_token WHERE token='".hash('sha256', $token)."' AND actif='oui' AND dte_expire>'".now()."'";
		$res = $sql->QueryRow($query);

//	$query="INSERT INTO ".$MyOpt["tbl"]."_token SET uid=".$gl_uid.", token='".$refreshTokenHash."', uid_creat='".$gl_uid."',uid_maj='".$gl_uid."',dte_creat='".now()."', dte_expire='".$expiresAt."'";
//		$query = "SELECT id,prenom,nom,mail,password,creds FROM ".$MyOpt["tbl"]."_utilisateurs WHERE ((mail='".$myusr."' AND mail<>'') OR (initiales='".$myusr."' AND initiales<>'')) AND actif='oui' AND virtuel='non'";


		if ((isset($res["id"])) && ($res["id"]>0))
		{
			$ret["status"]=200;
			$ret["code"]="token";
			$ret["uid"]=$res["uid"];

			if ($MyOpt["tokenexpire"]>0)
			{
				$t_expire=((isset($MyOpt["tokenexpire"])) && ($MyOpt["tokenexpire"]>0)) ? $MyOpt["tokenexpire"] : 7;
				generateRefreshToken($res["id"],$t_expire);
				$ret["token"]=generateJWT($res["id"]);

				$s_expire=((isset($MyOpt["sessionexpire"])) && ($MyOpt["sessionexpire"]>0)) ? $MyOpt["sessionexpire"] : 600;

				setcookie('t_session', $ret["token"], [
					'expires'  => time() + $s_expire,
					'path'     => '/',
					'httponly'  => true,    // JS n'en a pas besoin non plus
					'secure'   => true,
					'samesite' => 'Lax',    // ← Lax et non Strict, sinon le cookie
				]);                         //   ne part pas quand on arrive d'un
											//   lien externe (ex: email, Google)
			}

			$query="UPDATE ".$MyOpt["tbl"]."_token SET actif='non' WHERE id='".$res["id"]."'";
			$sql->Update($query);

			$query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."' WHERE id='".$res["id"]."'";
			$sql->Update($query);

			$ret["message"]="Access granted";

			$query = "SELECT prenom,nom FROM ".$MyOpt["tbl"]."_utilisateurs WHERE id='".$res["id"]."'";
			$res = $sql->QueryRow($query);
			$username=addslashes($res["prenom"])." ".addslashes($res["nom"]);

		}
		else
		{
			$ret["status"]=401;
			$ret["code"]="rejected";
			$ret["message"]="Bad token";
			$username="unkown";
		}



		$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".$username."',srcip='".getip()."',status='".$ret["code"]."',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($ret)."'";
		$sql->Insert($query);

		return $ret;
	}
/*
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
			$_COOKIE['uid']=$gl_uid;
			$_COOKIE['sessid']=$myid;

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
			$_COOKIE['sessid']=-1;

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

		if ((isset($res["id"])) && ($res["id"]>0) && (password_verify($mypwd,$res["creds"])))
		{
			$data["result"]="success";
			$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".addslashes($res["prenom"])." ".addslashes($res["nom"])."',srcip='".getip()."',status='password',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($data)."'";
			$sql->Insert($query);
			$_COOKIE['uid']=$res["id"];
			$gl_uid=$res["id"];

			$myid=0;
			$token="";
			if ($MyOpt["tokenexpire"]>0)
			{
				$token=generateToken($gl_uid,$MyOpt["tokenexpire"]);

				// $token=bin2hex(random_bytes(32));
				
				// $query="INSERT INTO ".$MyOpt["tbl"]."_token SET uid=".$gl_uid.", token='".password_hash($token, PASSWORD_BCRYPT, array('cost' => 12))."', uid_creat='".$gl_uid."',uid_maj='".$gl_uid."',dte_creat='".now()."', dte_expire='".date("Y-m-d H:i:s",time()+$MyOpt["tokenexpire"]*3600*24)."'";
				// $myid=$sql->Insert($query);
				// $_COOKIE['sessid']=$myid;

				// $ret["myid"]=$myid;
				$ret["token"]=$token;
			}
			
			$query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."' WHERE id='".$res["id"]."'";
			$sql->Update($query);

			$ret["auth"]="OK";
		}
		else if ((isset($res["id"])) && ($res["id"]>0) && ($res["creds"]=="") && ($mypwd==$res["password"]))  // For compatibility with older password
		{
			$data["result"]="success";
			$query="INSERT INTO ".$MyOpt["tbl"]."_login SET username='".addslashes($res["prenom"])." ".addslashes($res["nom"])."',srcip='".getip()."',status='password',dte_maj='".now()."',header='".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."',type='".json_encode($data)."'";
			$sql->Insert($query);
			$_COOKIE['uid']=$res["id"];
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
		
*/

?>