﻿<?php
// inclui a conexão
include_once('conectar.php');

// Abre o Arquvio no Modo r (para leitura)
$arquivo = fopen ('depreciacao.csv', 'r');

// Lê o conteúdo do arquivo
while(!feof($arquivo))
{
	
	// Pega os dados da linha
	$linha = fgets($arquivo, 1024);

	// Divide as Informações das celulas para poder salvar
	$dados = explode(';', $linha);

	ini_set('max_execution_time', 300);
	// Verifica se o Dados Não é o cabeçalho ou não esta em branco
	
	$codigo = explode(" - ", $dados[4]);
	$codigo_banco = $codigo[0];
	
	$data = date('Y-m-d', strtotime($dados[7]));

	mysqli_query($conexao_com_banco, "INSERT INTO tb_bem_patrimonial
	VALUES ('$dados[0]','$dados[1]', '$dados[2]', '$dados[3]', '$codigo_banco', '$dados[5]', '$dados[6]', '$data', '$dados[8]', '$dados[9]', '$dados[10]', '$dados[11]', '$dados[12]', '$dados[13]', '$dados[14]', '$dados[15]')") 
	or die(mysqli_error($conexao_com_banco));
	
}
echo "acabou tudo";
// Fecha arquivo aberto
fclose($arquivo);
?>