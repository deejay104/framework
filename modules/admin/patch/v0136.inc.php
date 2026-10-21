<?php

    $sql2 = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

//    $txt="edite->oui,description->,dte_deb->2026-05-31 14:30,dte_fin->2026-05-31 17:00,uid_pilote->1,uid_debite->0,uid_instructeur->0,uid_avion->1,destination->LOCAL,taxe->none,taxeok->non,nbpersonne->3,invite->non,accept->non,reel->oui,temps->0,tarif->P,tpsestime->60,tpsreel->0,horadeb->0,horafin->0,potentiel->0,carbavant->0,carbapres->0,prixcarbu->0,uid_maj->1,dte_maj->2026-05-31 11:03:00";
//    $id=13852;

    $q="SELECT id,comment FROM ".$MyOpt["tbl"]."_historique";
    $sql->Query($q);

    for($i=0; $i<$sql->rows; $i++)
    { 
        $sql->GetRow($i);
        $decoded = json_decode($sql->data["comment"], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Already a JSON
        }
        else
        {
            $q="UPDATE ".$MyOpt["tbl"]."_historique SET comment='".preg_replace("/'/"," ",json_encode(txtToArray($sql->data["comment"]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))."' WHERE id='".$sql->data["id"]."'";
            $sql2->Update($q);        
        }
    
    }




function txtToArray(string $txt): array
{
    $result = [];
    
    foreach (explode(',', $txt) as $pair) {
        if (trim($pair) === '') {
            continue; // ignore les éléments vides (ex. virgule finale)
        }
        // limite à 2 morceaux : seule la première "->" sépare clé et valeur
        $parts = explode('->', $pair, 2);
        $key   = trim($parts[0]);
        $value = $parts[1] ?? '';
        $result[$key] = $value;
    }
    
    return $result;
}
    
?>