<?
	require_once("class/echeance.inc.php");

// ---- Charge le template
	$tmpl_x = LoadTemplate ("echeances");
	$tmpl_x->assign("path_module",$corefolder."/".$module."/".$mod);

// ---- Vérifie le droit d'accès
	if (!GetDroit("AccesConfigEcheances")) { FatalError($tabLang["lang_accessdenied"]." (AccesConfigEcheances)"); }

// ---- Variables
	$order=checkVar("order","varchar");
	$trie=checkVar("trie","varchar");

	
// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);

// ---- Sauvegarde
	if ($fonc=="Enregistrer")
	{
		$form_description=checkVar("form_description","array");
		$form_data=checkVar("form_data","array");
		foreach($form_data as $id=>$d)
		{
			if (($id>0) || ($d["description"]!=""))
			{
				$ech = new echeancetype_core($id,$sql);
				foreach($d as $k=>$v)
				{
					$err=$ech->Valid($k,$v);
					affInformation($err,"error");
				}
				$ech->Save();
			}
		// if(trim($d)!="")
			// {
				// $t=array(
					// "description"=>$d,
					// "poste"=>$form_poste[$id],
					// "droit"=>$form_droit[$id],
					// "resa"=>$form_resa[$id],
					// "multi"=>$form_multi[$id],
					// "cout"=>$form_cout[$id],
					// "notif"=>$form_notif[$id],
					// "delai"=>$form_delai[$id]
				// );
				// $sql->Edit("echeance",$MyOpt["tbl"]."_echeancetype",$id,$t);
			// }
			
		}
	}
// ---- Supprime une échéance
	if (($fonc=="delete") && ($id>0))
	{
		// $sql->Edit("echeance",$MyOpt["tbl"]."_echeancetype",$id,array("actif"=>"non"));		
				$ech = new echeancetype_core($id,$sql);
				$ech->Delete();
	}

// ---- Liste des groupes
	// $query = "SELECT groupe FROM ".$MyOpt["tbl"]."_groupe ORDER BY description";
	// $sql->Query($query);
	// $tabgrp=array();
	// for($i=0; $i<$sql->rows; $i++)
	// { 
		// $sql->GetRow($i);
		// $tabgrp[$sql->data["groupe"]]=$sql->data["groupe"];
	// }
	
// ---- Affiche les types d'échéance
	// $query="SELECT * FROM ".$MyOpt["tbl"]."_echeancetype ORDER BY description";
	// $sql->Query($query);

	// for($i=0; $i<$sql->rows; $i++)
	// {
		// $sql->GetRow($i);
		// $tmpl_x->assign("form_id",$sql->data["id"]);
		// $tmpl_x->assign("form_description",$sql->data["description"]);
		// $tmpl_x->assign("form_droit",$sql->data["droit"]);
		// $tmpl_x->assign("form_cout",$sql->data["cout"]);
		// $tmpl_x->assign("form_delai",$sql->data["delai"]);

		// $tmpl_x->assign("select_resa_instructeur","");
		// $tmpl_x->assign("select_resa_obligatoire","");
		// $tmpl_x->assign("select_resa_facultatif","");
		// $tmpl_x->assign("select_multi_oui","");
		// $tmpl_x->assign("select_multi_non","");

		// $tmpl_x->assign("select_notif_oui","");
		// $tmpl_x->assign("select_notif_non","");

		// $tmpl_x->assign("select_resa_".$sql->data["resa"],"selected");
		// $tmpl_x->assign("select_multi_".$sql->data["multi"],"selected");
		// $tmpl_x->assign("select_notif_".$sql->data["notif"],"selected");

		// foreach($tabgrp as $grp=>$d)
		// {
			// $tmpl_x->assign("form_groupe",$grp);
			// $tmpl_x->assign("select_groupe",($sql->data["droit"]==$grp) ? "selected" : "");
			
			// $tmpl_x->parse("corps.lst_echeance.lst_groupe");
		// }
		
		// $tmpl_x->parse("corps.lst_echeance");
	// }

	
	$tabTitre=array(
		"description"=>array(
			"aff"=>$tabLang["lang_description"],
			"width"=>220
		),
		"calendar"=>array(
			"aff"=>$tabLang["lang_calendar"],
			"width"=>120
		),
		"right"=>array(
			"aff"=>$tabLang["lang_right"],
			"width"=>150
		),
		"multi"=>array(
			"aff"=>$tabLang["lang_multi"],
			"width"=>140
		),
		"notif"=>array(
			"aff"=>$tabLang["lang_notif"],
			"width"=>140
		),
		"recipient"=>array(
			"aff"=>$tabLang["lang_recipient"],
			"width"=>150
		),
		"delay"=>array(
			"aff"=>$tabLang["lang_delay"],
			"width"=>90
		),
		"action"=>array(
			"aff"=>"&nbsp;",
			"width"=>20
		)
	);


	$tabValeur=array();

	// $lstFiche=GetActiveFiche($sql,$uid_avion);
	$lstEcheance=ListEcheanceType($sql,"");
	$lstEcheance[]["id"]=0;

	foreach($lstEcheance as $i=>$d)
	{
		$id=$d["id"];
		$ech = new echeancetype_core($id,$sql);

		$tabValeur[$i]["description"]["val"]=$ech->val("description");
		$tabValeur[$i]["description"]["aff"]=$ech->aff("description","form","form_data[".$id."]");

		$tabValeur[$i]["calendar"]["val"]=$ech->val("resa");
		$tabValeur[$i]["calendar"]["aff"]=$ech->aff("resa","form","form_data[".$id."]");

		$tabValeur[$i]["right"]["val"]=$ech->val("droit");
		$tabValeur[$i]["right"]["aff"]=$ech->aff("droit","form","form_data[".$id."]");

		$tabValeur[$i]["multi"]["val"]=$ech->val("multi");
		$tabValeur[$i]["multi"]["aff"]=$ech->aff("multi","form","form_data[".$id."]");
		$tabValeur[$i]["notif"]["val"]=$ech->val("notif");
		$tabValeur[$i]["notif"]["aff"]=$ech->aff("notif","form","form_data[".$id."]");
		$tabValeur[$i]["recipient"]["val"]=$ech->val("recipient");
		$tabValeur[$i]["recipient"]["aff"]=$ech->aff("recipient","form","form_data[".$id."]");
		$tabValeur[$i]["delay"]["val"]=$ech->val("delai");
		$tabValeur[$i]["delay"]["aff"]=$ech->aff("delai","form","form_data[".$id."]");
		
		$tabValeur[$i]["id"]["val"]=$id;
		$tabValeur[$i]["action"]["val"]=$id;
		$tabValeur[$i]["action"]["aff"]="<div id='action_".$id."' style='display:none;'><a id='edit_".$id."' class='imgDelete' href='index.php?mod=admin&rub=echeances&fonc=delete&id=".$id."'><img src='".$corefolder."/".$module."/".$mod."/img/icn16_supprimer.png'></a></div>";
		
	}

	if ($order=="") { $order="description"; }
	if ($trie=="") { $trie="d"; }

	$tmpl_x->assign("aff_tableau",AfficheTableau($tabValeur,$tabTitre,$order,$trie,"",0,"",0,"action"));

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>