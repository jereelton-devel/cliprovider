<?php

/*Cliente de Exemplo*/

function showCli($data) {
	echo date("d-m-Y H:i:s")." ".$data;
}

function dataRequest($param1, $param2, $type) {

	#curl
	$token = "X0F1F2F3F4F5F56F7F8F9F0";
	$url   = "https://[DOMAIN]/cliprovider/server.php?type={$type}&token={$token}&param1={$param1}&param2={$param2}";
	$init  = curl_init($url);

	showCli("[Running] request: {$url}\n");

	curl_setopt($init, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($init, CURLOPT_SSL_VERIFYPEER, 0);
	/*curl_setopt($init, CURLOPT_POST, 1);
    curl_setopt($init, CURLOPT_POSTFIELDS, [$this->numberPlayer => $this->numberPlayer]);*/
	curl_setopt($init, CURLOPT_URL, $url);

	$getResponse = curl_exec($init);

	curl_close($init);

	return $getResponse;

}

function showDetails($logname, $rs) {

	echo "\n!!!!!!!!!!!!!![DETAILS]!!!!!!!!!!!!!!!\n";

	$tmp = json_decode(base64_decode($rs));

	foreach ($tmp as $key => $value) {
		foreach ($value as $k => $v) {
			echo $v.";";
			file_put_contents($logname, $v.";", FILE_APPEND);
		}
		echo "\n";
		file_put_contents($logname, "\n", FILE_APPEND);
	}
	file_put_contents($logname, "SEPARATOR;SEPARATOR;SEPARATOR;SEPARATOR;SEPARATOR;SEPARATOR;SEPARATOR;SEPARATOR;SEPARATOR;SEPARATOR;\n", FILE_APPEND);

}

showCli("[Wait] App Client is Initializing...\n\n");

if($argv[1] == "help") {
	echo("(HELP) App Client\n");
	echo("Use: php client [command] [param1] [param2]\n\n");
	echo("[Example]\n\n");
	echo("php client getdata csvfilename\n");
	exit;
}

if($argc < 3) {
	echo("[Error] Missing Parameters...\n");
	echo("Use: php client getdata csvfilename\n");
	echo("Use: php client help for view all commands\n");
	die("Abort Proccess!\n\n");
}

$script_run  = trim($argv[0]);
$command_run = trim($argv[1]);
$csvfilename = trim($argv[2]);

if(!$csvfilename) {
	showCli("[Erro] Please inform a valid csv filename");
	goto endapp;
}

if($command_run == "getdata" && $csvfilename == "tb_cad_ing_range.csv") {

	$arquivoCSV  = $csvfilename;

	$logname = "log/error_found_details-".date("dmYHis").".log";

	$delimitador = ',';
	$cerca = '"';

	$cep_uniq = [];
	$cep_duplic = [];

	$contador = 0;
	$contador_erro = 0;

	$f = fopen($arquivoCSV, 'r');

	$cabecalho = fgetcsv($f, 0, $delimitador, $cerca);

	while (!feof($f)) {

		$linha = fgetcsv($f, 0, $delimitador, $cerca);

		if (!$linha || empty($linha)) {
			continue;
		}

		$registro = array_combine($cabecalho, $linha);

		if (count($registro) == 0) {
			continue;
		}

		$contador++;

		$cep_ini = $registro["cep_inicial"];
		$cep_fin = $registro["cep_final"];

		echo "-----------------------------------------------------------------------------------------------------------------\n";
		echo "CURRENT: [{$contador}]\n";

		$result_ini = dataRequest($cep_ini, $cep_ini, 'consulta_duplicidade_cep');
		$result_fin = dataRequest($cep_fin, $cep_fin, 'consulta_duplicidade_cep');

		showCli("[Running] response: QTY: {$result_ini} for cep_ini = {$cep_ini}\n");
		showCli("[Running] response: QTY: {$result_fin} for cep_fin = {$cep_fin}\n");
		showCli("[Running] response: DETAILS: UF: {$registro['uf']}, CIDADE: {$registro['cidade']}, RANGE: {$registro['data_range']}\n");

		if($result_ini == 7) {
			//array_push($cep_uniq, $result_ini);
			echo "* status: [OK] for cep_ini = {$cep_ini}\n";
		}

		if($result_ini == 14) {
			//array_push($cep_duplic, $result_ini);
			echo "\n===> status: [ERRO] for cep_ini = {$cep_ini}\n";

			//TODO: Obter detalhes
			$result_details = dataRequest($cep_ini, $cep_ini, 'consulta_detalhes_duplicidade_cep');
			showDetails($logname, $result_details);
			$contador_erro++;

		}

		if($result_fin == 7) {
			//array_push($cep_uniq, $result_fin);
			echo "* status: [OK] for cep_fin = {$result_fin}\n";
		}

		if($result_fin == 14) {
			//array_push($cep_duplic, $result_fin);
			echo "\n===> status: [ERRO] for cep_fin = {$result_fin}\n";

			//TODO: Obter detalhes
			$result_details = dataRequest($cep_fin, $cep_fin, 'consulta_detalhes_duplicidade_cep');
			showDetails($logname, $result_details);
			$contador_erro++;
		}

		echo "-----------------------------------------------------------------------------------------------------------------\n\n";

	}

	//file_put_contents("log/cep_uni.log", print_r($cep_uniq));
	//file_put_contents("log/cep_duplic.log", print_r($cep_duplic));

	fclose($f);

	goto endapp;

}

if($command_run == "getdata" && $csvfilename == "tb_cad_cidades_join_tb_cad_uf.csv") {

	$arquivoCSV  = $csvfilename;

	$logname = "log/error_found_details-td_cidades-".date("dmYHis").".log";

	$delimitador = ',';
	$cerca = '"';

	$cep_uniq = [];
	$cep_duplic = [];

	$contador = 0;
	$contador_erro = 0;

	$f = fopen($arquivoCSV, 'r');

	$cabecalho = fgetcsv($f, 0, $delimitador, $cerca);

	while (!feof($f)) {

		$linha = fgetcsv($f, 0, $delimitador, $cerca);

		if (!$linha || empty($linha)) {
			continue;
		}

		$registro = array_combine($cabecalho, $linha);

		if (count($registro) == 0) {
			continue;
		}

		$contador++;

		$cep_ini = $registro["cidade_codigo_municipio"];
		$cep_fin = $registro["cidade_codigo_municipio"];
		$cidade  = $registro["cidade_nome"];
		$uf      = $registro["estado_nome"];

		echo "-----------------------------------------------------------------------------------------------------------------\n";
		echo "CURRENT: [{$contador}]\n";

		$result_ini = dataRequest($cep_ini, $cep_ini, 'consulta_duplicidade_cep');

		showCli("[Running] response: QTY: {$result_ini} for cep_ini = {$cep_ini}\n");
		showCli("[Running] response: DETAILS: UF: {$registro['estado_nome']}, CIDADE: {$registro['cidade_nome']}\n");

		if($result_ini == 7) {
			//array_push($cep_uniq, $result_ini);
			echo "* status: [OK] for cep_ini = {$cep_ini}\n";
		}

		if($result_ini == 14) {
			//array_push($cep_duplic, $result_ini);
			echo "\n===> status: [ERRO] for cep_ini = {$cep_ini}\n";

			file_put_contents($logname, "CEP_ATUAL;{$cep_ini};;;;;;;;;\n", FILE_APPEND);

			//TODO: Obter detalhes
			$result_details = dataRequest($cep_ini, $cep_ini, 'consulta_detalhes_duplicidade_cep');
			showDetails($logname, $result_details);
			$contador_erro++;

		}

		echo "-----------------------------------------------------------------------------------------------------------------\n\n";

	}

	//file_put_contents("log/cep_uni.log", print_r($cep_uniq));
	//file_put_contents("log/cep_duplic.log", print_r($cep_duplic));

	fclose($f);

	goto endapp;

}

showCli("[Error] Invalid Command...\n");
showCli("Use: php client getdata param1 param2\n");
showCli("Abort Proccess!\n\n");

endapp:
showCli("[Running] TOTAL PROCESSED: {$contador}, ERROR FOUND: {$contador_erro}\n\n");
showCli("[Done] App Client is Finished...\n\n");
exit;

?>
