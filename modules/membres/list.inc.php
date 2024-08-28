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
	if (!GetDroit("AccesMembres")) { FatalError($tabLang["lang_accessdenied"]." (AccesMembres)"); }

// ---- Valide les variables
	$aff=checkVar("aff","varchar");
	$grp=checkVar("grp","varchar");

// ---- Menu
	addPageMenu($corefolder,$mod,$tabLang["lang_list"],geturl("membres","","fonc=&aff=".$aff),"icn32_liste.png",false);
	addPageMenu($corefolder,$mod,$tabLang["lang_pictures"],geturl("membres","","fonc=trombi&aff=".$aff),"icn32_trombi.png",false);
	addPageMenu($corefolder,$mod,$tabLang["lang_emargement"],geturl("membres","list",""),"icn32_trombi.png",true);

	if (GetDroit("AccesMembresVirtuel"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_normal"],geturl("membres","","fonc=".$fonc."&aff="),"",($aff!="virtuel") ? true : false);
		addPageMenu($corefolder,$mod,$tabLang["lang_virtuals"],geturl("membres","","fonc=".$fonc."&aff=virtuel"),"",($aff=="virtuel") ? true : false);
	}

	if (GetDroit("CreeUser"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_add"],geturl("membres","detail","id=0"),"icn32_ajouter.png");
	}

    if (!isset($aff))
    { $aff=""; }

    $q="";
    if ($grp=="")
    {
        $query="SELECT id,groupe,description FROM ".$MyOpt["tbl"]."_groupe WHERE principale='oui' ORDER BY description";
        $sql->Query($query);
    
        for($i=0; $i<$sql->rows; $i++)
        { 
            $sql->GetRow($i);
            if ($sql->data["groupe"]!="SYS")
            {
                $tabgrp[$sql->data["groupe"]]=true;
            }
        }
    }
    else
    {
        $tabaff=array();
        $tabgrp=json_decode($grp,true);
  
        if ((is_array($tabgrp)) && (count($tabgrp)>0))
        {
            $q="AND groupe IN (";
            $s="";
            foreach ($tabgrp as $g=>$d)
            {
                $q.=$s."'".$d."'";
                $s=",";
                $tabaff[$d]=true;
            }
            $q.=")";            
        }
        else
        {
            $q="AND 1=0";
        }

    }


    $query="SELECT id,groupe,description FROM ".$MyOpt["tbl"]."_groupe WHERE principale='oui' ORDER BY description";
    $sql->Query($query);

    for($i=0; $i<$sql->rows; $i++)
    { 
        $sql->GetRow($i);
        if ($sql->data["groupe"]!="SYS")
        {
            $tmpl_x->assign("aff_codegroupe",$sql->data["groupe"]);
            $tmpl_x->assign("aff_groupe",$sql->data["description"]);
            $tmpl_x->assign("aff_checked",((isset($tabaff[$sql->data["groupe"]])) && ($tabaff[$sql->data["groupe"]])) ? "checked" :"");
            $tmpl_x->parse("corps.lst_groupe");
            $tmpl_x->parse("corps.lst_script");
        }
    }

    $query="SELECT id,prenom,nom FROM ".$MyOpt["tbl"]."_utilisateurs WHERE actif='oui' AND virtuel='non' ".$q." ORDER BY prenom, nom";
    $sql->Query($query);

    for($i=0; $i<$sql->rows; $i++)
    { 
        $sql->GetRow($i);

        $tmpl_x->assign("aff_displayname",AffFullName($sql->data["prenom"],$sql->data["nom"]));
        $tmpl_x->parse("corps.lst_membre");
        
    }

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");



?>
