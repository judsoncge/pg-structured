<?php 
include('../head.php');
include('../body.php'); 
verificar_permissao_pagina($_SESSION['permissao-visualizar-financeiro'], $conexao_com_banco);
$lista = retorna_despesas($conexao_com_banco);
?>

<!-- Conteúdo da Página -->
<div id="page-content-wrapper">
	<div class="container titulo-pagina">
		<p>Todas as despesas do órgão</p>
	</div>
	
	<?php include('tabela.php')?>

</div>

<?php include('../foot.php')?>