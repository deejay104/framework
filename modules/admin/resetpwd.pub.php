<?php
    $ret=array(
        "status"=>200,
        "message"=>""
    );

    $email=checkVar("email","varchar");
    $token=checkVar("token","varchar");

    $nbreq=5;    // Nombre de requêtes autorisées par IP
    $minwait=10; // Temps avant nouvelle autorisation de changement de mot de passe

    // Check POST token
/*
    $tid=verifyToken($token,"token_post",true);
    if ($tid==-1)
    {
        $ret=array(
            "status"=>403,
            "message"=>"Token rejected"
        );
        http_response_code(403);
        echo json_encode($ret);
        exit;
    }
*/

    // Get number of change from the same ip during the last 5 min
    if (!isset($_SERVER[$MyOpt["ipfield"]]))
    {
        echo json_encode(array("status"=>500,"message"=>"configuration"));
        exit;
    }
    $q="SELECT COUNT(*) AS nb FROM ".$MyOpt["tbl"]."_token_post WHERE client_ip='".$_SERVER[$MyOpt["ipfield"]]."' AND dte_creat>='".date('Y-m-d H:i:s', strtotime('-5 minutes'))."'";
    $res=$sql->QueryRow($q);

    if ($res["nb"]>$nbreq)
    {
        echo json_encode(array("status"=>502,"message"=>"token"));
        exit;
    }
    else
    {
        $ret["count_ip"]=$res["nb"];
    }


    // Get user info from the email
    $q="SELECT id,mail,dte_resetpwd FROM ".$MyOpt["tbl"]."_utilisateurs WHERE mail='".$email."' AND actif='oui'";
    $res=$sql->QueryRow($q);

    if (!isset($res["id"]))
    {
        echo json_encode(array("status"=>502,"message"=>"user"));
        exit;
    }

    $ret["uid"]=$res["id"];
    $ret["mail"]=$res["mail"];

    // Check last password update, skip if less than 10 min
    if (date_diff_txt($res["dte_resetpwd"],date("Y-m-d H:i:s"))<60*$minwait)
    {
        echo json_encode(array("status"=>403,"message"=>"waittime"));
        exit;
    }

    // Generate temp session token
    if ($ret["uid"]>0)
    {
        $q="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET dte_resetpwd='".now()."' WHERE id=".$ret["uid"];
        $sql->Update($q);
    
        // Generate a temp token with a 15 minutes validity
        $token=generateToken($ret["uid"],15,$base="token_resetpwd");

        // Send email
        $tabvar=array(
            "url"=>$MyOpt["host"]."/login.php?fonc=resetpwd&token=".$token,
        );
        $r=SendMailFromFile($MyOpt["from_email"],$ret["mail"],"","",$tabvar,"resetpwd");
    
        $ret["message"]=($r) ? "sent" : "email";
    }
    echo json_encode($ret);

?>