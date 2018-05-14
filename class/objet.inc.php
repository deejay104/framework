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
class objet_core
{
	# Constructor	
	function __construct($id=0,$sql)
	{
		global $MyOpt;
		global $gl_uid;

		$this->tbl=$MyOpt["tbl"];

		$this->sql=$sql;
		// $this->table="";

		$this->uid_creat=$gl_uid;
		$this->dte_creat=date("Y-m-d H:i:s");
		$this->uid_maj=$gl_uid;
		$this->dte_maj=date("Y-m-d H:i:s");

		$this->data["dte_maj"]=$this->dte_maj;

		$this->type["dte_creat"]="datetime";
		$this->type["dte_maj"]="datetime";
		
		if ($id>0)
		{
			$this->load($id);
		}

	}

	# Load object informations
	function load($id)
	{
		$sql=$this->sql;
		$this->id=$id;
		$query = "SELECT * FROM ".$this->tbl."_".$this->table." WHERE id='$id'";
		$res = $sql->QueryRow($query);
		if (!is_array($res))
		{
			return "";
		}

		$this->uid_creat=$res["uid_creat"];
		$this->dte_creat=$res["dte_creat"];
		$this->uid_maj=$res["uid_maj"];
		$this->dte_maj=$res["dte_maj"];
		
		// Charge les variables

		foreach($this->data as $k=>$v)
		{
			$this->data[$k]=$res[$k];
		}

	}
	
	# Show object informations
	function aff($key,$typeaff="html",$formname="form_data")
	{
		global $MyOpt;
		$txt=$this->data[$key];

		if (is_numeric($key))
		  { $ret="******"; }
		else if ($key=="uid_maj")
		  { $ret="******"; }
		else if ($this->type[$key]=="ucword")
		{
			$ret=ucwords($txt);
		}
		else if ($this->type[$key]=="uppercase")
		{
			$ret=strtoupper($txt);
		}
		else if ($this->type[$key]=="lowercase")
		{
			$ret=strtolower($txt);
		}
		else if ($this->type[$key]=="mail")
		{
			$ret=strtolower($txt);
			$type="email";
		}
		else if ($this->type[$key]=="duration")
		{
			$ret=AffTemps($txt,"no");
		}
		else if ($this->type[$key]=="number")
		{
			if (!is_numeric($txt))
			{
				$ret=0;
			}
			else
			{
				$ret=$txt;
			}
			$type="number";
		}

		else
		  { $ret=$txt; }


		// Si on a le droit de modif on autorise
		$mycond=true;
		if (($this->droit[$key]!="") && (!GetDroit($this->droit[$key])))
		  { $mycond=false; }

		// Si l'utilisateur a le droit de tout modifier alors on force
		if (GetDroit("SYS"))
		  { $mycond=true; }

		// Champs en lecture seule
		if (($key=="id") || ($key=="uid_maj") || ($key=="dte_maj"))
		  { $mycond=false; }
	  
		// Si on a pas le droit on repasse en visu
		if (!$mycond)
		{
			$typeaff="html";
		}
 	
		if ($typeaff=="form")
		{
			if ($this->type[$key]=="text")
		  	{
				$ret="<textarea id='".$key."'  name=\"".$formname."[$key]\" rows=5>".$ret."</textarea>";
			}
			else if ($this->type[$key]=="bool")
		  	{
				$ret ="<input id='".$key."' type='radio' name=\"".$formname."[$key]\" value='oui' ".(($txt=="oui") ? "checked='checked'" : "")."> Oui";
				$ret.="<input id='".$key."' type='radio' name=\"".$formname."[$key]\" value='non' ".(($txt=="non") ? "checked='checked'" : "")."> Non";
			}
			else if (($this->type[$key]=="enum") && (is_array($this->tabList[$key])))
			{
		  	  	$ret ="<select id='".$key."'  name=\"".$formname."[$key]\">";
				foreach($this->tabList[$key] as $k=>$v)
				{
					$ret.="<option value=\"".$k."\" ".(($txt==$k) ? "selected" : "").">".$this->tabList[$key][$k]."</option>";
				}
		  	  	$ret.="</select>";

			}
			else
			{
				$type=(isset($this->type[$key])) ? $this->type[$key] : "";
				$ret="<INPUT id='".$key."'  name=\"".$formname."[$key]\" value=\"".$ret."\" ".(($type!="") ? "type=\"".$type."\"" : "").">";
			}
		}
		else
		{
			$link=true;
			if ($this->type[$key]=="text")
			{
				$ret=nl2br(htmlentities($ret,ENT_HTML5,"ISO-8859-1"));
				$link=false;
			}
			else if ($this->type[$key]=="date")
			{
				$ret=sql2date($ret);
			}
			else if ($this->type[$key]=="datetime")
			{
				$ret=sql2date($ret);
			}
			else if ($this->type[$key]=="mail")
			{
				$ret="<A href=\"mailto:".strtolower($ret)."\">".strtolower($ret)."</A>";
			}
			else if (($this->type[$key]=="enum") && (is_array($this->tabList[$key])))
			{
				$ret=$this->tabList[$key][$ret];
			}

			// A voir si on met tous les champs en clicable ou pas
			if (($this->mod!="") && ($this->rub!="") && ($link))
			{
				$ret="<A href=\"index.php?mod=".$this->mod."&rub=".$this->rub."&id=".$this->id."\">".$ret."</A>";
			}

		}
	
		return $ret;
	}

	function val($key)
	{
		global $MyOpt;
		$ret=strtolower($this->data[$key]);

		if ($this->type[$key]=="text")
		{
			$ret=nl2br(htmlentities($ret,ENT_HTML5,"ISO-8859-1"));
		}
		return $ret;
	}
	
	function Create()
	{
		global $gl_uid;
		$sql=$this->sql;

		$this->id=$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,array("uid_creat"=>$gl_uid, "dte_creat"=>now(),"uid_maj"=>$gl_uid, "dte_maj"=>now()));		
		
		return $this->uid;
	}
	
	function Valid($key,$v,$ret=false){
		$vv="**none**";

		if ($this->type[$key]=="duration")
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
		{
			$this->data[$key]=$vv;
			return "";
		}
		else if ($ret==true)
		{
			return addslashes($vv);
		}
	}

	function Save()
	{
		global $gl_uid;
		$sql=$this->sql;

		if ($this->id==0)
		{
			$this->Create();
		}
		
		$td=array();
		foreach($this->data as $k=>$v)
		{ 
			$vv=$this->Valid($k,$v,true);
			$td[$k]=$vv;
		}
		$td["uid_maj"]=$gl_uid;
		$td["dte_maj"]=now();

		$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,$td);
	}

	function Delete()
	{
		global $gl_uid;
		$sql=$this->sql;
		$this->actif="non";

		$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,array("actif"=>'non', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
	}

	function Render($form,$typeaff)
	{
		global $tmpl_x;

		$tmpl_x->assign($form."_id",$this->id);
		if (isset($this->usr_maj))
		{
			$tmpl_x->assign($form."_usr_maj",$this->usr_maj->aff("fullname"));
		}
		foreach($this->data as $k=>$v)
		{
			$tmpl_x->assign($form."_".$k,$this->aff($k,$typeaff));
		}
	}
	
} # End of class

function ListeObjets($sql,$table,$champs=array(),$crit=array())
{
	global $MyOpt;
	
	$s="";
	if (count($champs)>0)
	{
		$s=",";
	}

	$w="";
	if ((is_array($crit)) && (count($crit)>0))
	{
		$w="WHERE 1=1";
		foreach ($crit as $c=>$v)
		{
			$w.=" AND ".$c."='".$v."'";
		}
	}
		
	$q="SELECT id".$s.implode(",",$champs)." FROM ".$MyOpt["tbl"]."_".$table." ".$w;
	$sql->Query($q);

	$lst=array();
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$lst[$sql->data["id"]]=$sql->data;
	}
	return $lst;
}

?>