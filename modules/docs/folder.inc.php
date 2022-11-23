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

	if (!GetDroit("AccesDocuments"))
	  { FatalError("Accès non autorisé (AccesDocuments)"); }

	$id=checkVar("id","numeric");
	$crit=checkVar("crit","varchar",50);

// --- Load folder's permission
	require_once ("class/folder.inc.php");

	$folder=new folder_core($id,$sql);

	if (!GetDroit($folder->val("group_read")))
	  { FatalError("Accès non autorisé (".$folder->val("group_read").")"); }


// ---- Affiche les bouttons
	// addPageMenu($corefolder,$mod,"Liste",geturl("docs","",""),"icn32_retour.png");
	// addPageMenu($corefolder,$mod,"Rechercher",geturl("docs","recherche",""),"icn32_rechercher.png",false,"","showSearch();");

	$tmpl_x->assign("id",$id);
	$tmpl_x->assign("crit",$crit);
	$tmpl_x->assign("form_today",date("Y-m-d"));
	$tmpl_x->assign("BorderBlack",$MyOpt["styleColor"]["BorderBlack"]);
	$tmpl_x->assign("LineBackgroundHover",$MyOpt["styleColor"]["LineBackgroundHover"]);
	$tmpl_x->assign("TextBackgroundHover",$MyOpt["styleColor"]["TextBackgroundHover"]);
	$tmpl_x->assign("TitleBackgroundNormal",$MyOpt["styleColor"]["TitleBackgroundNormal"]);
	$tmpl_x->assign("FormulaireBackgroundDark",$MyOpt["styleColor"]["FormulaireBackgroundDark"]);
	$tmpl_x->assign("FormulaireBackgroundNormal",$MyOpt["styleColor"]["FormulaireBackgroundNormal"]);
	$tmpl_x->assign("FormulaireBackgroundLight",$MyOpt["styleColor"]["FormulaireBackgroundLight"]);

	if (GetDroit($folder->val("group_write")))
	{
		$tmpl_x->parse("corps.edit");
		$tmpl_x->parse("corps.createdoc");
		$tmpl_x->parse("corps.adddoc");
	}
	else
	{
		$tmpl_x->parse("corps.noedit");
	}


// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>