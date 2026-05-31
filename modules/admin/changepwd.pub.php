<?php
    $token=checkVar("token","varchar");

    // Check RESET token
    $gl_uid=verifyToken($token,"token_resetpwd",true);

    if ($gl_uid==-1)
    {
        $ret=array(
            "status"=>403,
            "message"=>"Token rejected"
        );
        http_response_code(403);
        echo json_encode($ret);
        exit;
    }


    // Update user password

    $pwd=checkVar("password","varchar");
    $usr = new user_core($gl_uid,$sql,false);
    $ret=$usr->SavePassword($pwd);

    $ret=array(
        "status"=>200,
        "message"=>"User password changed"
    );
    echo json_encode($ret);

?>