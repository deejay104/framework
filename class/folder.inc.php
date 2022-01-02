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
		"dte_start"=>Array("type" => "date"),
		"dte_end"=>Array("type" => "date"),
	);

	
	protected $tabList=array(

	);


}

function ListActivePapers($id,$sql,$sort="dte_creat")
{
	global $MyOpt,$gl_uid;
	$crit=array("actif"=>"oui");
	if ($id>0)
	{
		$crit["id_folder"]=$id;
	}
		
	return ListeObjets($sql,"doc_paper",array("id","title","dte_start","dte_end","uid_creat","dte_creat"),$crit,array($sort));
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