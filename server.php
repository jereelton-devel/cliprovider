<?php

/*Servidor de exemplo*/

session_start();

header("Access-Control-Allow-Origin: *");

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('memory_limit', -1);
set_time_limit(0);

/*Include|Require Libs*/

$conn = new ConexaoMysql();

$request = $_REQUEST['type'];
$token   = $_REQUEST['token'];
$cep_ini = $_REQUEST['param1'];
$cep_fin = $_REQUEST['param2'];

if($token != "X0F1F2F3F4F5F56F7F8F9F0") {
    die("Acesso negado");
}

if($request == "consulta_duplicidade_cep") {

    $sql = "
        SELECT
            *
        FROM
            [TABLENAME]
        WHERE
            (
                CAST({$cep_ini} AS UNSIGNED INTEGER) >= CAST(cep_inicial AS UNSIGNED INTEGER)
                AND
                CAST({$cep_fin} AS UNSIGNED INTEGER) <= CAST(cep_final AS UNSIGNED INTEGER)
            );";

    $result = $conn->getData($sql);

    //file_put_contents('log/data_request.log', "Consultado: {$cep_ini} - {$cep_fin}\r\nSql: {$sql}\r\n", FILE_APPEND);

    echo count($result);

    exit;

}

if($request == "consulta_detalhes_duplicidade_cep") {

    $sql = "
        SELECT
            *
        FROM
            [TABLENAME]
        WHERE
            (
                CAST({$cep_ini} AS UNSIGNED INTEGER) >= CAST(cep_inicial AS UNSIGNED INTEGER)
                AND
                CAST({$cep_fin} AS UNSIGNED INTEGER) <= CAST(cep_final AS UNSIGNED INTEGER)
            );";

    $result = $conn->getData($sql);

    //file_put_contents('log/data_request.log', "Consultado: {$cep_ini} - {$cep_fin}\r\nSql: {$sql}\r\n", FILE_APPEND);

    echo base64_encode(json_encode($result));

    exit;

}

echo false;
exit;

?>

