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

	protected $type=array(
		"prenom"=>"ucword",
		"nom"=>"uppercase",
		"initiales"=>"uppercase",
		"mail"=>"email",
		"commentaire"=>"text",
		"notification"=>"bool",
		"virtuel"=>"bool",
		"groupe"=>"uppercase",
		"aff_jour"=>"date",
		"dte_login"=>"datetime",
		"language"=>"enum"
	);

	protected $droit=array(
		"prenom"=>"ModifUserInfos",
		"nom"=>"ModifUserInfos",
		"droits"=>"ModifUserDroits",
		"dte_login"=>"ModifUserDteLogin",
		"groupe"=>"ModifUserGroupe",
		"virtuel"=>"ModifUserVirtuel",
		"initiales"=>array("ownerid","ModifUserInfos"),
		"mail"=>array("ownerid","ModifUserInfos"),
		"notification"=>array("ownerid","ModifUserInfos"),
	);

	protected $tabList=array(
		"language"=>array(
			"fr"=>array('fr'=>"Fran�ais",'en'=>'Anglais'),
			"en"=>array('fr'=>"French",'en'=>'English'),
		)
	);

	protected $tabLang=array(
		"err_nickname"=>array(
			"fr"=>"Les initiales choisies existent d�j� !",
			"en"=>"This nickname already exists",
		),
		"err_mail"=>array(
			"fr"=>"Le mail choisi existe d�j� !",
			"en"=>"This email already exists",
		),
		"err_name"=>array(
			"fr"=>"Le nom est vide",
			"en"=>"Name is mandatory",
		)
	);
	
	# Constructor
	function __construct($id=0,$sql,$me=false)
	{
		$this->id=$id;
		$this->me=$me;
		$this->prenom="";
		$this->nom="";
		$this->fullname="";
		$this->actif="oui";
		$this->virtuel="non";
		$this->password="";
		$this->mail="";

		$this->data["nom"]="";
		$this->data["prenom"]="";
		$this->data["initiales"]="";
		$this->data["mail"]="";
		$this->data["notification"]="oui";
		$this->data["commentaire"]="";
		$this->data["virtuel"]="non";
		$this->data["language"]="fr";
		$this->data["groupe"]="";
		$this->data["aff_msg"]="0";
		$this->data["dte_login"]="0000-00-00 00:00:00";

		// Donn�es utilisateurs
		$this->donnees=array();

		// Droits utilisateurs
		$this->groupe=array();

		parent::__construct($id,$sql);

		
		// print_r($this);
	}
	
	// Charge les donn�es utilisateurs
	function load($id)
	{
		parent::load($id);
		
		$this->id=$id;
		$this->prenom=($this->data["prenom"]!="") ? ucwords($this->data["prenom"]) : "";
		$this->nom=($this->data["nom"]!="") ? strtoupper($this->data["nom"]) : "";
		$this->virtuel=$this->data["virtuel"];
		$this->mail=strtolower($this->data["mail"]);
		$this->fullname=AffFullName($this->prenom,$this->nom);
		$this->data["droits"]="";

		$sql=$this->sql;
		$query = "SELECT actif,password FROM ".$this->tbl."_utilisateurs WHERE id='".$this->id."'";
		$res=$sql->QueryRow($query);
		$this->password=$res["password"];
		$this->actif=$res["actif"];
	
		// Charge les droits
		$query = "SELECT groupe FROM ".$this->tbl."_droits WHERE uid='".$this->id."' ORDER BY groupe";
		$sql->Query($query);
		$this->groupe=array();
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$this->groupe[$sql->data["groupe"]]=true;
		}

		if (!isset($this->groupe["SYS"]))
		{
			$this->groupe["SYS"]=false;
		}
		
		// Charge les roles
		if ($this->me)
		{
			$this->LoadRoles();
		}
	}

	// Charge les roles
	function LoadRoles()
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

	function CheckDroit($role)
	{
		$mycond=parent::CheckDroit($role);

		if (GetDroit("ModifUserAll"))
		{
			$mycond=true;
		}

		return $mycond;
	}

	function TstDroit($role)
	{
		$this->LoadRoles();
		if ((isset($this->role[$role])) && ($this->role[$role]))
		  { return true; }
		return false;
	}

	// Charge les donn�es compl�mentaires
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

	function aff($key,$typeaff="html",$formname="form_data",&$render="")
	{
		$ret=parent::aff($key,$typeaff,$formname,$render);

		$sql=$this->sql;
		if ($render=="form")
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
					if ($sql->data["groupe"]!="SYS")
					{
						$ret.="<input type='checkbox' name='form_droits[".$sql->data["groupe"]."]' ".(((isset($this->groupe[$sql->data["groupe"]])) && ($this->groupe[$sql->data["groupe"]]>0)) ? "checked" : "")." value='".$sql->data["groupe"]."' /> ".$sql->data["description"]." (".$sql->data["groupe"].")<br />";
					}
				}
				if (GetDroit("SYS"))
				{
					$ret.="<input type='checkbox' name='form_droits[SYS]' ".(($this->groupe["SYS"]) ? "checked" : "")." value='SYS' /> Super Administrateur (SYS)<br />";
				}
				$ret="<span>".$ret."</span>";
			}
			else if ($key=="groupe")
			{
				$txt=strtoupper($this->data[$key]);
				$ret="";
				$sql=$this->sql;
				$query="SELECT id,groupe, description FROM ".$this->tbl."_groupe WHERE principale='oui' ORDER BY description";
				$sql->Query($query);
		
		  	  	$ret ="<select id='".$key."'  name=\"".$formname."[$key]\">";
				$ret.="<option value=\"\" ".(($txt=="") ? "selected" : "").">Aucun</option>";
				for($i=0; $i<$sql->rows; $i++)
				{ 
					$sql->GetRow($i);
					if ($sql->data["groupe"]!="SYS")
					{
						$ret.="<option value=\"".$sql->data["groupe"]."\" ".(($txt==$sql->data["groupe"]) ? "selected" : "").">".$sql->data["description"]."</option>";
					}
				}
				$ret.="</select>";
			}
		}
		else if ($render=="val")
		{
			if ($key=="fullname")
			{
				$ret=$this->fullname;
			}
		}
		else
		{
			if ($key=="droits")
			{
				$sql=$this->sql;
				$query="SELECT droits.groupe, groupe.description FROM ".$this->tbl."_droits AS droits LEFT JOIN ".$this->tbl."_groupe AS groupe ON droits.groupe=groupe.groupe WHERE uid='".$this->id."' GROUP BY droits.groupe ORDER BY description";
				$sql->Query($query);

				$ret="";
				for($i=0; $i<$sql->rows; $i++)
				{ 
					$sql->GetRow($i);
					if ($sql->data["groupe"]=="SYS")
					{
						$sql->data["description"]="Syst�me";
					}
					if (($sql->data["groupe"]!="SYS") || GetDroit("SYS"))
					{
						$ret.=(($sql->data["description"]!="") ? $sql->data["description"]." (".$sql->data["groupe"].")" : $sql->data["groupe"])."<br/>";
					}
				}
				$ret="<span>".$ret."</span>";
			}
			else if ($key=="groupe")
			{
				$txt=strtoupper($this->data[$key]);
				
				if (($txt!="ALL") && ($txt!=""))
				{
					$sql=$this->sql;
					$query="SELECT id,groupe, description FROM ".$this->tbl."_groupe WHERE groupe='".$txt."'";
					$res=$sql->QueryRow($query);

					$ret="<a href='index.php?mod=membres&rub=detail&id=".$this->id."'>".$res["description"]."</a>";
				}
				else
				{
					$ret="<a href='index.php?mod=membres&rub=detail&id=".$this->id."'>Aucun</a>";
				}
			}
			else if ($key=="nom")
			{
				$ret=strtoupper($this->data[$key]);
				if ($ret=="")
				{
					$ret="<i>NA</i>";
				}
				if ($this->actif!="oui")
				{
					$ret="<a href='index.php?mod=membres&rub=detail&id=".$this->id."'><s>".$ret."</s></a>";
				}

			}
			else if ($key=="prenom")
			{
				if ($this->actif!="oui")
				{
					$ret="<a href='index.php?mod=membres&rub=detail&id=".$this->id."'><s>".ucwords($this->data[$key])."</s></a>";
				}

			}
			else if ($key=="fullname")
			{
				if ($this->actif!="oui")
				{
					$ret="<a href='index.php?mod=membres&rub=detail&id=".$this->id."'><s>".$this->fullname."</s></a>";
				}
				else
				{
					$ret="<a href='index.php?mod=membres&rub=detail&id=".$this->id."'>".$this->fullname."</a>";
				}
			}
		}
		return $ret;
	}

	function val($key)
	{
		global $MyOpt;

		$ret=parent::val($key);

		if ($key=="fullname")
		{
			$ret=$this->fullname;
		}
		
		return $ret;
	}

	// Affiche les donn�es compl�mentaires
	function AffDonneesComp($i,$render="html")
	{
		// D�fini les droits de modification des utilisateurs
		$mycond=false;
		
		// Le user a le droit de modifier toutes ses donn�es
		if (GetMyId($this->id))
		  { $mycond=true; }

	  // Si on a le droit de modif on autorise
		if (GetDroit("ModifUserDonnees"))
		  { $mycond=true; }
		  
		// Si l'utilisateur a le droit de tout modifier alors on force
		if (GetDroit("ModifUserAll"))
		  { $mycond=true; }

	  // Si on a pas le droit on repasse en visu
		if ((!$mycond) && ($render!="val"))
		  { $render="html"; }
 	
		if ($render=="form")
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

		$tabgrp["SYS"]["bd"]=0;
		$tabgrp["SYS"]["new"]=0;
		$tabgrp["SYS"]["old"]=0;

		
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
		if ($this->data["groupe"]!="")
		{
			$tabgrp[$this->data["groupe"]]["new"]=1;
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

		// V�rifie la diff�rence
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
	{ global $lang;
		$v=stripslashes(parent::Valid($key,$v,true));

		if ($key=="initiales")
		  {
			if ($v=="")
			{ 
				$this->data["initiales"]=substr($this->data["prenom"],0,1).substr($this->data["nom"],0,1).substr($this->data["nom"],-1,1);
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
				return $this->tabLang["err_nickname"][$lang];
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
				return $this->tabLang["err_mail"][$lang];
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
		  	  	return $this->tabLang["err_name"][$lang];
			  }
			$vv=strtolower($v);
		}
		else if ($key=="groupe")
		{
			$vv=strtoupper($v);
		}
		else
		{
			$vv=$v;
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

			$query ="SELECT id FROM ".$this->tbl."_droits WHERE uid='".$this->id."' AND groupe='".$grp."'";
			$res=$sql->QueryRow($query);
	
			if ($res["id"]==0)
			{
				$query ="INSERT INTO ".$this->tbl."_droits SET groupe='".$grp."',uid='".$this->id."',uid_creat='".$gl_uid."',dte_creat='".now()."'";
				$sql->Insert($query);
			}
		}
	}

	function DelGroupe($grp) {
		$sql=$this->sql;
		$query="DELETE FROM ".$this->tbl."_droits WHERE uid='".$this->id."' AND groupe='".$grp."'";
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


function ListActiveUsers($sql,$order="",$tabtype=array(),$virtuel="non")
{
	global $MyOpt;
 
	$lstuser=array();

	if ($order=="std")
	{
		$order=(($MyOpt["globalTrie"]=="nom") ? "nom,prenom" : "prenom,nom");
	}

	$query ="SELECT id ";

	if ((is_array($tabtype)) && (count($tabtype)>0))
	{
		$type="";
		$s="";
		foreach($tabtype as $i=>$t)
		{
			$type.=$s."'".$t."'";
			$s=",";
		}
		
		$query.=", (SELECT COUNT(*) FROM ".$MyOpt["tbl"]."_droits AS droits LEFT JOIN ".$MyOpt["tbl"]."_roles AS roles ON droits.groupe=roles.groupe OR roles.groupe='ALL' WHERE roles.role IN (".$type.") AND droits.uid=usr.id) AS nb ";
	}

	$query.="FROM ".$MyOpt["tbl"]."_utilisateurs AS usr ";

	$query.="WHERE (";
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
	$query.=(($virtuel!="") ? " AND virtuel='$virtuel'" : "");
	
	if ($order!="")
	{
		$query.=" ORDER BY ".$order;
	}

	$sql->Query($query);

	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		if ( ((isset($sql->data["nb"])) && ($sql->data["nb"]>0)) || (!is_array($tabtype)) || (count($tabtype)==0))
		{
			$lstuser[$i]=$sql->data["id"];
		}
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



class groupe_core extends objet_core
{
	# Constructor
	protected $table="groupe";
	protected $mod="";
	protected $rub="";

	protected $fields=array
	(
		"groupe" => Array("type" => "varchar", "len" => "5", "index"=>1),
		"description" => Array("type" => "varchar", "len"=>200 ),
		"principale" => Array("type" => "bool", "default" => "non", "index" => "1", ),
	);

	function __construct($id=0,$sql,$grp="")
	{
		parent::__construct($id,$sql);
		if ($grp!="")
		{
			$this->loadgrp($grp);
		}
	}
	
	// Charge les donn�es utilisateurs
	function loadgrp($grp)
	{
		$sql=$this->sql;
		$query = "SELECT * FROM ".$this->tbl."_groupe WHERE groupe='".$grp."'";
		$res = $sql->QueryRow($query);
		if (!is_array($res))
		{
			return 0;
		}

		
		$this->id=$res["id"];
		
		$this->load($this->id);
	}
	
	function ListUsers()
	{
		$sql=$this->sql;
		$query="SELECT uid FROM ".$this->tbl."_droits WHERE groupe='".$this->data["groupe"]."'";
		$sql->Query($query);

		$lstuser=array();
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$lstuser[$i]=$sql->data["uid"];
		}
		return $lstuser;
		
	}
}
 
?>