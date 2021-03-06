<?php 
include('../componentes/sessao/iniciar-sessao.php'); 
include('header.php'); 
include('body-padrao.php');
$permissao = retorna_permissao_pessoa($_SESSION['CPF'],'visualizar_financeiro',$conexao_com_banco); if($permissao=='Não'){
	echo "<script>history.back();</script>";
	echo "<script>alert('Você não tem permissão para estar nesta página')</script>";
}
?>

<!-- Conteúdo da Página -->
<div id="page-content-wrapper">
	<div class="container titulo-pagina">
		<p>Combustíveis</p>
	</div>
	<div class="container caixa-conteudo">
		<div class="row">
			<div class="col-lg-12">
				<div class="container">
					<div class="well">
						<div class="row">
							<div class="col-sm-10">
								<div class="input-group margin-bottom-sm">
									<span class="input-group-addon"><i class="fa fa-search fa-fw"></i></span> <input type="text" class="input-search form-control" alt="tabela-dados" placeholder="Buscar placa, modelo, renavam, ano ou condutor do veículo" id="search"/>
								</div>
							</div>
							<div class="col-sm-2 col-xs-12 pull-right">
								<a href="cadastro-combustivel.php?sessionId=<?php echo $num ?>" class="btn btn-sm btn-info pull-right"><i class="fa fa-plus-circle"></i> Novo Combustível</a>
							</div>
						</div>
					</div>
					<div class="col-md-12 table-responsive" style="overflow: auto; width: 100%; height: 320px;">
						<table class="table table-hover tabela-dados">
							<thead>
								<tr>
									<th>Placa do veículo</th>
									<th>Data de abastecimento</th>
									<th>Valor pago</th>
									<th><center>Ver detalhes</center></th>
									
									<th id="ano">Ano</th>
								</tr>	
							</thead>
							<tbody>
								<?php $lista = retorna_dados("combustivel", $conexao_com_banco);
								while($r = mysqli_fetch_object($lista)){ ?>
								<tr>
									<td><?php echo $r -> Veiculo_placa ?></td>
									<td><?php echo arruma_data($r -> data_abastecimento) ?></td>
									<td>R$<?php echo arruma_numero($r -> valor) ?></td>
																										
									<td><center><button id='abre-modal' type='button' class='btn btn-default btn-sm' data-toggle='modal' 
									data-target='#<?php echo $r -> id ?>'><i class='fa fa-eye' aria-hidden='true'></i></button></center></td>

									<td id="ano-item"><?php echo $r -> ano ?></td>
								</tr>

								<div class='modal fade' id='<?php echo $r -> id ?>' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
								<div class='modal-dialog modal-lg'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
											<h4 class='modal-title' id='myModalLabel'> Combustível do veículo <?php echo $r -> Veiculo_placa ?></h4>
										</div>
										<div class='modal-body'>
										<div class="row">
												<div class="col-md-3">
													<label class="control-label" for="exampleInputEmail1">Placa do veículo: <b><?php echo $r -> Veiculo_placa ?></b></label>
												</div>												
												<div class="col-md-3">
													<label class="control-label" for="exampleInputEmail1">Data de abastecimento: <b><?php echo arruma_data($r -> data_abastecimento) ?></b></label>
												</div>
												<div class="col-md-3">
													<label class="control-label" for="exampleInputEmail1">Valor do litro abastecido: <b>R$ <?php echo arruma_numero($r -> valor_litro) ?></b></label>
												</div>																
											
												<div class="col-md-3">
													<label class="control-label" for="exampleInputEmail1">Quantidade de litros: <b><?php echo $r -> quantidade_litro ?></b></label>
												</div>
										
										</div>
										</div>
										
										<div class='modal-footer'>
										
										<?php $permissao = retorna_permissao_pessoa($_SESSION['CPF'],'cadastrar_financeiro',$conexao_com_banco); if($permissao=='Sim'){ ?>								
										<div class="row">
												
											<div class="col-md-6">
													<a href='edita-combustivel.php?sessionId=<?php echo $num ?>&combustivel=<?php echo $r->id ?>' name='submit' value='Send' id='btn-editar'><button type='submit' class='btn btn-sm btn-default btn-block pull-right' name='submit' value='Send' id='btn-editar'>Editar combustível &nbsp;&nbsp;<i class='fa fa-pencil-square' aria-hidden='true'></i></button></a>
											</div>	
											<div class="col-md-6">
													<a href='../componentes/combustivel/logica/excluir.php?id=<?php echo $r -> id ?>' name='submit' value='Send' id='btn-editar'><button type='submit' class='btn btn-sm btn-default btn-block pull-right' name='submit' value='Send' id='btn-editar'>Excluir combustível &nbsp;&nbsp;<i class='fa fa-pencil-square' aria-hidden='true'></i></button></a>
											</div>	
										
										</div>	
										
										
										
													
													<hr>
														
														<div class="row">
															<?php $falta = retorna_falta_empenhar($r -> valor, $r -> id , $conexao_com_banco); ?>
															<form name="cadastro" method="POST" action="../componentes/empenho/logica/cadastrar.php?despesa=<?php echo $r-> id ?>&valor=<?php echo $falta ?>" enctype="multipart/form-data">
																	<div class="col-md-3">
																		Ainda falta empenhar: R$<?php echo arruma_numero($falta); ?>
																	</div>
																	<div class="col-md-3">
																		<input class="form-control" id="empenho" name="empenho" placeholder="Número de empenho" type="text" maxlength="" required/>
																	</div>
																	<div class="col-md-3">
																		<div class="form-group">
																			<input class="form-control" id="valor" onkeypress="mascara(this,mreais)" name="valor" placeholder="Valor a ser empenhado" type="float" maxlength="" required/>
																			<div id="msg" ></div>
																		</div>  
																	</div>
																	<div class="col-md-3">
																		<button type='submit' class='btn btn-sm btn-default btn-block pull-right' name='submit2' value='Send' id='btn-editar'>Efetuar empenho</button>
																	</div>	
															</form>				
														</div>
											
											
													<hr>
														
														
										
											<?php } ?>	
											</div>	
									</div>
								</div>
							</div>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
</div>

</div>
<!-- /#Conteúdo da Página/-->

</div>
<!-- /#wrapper -->


<!-- chamando o bootstrap novamente para o modal funcionar -->
<script type="text/javascript" src="js/bootstrap.js"></script>

<script type="text/javascript">
	/*menu lateral*/
	$("#menu-toggle").click(function(e) {
		e.preventDefault();
		$("#wrapper").toggleClass("toggled");
	});
</script>

<?php include('footer.php')?>