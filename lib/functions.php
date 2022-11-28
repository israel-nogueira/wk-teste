<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use app\mariaDB;


	
if(isset($_POST['functions'])){
	switch ($_POST['functions']) {

		case 'listaUsuarios':
			$_SELECT = new mariaDB();
			$_SELECT->connect();
			$_SELECT->set_table('USUARIOS');
			$_RESULT = $_SELECT->dataTable($_POST['oAjaxData']); 
			die(json_encode($_RESULT,true));
		break;
		case 'excluiPedido':

			$DELETE = new mariaDB();
			$DELETE->connect();

			$DELETE->set_table('PEDIDOS_PRODUTOS');
			$DELETE->set_where('CODE_PEDIDO="'.$_POST['CODE'].'"');
			$DELETE->prepare_delete();


			$DELETE->set_table('PEDIDOS');
			$DELETE->set_where('CODE="'.$_POST['CODE'].'"');
			$DELETE->prepare_delete();

			$DELETE->transaction(function ($ERROR) {
				print_r($ERROR);
			});
			$DELETE->execQuery();
			


		break;
		
		case 'listaPedidos':
			$_SELECT = new mariaDB();
			$_SELECT->connect();
			$_SELECT->set_table('PEDIDOS');
			$_SELECT->set_colum('PEDIDOS.*');

			$_SELECT->set_colum('USUARIOS.NOME AS NOME_USUARIO');
			$_SELECT->set_colum('PEDIDOS_STATUS.STATUS AS STATUS');

			$_SELECT->join('INNER','USUARIOS',		'USUARIOS.CODE=PEDIDOS.CODE_USUARIO');
			$_SELECT->join('INNER','PEDIDOS_STATUS','PEDIDOS_STATUS.CODE=PEDIDOS.CODE_STATUS');

			$_RESULT = $_SELECT->dataTable($_POST['oAjaxData']); 
			die(json_encode($_RESULT,true));

		break;
		case 'listaProdutos':
			$_SELECT = new mariaDB();
			$_SELECT->connect();
			$_SELECT->set_table('PRODUTOS');
			$_RESULT = $_SELECT->dataTable($_POST['oAjaxData']); 
			die(json_encode($_RESULT,true));

		break;

		case 'salvaCampoProduto':
			$UPDATE = new mariaDB();
			$UPDATE->connect();
			$UPDATE->set_table('PRODUTOS');
			$UPDATE->set_update($_POST["COLUNA"],$_POST["VALOR"]);
			$UPDATE->set_where('CODE="'.$_POST["ORIGINAL"]['CODE'].'"');
			$UPDATE->update();
		break;

		case 'salvaCampoUsuario':
			$UPDATE = new mariaDB();
			$UPDATE->connect();
			$UPDATE->set_table('USUARIOS');
			$UPDATE->set_update($_POST["COLUNA"],$_POST["VALOR"]);
			$UPDATE->set_where('CODE="'.$_POST["ORIGINAL"]['CODE'].'"');
			$UPDATE->update();
		break;

		case 'mudatSatusPedido':
			$UPDATE = new mariaDB();
			$UPDATE->connect();
			$UPDATE->set_table('PEDIDOS');
			$UPDATE->set_update('CODE_STATUS',$_POST["CODE_STATUS"]);
			$UPDATE->set_where('CODE="'.$_POST["CODE"].'"');
			$UPDATE->update();
		break;

		case 'addProdutoPedido':

			//------------------------------------------------------------------
			//	PESQUISAMOS SE JÁ EXISTE PRODUTO NESSE PEDIDO
			//------------------------------------------------------------------
			$_SELECT = new mariaDB();
			$_SELECT->connect();
			$_SELECT->set_table('PEDIDOS_PRODUTOS');
			$_SELECT->set_where('CODE_PEDIDO="'.$_POST['CODE_PEDIDO'].'" AND CODE_PRODUTO="'.$_POST['PRODUTO'][0].'"');
			$_SELECT->select('param');


			//------------------------------------------------------------------
			//	SE NÃO EXISTIR, ACRESCENTAMOS UM
			//------------------------------------------------------------------
			if($_SELECT->_num_rows==0){
				$INSERT = new mariaDB();
				$INSERT->connect();
				$INSERT->set_table("PEDIDOS_PRODUTOS");
				$INSERT->set_insert('CODE_PEDIDO', $_POST['CODE_PEDIDO']);
				$INSERT->set_insert('CODE_PRODUTO', $_POST['PRODUTO'][0]);
				$INSERT->set_insert('QTDD_PRODUTO',1);
				$INSERT->insert();
			}else{
			//------------------------------------------------------------------
			//	SE EXISTIR ACRESCENTAMOS UM NO VALOR JÁ EXISTENTE
			//------------------------------------------------------------------
				$_PEDIDO = $_SELECT->fetch_array('param')[0];
				$UPDATE = new mariaDB();
				$UPDATE->connect();
				$UPDATE->set_table('PEDIDOS_PRODUTOS');
				$UPDATE->set_update('QTDD_PRODUTO',(intVal($_PEDIDO['QTDD_PRODUTO'])+1));
				$UPDATE->set_where('CODE_PEDIDO="'.$_POST['CODE_PEDIDO'].'"');
				$UPDATE->set_where('AND CODE_PRODUTO="'.$_POST['PRODUTO'][0].'"');
				$UPDATE->update();
			}

		break;

		case 'novoPedido':

			$INSERT = new mariaDB();
			$INSERT->connect();
			$INSERT->set_table("PEDIDOS");
			$INSERT->set_insert_form($_POST['form']);
			$INSERT->insert();

			$_SELECT = new mariaDB();
			$_SELECT->connect();
			$_SELECT->set_table('PEDIDOS');
			$_SELECT->set_where('ID="'.$INSERT->last_id().'"');
			$_SELECT->select('param');
			$_RESULT = $_SELECT->fetch_array('param')[0];
			die(json_encode($_RESULT,true));
			
		break;
		case 'novoUsuario':

			$INSERT = new mariaDB();
			$INSERT->connect();
			$INSERT->set_table("USUARIOS");
			$INSERT->set_insert_form($_POST['form']);
			$INSERT->insert();

			$_SELECT = new mariaDB();
			$_SELECT->connect();
			$_SELECT->set_table('USUARIOS');
			$_SELECT->set_where('ID="'.$INSERT->last_id().'"');
			$_SELECT->select('param');
			$_RESULT = $_SELECT->fetch_array('param')[0];
			die(json_encode($_RESULT,true));
		break;

		case 'novoProduto':

			$INSERT = new mariaDB();
			$INSERT->connect();
			$INSERT->set_table("PRODUTOS");
			$INSERT->set_insert_form($_POST['form']);
			$INSERT->insert();

			$_SELECT = new mariaDB();
			$_SELECT->connect();
			$_SELECT->set_table('PRODUTOS');
			$_SELECT->set_where('ID="'.$INSERT->last_id().'"');
			$_SELECT->select('param');
			$_RESULT = $_SELECT->fetch_array('param')[0];
			die(json_encode($_RESULT,true));
			
		break;

		default:
			die('["Função invalida"]');
		break;
	}
}