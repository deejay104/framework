<?php
/*
    MnMs Framework
    Copyright (C) 2021 Matthieu Isorez

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

// Class Document

class folder_core extends objet_core {
	protected $table="doc_folder";
	protected $mod="document";
	protected $rub="index";

	protected $droit=array();

	protected $fields=array(

		"title"=>Array("type" => "varchar", "len"=>100),
		"description"=>Array("type" => "text"),
		"group_read"=>Array("type" => "varchar", "len"=>3, "default"=>"ALL"),
		"group_write"=>Array("type" => "varchar", "len"=>3),
	);

	
	protected $tabList=array(

	);


}


function ListActiveFolders($sql)
{
	global $MyOpt,$gl_uid;

	return ListeObjets($sql,"doc_folder",array("id","title","description","group_read","group_write"),array("actif"=>"oui"),array("title"));
}


class paper_core extends objet_core {
	protected $table="doc_paper";
	protected $mod="document";
	protected $rub="folder";

	protected $droit=array();

	protected $fields=array(
		"id_folder"=>Array("type" => "number", "index"=>1),
		"title"=>Array("type" => "varchar", "len"=>100),
		"refid"=>Array("type" => "numeric","default"=>0,"index"=>1),
		"version"=>Array("type" => "numeric","default"=>1),
		"controlled"=>Array("type" => "bool", "default"=>"non"),
		"nature"=>Array("type" => "enum", "default"=>"GEN"),
		"status"=>Array("type" => "enum", "default"=>"open","index"=>1),
		"mandatory"=>Array("type" => "bool", "default"=>"non"),
		"history"=>Array("type" => "text"),
		"dte_start"=>Array("type" => "date"),
		"dte_end"=>Array("type" => "date"),
		"dte_review"=>Array("type" => "date"),
		"uid_checked"=>Array("type" => "numeric"),
		"dte_checked"=>Array("type" => "datetime"),
		"uid_approved"=>Array("type" => "numeric"),
		"dte_approved"=>Array("type" => "datetime"),
	);

	
	protected $tabList=array(
		"status"=>array(
			"fr"=>array('open'=>'Normal','draft'=>'Brouillon','submitted'=>'Soumis','approved'=>'Approuvé','cancel'=>'Annulé'),
			"en"=>array('open'=>'Open','draft'=>'Draft','submitted'=>'Submitted','approved'=>'Approuved','cancel'=>'Canceled'),
			"ca"=>array('open'=>'Normal','draft'=>'Brouillon','submitted'=>'Soumis','approved'=>'Approuvé','cancel'=>'Annulé'),
		),
		"nature"=>array(
			"fr"=>array('GEN'=>'Autre','SOP'=>'Procédure','ANX'=>'Annexe','FOR'=>'Formulaire'),
			"en"=>array('GEN'=>'Generic','SOP'=>'Procedure','ANX'=>'Annex','FOR'=>'Form'),
			"ca"=>array('GEN'=>'Autre','SOP'=>'Procédure','ANX'=>'Annexe','FOR'=>'Formulaire'),
		),
	);

	function ArchiveOlder()
	{
		$sql=$this->sql;

		if (($this->val("dte_start")=="0000-00-00") || (strtotime($this->val("dte_start"))-time()<0))
		{
			$this->valid("dte_start",now()); 
		}
		if (($this->val("dte_review")=="0000-00-00") || (strtotime($this->val("dte_review"))-time()<0))
		{
			$this->valid("dte_review",CalcDate($this->val("dte_start"), 365)); 
		}
		$this->Save();

		$dte=$this->val("dte_start");
		$query="UPDATE ".PREFIX_TBL."_doc_paper SET dte_end='".$this->val("dte_start")."' WHERE refid='".$this->val("refid")."' AND refid>0";
		$sql->Update($query);
	}
	function getNextID()
	{
		$sql=$this->sql;
		$query="SELECT MAX(refid) AS nb FROM ".PREFIX_TBL."_doc_paper ";
		$res=$sql->QueryRow($query);
		return $res["nb"]+1;
	}
	function ref()
	{
		return $this->val("nature")."-".CompleteTxt($this->val("refid"),3,"0");
	}
	function docs()
	{
		return ListDocsPaper($this->sql,$this->id);
	}

	function nbRead()
	{
		$sql=$this->sql;
		$query="SELECT COUNT(*) AS nb FROM ".PREFIX_TBL."_doc_read WHERE id_paper=".$this->id;
		$res=$sql->QueryRow($query);
		return $res["nb"];
	}
	function nbTotalRead($grp)
	{
		$sql=$this->sql;
		$query="SELECT COUNT(*) AS nb FROM ".PREFIX_TBL."_droits WHERE groupe=".$grp;
		$res=$sql->QueryRow($query);
		

		return $res["nb"];		
	}

	function history()
	{
		$sql=$this->sql;
/*
		array('version' => '4', 'dte_effective' => '2026-09-01', 'author' => '…', 'checked_by' => '…', 'approved_by' => 'Matthieu ISOREZ', 'changes' => 'Mise à jour de la liste des instructeurs'),
		array('dte_review' => '2026-03-12', 'par' => 'Paul MARTIN'),
*/
		$res=array();
		$query ="SELECT p.version,p.dte_start,p.history,u.prenom,u.nom,a.prenom AS prenom_app,a.nom AS nom_app FROM ".PREFIX_TBL."_doc_paper AS p ";
		$query.="LEFT JOIN ".PREFIX_TBL."_utilisateurs AS u ON p.uid_creat=u.id ";
		$query.="LEFT JOIN ".PREFIX_TBL."_utilisateurs AS a ON p.uid_approved=a.id ";
		$query.="WHERE p.refid=".$this->data["refid"]." AND p.refid>0 ORDER BY version DESC";

		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);	
			$res[]=array(
				"version"=>$sql->data["version"],
				"dte_effective"=>$sql->data["dte_start"],
				"author"=>ucwords($sql->data["prenom"])." ".strtoupper($sql->data["nom"]),
				"approved_by"=>ucwords($sql->data["prenom_app"])." ".strtoupper($sql->data["nom_app"]),
				"changes"=>$sql->data["history"],
			);
		}
		return $res;
	}


	function getReads()
	{
		global $gl_uid;

		$sql=$this->sql;

		$folder=new folder_core($this->data["id_folder"],$sql);
		$res=array();

		$query ="SELECT u.id,u.prenom,u.nom,doc.dte_read, (SELECT COUNT(*) FROM ".PREFIX_TBL."_doc_lus AS l LEFT JOIN ".PREFIX_TBL."_doc_docs AS d ON l.id_doc=d.id_doc WHERE d.id_paper=".$this->id." AND l.uid_creat=u.id) AS nb ";
	
		if ($folder->val("group_read")=="ALL")
		{
			$query.="FROM ".PREFIX_TBL."_utilisateurs AS u ";
		}
		else
		{
			$query.="FROM ".PREFIX_TBL."_droits AS grp ";
			$query.="LEFT JOIN ".PREFIX_TBL."_utilisateurs AS u ON grp.uid=u.id ";
		}

		$query.="LEFT JOIN ".PREFIX_TBL."_doc_read AS doc ON doc.uid=u.id AND doc.id_paper=".$this->id." ";
		$query.="WHERE u.actif='oui' ";

		if ($folder->val("group_read")=="ALL")
		{
			$query.="AND u.virtuel='non' ";
		}
		else
		{
			$query.="AND grp.groupe='".$folder->val("group_read")."' ";
		}
		if (!GetDroit($folder->val("group_write")))
		{
			$query.="AND u.id=".$gl_uid." ";
		}
		$query.="GROUP BY u.id ORDER BY dte_read ASC,u.prenom,u.nom";	


		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);	
			$res[]=array(
				"user"=>ucwords($sql->data["prenom"])." ".strtoupper($sql->data["nom"]),
				"dte_read"=>($sql->data["dte_read"]!='') ? sql2date($sql->data["dte_read"],"jour") : "-",
				"start"=>($sql->data["nb"]>0) ? true : false,
			);	
		}
		return $res;
	}
}

function ListActivePapers($id,$sql,$sort="dte_creat")
{
	global $MyOpt,$gl_uid;
	$crit=array("actif"=>"oui");
	if ($id>0)
	{
		$crit["id_folder"]=$id;
	}
	
	return ListeObjets($sql,"doc_paper",array("id","id_folder","title","nature","refid","version","mandatory","controlled","dte_start","dte_end","dte_review","status","uid_creat","dte_creat","uid_checked","dte_checked","uid_approved","dte_approved"),$crit,array($sort));
}



function MandatoryPaper($uid)
{
	global $sql,$MyOpt;

	$query ="SELECT paper.id AS id_paper, paper.id_folder, folder.group_read, doc.id AS id_doc ";
	$query.="FROM ".PREFIX_TBL."_doc_paper AS paper ";
	$query.="LEFT JOIN ".PREFIX_TBL."_doc_folder AS folder ON folder.id=paper.id_folder ";
	$query.="LEFT JOIN ".PREFIX_TBL."_document AS doc ON paper.id=doc.uid AND doc.type='paper' AND doc.actif='oui' ";
	$query.="WHERE paper.actif='oui' AND paper.status='approved' AND (paper.dte_end>'".now()."' OR paper.dte_end='0000-00-00') AND paper.dte_start<'".now()."' AND paper.mandatory='oui'";

	$sql->Query($query);

	$lstdoc=array();
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		if (GetDroit($sql->data["group_read"]))
		{
			$lstdoc[$sql->data["id_paper"]]["perm"]=$sql->data["group_read"];
			$lstdoc[$sql->data["id_paper"]]["folder"]=$sql->data["id_folder"];
			$lstdoc[$sql->data["id_paper"]]["paper"]=$sql->data["id_paper"];
			$lstdoc[$sql->data["id_paper"]]["read"]=0;			
		}
	}

	// Reprendre la requete précédente en tenant compte des documents lus après dte_maj
	$query ="SELECT id_paper FROM ".PREFIX_TBL."_doc_read AS lus WHERE uid='".$uid."'";

	$sql->Query($query);

	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		if (isset($lstdoc[$sql->data["id_paper"]]))
		{
			$lstdoc[$sql->data["id_paper"]]["read"]=1;		
		}
	}

	return $lstdoc;
}

class paper_docs_core extends objet_core {
	protected $table="doc_docs";
	protected $mod="document";
	protected $rub="folder";

	protected $droit=array();

	protected $fields=array(
		"id_paper"=>Array("type" => "number", "index"=>1),
		"id_doc"=>Array("type" => "number", "index"=>1),
		"name"=>Array("type" => "varchar", "len"=>100),
	);
	
	protected $tabList=array(

	);

	function read($uid)
	{	global $MyOpt;

		$sql=$this->sql;	
		$query="SELECT id FROM ".PREFIX_TBL."_doc_lus WHERE actif='oui' AND uid='".$uid."' AND id_doc='".$this->data["id_doc"]."'";
		$res=$sql->QueryRow($query);
	
		if ((isset($res["id"])) && ($res["id"]>0))
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	function getAttributes()
	{
		return getAttributes($this->data["name"]);
	}

}


function ListDocsPaper($sql,$id)
{
	global $MyOpt, $gl_uid, $myuser;

	$query ="SELECT doc.id,doc.id_doc,d.droit ";
	$query.="FROM ".PREFIX_TBL."_doc_docs AS doc ";
	$query.="LEFT JOIN ".PREFIX_TBL."_document AS d ON doc.id_doc=d.id ";
	$query.="WHERE doc.actif='oui' ".(($id>0) ? "AND doc.id_paper='".$id."'" : "" )." AND d.type='paper' ORDER BY doc.name";

	$sql->Query($query);
	$lstdoc=array();
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		if ( 
			($gl_uid==$sql->data["id"]) 
			|| (($sql->data["droit"]!="") && (isset($myuser->groupe[$sql->data["droit"]])) && ($myuser->groupe[$sql->data["droit"]]))
			|| ($sql->data["droit"]=="ALL") 
			|| (GetDroit("VisuDocument"))
			|| (GetDroit("VisuUserDocument"))
		)
		{
			$lstdoc[$i]=$sql->data["id"];
		}
	}

	return $lstdoc;
}

class paper_read_core extends objet_core {
	protected $table="doc_read";
	protected $mod="document";
	protected $rub="folder";

	protected $droit=array();

	protected $fields=array(
		"id_paper"=>Array("type" => "number", "index"=>1),
		"uid"=>Array("type" => "number", "index"=>1),
		"dte_read"=>Array("type" => "datetime"),
	);
	
	protected $tabList=array(

	);
}


class comment_core extends objet_core {
	protected $table="doc_comment";
	protected $mod="document";
	protected $rub="folder";

	protected $droit=array();

	protected $fields=array(
		"id_paper"=>Array("type" => "number", "index"=>1),
		"description"=>Array("type" => "text"),
	);


	protected $tabList=array(

	);
}

function ListActiveComments($id,$sql)
{
	global $MyOpt,$gl_uid;

	return ListeObjets($sql,"doc_comment",array("id","id_paper","description","uid_creat","dte_creat"),array("actif"=>"oui","id_paper"=>$id),array("dte_creat"));
}



?>