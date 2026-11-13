<?php
/*
    SoceIt v2.0
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
?>

<?php
	if ( (!GetDroit("AccesDocuments")) && (!GetMyId($id)) )
	  { FatalError("Accès non autorisé (AccesDocuments)"); }


// ---- Affiche les bouttons
	// addPageMenu($corefolder,$mod,"Rechercher",geturl("docs","recherche",""),"icn32_rechercher.png",false,"","showSearch();");
	if (GetDroit("CreeDossier"))
	{
		addPageMenu($corefolder,$mod,"Nouveau dossier","","",false,"","createFolder();");
	}

// ---- Liste les groupes
	$query="SELECT groupe FROM ".PREFIX_TBL."_groupe ORDER BY groupe";
	$sql->Query($query);

	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);

		if ($sql->data["groupe"]!="ALL")
		{
			$tmpl_x->assign("aff_group",$sql->data["groupe"]);
			$tmpl_x->parse("corps.lst_group_read");
			$tmpl_x->parse("corps.lst_group_write");
		}
	}

// ---- Liste des tags
	$query="SELECT * FROM ".PREFIX_TBL."_tags_conf ORDER BY name";
	$sql->Query($query);

	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);

		$tmpl_x->assign("aff_tag_id",$sql->data["id"]);
		$tmpl_x->assign("aff_tag_name",$sql->data["name"]);
		$tmpl_x->parse("corps.lst_tag");
	}

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>