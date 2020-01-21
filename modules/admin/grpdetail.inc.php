<?php
// ---------------------------------------------------------------------------------------------
//   Admin groupe
//     ($Author: miniroot $)
// ---------------------------------------------------------------------------------------------
//   Variables  : 
// ---------------------------------------------------------------------------------------------
/*

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
// ---- Vérifie le droit d'accès
	if (!GetDroit("AccesConfigGroupes")) { FatalError($tabLang["lang_accessdenied"]." (AccesConfigGroupes)"); }

// ---- Vérifie les variables
	$sup=checkVar("sup","numeric");
	$search=checkVar("search","varchar");
	$grp=checkVar("grp","varchar",5);

	$form_grp=checkVar("form_grp","varchar");
	$form_desc=checkVar("form_desc","varchar");
	$form_princ=checkVar("form_princ","varchar");

// ---- Charge les roles
	$tabRoles=array();
	require_once("modules/".$mod."/conf/roles.tmpl.php");
	if (file_exists($appfolder."/modules/".$mod."/conf/roles.tmpl.php"))
	{
		require_once($appfolder."/modules/".$mod."/conf/roles.tmpl.php");
	}



// ---- Enregistre le groupe
	if (($grp=="") && ($fonc==$tabLang["lang_save"]) && (GetDroit("CreeGroupe")) && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{
		$q="INSERT INTO ".$MyOpt["tbl"]."_groupe SET groupe='$form_grp',description='$form_desc',principale='$form_princ'";
		$sql->Insert($q);
		$grp=$form_grp;
		$_SESSION['tab_checkpost'][$checktime]=$checktime;
	}
	else if (($grp!="") && ($fonc==$tabLang["lang_save"]) && (GetDroit("ModifGroupe")) && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{
		$q="UPDATE ".$MyOpt["tbl"]."_groupe SET groupe='$form_grp',description='$form_desc',principale='$form_princ' WHERE groupe='$grp'";
		$sql->Update($q);

		$q="UPDATE ".$MyOpt["tbl"]."_roles SET groupe='$form_grp' WHERE groupe='$grp'";
		$sql->Update($q);

		$q="UPDATE ".$MyOpt["tbl"]."_droits SET groupe='$form_grp' WHERE groupe='$grp'";
		$sql->Update($q);

		$grp=$form_grp;

		$_SESSION['tab_checkpost'][$checktime]=$checktime;
	}

// ---- Supprime le groupe
	if (($grp!="") && ($fonc=="supprimer") && (GetDroit("SupprimeGroupe")))
	{
		// Suppression du groupe
		$q="DELETE FROM ".$MyOpt["tbl"]."_groupe WHERE groupe='$grp'";
		$sql->Delete($q);
		// Purge roles
		// A voir si nécessaire ? Sinon cela permet de les récupérer en cas de mauvaise manip
		$q="DELETE FROM ".$MyOpt["tbl"]."_roles WHERE groupe='$grp'";
		$sql->Delete($q);

		// Purge droits
		$q="DELETE FROM ".$MyOpt["tbl"]."_droits WHERE groupe='$grp'";
		$sql->Delete($q);
		$grp="";
	}

// ---- Copie un groupe
	if (($grp!="") && ($fonc=="copier") && (GetDroit("CreeGroupe")) && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{

		$query="SELECT description FROM ".$MyOpt["tbl"]."_groupe WHERE groupe='$grp' LIMIT 1";
		$res=$sql->QueryRow($query);

		$form_grp=substr($grp,0,4)."2";

		$q="INSERT INTO ".$MyOpt["tbl"]."_groupe SET groupe='$form_grp',description='".$res["description"]."'";
		$sql->Insert($q);


		$t=array();		
		$query="SELECT role FROM ".$MyOpt["tbl"]."_roles WHERE groupe='$grp'";
		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);
			$t[$i]=$sql->data["role"];
		}
		foreach ($t as $i=>$d)
		{
	 		$q="INSERT INTO ".$MyOpt["tbl"]."_roles SET groupe='$form_grp',role='$d'";
			$sql->Insert($q);
		}		


		$grp=$form_grp;
		$_SESSION['tab_checkpost'][$checktime]=$checktime;
	}


// ---- Supprime un utilisateur du groupe
	if (($grp!="") && ($sup>0) && (GetDroit("ModifUserDroits")))
	{
		$q="DELETE FROM ".$MyOpt["tbl"]."_droits WHERE groupe='$grp' AND uid='$sup'";
		$sql->Delete($q);
	}

// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);

// <div class="pagetitle pagemenu">
	// <p><A href="index.php?mod=admin&rub=groupes"><IMG src="{path_module}/img/icn32_retour.png" border=0 alt="">{lang_list}</A></p>
	// <p><A href="index.php?mod=admin&rub=grpdetail"><IMG src="{path_module}/img/icn32_groupeadd.png" border=0 alt="">{lang_new}</A></p>
	// <p><A href="index.php?mod=admin&rub=grpdetail&grp={form_grp}&fonc=copier"><IMG src="{path_module}/img/icn32_groupecopy.png" border=0 alt="">{lang_copy}</A></p>
	// <p><A href="#" OnClick="ConfirmeClick('index.php?mod=admin&rub=grpdetail&grp={form_grp}&fonc=supprimer','Souhaitez-vous supprimer ce groupe ?');"><IMG src="{path_module}/img/icn32_groupesup.png" border=0 alt="">{lang_delete}</A></p>
// </div>
	addSubMenu("",$tabLang["lang_list"],geturl("admin","groupes",""),"icn32_retour.png",false);
	addSubMenu("",$tabLang["lang_new"],geturl("admin","grpdetail",""),"icn32_groupeadd.png",false);
	addSubMenu("",$tabLang["lang_copy"],geturl("admin","grpdetail","grp=".$grp."&fonc=copier"),"icn32_groupecopy.png",false);
	addSubMenu("",$tabLang["lang_delete"],geturl("admin","grpdetail","grp=".$grp."&fonc=supprimer"),"icn32_groupesup.png",false,"Souhaitez-vous supprimer ce groupe ?");
	affSubMenu();


// ---- Affiche les informations
	$tmpl_x->assign("form_grp",$grp);

	$query="SELECT description,principale FROM ".$MyOpt["tbl"]."_groupe WHERE groupe='$grp' LIMIT 1";
	$res=$sql->QueryRow($query);
	$tmpl_x->assign("form_desc",$res["description"]);
	$tmpl_x->assign("form_princ_".$res["principale"],"checked");

// ---- Liste les roles
	$tabRolesNok=$tabRoles;
		
	$query="SELECT * FROM ".$MyOpt["tbl"]."_roles WHERE groupe='$grp' ORDER BY role";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$tmpl_x->assign("aff_role",$sql->data["role"]);
		$tmpl_x->assign("aff_help",$tabRoles[$sql->data["role"]]);
		unset($tabRolesNok[$sql->data["role"]]);
		$tmpl_x->parse("corps.aff_config.lst_roles_".$sql->data["autorise"]);
	}

	ksort($tabRolesNok,SORT_STRING | SORT_FLAG_CASE);

	foreach($tabRolesNok as $r=>$h)
	{
		$tmpl_x->assign("aff_role",$r);
		$tmpl_x->assign("aff_help",$h);

		$tmpl_x->assign("aff_couleur","");
		if ($search!="")
		{
			if ( (preg_match("/".$search."/i",$r)) || (preg_match("/".$search."/i",$h)) )
			{
				$tmpl_x->assign("aff_couleur","adminRouge");
			}
		}
		$tmpl_x->parse("corps.aff_config.lst_roles_nok");
	}

// ---- Liste les utilisateurs
	$tUser=array();
	$query="SELECT uid FROM ".$MyOpt["tbl"]."_droits AS droits LEFT JOIN ".$MyOpt["tbl"]."_utilisateurs AS usr ON droits.uid=usr.id WHERE droits.groupe='$grp' ORDER BY usr.nom";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$tUser[$i]=$sql->data["uid"];
	}

	foreach($tUser as $i=>$id)
	{
		$usr = new user_core($id,$sql,false,false);
		$tmpl_x->assign("aff_user",$usr->aff("fullname"));
		$tmpl_x->assign("aff_uid",$id);
	
		if (GetDroit("ModifUserDroits"))
		  {
				$tmpl_x->parse("corps.aff_config.lst_user.aff_sup");
		  }
		$tmpl_x->parse("corps.aff_config.lst_user");
	}

	if ($grp!="")
	{
		$tmpl_x->parse("corps.aff_config");
	}

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>
