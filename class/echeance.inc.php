<?
class echeance_core extends objet_core
{
	# Constructor
	protected $table="ressources";
	protected $mod="";
	protected $rub="";

	protected $fields=array
	(
		"typeid" => Array("type" => "number", "index" => "1", ),
		"uid" => Array("type" => "number", "index" => "1", ),
		"dte_echeance" => Array("type" => "date", ),
	);



 	# Constructor
	function __construct($id="",$sql,$uid=0){
		global $MyOpt;
		global $gl_uid;

		$this->sql=$sql;
		$this->tbl=$MyOpt["tbl"];
		$this->myuid=$gl_uid;

		$this->id=0;
		$this->type="user";
		$this->typeid="";
		$this->poste=0;
		$this->description="";
		$this->uid=$uid;
		$this->dte_echeance="";
		$this->paye="non";
		$this->editmode="html";
		$this->droit="html";

		if ($id>0)
		{
			$this->load($id);
		}
	}

	# Charge une échéance par son id
	function load($id){
		$this->id=$id;
		$sql=$this->sql;
		$query = "SELECT echeance.*, echeancetype.context, echeancetype.poste, echeancetype.description, echeancetype.droit, echeancetype.multi, echeancetype.resa FROM ".$this->tbl."_echeance AS echeance LEFT JOIN ".$this->tbl."_echeancetype AS echeancetype ON echeance.typeid=echeancetype.id WHERE echeance.id='$id'";
		$res = $sql->QueryRow($query);
		// Charge les variables
		$this->context=$res["context"];
		$this->typeid=$res["typeid"];
		$this->poste=$res["poste"];
		$this->uid=$res["uid"];
		$this->dte_echeance=$res["dte_echeance"];
		$this->paye=$res["paye"];
		$this->description=$res["description"];
		$this->droit=$res["droit"];
		$this->multi=$res["multi"];
		$this->resa=$res["resa"];
	}

	# Charge une échéance par son type
	function loadtype($tid){
		$sql=$this->sql;
		$query = "SELECT echeance.*, echeancetype.context, echeancetype.poste, echeancetype.description, echeancetype.droit, echeancetype.multi, echeancetype.resa FROM ".$this->tbl."_echeance AS echeance LEFT JOIN ".$this->tbl."_echeancetype AS echeancetype ON echeance.typeid=echeancetype.id WHERE echeance.typeid='$tid' AND echeance.uid='".$this->uid."'";
		$res = $sql->QueryRow($query);
		// Charge les variables
		$this->id=$res["id"];
		$this->context=$res["context"];
		$this->typeid=$res["typeid"];
		$this->poste=$res["poste"];
		// $this->uid=$res["uid"];
		$this->dte_echeance=$res["dte_echeance"];
		$this->paye=$res["paye"];
		$this->description=$res["description"];
		$this->droit=$res["droit"];
		$this->multi=$res["multi"];
		$this->resa=$res["resa"];
	}

	function Valid($k,$v,$ret = false) 
	{

		$vv=$v;
		if ($k=="dte_echeance")
		{
	  	  	if (date2sql($v)!="nok")
	  	  	  { $vv=date2sql($v); }
	  	  	else if (preg_match("/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})([0-9: ]*)$/",$v))
	  	  	  { $vv=$v; }
		}
		else if ($k=="typeid")
		{
			if ( (is_numeric($v)) && ($v>0) )
			{
				$vv=$v;
			}
			else
			{
				$vv=$this->typeid;
			}
		}
		else if ($k=="uid")
		{
			if ( (is_numeric($v)) && ($v>0) )
			{
				$vv=$v;
			}
			else
			{
				$vv=$this->uid;
			}
		}
		return $vv;
	}

	function Create()
	{
		$sql=$this->sql;
		$this->id=$sql->Edit("echeance",$this->tbl."_echeance",0,array("uid_creat"=>$this->myuid, "dte_creat"=>now(), "uid_maj"=>$this->myuid, "dte_maj"=>now()));		
	}

	function Delete()
	{
		$sql=$this->sql;
		$sql->Edit("echeance",$this->tbl."_echeance",$this->id,array("actif"=>"non", "uid_maj"=>$this->myuid, "dte_maj"=>now()));
	}

	function Save()
	{
		$sql=$this->sql;
		if ($this->id==0)
		{
			$this->Create();
		}

		$sql->Edit("echeance",$this->tbl."_echeance",$this->id,array("typeid"=>$this->Valid("typeid",$this->typeid),"uid"=>$this->Valid("uid",$this->uid),"dte_echeance"=>$this->Valid("dte_echeance",$this->dte_echeance),"paye"=>$this->Valid("paye",$this->paye),"uid_maj"=>$this->myuid, "dte_maj"=>now()));		
	}

	function Affiche($type="") 
	{ global $MyOpt;
		$ret="";

		if ($this->editmode=="form")
		{
			$sql=$this->sql;
			$n=0;

			$ret.="<p>";
			$ret.="<div id='echeance_0'></div>";
			$ret.="</p>";

			$ret.="<script>";
			$ret.="function AddEcheance(i) {";

			$ret.="var r=\"<img src='static/images/icn16_vide.png' style='vertical-align:middle; border: 0px;  height: 16px; width: 16px;'>&nbsp;\";\n";
			$ret.="r=r+\"<select name='form_echeance_type[a\"+i+\"]' OnChange=''>\";\n";

			$tabEcheance=array();
			$query ="SELECT echeance.typeid,echeancetype.multi FROM ".$MyOpt["tbl"]."_echeance AS echeance ";
			$query.="LEFT JOIN ".$MyOpt["tbl"]."_echeancetype AS echeancetype ON echeance.typeid=echeancetype.id ";
			$query.="WHERE echeancetype.context='".$this->context."' AND echeance.uid='".$this->uid."' AND echeance.actif='oui'";

			$sql->Query($query);
			for($i=0; $i<$sql->rows; $i++)
			{
				$sql->GetRow($i);
				if ($sql->data["multi"]=="non")
				{
					$tabEcheance[$sql->data["typeid"]]="ok";
				}
			}

			$query="SELECT id,description,droit FROM ".$MyOpt["tbl"]."_echeancetype AS type WHERE context='".$this->context."' AND actif='oui' ORDER BY description";
			$sql->Query($query);
			for($i=0; $i<$sql->rows; $i++)
			{
				$sql->GetRow($i);
				if ( (GetDroit($sql->data["droit"])) && ((!isset($tabEcheance[$sql->data["id"]])) || ($tabEcheance[$sql->data["id"]]=="")) )
				{
					$ret.="r=r+\"<option value='".$sql->data["id"]."'>".$sql->data["description"]."</option>\";\n";
					$n=$n+1;
				}
			}
			$ret.="r=r+\"</select>&nbsp;\";\n";

			$ret.="r=r+\"<input name='form_echeance[a\"+i+\"]' value='' type='date' style='width: 140px;' OnChange='AddEcheance(\"+(i+1)+\");'><div id='echeance_\"+(i+1)+\"'></div>\";\n";
			$ret.="var d=document.getElementById('echeance_'+i);\n";
			$ret.="d.innerHTML=r;\n";
				
			$ret.="}\n";
			
			$ret.="AddEcheance(0);\n";
			$ret.="</script>";

			if ($n==0)
			{
				$ret="";
			}
		}
		else if ( ($this->editmode=="edit") && (GetDroit($this->droit)) )
		{
			$ret ="<p>";
			$ret.="<div id='aff_echeance".$this->id."' OnMouseOver='document.getElementById(\"echeance_del_".$this->id."\").style.visibility=\"visible\";' OnMouseOut='document.getElementById(\"echeance_del_".$this->id."\").style.visibility=\"hidden\";'>";
			$ret.="<img src='static/images/icn16_vide.png' style='vertical-align:middle; border: 0px;  height: 16px; width: 16px;'>&nbsp;";
			$ret.="Echéance ".$this->description." le <input name='form_echeance[".$this->id."]' id='form_echeance".$this->id."' value='".$this->dte_echeance."' type='date' style='width: 165px;'>&nbsp;";
			$ret.="<a href=\"#\" OnClick=\"document.getElementById('form_echeance".$this->id."').value=''; document.getElementById('aff_echeance".$this->id."').style.display='none';\" class='imgDelete'><img  id='echeance_del_".$this->id."' src='static/images/icn16_supprimer.png' style='visibility:hidden;'></a>";
			$ret.="</div>";
			$ret.="</p>";
		}
		else if ($type=="val")
		{
			$ret=AffDate($this->dte_echeance);
		}
		else
		{
			$ret ="<p>";
			$ret.="<img src='static/images/icn16_".TestDate($this->dte_echeance).".png' style='vertical-align:middle; border: 0px;  height: 16px; width: 16px;'>&nbsp;";
			$ret.="Echéance ".$this->description." le ".AffDate($this->dte_echeance);
			$ret.="</p>";
		}
		return $ret;
	}

	function val($key="")
	{ global $MyOpt;
		
		return $this->dte_echeance;
	}

}


function ListEcheance($sql,$id,$context="user")
  {
	global $MyOpt, $gl_uid, $myuser;

	$query ="SELECT echeance.id FROM ".$MyOpt["tbl"]."_echeance AS echeance ";
	$query.="LEFT JOIN ".$MyOpt["tbl"]."_echeancetype AS type ON echeance.typeid=type.id ";
	$query.="WHERE type.context='".$context."' AND echeance.actif='oui' ".(($id>0) ? "AND uid='$id'" : "" )." ORDER BY dte_echeance";
	$sql->Query($query);
	$lstdte=array();
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$lstdte[$i]=$sql->data["id"];
	}

	return $lstdte;
  }

function VerifEcheance($sql,$id,$context="user")
  {
	global $MyOpt, $gl_uid, $myuser;

	$query ="SELECT echeancetype.description,echeancetype.resa,echeance.dte_echeance FROM ".$MyOpt["tbl"]."_echeancetype AS echeancetype ";
	$query.="LEFT JOIN ".$MyOpt["tbl"]."_echeance AS echeance ON echeancetype.id=echeance.typeid AND echeance.actif='oui' AND echeance.uid='".$id."' ";
	$query.="WHERE echeancetype.actif='oui' AND echeancetype.type='".$context."' AND (echeance.dte_echeance<'".now()."' OR echeance.dte_echeance IS NULL) ORDER BY echeance.dte_echeance";

	$sql->Query($query);
	$lstdte=array();
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$lstdte[$i]["description"]=$sql->data["description"];
		$lstdte[$i]["resa"]=$sql->data["resa"];
		$lstdte[$i]["dte_echeance"]=$sql->data["dte_echeance"];
	}

	return $lstdte;
  }

function ListeEcheanceParType($sql,$id,$context="user") 
{ global $MyOpt;
	$query ="SELECT echeance.id FROM ".$MyOpt["tbl"]."_echeance AS echeance ";
	$query.="LEFT JOIN ".$MyOpt["tbl"]."_echeancetype AS type ON echeance.typeid=type.id ";
	$query.="LEFT JOIN ".$MyOpt["tbl"]."_utilisateurs AS usr ON echeance.uid=usr.id ";
	$query.="WHERE type.context='".$context."' AND echeance.actif='oui' AND echeance.typeid='".$id."' AND usr.actif='oui'";

	$sql->Query($query);
	$lstdte=array();
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$lstdte[$i]=$sql->data["id"];
	}

	return $lstdte;		
}






class echeancetype_core extends objet_core
{
	# Constructor
	protected $table="echeancetype";
	protected $mod="";
	protected $rub="";

	protected $fields=array
	(
		"description" => Array("type" => "varchar", "len"=>100),
		"resa" => Array("type" => "enum" ),
		"droit" => Array("type" => "varchar","len"=>5 ),
		"multi" => Array("type" => "bool", "default" => "non" ),
		"notif" => Array("type" => "bool", "default" => "non" ),
		"delai" => Array("type" => "number", "default" => "30" ),
		"context" => Array("type" => "varchar", "len"=>10, "default" => "user" ),
		"recipient" => Array("type" => "varchar", "len"=>10, "default" => "user" ),
	);

	protected $tabList=array(
		"resa"=>array('obligatoire'=>'Obligatoire','instructeur'=>'Instructeur','facultatif'=>'Facultatif'),
	);
	
	function aff($key,$typeaff="html",$formname="form_data",&$render="")
	{
		$ret=parent::aff($key,$typeaff,$formname,$render);

		if ($typeaff=="form")
		{
			if ($key=="droit")
			{
				$sql=$this->sql;
				$query = "SELECT groupe,description FROM ".$this->tbl."_groupe ORDER BY description";
				$sql->Query($query);
				$tabgrp=array();
				for($i=0; $i<$sql->rows; $i++)
				{ 
					$sql->GetRow($i);
					$tabgrp[$sql->data["groupe"]]=$sql->data["description"];
				}

				$ret="<select name=\"".$formname."[$key]\">";
				$ret.="<option value=''>Tout le monde</option>";
				foreach($tabgrp as $grp=>$d)
				{
					$ret.="<option value='".$grp."' ".(($grp==$this->data[$key]) ? "selected" : "").">".$d."</option>";
				}
				$ret.="</select>";
			}
			else if ($key=="recipient")
			{
				$sql=$this->sql;
				$query = "SELECT groupe,description FROM ".$this->tbl."_groupe ORDER BY description";
				$sql->Query($query);
				$tabgrp=array();
				for($i=0; $i<$sql->rows; $i++)
				{ 
					$sql->GetRow($i);
					$tabgrp[$sql->data["groupe"]]=$sql->data["description"];
				}

				$ret="<select name=\"".$formname."[$key]\">";
				$ret.="<option value=''>Utilisateur</option>";
				foreach($tabgrp as $grp=>$d)
				{
					$ret.="<option value='".$grp."' ".(($grp==$this->data[$key]) ? "selected" : "").">".$d."</option>";
				}
				$ret.="</select>";
			}		}
		return $ret;
	}

	function ListeEcheance($context="user") 
	{
		$query ="SELECT echeance.id FROM ".$this->tbl."_echeance AS echeance ";
		$query.="LEFT JOIN ".$this->tbl."_echeancetype AS type ON echeance.typeid=type.id ";
		$query.="LEFT JOIN ".$this->tbl."_utilisateurs AS usr ON echeance.uid=usr.id ";
		$query.="WHERE type.context='".$context."' AND echeance.actif='oui' AND echeance.typeid='".$this->id."' AND usr.actif='oui'";

		$sql=$this->sql;
		$sql->Query($query);
		$lstdte=array();
		for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);
			$lstdte[$i]=$sql->data["id"];
		}

		return $lstdte;		
	}
}

function ListEcheanceType($sql,$context="user")
{
	if ($context=="")
	{
		$t=array("actif"=>"oui");
	}
	else
	{
		$t=array("actif"=>"oui","context"=>$context);
	}
	return ListeObjets($sql,"echeancetype",array("id"),$t);
}

?>