﻿<?php
include('../head.php');
include('../body.php');
verificar_permissao_pagina($_SESSION['permissao-visualizar-todos-processos'], $conexao_com_banco);
?>

	<script type="text/javascript" src="<?php echo $ROOT ?>/interface/js/jspdf.min.js"></script>
	<script type="text/javascript" src="<?php echo $ROOT ?>/interface/js/jspdf.plugin.autotable.js"></script>
	<script type="text/javascript" src="<?php echo $ROOT ?>/interface/js/jquery.techbytarun.excelexportjs.min.js"></script>
	
<script type="text/javascript">
var pagina = 0, max = 100, status = '', lugar = '', setor = '', situacao = '';

function iniciar_carregamento() {
	$('#tabela-processos').hide();
	$('#paginacao').hide();
	$('#nao-encontrado').hide();
	$('#erro-busca').hide();
	$('#carregando').show();
}

function finalizar_carregamento() {
	$('#carregando').hide();
	$('#tabela-processos').show();	
	$('#paginacao').show();	
}

function listar_processos(nova_pagina) {
	iniciar_carregamento();
	if (!nova_pagina) {
		pagina = 0;
	} else {
		pagina = nova_pagina;
	}
	var query = $('#search').val().replace(/\*/g, "%");
	$.ajax({ 
	url: url + '/sistema/servicos/processos.php',
	data: {
	 'acao': 'Listar',
	 'query':query,
	 'lugar': lugar,
	 'status': status,
	 'offset': pagina * max,
	 'setor': setor,
	 'situacao': situacao,
	 'max': max
	 },
    dataType: 'json',
	type: 'get',
	success: function(data, status, resposta) {
		finalizar_carregamento();
		$('#lista-processos').empty();
		for (var i = 0; i < data.length; i++) {
			data[i].NM_SERVIDOR_LOCALIZACAO = data[i].NM_SERVIDOR_LOCALIZACAO !== null ? data[i].NM_SERVIDOR_LOCALIZACAO : '';
		var toAppend = '';
		if(data[i].NM_SITUACAO === 'Análise em atraso' || data[i].NM_SITUACAO_FINAL === 'Finalização em atraso') { 
			toAppend += '<tr style="background-color: #e74c3c; color:white;" >';
		} else {
			toAppend += '<tr>';
		}
		if (data[i].URGENTE === '0') {
			toAppend += '<td> </td>';
		} else {
			toAppend += '<td class="text-center"><i class="fa fa-exclamation-triangle"><i></td>';	
		}
		toAppend += '<td>' + data[i].CD_PROCESSO + '</td>' +
														
		'<td>' + data[i].NM_SERVIDOR_LOCALIZACAO + '</td>' +	
		'<td class="text-center">' + data[i].CD_SETOR_LOCALIZACAO + '</td>' +	
		'<td class="text-center">' + data[i].NM_SITUACAO + '</td>' +
		'<td class="text-center">' + data[i].NM_SITUACAO_FINAL + '</td>' +
		'<td class="text-center">' + data[i].NR_DIAS + '</td>' +	
		'<td>' +
			'<center>' +
				'<a href="detalhes.php?processo=' + data[i].CD_PROCESSO + '">' + 
					'<button id="detalhes" type="button" class="btn btn-default btn-sm">' +
						'<i class="fa fa-eye" aria-hidden="true"></i>' +
					'</button>' +
				'</a>' +
			'</center>' +
		'</td>' +
		'<td><center>';
		if (data[i].EDITAR) {
			toAppend += '<a href="editar.php?processo=' + data[i].CD_PROCESSO + '">' +
				'<button type="button" class="btn btn-secondary btn-sm" title="Editar">' +
					'<i class="fa fa-pencil" aria-hidden="true"></i>' +
				'</button>' +
			'</a>';
		} else { 
			toAppend += '-';
		}
		if (data[i].EXCLUIR) {
			toAppend += '<a href="logica/excluir.php?processo=' + data[i].CD_PROCESSO + '" onclick="return confirm(\'Você tem certeza que deseja apagar este processo?\');">' +
				'<button type="button" class="btn btn-secondary btn-sm" title="Excluir">' +
					'<i class="fa fa-trash" aria-hidden="true"></i>' +
				'</button>' +
			'</a>';
		} else { 
			toAppend += '-';
		}
		toAppend += '</center></td>' +
			'</tr>';			
			$('#lista-processos').append(toAppend);
		}
		atualizar_total(resposta.getResponseHeader('total'));
	},
	 error: function () {
		$('#tabela-processos').hide();
		$('#paginacao').hide();
		$('#nao-encontrado').hide();
		$('#carregando').hide();
		$('#erro-busca').show();
    }
	});
}

function criar_paginacao() {
	$('#paginacao').empty();
	var pagina_final = parseInt(total / max);
	var inicio = inicio > pagina_final - 6 ? pagina_final - 6 : inicio;
	inicio = pagina < 4 ? 0 : pagina - 3;
	
	var i = inicio;
	$('#paginacao').append('<li><a href="javascript:void(0);" onclick="listar_processos(' + 0 + ')">Inicio</a></li>');
	if (inicio !== 0) {
		$('#paginacao').append('<li><a href="javascript:void(0);" onclick="listar_processos(' + (inicio - 1) + ')">&laquo; </a></li>');
	} else {
		$('#paginacao').append('<li class="disabled"><a href="javascript:void(0);">&laquo;</a></li>');
	}
	for (; i <= pagina_final && i < inicio + 7; i++) {
		$('#paginacao').append();
		if (i === pagina) {
		$('#paginacao').append('<li><a href="javascript:void(0);" onclick="listar_processos(' + i + ')"><b><u>' + i + '<u></b></a></li>');
		} else {
		$('#paginacao').append('<li><a href="javascript:void(0);" onclick="listar_processos(' + i + ')">' + i + '</a></li>');
		}
	}
	if (i < total / max ) {
		$('#paginacao').append('<li><a href="javascript:void(0);" onclick="listar_processos(' + i + ')">&raquo</a></li>');
	} else {
		$('#paginacao').append('<li class="disabled"><a href="javascript:void(0);">&raquo</a></li>');		
	}
	$('#paginacao').append('<li><a href="javascript:void(0);" onclick="listar_processos(' + pagina_final + ')">Fim</a></li>');
}

window.onload = function() {
	if (getParameterByName('query')) {
		servidor = getParameterByName('query');		
	}
	listar_processos();
	
	//Solução extraída de https://stackoverflow.com/questions/10318575/jquery-search-as-you-type-with-ajax e https://stackoverflow.com/questions/574941/best-way-to-track-onchange-as-you-type-in-input-type-text
	var thread = null;
	$('#search').on('keyup cut paste', function() {
		iniciar_carregamento();
		clearTimeout(thread);thread = setTimeout(function(){listar_processos()}, 1000);
	});
	
	$(window).click(function() {
			 $(".menu-vertical-menu").hide();
	});

	$('.menu-vertical-menu').click(function(event){
		event.stopPropagation();
	});
	
	$('.menu-vertical-toggle').click(function(event){	
		event.stopPropagation();	
		$(this).closest('.menu-vertical-drop').children('.menu-vertical-menu').toggle();
	});
	
}

function listar_lugar(novo_lugar) {
	
	if (novo_lugar !== 'todos') {
		lugar = novo_lugar;
	} else {
		lugar = '';
	}
	$('.lugar').removeClass('ativo');
	$('#listar-lugar-' + novo_lugar).addClass('ativo');
	if (novo_lugar !== 'setor') {
		iniciar_carregamento();
		thread = setTimeout(function(){listar_processos()}, 1000);
	}
	
}

function listar_setor(novo_setor) {
	listar_lugar('setor');
	$('#setor-' + novo_setor).addClass('ativo');
	setor = novo_setor;
	iniciar_carregamento();
	thread = setTimeout(function(){listar_processos()}, 1000);
}

function listar_status(novo_status) {
	if (novo_status !== 'todos') {
		status = novo_status;
	} else {
		status = '';
	}
	$('.status').removeClass('ativo');
	$('#listar_status_' + novo_status).addClass('ativo');
	iniciar_carregamento();
	thread = setTimeout(function(){listar_processos()}, 1000);
}

function listar_situacao(nova_situacao) {
	if (nova_situacao !== 'todos') {
		situacao = nova_situacao;
	} else {
		situacao = '';
	}
	$('.situacao').removeClass('ativo');
	$('#listar-situacao-' + nova_situacao).addClass('ativo');
	iniciar_carregamento();
	thread = setTimeout(function(){listar_processos()}, 1000);
}


function listar_total(limite) {
	max = limite;
	$('.limite').removeClass('ativo');
	$('#limite_' + limite).addClass('ativo');
	iniciar_carregamento();
	thread = setTimeout(function(){listar_processos()}, 1000);
}

function atualizar_total(novo_total) {
	$('#total').empty().append('Total: ' + novo_total);
	total = novo_total;
	criar_paginacao();
	if (total == 0) {
		$('#tabela-processos').hide();
		$('#paginacao').hide();
		$('#nao-encontrado').show();
	} else if (total <= max) {
		$('#tabela-processos').show();
		$('#paginacao').hide();
		$('#nao-encontrado').hide();
	} else {
		$('#tabela-processos').show();
		$('#paginacao').show();
		$('#nao-encontrado').hide();
	}
}

var exportando = false;
function exportando_tabela() {
	exportando = true;
	$('#export-button').addClass('disabled');
	$('#exportar').hide();
	$('#exportando').show();
}

function exportar(sucesso) {
	var query = $('#search').val().replace(/\*/g, "%");
	if (!exportando){
		exportando_tabela();
	$.ajax({ 
	url: url + '/sistema/servicos/processos.php',
	data: {
	 'acao': 'Listar PDF',
	 'query':query,
	 'lugar': lugar,
	 'status': status,
	 'setor': setor,
	 'situacao': situacao,
	 },
	dataType: 'json',
	type: 'get',
	success: function(data) {
		sucesso(data);
	},
	 error: function () {
		$('#export-button').removeClass('disabled');
		$('#exportar').show();
		$('#exportando').hide();
		exportando = false;
    }
	});
	}
}

function exportar_tipo(tipo) {
	$('.menu-vertical-menu').hide();
	if (tipo === 'pdf') {
		exportar(exportar_tabela_pdf);
	} else if (tipo === 'xls') {
		exportar(exportar_tabela_xls);
	}
}

function exportar_tabela_pdf(data) {
		var rows = [];
		var toAppend = '<html><body>' +
		'<table>' +
			'<thead>' +
				'<tr>' +
					'<th> <th>' +
					'<th>Processo</th>' +
					'<th>Está com</th>' +
					'<th class="text-center">No setor</th>' +
					'<th class="text-center">Situação</th>' +
					'<th class="text-center">Situação Final</th>' +
					'<th class="text-center">Dias no órgão</th>' +
				'</tr>	' +
			'</thead>' +
			'<tbody>';
		
		for (var i = 0; i < data.length; i++) {
			data[i] = completar_dados(data[i]);
			rows.push([data[i].CD_PROCESSO, data[i].NM_SERVIDOR_LOCALIZACAO, data[i].CD_SETOR_LOCALIZACAO, data[i].NM_SITUACAO, data[i].NM_SITUACAO_FINAL, data[i].NR_DIAS]);
		}		
		var doc = new jsPDF('p', 'pt');

		doc.autoTable(['Processo', 'Está com', 'No setor', 'Situação', 'Situação Final', 'Dias no órgão'], rows,
		{
		styles: {overflow: 'linebreak', halign: 'middle', valign: 'middle'},
		columnStyles: {
			id: {fillColor: [41, 128, 185]},
			halign: 'middle',
			valign: 'middle',
			0: {halign: 'left'},
		},
		alternateRowStyles: {
			halign: 'middle'
		}
		});
		doc.save('Lista de processos.pdf');
		$('#export-button').removeClass('disabled');
		$('#exportar').show();
		$('#exportando').hide();
		exportando = false;
}

function exportar_tabela_xls(data) {	
		for (var i = 0; i < data.length; i++) {
			data[i] = completar_dados(data[i]);
		}
		var uri = $("#dvjson").excelexportjs({
			containerid: "dvjson"
			, datatype: 'json'
			, dataset: data
			, returnUri: true
			, columns: [
				{ headertext: "Processo", datatype: "string", datafield: "CD_PROCESSO", ishidden: false }
				, { headertext: "Está com", datatype: "string", datafield: "NM_SERVIDOR_LOCALIZACAO", width: "100px" }
				, { headertext: "No setor", datatype: "string", datafield: "CD_SETOR_LOCALIZACAO", width: "100px" }
				, { headertext: "Situação", datatype: "string", datafield: "NM_SITUACAO", width: "100px" }
				, { headertext: "Situação Final", datatype: "string", datafield: "NM_SITUACAO_FINAL", width: "100px" }
				, { headertext: "Dias no órgão", datatype: "string", datafield: "NR_DIAS", width: "100px" }
				
			]
		});
		$('#relatorio-xls').attr('download', 'Lista de processos.xls').attr('href', uri).attr('target', '_blank');
		$('#relatorio-xls')[0].click();	
		$('#relatorio-xls').removeAttr('download').attr('href', 'javascript:void(0)').removeAttr('target');
		$('#export-button').removeClass('disabled');
		$('#exportar').show();
		$('#exportando').hide();
		exportando = false;
}

function completar_dados(processo) {
	if (!processo.CD_PROCESSO) {
		processo.CD_PROCESSO = '';
	}	
	if (!processo.NM_SERVIDOR_LOCALIZACAO) {
		processo.NM_SERVIDOR_LOCALIZACAO = '';
	}
	if (!processo.CD_SETOR_LOCALIZACAO) {
		processo.CD_SETOR_LOCALIZACAO = '';
	}
	if (!processo.NM_SITUACAO) {
		processo.NM_SITUACAO = '';
	}
	if (!processo.NM_SITUACAO_FINAL) {
		processo.NM_SITUACAO_FINAL = '';
	}
	if (!processo.NR_DIAS) {
		processo.NR_DIAS = '';
	}
	return processo;
}

</script>

 
<div id="page-content-wrapper">

	<div class="container titulo-pagina">
		<p>Processos</p>
	</div>
	
	<?php include('../includes/mensagem.php'); ?>
	<div class="container caixa-conteudo">
		<div class="row">
			<div class="col-lg-12">
				<div class="container">
					<div class="well">						
						<div class="row">
							<div class="col-sm-8">
								<div class="input-group margin-bottom-sm">
									<span class="input-group-addon"><i class="fa fa-search fa-fw"></i></span> <input type="text" placeholder="Buscar por número do processo ou situação ou situação final" id="search" autofocus="autofocus" class="input-search form-control"/>
								</div>
							</div>
							<?php if($_SESSION['permissao-abrir-processo']=='sim'){ ?>
								<div class="col-sm-2 col-xs-12 pull-right">
									<a href="cadastrar.php" class="btn btn-sm btn-info pull-right"><i class="fa fa-plus-circle"></i> Processo</a>
								</div>
							<?php } ?>
							<div class="col-sm-2 col-xs-12 pull-right">
							<div class="menu-vertical-drop pull-right">
									<button href="javascript:void(0);" id="export-button" class="btn btn-sm btn-info  menu-vertical-toggle" >
										<span id="exportar"><i class="fa fa-file"></i> Exportar</span>
										<span id="exportando" style="display:none;"><i class="fa fa-refresh spinner" aria-hidden="true"></i> Gerando...</span>
									</button>
									<ul class="menu-vertical-menu" aria-labelledby="dropdownMenu1">		
									<li>
										<a id="relatorio-pdf" href="javascript:void(0);" onclick="exportar_tipo('pdf');" class="btn btn-sm btn-info">
										<i class="fa fa-file-pdf-o"></i> PDF
										</a>
									</li>
									<li>
										<a id="relatorio-xls" href="javascript:void(0);" onclick="exportar_tipo('xls');" class="btn btn-sm btn-info">
											<i class="fa fa-file-excel-o"></i> Excel	
										</a>							
									</li>
									</ul>
							</div>		
							</div>
						</div>
						
						<div class="row">
							<div class="col-sm-4 filter-opt">
								<div><h6>Localização</h6></div>
								<div class="btn-group search lugar" role="group">
									  <a href="javascript:void(0);" onclick = "listar_lugar('todos');")><button type="button" id="listar-lugar-todos" class="btn ativo botao-comunicacao lugar">Todas</button></a>
									  
									  <div class="menu-vertical-drop" style="display: inline-block;width: 117px;">									
										<a href="javascript:void(0);">
										<button type="button" id="listar-lugar-setor" class="btn botao-comunicacao lugar menu-vertical-toggle">Setor</button></a>									
											<ul class="menu-vertical-menu" >
												<?php $lista = retorna_setores($conexao_com_banco);
												while($r = mysqli_fetch_object($lista)){ ?>
												<li><a><button type="button" id="setor-<?php echo $r->CD_SETOR ?>" class="btn botao-comunicacao lugar menu-vertical-toggle" onclick="listar_setor('<?php echo $r->CD_SETOR ?>')"><?php echo $r->CD_SETOR ?></button></a></li><?php } ?>		
											</ul>
									  </div>
									  
									  
									  <a href="javascript:void(0);" onclick = "listar_lugar('comigo');"><button type="button" id="listar-lugar-comigo" class="btn botao-comunicacao lugar">Comigo</button></a>									 							  
								</div>
							</div>
							<div class="col-sm-4 filter-opt">
								<div><h6>Status</h6></div>
								<div class="btn-group search" role="group">
									  <a href="javascript:void(0);" onclick = "listar_status('todos');"><button type="button" id="listar_status_todos" class="btn botao-comunicacao status ativo">Todos</button></a>
									  <a href="javascript:void(0);" onclick = "listar_status('saiu');"><button type="button" id="listar_status_saiu" class="btn botao-comunicacao status">Saíram</button></a>
									  <a href="javascript:void(0);" onclick = "listar_status('saiupol');"><button type="button" id="listar_status_saiupol" class="btn botao-comunicacao status">Saíram pol.</button></a>
									  <a href="javascript:void(0);" onclick = "listar_status('arquivado');"><button type="button" id="listar_status_arquivado" class="btn botao-comunicacao status">Arquivados</button></a>							  
								</div>
							</div>
							<div class="col-sm-4 filter-opt">
								<div><h6>Quantidade por Página</h6></div>
								<div class="btn-group search" role="group">
									  <a href="javascript:void(0);" onclick = "listar_total(100);")><button type="button" id="limite_100" class="btn ativo botao-comunicacao limite">100</button></a>
									  <a href="javascript:void(0);" onclick = "listar_total(50);")><button type="button" id="limite_50" class="btn botao-comunicacao limite">50</button></a>
									  <a href="javascript:void(0);" onclick = "listar_total(10);"><button type="button" id="limite_10" class="btn botao-comunicacao limite">10</button></a>
																  
								</div>
							</div>
						</div>
					<div class="row">
							<div class="col-sm-12 filter-opt">
								<div><h6>Situação final</h6></div>
								<div class="btn-group search lugar" role="group">
									  <a href="javascript:void(0);" onclick = "listar_situacao('todos');")><button type="button" id="listar-situacao-todos" class="btn ativo botao-comunicacao situacao">Todas</button></a>
									  <a href="javascript:void(0);" onclick = "listar_situacao('aberto');")><button type="button" id="listar-situacao-aberto" class="btn botao-comunicacao situacao">Aberto</button></a>
									  <a href="javascript:void(0);" onclick = "listar_situacao('andamento');")><button type="button" id="listar-situacao-andamento" class="btn botao-comunicacao situacao">Finalização em andamento</button></a>
									  <a href="javascript:void(0);" onclick = "listar_situacao('andamento-atraso');")><button type="button" id="listar-situacao-andamento-atraso" class="btn botao-comunicacao situacao">Finalização em atraso</button></a>
									  <a href="javascript:void(0);" onclick = "listar_situacao('finalizado');")><button type="button" id="listar-situacao-finalizado" class="btn botao-comunicacao situacao">Finalizado</button></a>
									  <a href="javascript:void(0);" onclick = "listar_situacao('finalizado-atraso');")><button type="button" id="listar-situacao-finalizado-atraso" class="btn botao-comunicacao situacao">Finalizado com atraso</button></a>
									  
									  									 							  
								</div>
							</div>
							
						</div>
					
					</div>
					<div class="col-md-12">
							<div id="carregando" class="carregando"><i class="fa fa-refresh spin" aria-hidden="true"></i> <span>Carregando dados...</span></div>
							<div id="nao-encontrado" class="carregando"><i class="fa fa-search-minus" aria-hidden="true"></i> <span>Não foram encontradas ocorrências para essa busca.</span></div>
							<div id="erro-busca" class="carregando"><i class="fa fa-times" aria-hidden="true" style="color: #e74c3c;"></i> <span>O servidor se comportou de maneira inesperada e não foi possível completar sua busca.</br> Caso o erro persista, entre em contato com o suporte.</span></div>
					</div>
					<div class="col-md-12 table-responsive" id="tabela-processos" style="overflow: auto; width: 100%; height: 300px;">
						<table class="table table-hover tabela-dados">
							<thead>
								<tr>
									<th> </th>
									<th>Processo</th>
									<th>Está com</th>
									<th class="text-center">No setor</th>
									<th class="text-center">Situação</th>
									<th class="text-center">Situação Final</th>
									<th class="text-center">Dias no órgão</th>
									<th class="text-center">+</th>
									<th class="text-center"><i class="fa fa-pencil" aria-hidden="true"></i></th>
								</tr>	
							</thead>
							<tbody id="lista-processos">
									
							</tbody>
						</table>
					</div>					
				</div>
				<ul class="paginacao" id="paginacao"></ul>
				
			</div>
		</div>
	</div>
<div class="pull-right" style="margin-right: 50px; margin-top: 20px;" id="total"></div>
	
</div>
<div id="dvjson"> </div>


<?php include('../foot.php')?>