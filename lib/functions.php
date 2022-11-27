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

		default:
			die('["Função invalida"]');
		break;
	}
}