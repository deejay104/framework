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
?>

<?php
	require_once ("class/document.inc.php");

// ---- Paramètres
	$fid=checkVar("fid","numeric");
	$mid=checkVar("mid","numeric");
	$fpars=checkVar("fpars","numeric");
	$fprec=checkVar("fprec","varchar");

// ---- Titre de la page

	if ($mid>0)
	  { $tmpl_x->assign("infos", "Modifier un message"); }
	else
	  { $tmpl_x->assign("infos", "Ajouter un message"); }



// ---- Info de la page
	// if ($error!="")
	  // {
			// $tmpl_x->assign("msg_error", $error);
			// $tmpl_x->parse("corps.msg_error");
		// }
	if (!isset($error))
	{
		$error="";
	}
		

	if ($error!="")
	  {
			$tmpl_x->assign("titre", preg_replace('/"/s','&quote;', stripslashes($form_titre)));
			$tmpl_x->assign("corps", preg_replace('/"/s','&quote;', stripslashes($form_corps)));
	  }
	else if ($mid > 0)
	  {
			$query = "SELECT forum.titre AS titre,";
			$query.= "forum.message AS corps, mailing, droit_r, droit_w ";
			$query.= "FROM ".$MyOpt["tbl"]."_forums AS forum ";
			$query.= "WHERE forum.id=".$mid;
			$res=$sql->QueryRow($query);
	
			$tmpl_x->assign("titre", $res["titre"]);
			$tmpl_x->assign("corps", $res["corps"]);
	  }
	else
	  {
			$tmpl_x->assign("titre", "");
			$tmpl_x->assign("corps", " ");
	  }


// ---- Initialisation des variables
	$tmpl_x->assign("fid", $fid);
	$tmpl_x->assign("mid", $mid);
	$tmpl_x->assign("fpars", $fpars);
	$tmpl_x->assign("fprec", $fprec);

// ---- Liste des documents attachés

	$doc = new document_core(0,$sql);
	$doc->editmode="form";
	$tmpl_x->assign("form_document",$doc->Affiche());
	$tmpl_x->parse("corps.lst_document");
	  	
	if ($mid>0)
	  {
		$lstdoc=ListDocument($sql,$mid,"forum");
		if ( (is_array($lstdoc)) && (count($lstdoc)>0) )
		  {
			foreach($lstdoc as $i=>$did)
			  {
					$doc = new document_core($did,$sql);
					$doc->editmode="edit";
					$tmpl_x->assign("form_document",$doc->Affiche());
					$tmpl_x->parse("corps.lst_document");
			  }
		  }
	  }

// ---- Création d'un nouveau forum
	if (($fid==0) || ($fid==$mid))
	  {
		$tgrp=array();

		$query="SELECT groupe FROM ".$MyOpt["tbl"]."_groupe ORDER BY groupe";
		$sql->Query($query);

		for($i=0; $i<$sql->rows; $i++)
			{
				$sql->GetRow($i);
		  	if ($sql->data["groupe"]!="ALL")
		  	  { $tgrp[$sql->data["groupe"]]="ok"; }
		  }
		foreach($tgrp as $grp=>$v)
		  {
		  	$tmpl_x->assign("droit_grp",$grp);
		  	$tmpl_x->assign("chk_droit_r",($res["droit_r"]==$grp) ? "selected" : "");
		  	$tmpl_x->assign("chk_droit_w",($res["droit_w"]==$grp) ? "selected" : "");
				$tmpl_x->parse("corps.droits.lst_droits_r");
				$tmpl_x->parse("corps.droits.lst_droits_w");
		  }

		$tmpl_x->parse("corps.droits");
	  }


// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>
