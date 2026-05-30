<?php
	function verifyCredentials($myusr,$mypwd)
	{
		global $sql,$MyOpt;


		$ret["type"]="password";
		$ret["myusr"]=$myusr;
		$ret["status"]=500;
		$ret["uid"]=0;
		$ret["code"]="none";

		$query = "SELECT id,prenom,nom,mail,creds FROM ".$MyOpt["tbl"]."_utilisateurs WHERE ((mail='".$myusr."' AND mail<>'') OR (initiales='".$myusr."' AND initiales<>'')) AND actif='oui' AND virtuel='non'";
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

		$gl_uid=verifyToken($token,"token");

		if ($gl_uid>0)
		{
			$ret["status"]=200;
			$ret["code"]="token";
			$ret["uid"]=$gl_uid;

			if ($MyOpt["tokenexpire"]>0)
			{
				$t_expire=((isset($MyOpt["tokenexpire"])) && ($MyOpt["tokenexpire"]>0)) ? $MyOpt["tokenexpire"] : 7;
				generateRefreshToken($ret["uid"],$t_expire);
			}

			$ret["token"]=generateJWT($gl_uid);
			$s_expire=((isset($MyOpt["sessionexpire"])) && ($MyOpt["sessionexpire"]>0)) ? $MyOpt["sessionexpire"] : 600;

			setcookie('t_session', $ret["token"], [
				'expires'  => time() + $s_expire,
				'path'     => '/',
				'httponly'  => true,    // JS n'en a pas besoin non plus
				'secure'   => true,
				'samesite' => 'Lax',    // ← Lax et non Strict, sinon le cookie
			]);                         //   ne part pas quand on arrive d'un
										//   lien externe (ex: email, Google)

			$query="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_login='".now()."' WHERE id='".$ret["uid"]."'";
			$sql->Update($query);

			$ret["message"]="Access granted";

			$query = "SELECT prenom,nom FROM ".$MyOpt["tbl"]."_utilisateurs WHERE id='".$ret["uid"]."'";
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


?>