<?php

// ---- Refuse l'accès en direct
if ((!isset($token)) || ($token==""))
{ header("HTTP/1.0 401 Unauthorized"); exit; }

//if (!GetDroit("ModifFormation"))
//{ header("HTTP/1.0 401 Unauthorized"); exit; }

require_once ("class/tags.inc.php");


// ---- Variable
$id=checkVar("id","numeric");

// ----
$result=array();

if (($_SERVER['REQUEST_METHOD']=="GET") && ($id>0))
{
    $tag=new tags_conf_core($id,$sql);
    $result=$tag->export();
}
else if ($_SERVER['REQUEST_METHOD']=="GET")
{
    $c=checkVar("columns","array");
    $s=checkVar("order","array");
    $draw=checkVar("draw","numeric");
    $ts=checkVar("start","numeric");
    $tl=checkVar("length","numeric");
    $search=checkVar("search","array");
    $term=checkVar("term","varchar");
    if ($term!="")
    {
        $search=array("value"=>$term);
    }

    $order="name";
    $trie="asc";
    if ((isset($s[0]["column"])))
    {
        $order=$c[$s[0]["column"]]["data"];
    }
    if (isset($s[0]["dir"]))
    {
        $trie=($s[0]["dir"]=="asc") ? "asc" : "desc";
    }

    
    $lst=ListTagsConf($sql,"",array($order." ".$trie));

    $result["draw"]=$draw;
    $result["recordsTotal"]=count($lst);
    $result["recordsFiltered"]=count($lst);
    $result["data"]=array();

    $ii=0;
    foreach($lst as $i=>$d)
    {
        $ref=new tags_conf_core($d["id"],$sql);

        if ($ref->search($search["value"]))
        {
            $result["data"][$ii]["id"]=$ref->id;
            $result["data"][$ii]["name"]=$ref->val("name");
            $result["data"][$ii]["label"]=$ref->val("name");
            $result["data"][$ii]["scope"]=$ref->val("scope");
            $ii++;
        }
    }
    $result["recordsFiltered"]=$ii;

}
else if ($_SERVER['REQUEST_METHOD']=="POST")
{
    $data=checkVar("data","array");

    $q ="SELECT * FROM ".PREFIX_TBL."_tags AS tag WHERE tag.id_obj='".$data["oid"]."' AND tag.type_obj='".$data["type"]."'";
	$sql->Query($q);

	$lsttag=array();
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
        $lsttag[$sql->data["id_tag"]]["db"]=true;
        if ($sql->data["actif"]=="non")
        {
            $lsttag[$sql->data["id_tag"]]["del"]=true;        
            $lsttag[$sql->data["id_tag"]]["db"]=false;        
        }
    }

    foreach($data["ids"] as $k)
    {
        $lsttag[$k]["api"]=true;      
    }

    foreach($lsttag as $k=>$d)
    {
        $db=$d["db"] ?? false;
        $api=$d["api"] ?? false;
        $del=$d["del"] ?? false;

        if (($db) && ($api))
        {
           // tag already there
            $result["data"][$k]="kept";
        }
        else if ((!$db) && ($api))
        {
            if ($del)
            {
                $q ="UPDATE ".PREFIX_TBL."_tags SET actif='oui',uid_maj='".$gl_uid."', dte_maj='".now()."' WHERE id_obj='".$data["oid"]."' AND type_obj='".$data["type"]."' AND id_tag='".$k."'";
                $sql->Update($q);
                error_log($q);
                $result["data"][$k]="activated";                
            }
            else
            {
                $q ="INSERT INTO ".PREFIX_TBL."_tags SET id_obj='".$data["oid"]."', type_obj='".$data["type"]."', id_tag='".$k."',uid_maj='".$gl_uid."', dte_maj='".now()."'";
                $sql->Insert($q);
                error_log($q);
                $result["data"][$k]="added";                
            }

        }
        else if (($db) && (!$api))
        {
            $q ="UPDATE ".PREFIX_TBL."_tags SET actif='non',uid_maj='".$gl_uid."', dte_maj='".now()."' WHERE id_obj='".$data["oid"]."' AND type_obj='".$data["type"]."' AND id_tag='".$k."'";
	        $sql->Update($q);
            error_log($q);
            $result["data"][$k]="removed";
        }
    }


/*
    $data=checkVar("data","array");
    $formation=new formation_class($id,$sql);
    $formation->Valid("description",$data["description"]);
    $formation->Valid("status",$data["status"]);
    $formation->Valid("enac",$data["enac"]);
    $formation->Save();

    $result=array("status"=>200);
*/
}
else if ($_SERVER['REQUEST_METHOD']=="PATCH")
{
/*
    // Créer une nouvelle version de la formation
    $formation_src=new formation_class($id,$sql);
    $formation_dst=new formation_class(0,$sql);
    $formation_dst->Valid("description",$formation_src->val("description"));
    $formation_dst->Valid("version",$formation_src->val("version")+1);
    $formation_dst->Valid("status",'draft');
    $formation_dst->Valid("enac",$formation_src->val("enac"));
    $formation_dst->Save();

    $formation_src->Valid("status",'closed');
    $formation_src->Save();

    $newid=$formation_dst->id;

    $result=array("status"=>200,"id"=>$newid);

    // Copie la liste des séances (référence)
    $lst=ListeReference($sql,$id);

    foreach($lst as $i=>$d)
    {
        $ref_src=new reference_class($d["id"],$sql);
        $ref_dst=new reference_class(0,$sql);

        $ref_dst->valid("idformation",$newid);
        $ref_dst->valid("refffa", $ref_src->val("refffa"));
        $ref_dst->valid("theme", $ref_src->val("theme"));
        $ref_dst->save();
    }
    $result["count"]["reference"]=count($lst);

    // Copie la liste des exercices
    $search=array("actif"=>"oui","idformation"=>$id);
    $lst=ListeExercicesConf($sql,$search,array("id"));

    $result["count"]["progression"]=0;
    foreach($lst as $i=>$d)
    {
        $exo_src=new exercice_conf_class($d["id"],$sql);
        $exo_dst=new exercice_conf_class(0,$sql);

        $exo_dst->valid("idformation", $newid);
        $exo_dst->valid("focus", $exo_src->val("focus"));
        $exo_dst->valid("description", $exo_src->val("description"));
        $exo_dst->valid("competence", $exo_src->val("competence"));
        $exo_dst->valid("compcat", $exo_src->val("compcat"));
        $exo_dst->valid("type", $exo_src->val("type"));
        $exo_dst->valid("module", $exo_src->val("module"));
        $exo_dst->valid("refffa", $exo_src->val("refffa"));
        $exo_dst->valid("refenac", $exo_src->val("refenac"));    
        $exo_dst->save();

        // Copie les progressions
        $lst_prog=ListeProgression($sql,$exo_src->id);
        foreach($lst_prog as $ii=>$dd)
        {
            $prog_src=new exercice_prog_class($dd["id"],$sql);
            $prog_dst=new exercice_prog_class(0,$sql);

            $prog_dst->valid("idexercice", $exo_dst->id);
            $prog_dst->valid("refffa", $prog_src->val("refffa"));
            $prog_dst->valid("progression", $prog_src->val("progression"));    
            $prog_dst->save();
        }
        $result["count"]["progression"]=$result["count"]["progression"]+count($lst_prog);
    }
    $result["count"]["exercice"]=count($lst);
*/


}


echo json_encode($result);
