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

// --- Load folder's permission
	require_once ("class/folder.inc.php");


// ---- Affiche les bouttons
	addPageMenu($corefolder,$mod,"Liste",geturl("docs","",""),"mdi-keyboard-backspace");
	// addPageMenu($corefolder,$mod,"Rechercher",geturl("docs","recherche",""),"icn32_rechercher.png",false,"","showSearch();");

	$tmpl_x->assign("BorderBlack",$MyOpt["styleColor"]["BorderBlack"]);
	$tmpl_x->assign("LineBackgroundHover",$MyOpt["styleColor"]["LineBackgroundHover"]);
	$tmpl_x->assign("TextBackgroundHover",$MyOpt["styleColor"]["TextBackgroundHover"]);
	$tmpl_x->assign("TitleBackgroundNormal",$MyOpt["styleColor"]["TitleBackgroundNormal"]);
	$tmpl_x->assign("FormulaireBackgroundDark",$MyOpt["styleColor"]["FormulaireBackgroundDark"]);
	$tmpl_x->assign("FormulaireBackgroundNormal",$MyOpt["styleColor"]["FormulaireBackgroundNormal"]);
	$tmpl_x->assign("FormulaireBackgroundLight",$MyOpt["styleColor"]["FormulaireBackgroundLight"]);

// ---- Affiche la liste des utilisateurs
	if ((GetDroit("ModifUserAll")) || (GetDroit("ModifUserDocument")))
	{
		$uid=checkVar("id","numeric");
		if ($uid==0)
		{
			$uid=$gl_uid;
		}
		$tmpl_x->assign("form_lstusers", AffListeMembres($sql,$uid,"form_id","","","std","oui",array()));
		$tmpl_x->parse("corps.lstusers");

	}
	else
	{
		$uid=$gl_uid;
	}		

	$tmpl_x->assign("uid",$uid);

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>