<?php
use App\DB\connectionDB;
use App\Config\Security;
 //echo "he llegado al recurso auth";
 echo json_encode(Security::secretKey());
 echo json_encode(Security::createPassword('Hola'));


 //validancdo contraseña
 $pass = Security::createPassword('Hola');
 if (Security::validatePassword('Hola',$pass)){
    echo json_encode('contraseña correcta');
 }else{
    echo json_encode('contraseña incorrecta');
 }

 //probando el jwt
 //echo Security::createTokenJwt(Security::secretKey(),['hola]);
 echo(json_encode(Security::createTokenJwt(Security::secretKey(),['Hola'])));

connectionDB::getConnection();


 ?>


