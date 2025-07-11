<?php
use App\Config\Security;
//echo'he llegado al recurso user';
echo json_encode(Security::secretKey());
 ?>