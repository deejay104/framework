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

// Class Document

class document_core{
	protected $table="document";
	
 	# Constructor
	function __construct($id="",$sql,$type="document"){
		global $MyOpt;
		global $gl_uid;

		$this->sql=$sql;
		$this->tbl=$MyOpt["tbl"];
		$this->myuid=$gl_uid;
		$this->expire=$MyOpt["expireCache"];

		$this->id=0;
		$this->name="";
		$this->filename="";
		$this->uid="";
		$this->type=$type;
		$this->dossier="";
		$this->droit="";
		$this->actif="";
		$this->uid_creat=0;
		$this->dte_creat="";
		$this->editmode="std";
		$this->filepath="../documents";

		if ($id>0)
		{
			$this->load($id);
		}
		// else if ($id==-1)
		// {
			// $this->id=0;
			// $this->filepath="static/images";
			// $this->filename="icn64_membre.png";
			// $this->droit="ALL";
		// }
		else
		{
			$this->id=0;
			$this->filename="";
			$this->droit="";
		}
	}

	# Load document
	function load($id){
		$this->id=$id;
		$sql=$this->sql;
		$query = "SELECT * FROM ".$this->tbl."_document WHERE id='$id'";
		$res = $sql->QueryRow($query);

		if ((isset($res["id"])) && ($res["id"]>0))
		{
			// Charge les variables
			$this->name=$res["name"];
			$this->filename=$res["filename"];
			$this->uid=$res["uid"];
			$this->type=$res["type"];
			$this->dossier=$res["dossier"];
			$this->droit=$res["droit"];
			$this->actif=$res["actif"];
			$this->uid_creat=$res["uid_creat"];
			$this->dte_creat=$res["dte_creat"];
		}
	}

	function Valid($k,$v) 
	{
	}


	function Save($id,$name,$tmpname)
	{ global $gl_uid;
		$sql=$this->sql;

		$ret="";
		
		$myext=GetExtension($name);
		if (strlen($name)>100)
		{
			$filename=substr(GetFilename($name),0,96).".".$myext;
		}

	  	$query="INSERT INTO ".$this->tbl."_document SET name='$name', uid='$id', droit='$this->droit', type='$this->type', actif='oui', uid_creat='$gl_uid',dte_creat='".now()."', uid_maj='$gl_uid',dte_maj='".now()."'";
		$this->id=$sql->Insert($query);

		$myname=CompleteTxt($this->id,6,"0");
		$mypath=substr($myname,0,3);

		if (!is_dir($this->filepath."/".$mypath))
		{
		  	mkdir($this->filepath."/".$mypath);
		}

		$this->uid=$id;
		$this->filename=$mypath."/".$myname.".".$myext;
		$this->name=$name;			

		if (!move_uploaded_file($tmpname,$this->filepath."/".$this->filename))
		{
		  	$ret.="Erreur de chargement du fichier<br/>";
		}
		else
		{
		  	$query="UPDATE ".$this->tbl."_document SET filename='".$this->filename."' WHERE id='".$this->id."'";
			$sql->Update($query);			
		}

		$this->orientation();

		return $ret;
	}

	function Update()
	{ global $gl_uid;
		$sql=$this->sql;

		$td["uid"]=$this->uid;
		$td["type"]=$this->type;
		$td["droit"]=$this->droit;
		$td["uid_maj"]=$gl_uid;
		$td["dte_maj"]=now();
		$this->uid_maj=$gl_uid;
		$this->dte_maj=now();

		$this->id=$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,$td);

		return $ret;
	}

	function Import($id,$name,$filename="",$droit="")
	{ global $gl_uid;
		$sql=$this->sql;

		$ret="";
		
		$myext=GetExtension($name);
		if (strlen($name)>100)
		{
			$filename=substr(GetFilename($name),0,96).".".$myext;
		}

	  	$query="INSERT INTO ".$this->tbl."_document SET name='".(($filename!="") ? $filename : $name)."', uid='$id', type='$this->type', droit='$droit',actif='oui', uid_creat='$gl_uid',dte_creat='".now()."'";
		$this->id=$sql->Insert($query);

		$myname=CompleteTxt($this->id,6,"0");
		$mypath=substr($myname,0,3);

		if (!is_dir($this->filepath."/".$mypath))
		  {
		  	mkdir($this->filepath."/".$mypath);
		  }
		$this->uid=$id;
		$this->filename=$mypath."/".$myname.".".$myext;

		rename($name,$this->filepath."/".$this->filename);
	  	$query="UPDATE ".$this->tbl."_document SET filename='".$this->filename."' WHERE id='".$this->id."'";
		$sql->Update($query);

		return $ret;		
	}

	function Delete()
	{ global $mysql,$gl_uid,$myuser;
		$sql=$this->sql;
		$ret="";

		if ( ($this->uid_creat==$gl_uid) || (GetDroit("SupprimeDocument")) || ((isset($myuser->groupe[$this->droit])) && ($myuser->groupe[$this->droit])) )
		{
			if (file_exists($this->filepath."/".$this->filename))
			{
				if (unlink($this->filepath."/".$this->filename))
				{
				  	$ret.="Fichier supprimé";
				}
			}
			
			if (!file_exists($this->filepath."/".$this->filename))
			{
				$sql->Edit("document",$this->tbl."_document",$this->id,array("actif"=>'non', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
			}
		}
		return $ret;
	}

	function Affiche()
	{ global $MyOpt,$corefolder;
		$myext=GetExtension($this->name);

		if ($myext=="xls")
		  { $icon="excel"; }
		else if ($myext=="xlsx")
		  { $icon="excel"; }
		else if ($myext=="doc")
		  { $icon="word"; }
		else if ($myext=="docx")
		  { $icon="word"; }
		else if ($myext=="ppt")
		  { $icon="powerpoint"; }
		else if ($myext=="pptx")
		  { $icon="powerpoint"; }
		else if ($myext=="pps")
		  { $icon="powerpoint"; }
		else if ($myext=="jpg")
		  { $icon="image"; }
		else if ($myext=="jpeg")
		  { $icon="image"; }
		else if ($myext=="gif")
		  { $icon="image"; }
		else if ($myext=="png")
		  { $icon="image"; }
		else if ($myext=="pdf")
		  { $icon="pdf"; }
		else if ($myext=="mp3")
		  { $icon="music"; }
		else if ($myext=="zip")
		  { $icon="multiple"; }
		else if ($myext=="rar")
		  { $icon="multiple"; }
		else if ($myext=="xml")
		  { $icon="xml"; }
		else if ($myext=="json")
		  { $icon="document"; }
		else if ($myext=="css")
		  { $icon="document"; }
		else if ($myext=="txt")
		  { $icon="document"; }
		else
		  { $icon="file"; }


		if ($this->editmode=="form")
		{
			$txt ="<div id='doc_0'></div>";

			$txt.="<script>";
			$txt.="function AddDocument(i) {";

			$txt.="var r=\"<input name='form_adddocument[\"+i+\"]' type='file' size='60' class='form-control' OnChange='AddDocument(\"+(i+1)+\");'/>\";\n";
			$txt.="r=r+\"<div id='doc_\"+(i+1)+\"'></div>\";\n";
			$txt.="var d=document.getElementById('doc_'+i);\n";
			$txt.="d.innerHTML=r;\n";
			$txt.="}\n";
			
			$txt.="AddDocument(0);\n";
			$txt.="</script>";
		}
		else if ($this->editmode=="regular")
		{
			$txt ="<div id='doc_".$this->id."' class='docLink'>";
			$txt.="<p>";
			if (file_exists($this->filepath."/".$this->filename))
			{
				$fsize=CalcSize(filesize($this->filepath."/".$this->filename));
				// $txt.="<a href='".$MyOpt["host"]."/doc.php?id=".$this->id."' target='_blank'><img src='".$MyOpt["host"]."/".$corefolder."/static/images/icn16_".$icon.".png' width=16 height=16 border=0> ".$this->name." ($fsize) </a>";
				$txt.="<a href='".$MyOpt["host"]."/doc.php?id=".$this->id."' target='_blank'><i class='mdi mdi-list mdi-file-".$icon."'></i> ".$this->name." ($fsize) </a>";
			}
			else
			{
				$txt.="<i class='mdi mdi-list mdi-file-hidden'></i> <s>".$this->name."</s>";
			}
			$txt.="</p>";
			$txt.="</div>";
		}
		else if ($this->editmode=="read")
		{
			if (file_exists($this->filepath."/".$this->filename))
			{
				$fsize=CalcSize(filesize($this->filepath."/".$this->filename));
				$txt.="<a href='".$MyOpt["host"]."/doc.php?id=".$this->id."' target='_blank'><i class='mdi mdi-list mdi-file-".$icon."'></i> ".$this->name." ($fsize) </a>";
			}
			else
			{
				$txt.="<i class='mdi mdi-list mdi-file-hidden'></i> <s>".$this->name."</s>";
			}
		}
		else
		{
			$txt ="<div id='doc_".$this->id."' OnMouseOver='document.getElementById(\"doc_del_".$this->id."\").style.visibility=\"visible\";' OnMouseOut='document.getElementById(\"doc_del_".$this->id."\").style.visibility=\"hidden\";'>";
			$txt.="<p>";
			if (file_exists($this->filepath."/".$this->filename))
			{
					$fsize=CalcSize(filesize($this->filepath."/".$this->filename));
					$txt.="<a href='".$MyOpt["host"]."/doc.php?id=".$this->id."' target='_blank'><i class='mdi mdi-list mdi-file-".$icon."'></i> ".$this->name." ($fsize) </a>";
			}
			else
			{
					$txt.="<i class='mdi mdi-list mdi-file-hidden'></i> <s>".$this->name."</s>";
			}

			// Si mode édition
			if ($this->editmode=="edit")
			{
				// $txt.=" <a href=\"#\" OnClick=\"var win=window.open('doc.php?id=".$this->id."&fonc=delete','scrollbars=no,resizable=no,width=10'); return false;\" class='imgDelete'><img src='".$corefolder."/static/images/icn16_supprimer.png'></a>";
				$txt.=" <a href=\"#\" OnClick=\"$(function() { $.ajax({url:'".$MyOpt["host"]."/doc.php?id=".$this->id."&fonc=delete'}); document.getElementById('doc_".$this->id."').style.visibility='hidden'; document.getElementById('doc_".$this->id."').style.height='0'; })\" class='imgDelete'><img  id='doc_del_".$this->id."' src='".$MyOpt["host"]."/".$corefolder."/static/images/icn16_supprimer.png' style='visibility:hidden;'></a>";
			}
			$txt.="</p>";
			$txt.="</div>";
		}

		return $txt;
	}

	function Download($mode)
	{
		global $myuser,$gl_uid;

		if ( ($this->uid_creat!=$gl_uid) && (!GetDroit($this->droit)) && ($this->droit!="ALL") && (!GetDroit("VisuDocument")) )
		{
			header("HTTP/1.1 401 Unauthorized"); 
			exit;
		}

		if ($mode=="inline")
		{
			$mode="inline;";
		}
		else if ($mode=="attachment")
		{
			$mode="attachment;";
		}
		else
		{
			$mode="";
		}

		$myext=GetExtension($this->name);
		$fname=$this->filepath."/".$this->filename;
		if (!file_exists($fname))
		{
		  	$fname="static/images/icn32_erreur.png";
		  	$myext="png";
		}

		if ($fd = fopen ($fname, "r"))
		{
			$fsize = filesize($fname);
		
			if ($myext=="jpg")
			  { header("Content-Type: image/jpeg"); }
			else if ($myext=="png")
			  { header("Content-Type: image/png"); }
			else if ($myext=="pdf")
			  { header("Content-type: application/pdf"); }
			else
			  { header("Content-type: application/octet-stream"); }

			header("Content-Disposition: ".$mode." filename=\"".$this->name."\";");
			header("Content-length: $fsize");
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		
			while(!feof($fd))
			{
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}
	}

	function Resize($newwidth,$newheight,$dest="")
	{
		$file=$this->filepath."/".$this->filename;
		if ($dest=="")
		{
			$dest=$this->filepath."/".$this->filename;
		}

		if ((!is_numeric($newwidth)) || (!is_numeric($newheight)))
		{
		  	list($newwidth, $newheight) = getimagesize($file);
		}
		
		if (!file_exists($file))
		{
		  	$file="static/images/icn32_erreur.png";
		}
		
		$thumb = imagecreatetruecolor($newwidth, $newheight);
		$white = imagecolorallocate ($thumb, 255, 255, 255);
		imagefill($thumb,0,0,$white); 

		if (exif_imagetype($file)==IMAGETYPE_JPEG)
		{
			$source = imagecreatefromjpeg($file);
		}
		else if (exif_imagetype($file)==IMAGETYPE_PNG)
		{
			$source = imagecreatefrompng($file);
		}
		else if (exif_imagetype($file)==IMAGETYPE_GIF)
		{
			$source = imagecreatefromgif($file);
		}
		else
		{
			$file="static/images/icn32_erreur.png";
			$source = imagecreatefrompng($file);
		}

		list($width, $height) = getimagesize($file);
		
		if (($width<$height) && ($newwidth>0))
		{
			$w = $newwidth;
			$h = floor(($height/$width) * $newwidth );
			imagecopyresampled($thumb, $source, 0, ($newheight-$h)/2, 0, 0, $w, $h, $width, $height);
		}
		else if (($width>$height) && ($newheight>0))
		{
			$w = floor(($width/$height) * $newheight);
			$h = $newheight;
			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $w, $h, $width, $height);
		}
		else
		{
			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newwidth, $width, $height);
		}

		
		if ($dest=="show")
		{
			return $thumb;
		}
		
		if (exif_imagetype($file)==IMAGETYPE_JPEG)
		{
			imagejpeg($thumb,$dest,95);
		}
		else if (exif_imagetype($file)==IMAGETYPE_PNG)
		{
			imagepng($thumb,$dest,6);
		}
		else if (exif_imagetype($file)==IMAGETYPE_GIF)
		{
			imagegif($thumb,$dest);
		}
	}

	function orientation()
	{
		$file=$this->filepath."/".$this->filename;
	  	list($newwidth, $newheight) = getimagesize($file);
		
		if (!file_exists($file))
		{
		  	return;
		}
				
		$exif = @exif_read_data($file);
		$orientation = $exif['Orientation'];

		$deg=0;
		if (isset($orientation) && $orientation != 1)
		{
			switch ($orientation) {
				case 3:
					$deg = 180;
					break;
				case 6:
					$deg = 270;
					break;
				case 8:
					$deg = 90;
					break;
			}
		}
		else
		{
			return false;
		}

		if (exif_imagetype($file)==IMAGETYPE_JPEG)
		{
			$source = imagecreatefromjpeg($file);
		}
		else if (exif_imagetype($file)==IMAGETYPE_PNG)
		{
			$source = imagecreatefrompng($file);
		}
		else if (exif_imagetype($file)==IMAGETYPE_GIF)
		{
			$source = imagecreatefromgif($file);
		}
		else
		{
			return false;
		}

		if ($deg>0) {
			$source = imagerotate($source, $deg, 0);

			if (exif_imagetype($file)==IMAGETYPE_JPEG)
			{
				imagejpeg($source,$file,95);
			}
			else if (exif_imagetype($file)==IMAGETYPE_PNG)
			{
				imagepng($source,$file,6);
			}
			else if (exif_imagetype($file)==IMAGETYPE_GIF)
			{
				imagegif($source,$file);
			}
		}
	}

	function newSize($newwidth,$newheight)
	{
		list($width, $height) = $this->getSize();

		if (($width<$height) && ($newwidth>0))
		{
			$w = $newwidth;
			$h = floor(($height/$width) * $newwidth );
		}
		else if (($width>$height) && ($newheight>0))
		{
			$w = floor(($width/$height) * $newheight);
			$h = $newheight;
		}
		else
		{
			$w = $newwidth;
			$h = $newheight;
		}
		$ret=array($w,$h);
		return $ret;
	}

	function ShowImage($newwidth=0,$newheight=0)
	{
		if (($newwidth==0) || ($newheight==0))
		{
			list($newwidth, $newheight) = $this->getSize();
		}
		list($newwidth, $newheight) = $this->newSize($newwidth, $newheight);
		
		$thumb=$this->Resize($newwidth,$newheight,"show");

		header('Content-Type: image/png');
		imagepng($thumb);
	}

	function isImage()
	{
		$file=$this->filepath."/".$this->filename;

		if(is_array(getimagesize($file))){
			return true;
		}
		return false;
	}

	function getSize()
	{
		$file=$this->filepath."/".$this->filename;

		if (is_array(getimagesize($file)))
		{
			return getimagesize($file);
		}
		return array();
	}
	
	function GenerePath($w,$h)
	{
		// $f=preg_split("/\\//",$this->filename);
		// $file=$f[1];
		// if ($file=="")
		// {
			// $file=$f[0];
		// }
		$myid=CompleteTxt($this->id,6,"0");
		$myext=GetExtension($this->filename);

		$type="";
		if (($w>0) && ($h>0))
		{
			$type="&type=".$type."&width=".$w."&height=".$h;
			$file=$w."x".$h.".".$myext;
		}
		else
		{
			$file="original.".$myext;
		}
		$mypath="static/cache/".$myid."/".$file;
		
		if ($this->droit=="ALL")
		{
			if (!is_dir("../static/cache/".$myid))
			{
				mkdir("../static/cache/".$myid);
			}

			if ((file_exists("../".$mypath)) && ($this->expire>0) && (time()-filectime($mypath)>3600*$this->expire))
			{
				error_log("clear cache:".$mypath);
				unlink($mypath);
			}
			
			if (!file_exists("../".$mypath))
			{
				if (($w>0) && ($h>0))
				{
					$this->Resize($w,$h,"../".$mypath);
				}
				else
				{
					copy($this->filepath."/".$this->filename,"../".$mypath);
				}
			}

			if (file_exists("../".$mypath))
			{
				error_log("path from cache:".$mypath);
				return $mypath;
			}
		}
		return "doc.php?id=".$this->id.$type;
	}
}
	
// Gestion de fichier
function GetExtension($file)
{
	$myext=strtolower(substr($file,strrpos($file,".")+1,strlen($file)-strrpos($file,".")-1));
	return $myext;
}
function GetFilename($file)
{
	$p=strrpos("/".$file,"/");
	$myfile=substr("/".$file,$p+1,strlen($file)-$p-1);
	$myfile=substr($myfile,0,strrpos($myfile,"."));
	return $myfile;
}

function ListDocument($sql,$id,$type)
  {
	global $MyOpt, $gl_uid, $myuser;

	$query="SELECT ".$MyOpt["tbl"]."_document.id,".$MyOpt["tbl"]."_document.uid,".$MyOpt["tbl"]."_document.droit  FROM ".$MyOpt["tbl"]."_document WHERE ".$MyOpt["tbl"]."_document.actif='oui' ".(($id>0) ? "AND ".$MyOpt["tbl"]."_document.uid='$id'" : "" )." ".(($type!="") ? "AND ".$MyOpt["tbl"]."_document.type='$type'" : "" )." ORDER BY name";

	$sql->Query($query);
	$lstdoc=array();
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		if ( 
			($gl_uid==$sql->data["uid"]) 
			|| (($sql->data["droit"]!="") && (isset($myuser->groupe[$sql->data["droit"]])) && ($myuser->groupe[$sql->data["droit"]]))
			|| ($sql->data["droit"]=="ALL") 
			|| (GetDroit("VisuDocument"))
		)
		{
			$lstdoc[$i]=$sql->data["id"];
		}
	}

	return $lstdoc;
  }
	
?>
