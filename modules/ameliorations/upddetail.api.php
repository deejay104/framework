<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");

	require_once ("class/amelioration.inc.php");
	
	$res=array();
	$msg_erreur="";
	$msg_confirmation="";

	$pb = new amelioration_core($id,$sql);

	$data=json_decode(file_get_contents('php://input'), true);

	if (count($data["data"])>0)
	{
		foreach($data["data"] as $k=>$v)
		{
			$msg_erreur.=$pb->Valid($k,$v);
		}
		$msg_confirmation.="Vos données ont été enregistrées.<BR>";
	}
	
	$pb->Save();

	// Send JSON to the client.
	$res["msg_erreur"]=utf8_encode($msg_erreur);
	$res["msg_confirmation"]=utf8_encode($msg_confirmation);
	$res["id"]=$pb->id;
	echo json_encode($res);

?>