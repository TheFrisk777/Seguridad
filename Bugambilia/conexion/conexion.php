<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';
function db(): mysqli {
    static $conn=null;
    if ($conn instanceof mysqli) { return $conn; }
    $user=getenv('DB_USER'); $pass=getenv('DB_PASSWORD');
    if (!$user || strtolower($user)==='root' || !$pass || $pass==='REPLACE_OUTSIDE_GIT') { throw new RuntimeException('Invalid database configuration'); }
    mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
    $conn=new mysqli(getenv('DB_HOST') ?: '127.0.0.1',$user,$pass,getenv('DB_NAME') ?: 'bugambilia',(int)(getenv('DB_PORT') ?: 3306));
    $conn->set_charset('utf8mb4');
    return $conn;
}
