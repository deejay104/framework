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
// Class Utilisateur

class user_core extends objet_core
{
	protected $table="utilisateurs";
	protected $mod="membres";
	protected $rub="detail";

	protected $droit=array("prenom"=>"ModifUser","nom"=>"ModifUser","droits"=>"ModifUserDroits","password"=>"modifUserPassword","dte_login"=>"ModifUserDteLogin");
	protected $type=array("prenom"=>"ucword","nom"=>"uppercase","initiales"=>"uppercase","mail"=>"mail","commentaire"=>"text","notification"=>"bool","virtuel"=>"bool");

	// protected $tabList=array(
		// "status"=>array('1new'=>'Nouveau','2sched'=>'Prochaine version','3inprg'=>'En cours','4test'=>'En test','5close'=>'Publié'),
		// "module"=>array("core"=>"Framework","user"=>"Utilisateur","admin"=>"Administration","docs"=>"Documents","custom"=>"Autre")
	// );

	# Constructor
	function __construct($id=0,$sql,$me=false,$setdata=true)
	{
		$this->id=$id;
		$this->me=$me;
		$this->prenom="";
		$this->nom="";
		$this->fullname="";
		$this->actif="oui";
		$this->virtuel="non";
		$this->mail="";

		$this->data["nom"]="";
		$this->data["prenom"]="";
		$this->data["initiales"]="";
		$this->data["password"]="";
		$this->data["mail"]="";
		$this->data["notification"]="oui";
		$this->data["commentaire"]="";
		$this->data["actif"]="oui";
		$this->data["virtuel"]="non";
		$this->data["aff_msg"]="0";
		$this->data["dte_login"]="0000-00-00 00:00:00";


		// Données utilisateurs
		$this->donnees=array();

		// Droits utilisateurs
		$this->groupe=array();

		parent::__construct($id,$sql);

		
		// print_r($this);
	}

	// Charge les données utilisateurs
	function load($id)
	{
		parent::load($id);
		
		$this->id=$id;
		$this->prenom=($this->data["prenom"]!="") ? ucwords($this->data["prenom"]) : "";
		$this->nom=($this->data["nom"]!="") ? strtoupper($this->data["nom"]) : "";
		$this->actif=$this->data["actif"];
		$this->virtuel=$this->data["virtuel"];
		$this->mail=strtolower($this->data["mail"]);

		$this->fullname=AffFullName($this->prenom,$this->nom);
		// $this->data["fullname"]=$this->fullname;
		$this->data["droits"]="";

		// Charge les droits
		$sql=$this->sql;
		$query = "SELECT groupe FROM ".$this->tbl."_droits WHERE uid='".$this->id."' ORDER BY groupe";
		$sql->Query($query);
		$this->groupe=array();
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$this->groupe[$sql->data["groupe"]]=true;
		}

		// Charge les roles
		if ($this->me)
		{
			$this->loadRoles();
		}
	}

	// Charge les roles
	function loadRoles()
	{
		$this->role=array();
		$this->role[""]=true;
		$sql=$this->sql;
		$query = "SELECT roles.role, roles.autorise FROM ".$this->tbl."_roles AS roles LEFT JOIN ".$this->tbl."_droits AS droits ON droits.groupe=roles.groupe  WHERE (uid='".$this->id."' OR roles.groupe='ALL') AND roles.role IS NOT NULL ORDER BY roles.autorise";

		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$this->role[$sql->data["role"]]=($sql->data["autorise"]=="oui") ? true : false;
		}
	}

	// Charge les données complémentaires
	function LoadDonneesComp()
	{
		$sql=$this->sql;
		$query = "SELECT donnees.id,def.id AS did,def.nom,donnees.valeur FROM ".$this->tbl."_utildonneesdef AS def LEFT JOIN ".$this->tbl."_utildonnees AS donnees ON donnees.did=def.id AND (donnees.uid='$this->id' OR donnees.uid IS NULL) WHERE def.actif='oui' ORDER BY ordre, nom";
		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$this->donnees[$sql->data["did"]]["id"]=$sql->data["id"];
			$this->donnees[$sql->data["did"]]["nom"]=$sql->data["nom"];
			$this->donnees[$sql->data["did"]]["valeur"]=$sql->data["valeur"];
		}
	}

	function aff($key,$typeaff="html",$formname="form_data")
	{
		$ret=parent::aff($key,$typeaff,$formname);

		$sql=$this->sql;
		if ($typeaff=="form")
		{
			if ($key=="droits")
			{
				$ret="";
				$sql=$this->sql;
				$query="SELECT id,groupe, description FROM ".$this->tbl."_groupe ORDER BY description";
				$sql->Query($query);
		
				for($i=0; $i<$sql->rows; $i++)
				{ 
					$sql->GetRow($i);
					$ret.="<input type='checkbox' name='form_droits[".$sql->data["groupe"]."]' ".(($this->groupe[$sql->data["groupe"]]>0) ? "checked" : "")." value='".$sql->data["groupe"]."' /> ".$sql->data["description"]." (".$sql->data["groupe"].")<br />";
				}
				if (GetDroit("SYS"))
				{
					$ret.="<input type='checkbox' name='form_droits[SYS]' ".(($this->groupe["SYS"]>0) ? "checked" : "")." value='SYS' /> Super Administrateur (SYS)<br />";
				}
				$ret="<span>".$ret."</span>";
			}
		}
		else
		{
			if ($key=="droits")
			{
				$sql=$this->sql;
				$query="SELECT droits.groupe, groupe.description FROM ".$this->tbl."_droits AS droits LEFT JOIN ".$this->tbl."_groupe AS groupe ON droits.groupe=groupe.groupe WHERE uid='".$this->id."' ORDER BY description";
				$sql->Query($query);
		
				$ret="";
				for($i=0; $i<$sql->rows; $i++)
				{ 
					$sql->GetRow($i);
					if (($sql->data["groupe"]!="SYS") || GetDroit("SYS"))
					{
						$ret.=(($sql->data["description"]!="") ? $sql->data["description"]." (".$sql->data["groupe"].")" : $sql->data["groupe"])."<br/>";
					}
				}
				$ret="<span>".$ret."</span>";
			}
		}
		return $ret;
	}
	
	// Affiche les données complémentaires
	function AffDonneesComp($i,$typeaff="html")
	{
		// Défini les droits de modification des utilisateurs
		$mycond=$this->me;	// Le user a le droit de modifier toutes ses données
		// Si on a le droit de modif on autorise
		if (GetDroit("ModifUserDonnees"))
		  { $mycond=true; }
		  
		// Si l'utilisateur a le droit de tout modifier alors on force
		if (GetDroit("ModifUserAll"))
		  { $mycond=true; }
		// Si on a pas le droit on repasse en visu
		if ((!$mycond) && ($typeaff!="val"))
		  { $typeaff="html"; }
 	
		if ($typeaff=="form")
		{
			$ret="<label>".$this->donnees[$i]["nom"]."</label><input name='form_donnees[".$i."]' value='".$this->donnees[$i]["valeur"]."'></br>";
		}
		else
		{
			$ret="<label>".$this->donnees[$i]["nom"]."</label>".$this->donnees[$i]["valeur"]."</br>";
  	  	}
		return $ret;
	}

	function SaveDonneesComp($tabd)
	{ 
		global $gl_uid;
		$sql=$this->sql;
		foreach($this->donnees as $did=>$d)
		{
			$this->donnees[$did]["valeur"]=$tabd[$did];
			$td=array("valeur"=>$this->donnees[$did]["valeur"], "uid"=>$this->id, "did"=>$did);
			$sql->Edit("user",$this->tbl."_utildonnees",$d["id"],$td);
		}
	}
	
	function SaveDroits($tabDroits)
	{
		global $gl_uid;
		$sql=$this->sql;
		// Charge les enregistrements
		$query = "SELECT * FROM ".$this->tbl."_groupe";
		$sql->Query($query);
		$tabgrp=array();
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			if (($sql->data["groupe"]!="SYS") || (GetDroit("SYS")))
			{
				$tabgrp[$sql->data["groupe"]]["bd"]=$sql->data["id"];
				$tabgrp[$sql->data["groupe"]]["new"]=0;
				$tabgrp[$sql->data["groupe"]]["old"]=0;
			}
		}
		
		// Charge les nouvelles valeurs
		if (is_array($tabDroits))
		{
			foreach($tabDroits as $g=>$d)
			{
				if (($g!="SYS") || (GetDroit("SYS")))
				{
					$tabgrp[$g]["new"]=1;
				}
			}
		}
		// Charge les anciennnes
		if (is_array($this->groupe))
		{
			foreach($this->groupe as $g=>$id)
			{
				if (($g!="SYS") || (GetDroit("SYS")))
				{
					$tabgrp[$g]["old"]=$id;
				}
			}
		}
		
		// Vérifie la différence
		foreach($tabgrp as $grp=>$v)
		{
			if (($v["new"]==1) && ($v["old"]>0))
			{
				// On ne fait rien
			}
			else if (($v["new"]==0) && ($v["old"]>0))
			{
				// Suppression du groupe
				$this->DelGroupe($grp);
			}
			else if (($v["new"]==1) && ($v["old"]==0))
			{
				// Ajout du groupe
				$this->AddGroupe($grp);
			}
		}
		
		return "";
	}

	function Valid($key,$v,$ret=false)
	{
		if ($key=="initiales")
		  {
			if ($v=="")
			{ 
				$this->data["initiales"]=substr($this->data["prenom"],0,1).substr($this->data["nom"],0,2);
				$vv=$this->data["initiales"];
			}
			else
			{
			  	$vv=$v;
			}
			$sql=$this->sql;
			$query = "SELECT COUNT(*) AS nb FROM ".$this->tbl."_utilisateurs WHERE initiales='".$vv."' AND id<>'".$this->id."' AND actif='oui'";
			$res = $sql->QueryRow($query);
			if (($res["nb"]>0) && ($ret==false) && ($v!=""))
			{
				return "Les initiales choisies existent déjà !";
			}
			else if ($res["nb"]>0)
			{
			  	$vv="";
			}
			else
			{
			  	$vv=strtolower($vv);
			}
		}
		else if ($key=="mail")
		{
		  	$vv=$v;
			$sql=$this->sql;
			$query = "SELECT COUNT(*) AS nb FROM ".$this->tbl."_utilisateurs WHERE mail='".$vv."' AND id<>'".$this->id."' AND actif='oui'";
			$res = $sql->QueryRow($query);
			if (($res["nb"]>0) && ($ret==false) && ($v!=""))
			{
				return "Le mail choisi existe déjà !";
			}
			else if ($res["nb"]>0)
			{
			  	$vv="";
			}
			else
			{
			  	$vv=strtolower($vv);
			}
		}
		else if ($key=="prenom")
		{	
			$vv=preg_replace("/ /","-",$v);
			$vv=strtolower($vv);
		}
		else if ($key=="nom")
		{
		  	if ($v=="")
		  	  {
		  	  	return "Le nom est vide.<br />";
			  }
			$vv=strtolower($v);
		}
		else if ($this->type[$key]=="duration")
		{
			$vv=CalcTemps($v,false);
		}
		else if ($this->type[$key]=="text")
		{
			$vv=$v;
		}
		else
		{
			$vv=strtolower($v);
		}

		if ( (!is_numeric($key)) && ("($vv)"!="(**none**)") && ($ret==false))
		  { $this->data[$key]=$vv; }
		else if ($ret==true)
		  { return addslashes($vv); }
	}

	
	# Save Password
	function SaveMdp($mdp){
		$sql=$this->sql;
		$this->data["password"]=$mdp;
		$sql->Edit("user",$this->tbl."_utilisateurs",$this->id,array("password"=>$mdp));		
		return "";
	}
	
	function AddGroupe($grp) {
		global $gl_uid;
		$sql=$this->sql;
		$grp=trim($grp);
		
		if (($grp!="") && (($grp!="SYS") || (($grp=="SYS") && (GetDroit("SYS")))))
		{	
			$query ="INSERT INTO ".$this->tbl."_droits (`groupe` ,`uid` ,`uid_creat` ,`dte_creat`) ";
			$query.="VALUES ('".trim($grp)."' , '".$this->id."', '".$gl_uid."', '".now()."')";
			$sql->Insert($query);
		}
	}

	function DelGroupe($grp) {
		$sql=$this->sql;
		$query="DELETE FROM ".$this->tbl."_droits WHERE uid='".$this->id."' AND groupe='".$gl_grp."'";
		$sql->Delete($query);
	}

	function RazGroupe() {
		$sql=$this->sql;
		$query="DELETE FROM ".$this->tbl."_droits WHERE uid='".$this->id."'";
		$sql->Delete($query);
	}

	function Desactive(){
		global $gl_uid;
		$sql=$this->sql;
		$this->actif="off";
		$sql->Edit("user",$this->tbl."_utilisateurs",$this->id,array("actif"=>'off', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
	}

	function Active(){
		global $gl_uid;
		$sql=$this->sql;
		$this->actif="oui";
		$sql->Edit("user",$this->tbl."_utilisateurs",$this->id,array("actif"=>'oui', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
	}
}



// *********************************************************************************************************


function ListActiveUsers($sql,$order="",$tabtype="",$virtuel="non")
{
	global $MyOpt;
 
	$lstuser=array();
	$type=array();
	
	if ($tabtype!="")
	  { 
	  	$type=explode(",",$tabtype);
	  }
	$reqAnd="";
	$reqOr="";
	if ( (is_array($type)) && (count($type)>0) )
	  {
			foreach($type as $t)
			  {
			  	if (substr($t,0,1)=="!")
			  	  { $reqAnd.=" AND type<>'".substr($t,1,strlen($t)-1)."'"; }
			  	else
			  	  { $reqOr.="type='$t' OR "; }
			  }
			if ($reqOr!="")
			  {
					$reqOr.="1=0";
				}
	  }
	if ($order=="std")
	  { $order=(($MyOpt["globalTrie"]=="nom") ? "nom,prenom" : "prenom,nom"); }
	$query="SELECT id FROM ".$MyOpt["tbl"]."_utilisateurs WHERE (";
	$query.="actif='oui'";
	if ((GetDroit("ListeUserDesactive")) && ($MyOpt["showDesactive"]=="on"))
	{
		$query.=" OR actif='off'";
	}
	if ((GetDroit("ListeUserSupprime")) && ($MyOpt["showSupprime"]=="on"))
	{
		$query.="OR actif='non'";
	}
	$query.=") ";
	$query.=(($virtuel!="") ? " AND virtuel='$virtuel'" : "").(($reqOr!="") ? " AND (".$reqOr.")" : "").(($reqAnd!="") ? $reqAnd : "").(($order!="") ? " ORDER BY $order" : "");
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	  { 
		$sql->GetRow($i);
		$lstuser[$i]=$sql->data["id"];
	  }
	return $lstuser;
}

function ListActiveMails($sql)
{
	global $MyOpt;
	$lstuser=array();
	$query="SELECT id FROM ".$MyOpt["tbl"]."_utilisateurs WHERE actif='oui' AND virtuel='non' AND mail<>'' AND notification='oui'";
	$sql->Query($query);
	
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$lstuser[$i]=$sql->data["id"];
	}
	return $lstuser;
}

function AffListeMembres($sql,$form_uid,$name,$type="",$sexe="",$order="std",$virtuel="non")
{
	global $MyOpt;
	if ($order=="std")
	  { $order=(($MyOpt["globalTrie"]=="nom") ? "nom,prenom" : "prenom,nom"); }
	$query ="SELECT id,prenom,nom FROM ".$MyOpt["tbl"]."_utilisateurs WHERE actif='oui' ";
	$query.=(($virtuel!="") ? "AND virtuel='$virtuel' " : "");
	$query.=(($type!="") ? "AND type='$type' " : "");
	$query.=(($sexe!="") ? "AND sexe='$sexe' " : "");
	$query.=(($order!="") ? " ORDER BY $order" : "");
	
	$sql->Query($query);
	$lstuser ="<select name=\"$name\">";
	$lstuser.="<option value=\"0\">Aucun</option>";
	for($i=0; $i<$sql->rows; $i++)
	  { 
		$sql->GetRow($i);
		$sql->data["nom"]=strtoupper($sql->data["nom"]);
		$sql->data["prenom"]=ucwords($sql->data["prenom"]);
		$fullname=AffFullName($sql->data["prenom"],$sql->data["nom"]);
		$lstuser.="<option value=\"".$sql->data["id"]."\" ".(($form_uid==$sql->data["id"]) ? "selected" : "").">".$fullname."</option>";
	  }
	$lstuser.="</select>";
	return $lstuser;
}

function AffFullname($prenom,$nom)
{
	global $MyOpt;
	$fullname="";
	$nom=strtoupper($nom);
	$prenom=preg_replace("/-/"," ",$prenom);
	$prenom=ucwords($prenom);
	$prenom=preg_replace("/ /","-",$prenom);
	if ($MyOpt["globalTrie"]=="nom")
	{
		$fullname=$nom;
		$fullname.=(($prenom!="") && ($nom!=""))?" ":"";
		$fullname.=$prenom;
		$fullname.=(($prenom=="")&&($nom==""))?"N/A":"";
	}		
	else
	{
		$fullname=$prenom;
		$fullname.=(($prenom!="") && ($nom!=""))?" ":"";
		$fullname.=$nom;
		$fullname.=(($prenom=="")&&($nom==""))?"N/A":"";
	}		
	return $fullname;
}
 
function AffInitiales($res)
{
  	if ($res["initiales"]!="")
  	  { return strtoupper($res["initiales"]); }
  	else
  	  { return strtoupper(substr($res["prenom"],0,1).substr($res["nom"],0,1)); }
}

// Test si un ID correspond à l'utilisateur ou un de ses enfants
function GetMyId($id)
{
	global $myuser;
  	if ($id==$myuser->id)
  	  { return true; }

	if (GetModule("creche"))
	{
	  	$myuser->LoadEnfants();
	}
	
  	if (is_array($myuser->data["enfant"]))
  	{
       	foreach($myuser->data["enfant"] as $enfant)
		{
        	if ($enfant["id"]==$id)
        	  { return true; }
        }
	}
  	return false;
}
 
?>