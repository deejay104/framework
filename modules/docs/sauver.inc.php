<?
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

<?
	require_once ("class/document.inc.php");

	
	$fid=checkVar("fid","numeric");
	$mid=checkVar("mid","numeric");
	$fpars=checkVar("fpars","numeric");
	$fprec=checkVar("fprec","varchar");
	
// ---- V�rifie les variables
	if (!is_numeric($fid))
	  { echo "Erreur dans la variable fid"; exit; }
	if ((!is_numeric($mid)) && ($mid!=""))
	  { echo "Erreur dans la variable mid"; exit; }
	if (!is_numeric($fpars))
	  { echo "Erreur dans la variable fpars"; exit; }


// ---- Sauvegarde
	if ($fonc=="Enregistrer")
	{
		$form_titre=checkVar("form_titre","varchar");
		$form_corps=checkVar("form_corps","text");
		$form_droit_r=checkVar("form_droit_r","varchar");
		$form_droit_w=checkVar("form_droit_w","varchar");

		$error="";
		if (trim($form_titre)=="")
		  {	$error="Le titre du message est obligatoire."; affInformation($error,"error");  }
		// if (trim($form_corps)=="")
		  // {	$error .= "Le message n'a pas de texte<BR>"; }

		if (!isset($fid))
		  {	$error = "Erreur dans les param�tres."; affInformation($error,"error"); }
		else if ($fid>0)
		{ 
			$query = "SELECT forum.droit_w AS droit ";
			$query.= "FROM ".$MyOpt["tbl"]."_forums AS forum ";
			$query.= "WHERE forum.id=".$fid;
			$res=$sql->QueryRow($query);

			if (!GetDroit($res["droit"]))
			  {	$error = "Acc�s refus�."; affInformation($error,"error"); }
		}

		if (($error=="") && (($fid>0) || (GetDroit("ModifClasseur"))) && ($mid>0))
		  {
			// Editer un message
			$form_corps=strip_tags($form_corps);

			$query ="UPDATE ".$MyOpt["tbl"]."_forums SET ";
			$query.="titre='".addslashes($form_titre)."',";
			$query.="message='".addslashes($form_corps)."',";
			$query.="mail_diff='".$myuser->data["mail"]."',";
			$query.="droit_r='".$form_droit_r."',";
			$query.="droit_w='".$form_droit_w."',";			
			$query.="dte_maj='".now()."',";
			$query.="uid_maj=$uid ";
			$query.="WHERE id=$mid";
			$sql->Update($query);

			$query = "DELETE FROM ".$MyOpt["tbl"]."_forums_lus WHERE forum_msg=".$mid." AND forum_usr<>".$uid;
			$sql->Delete($query);
		  }
		else if (($error=="") && (($fid>0) || (GetDroit("CreeClasseur"))))
		  {
			// Cr��r un nouveau message
			$form_corps=strip_tags($form_corps);

			$query ="INSERT INTO ".$MyOpt["tbl"]."_forums SET ";
			$query.="fid='$fid',";
			$query.="fil='$fpars',";
			$query.="titre='".addslashes($form_titre)."',";
			$query.="message='".addslashes($form_corps)."',";
			// $query.="pseudo='".addslashes($buque)."',";
			$query.="mail_diff='".$myuser->data["mail"]."',";
			$query.="droit_r='".$form_droit_r."',";
			$query.="droit_w='".$form_droit_w."',";			
			$query.="dte_maj='".now()."',";
			$query.="uid_maj=$uid,";
			$query.="dte_creat='".now()."',";
			$query.="uid_creat=$uid";

			$mid=$sql->Insert($query);
		  }

		if ($error=="")
		{
			// Sauvegarde les documents
			if (is_array($_FILES["form_adddocument"]["name"]))
			{
				foreach($_FILES["form_adddocument"]["name"] as $i=>$n)
				{
					if ($n!="")
					{
						$doc = new document_core(0,$sql,"forum");
		
						$query="SELECT droit_r FROM ".$MyOpt["tbl"]."_forums WHERE id=$fid";
						$res=$sql->QueryRow($query);
		
						$doc->droit=($res["droit_r"]=="") ? "ALL" : $res["droit_r"];
						$doc->Save($mid,$_FILES["form_adddocument"]["name"][$i],$_FILES["form_adddocument"]["tmp_name"][$i]);

					}
				}
			}


			// S'il y a du mailing pour les utilisateurs
			if ((isset($mailtype)) && (is_array($mailtype)))
			{
				// $txtmail=nl2br(htmlentities($form_corps,ENT_HTML5,"ISO-8859-1"));
				$txtmail=nl2br($form_corps);

				$lstdoc=ListDocument($sql,$mid,"forum");
					
				if ((is_array($lstdoc)) && (count($lstdoc)>0))
				{
					$txtmail.="<br/><br/>Pi�ce(s) attach�e(s) :<br/>";
					foreach($lstdoc as $i=>$did)
					{
						$doc = new document_core($did,$sql);
						$txtmail.=$doc->Affiche()."<br/>";
					}
				}

				$txtmail.="<br /><br />-Email envoy� � partir du site ".$MyOpt["site_title"]."-";
				
				// Pour chaque type s�lectionn�
				foreach($mailtype as $typeid=>$typechk)
				{
					// On r�cup�re la liste
					$lst=ListActiveUsers($sql,"",array($typeid));
	
					foreach($lst as $i=>$uid)
					{
						// Et on envoie un mail � chacune des personnes de la liste
						$usr = new user_core($uid,$sql,false);
	
						if ($usr->mail!="")
						{
							MyMail($myuser->data["mail"],$usr->mail,"",stripslashes($form_titre),$txtmail);
						}
					}
				}

				// Sauvegarde le fait que le mailing a �t� fait
				$query = "UPDATE ".$MyOpt["tbl"]."_forums AS forum SET mailing=1 ";
				$query.= "WHERE forum.id=".$mid;
				$res=$sql->Update($query);
			}
	
			// Si le mailing a une liste de diffusion est s�lectionn�
			if ((isset($maildiff)) && ($maildiff=="on"))
			{
				// R�cup�re le mail du forum parent
				$query = "SELECT forum.mail_diff AS mail ";
				$query.= "FROM ".$MyOpt["tbl"]."_forums AS forum ";
				$query.= "WHERE forum.id=".$fid;
				$res=$sql->QueryRow($query);
	
				if ($res["mail"]!="")
				{
					MyMail($myuser->data["mail"],$res["mail"],"",stripslashes($form_titre),$txtmail);
				}
			}
	
		}

		if ($error=="")
		  {
			// Si pas d'erreur
			$fonc="";

			$query="INSERT INTO ".$MyOpt["tbl"]."_forums_lus SET forum_msg=$mid, forum_usr=$gl_uid, forum_date='".now()."'";
			$sql->Insert($query);
			
			if ($fid==$fpars)
			{
				$affrub="liste";
			}
			else
			{
				$affrub="detail";
			}
		  }
		else
		  {
			// Sinon on recharche la page
			$fonc="";
			$affrub="editer";
		  }
	  }
	else if (($fonc=="Annuler") && ($mid>0))
	  {
			$fonc="";
			$affrub="detail";
	  }
	else if (($fonc=="Annuler") && ($fpars>0))
	  {
			$fonc="";
			$mid=$fpars;
			$affrub=$fprec;
	  }
	else if (($fonc=="Annuler") && ($fid>0))
	  {
			$fonc="";
			$affrub="liste";
	  }
	else if ($fonc=="Annuler")
	  {
			$fonc="";
			$affrub="forums";
	  }
	else
	  {
			$fonc="";
			$affrub="forums";
	  }

?>
