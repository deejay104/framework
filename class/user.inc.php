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
class user_core{
	# Constructor
	function __construct($uid=0,$sql,$me=false,$setdata=true){
		global $MyOpt;
		global $gl_uid;

		$this->tbl=$MyOpt["tbl"];

		$this->uid=$uid;
		$this->sql=$sql;
		$this->me=$me;
		$this->prenom="";
		$this->nom="";
		$this->actif="oui";
		$this->virtuel="non";
		$this->mail="";
		$this->uidmaj=0;
		$this->dtemaj=date("Y-m-d");

		$this->data["nom"]="";
		$this->data["prenom"]="";
		$this->data["fullname"]="";
		$this->data["initiales"]="";
		$this->data["password"]="";
		$this->data["mail"]="";
		$this->data["actif"]="oui";
		$this->data["virtuel"]="non";
		$this->data["dte_login"]="0000-00-00 00:00:00";
		$this->data["notification"]="oui";
		$this->data["aff_msg"]="0";

		$this->data["uid_maj"]="0";
		$this->data["dte_maj"]=date("Y-m-d H:i:s");

		// Données utilisateurs
		$this->donnees=array();

		// Droits utilisateurs
		$this->groupe=array();

		if ($uid>0)
		{
			$this->load($uid,$setdata,$me);
		}
	}

	# Load user informations
	function load($uid,$setdata=true,$me)
	{ global $Droits;

		$this->uid=$uid;
		$sql=$this->sql;
		if ($setdata)
		  { $query = "SELECT * FROM ".$this->tbl."_utilisateurs WHERE id='$uid'"; }
		else
		  { $query = "SELECT id,prenom,nom,actif,virtuel,mail,uid_maj,dte_maj,droits FROM ".$this->tbl."_utilisateurs WHERE id='$uid'"; }
		$res = $sql->QueryRow($query);
		if (!is_array($res))
		{
			return "";
		}

		// Charge les variables
		$this->prenom=($res["prenom"]!="")?ucwords($res["prenom"]):"";
		$this->nom=($res["nom"]!="")?strtoupper($res["nom"]):"";
		$this->actif=$res["actif"];
		$this->virtuel=$res["virtuel"];
		$this->mail=$res["mail"];
		$this->uidmaj=$res["uid_maj"];
		$this->dtemaj=$res["dte_maj"];

		if ($setdata)
		{ 
			foreach($res as $k=>$v)
			{
				if (!is_numeric($k))
				{
					$this->data[$k]=$v;
				}
			}

			// Charge les droits
			$query = "SELECT groupe FROM ".$this->tbl."_droits WHERE uid='$uid' ORDER BY groupe";
			$sql->Query($query);
			$this->data["droits"]="";
			$s="";
			$this->groupe=array();
			for($i=0; $i<$sql->rows; $i++)
			{ 
				$sql->GetRow($i);
				$this->groupe[$sql->data["groupe"]]=true;
				$this->data["droits"].=$s.$sql->data["groupe"];
				$s=",";
			}
		}

		$this->data["fullname"]=AffFullName($this->prenom,$this->nom);
		$this->fullname=$this->data["fullname"];

		if ($me)
		{
			$this->loadRoles();
		}
	}

	function loadRoles()
	{
		// Charge les roles
		$this->role=array();
		$this->role[""]=true;
		$sql=$this->sql;
		$query = "SELECT roles.role, roles.autorise FROM ".$this->tbl."_roles AS roles LEFT JOIN ".$this->tbl."_droits AS droits ON droits.groupe=roles.groupe  WHERE (uid='".$this->uid."' OR roles.groupe='ALL') AND roles.role IS NOT NULL ORDER BY roles.autorise";
		$sql->Query($query);

		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$this->role[$sql->data["role"]]=($sql->data["autorise"]=="oui") ? true : false;
		}
	}

	function LoadDonneesComp()
	{
		$sql=$this->sql;
		$query = "SELECT donnees.id,def.id AS did,def.nom,donnees.valeur FROM ".$this->tbl."_utildonneesdef AS def LEFT JOIN ".$this->tbl."_utildonnees AS donnees ON donnees.did=def.id AND (donnees.uid='$this->uid' OR donnees.uid IS NULL) WHERE def.actif='oui' ORDER BY ordre, nom";

		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$this->donnees[$sql->data["did"]]["id"]=$sql->data["id"];
			$this->donnees[$sql->data["did"]]["nom"]=$sql->data["nom"];
			$this->donnees[$sql->data["did"]]["valeur"]=$sql->data["valeur"];
		}
	}
	
	# Show user informations
	function aff($key,$typeaff="html",$formname="form_info")
	{ global $MyOpt,$tabTypeNom;
		$txt=$this->data[$key];

		if (is_numeric($key))
		  { $ret="******"; }
		else if ($key=="prenom")
		  {
			$ret=preg_replace("/-/"," ",$txt);
			$ret=ucwords($ret);
			$ret=preg_replace("/ /","-",$ret);
		  }
		else if ($key=="nom")
		  { $ret=strtoupper($txt); }
		else if ($key=="fullname")
		  { $ret=$txt; }
		else if ($key=="mail")
		  { $ret=strtolower($txt); $type="email"; }
		else if ($key=="initiales")
		  { $ret=strtoupper($txt); }
		else if ($key=="aff_rapide")
		  { $ret=($txt=="n") ? "Normal" : "Rapide"; }
		else if ($key=="password")
		  { $ret="******"; }
		else if ($key=="uid_maj")
		  { $ret="******"; }
		else
		  { $ret=$txt; }

		// Défini les droits de modification des utilisateurs
		$mycond=$this->me;	// Le user a le droit de modifier toutes ses données

		// Si on a le droit de modif on autorise
		if (GetDroit("ModifUser"))
		  { $mycond=true; }

		// Test les exceptions
		if ($key=="prenom")
		{
			if (!GetDroit("ModifUser"))
			  { $mycond=false; }
		}
		else if ($key=="nom")
		{
			if (!GetDroit("ModifUser"))
			  { $mycond=false; }
		}
		else if ($key=="droits")
		{
			if (!GetDroit("ModifUserDroits"))
			  { $mycond=false; }
		}

		// Si l'utilisateur a le droit de tout modifier alors on force
		if (GetDroit("ModifUserAll"))
		  { $mycond=true; }

		// Si on a pas le droit on repasse en visu
		if ((!$mycond) && ($typeaff!="val"))
		  { $typeaff="html"; }
 	
		if ($typeaff=="form")
		{
			if ($key=="commentaire")
		  	{
				$ret="<TEXTAREA id='".$key."'  name=\"".$formname."[$key]\" rows=5>$ret</TEXTAREA>";
			}
			else if ($key=="notification")
		  	{
		  	  	$ret ="<SELECT id='".$key."'  name=\"".$formname."[$key]\">";
		  	  	$ret.="<OPTION value=\"oui\" ".(($txt=="oui")?"selected":"").">Oui</OPTION>";
		  	  	$ret.="<OPTION value=\"non\" ".(($txt=="non")?"selected":"").">Non</OPTION>";
		  	  	$ret.="</SELECT>";
		  	}
			else if ($key=="virtuel")
		  	{
		  	  	$ret ="<SELECT id='".$key."'  name=\"".$formname."[$key]\">";
		  	  	$ret.="<OPTION value=\"oui\" ".(($txt=="oui")?"selected":"").">Oui</OPTION>";
		  	  	$ret.="<OPTION value=\"non\" ".(($txt=="non")?"selected":"").">Non</OPTION>";
		  	  	$ret.="</SELECT>";
		  	}
 			else if ($key=="droits")
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
			}
			else
			{
				$ret="<INPUT id='".$key."'  name=\"".$formname."[$key]\" id=\"$key\" value=\"$ret\" ".(($type!="") ? "type=\"".$type."\"" : "").">";
			}
		}
		else if ($typeaff=="val")
		{
			if ($key=="commentaire")
			  { $ret=nl2br(htmlentities($ret,ENT_HTML5,"ISO-8859-1")); }
			else if ($key=="mail")
			  { $ret=strtolower($ret); }
		}
		else
		{
			if ($key=="commentaire")
			{
				$ret=nl2br(htmlentities($ret,ENT_HTML5,"ISO-8859-1"));
			}
			else if ($key=="mail")
			{
				$ret="<A href=\"mailto:".strtolower($ret)."\">".strtolower($ret)."</A>";
			}
			else if ( ($key=="fullname") && ($this->actif=="off"))
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\"><s>".$ret."</s></a>";
			}
			else if ( ($key=="fullname") && ($this->actif=="non"))
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\"><s style='color:#ff0000;'>".$ret."</s></a>";
			}
			else if ($key=="fullname")
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\">".$ret."</a>";
			}
			else if ( ($key=="nom") && ($this->actif=="off"))
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\"><s>".$ret."</s></a>";
			}
			else if ( ($key=="nom") && ($this->data["password"]=="") && (GetDroit("ModifUserPassword")))
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\"><i>".$ret." (*)</i></a>";
			}
			else if ( ($key=="nom") && ($this->actif=="non"))
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\"><s style='color:#ff0000;'>".$ret."</s></a>";
			}
			else if ($key=="nom")
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\">".$ret."</a>";
			}
			else if ( ($key=="prenom") && ($this->actif=="off"))
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\"><s>".$ret."</s></a>";
			}
			else if ( ($key=="prenom") && ($this->actif=="non"))
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\"><s style='color:#ff0000;'>".$ret."</s></a>";
			}
			else if ($key=="prenom")
			{
				$ret="<a href=\"index.php?mod=membres&rub=detail&id=".$this->uid."\">".$ret."</a>";
			}
			else if ($key=="droits")
			{
				$sql=$this->sql;
				$query="SELECT droits.groupe, groupe.description FROM ".$this->tbl."_droits AS droits LEFT JOIN ".$this->tbl."_groupe AS groupe ON droits.groupe=groupe.groupe WHERE uid='".$this->uid."' ORDER BY description";
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
			}
		}
	
		return $ret;
	}

	function AffDonnees($i,$typeaff="html")
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

	# Save Password
	function SaveMdp($mdp){
		$sql=$this->sql;
		$this->data["password"]=$mdp;

		$sql->Edit("user",$this->tbl."_utilisateurs",$this->uid,array("password"=>$mdp));		

		return "";
	}

	function Create(){
		global $uid;
		$sql=$this->sql;

		$this->uid=$sql->Edit("user",$this->tbl."_utilisateurs",$this->uid,array("uid_maj"=>$uid, "dte_maj"=>now()));		
		
		return $this->uid;
	}
	
	function Valid($k,$v,$ret=false){
		$vv="**none**";
		if ($k=="initiales")
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
			$query = "SELECT COUNT(*) AS nb FROM ".$this->tbl."_utilisateurs WHERE initiales='".$vv."' AND id<>'".$this->uid."' AND actif='oui'";
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
		else if ($k=="mail")
		{
		  	$vv=$v;

			$sql=$this->sql;
			$query = "SELECT COUNT(*) AS nb FROM ".$this->tbl."_utilisateurs WHERE mail='".$vv."' AND id<>'".$this->uid."' AND actif='oui'";
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
		else if ($k=="prenom")
		  {	
			$vv=preg_replace("/ /","-",$v);
			$vv=strtolower($vv);
		  }
		else if ($k=="nom")
		  {
		  	if ($v=="")
		  	  {
		  	  	return "Le nom est vide.<br />";
			  }
			$vv=strtolower($v);
		  }
	  	else if ($k=="commentaire")
	  	  { $vv=$v; }
	  	else
	  	  { $vv=strtolower($v); }

		if ( (!is_numeric($k)) && ("($vv)"!="(**none**)") && ($ret==false))
		  { $this->data[$k]=$vv; }
		else if ($ret==true)
		  { return addslashes($vv); }
	}

	function Save()
	{
		global $uid;
		$sql=$this->sql;

		$td=array();
		foreach($this->data as $k=>$v)
		{ 
			if ((!is_numeric($k)) && ($k!="fullname") && ($k!="password"))
			{
				$vv=$this->Valid($k,$v,true);
			  	$td[$k]=$vv;
			}
		}
		$td["uid_maj"]=$uid;
		$td["dte_maj"]=now();
		$sql->Edit("user",$this->tbl."_utilisateurs",$this->uid,$td);

	}

	function SaveDroits($tabDroits)
	{
		global $uid;

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
	
	function AddGroupe($grp) {
		global $uid;
		$sql=$this->sql;
		$grp=trim($grp);
		
		if (($grp!="") && (($grp!="SYS") || (($grp=="SYS") && (GetDroit("SYS")))))
		{	
			$query ="INSERT INTO ".$this->tbl."_droits (`groupe` ,`uid` ,`uid_creat` ,`dte_creat`) ";
			$query.="VALUES ('".trim($grp)."' , '".$this->uid."', '$uid', '".now()."')";
			$sql->Insert($query);
		}
	}

	function DelGroupe($grp) {
		$sql=$this->sql;
		$query="DELETE FROM ".$this->tbl."_droits WHERE uid='$this->uid' AND groupe='$grp'";
		$sql->Delete($query);
	}

	function RazGroupe() {
		$sql=$this->sql;
		$query="DELETE FROM ".$this->tbl."_droits WHERE uid='$this->uid'";
		$sql->Delete($query);
	}

	function SaveDonnees()
	{ 
		global $uid;
		$sql=$this->sql;
		
		foreach($this->donnees as $did=>$d)
		{
			$td=array("valeur"=>$d["valeur"], "uid"=>$this->uid, "did"=>$did);
			$sql->Edit("user",$this->tbl."_utildonnees",$d["id"],$td);
		}
	}

	function Desactive(){
		global $gl_uid;
		$sql=$this->sql;
		$this->actif="off";

		$sql->Edit("user",$this->tbl."_utilisateurs",$this->uid,array("actif"=>'off', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
	}

	function Active(){
		global $gl_uid;
		$sql=$this->sql;
		$this->actif="oui";

		$sql->Edit("user",$this->tbl."_utilisateurs",$this->uid,array("actif"=>'oui', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
	}
	
	function Delete(){
		global $uid;
		$sql=$this->sql;
		$this->actif="non";

		$sql->Edit("user",$this->tbl."_utilisateurs",$this->uid,array("actif"=>'non', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
	}	


} # End of class




function ListActiveUsers($sql,$order="",$tabtype="",$virtuel="non")
 { global $MyOpt;
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
 { global $MyOpt;
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
 { global $MyOpt;
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
  { global $MyOpt;
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
  { global $myuser;
  	if ($id==$myuser->uid)
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