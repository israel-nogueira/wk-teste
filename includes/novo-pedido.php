<?
	use app\mariaDB;   
    $inner_HTML->HEADER= "CADASTRO DE NOVO PEDIDO";

    //------------------------------------------------------------------
    //	BUSCAMOS OS DADOS DO USUARIO
    //------------------------------------------------------------------
    $_SELECT = new mariaDB();
    $_SELECT->connect();
    $_SELECT->set_table('USUARIOS'); 
    $_SELECT->select();   
    $_USUARIO = $_SELECT->fetch_array()['response'];

    //------------------------------------------------------------------
    //	ENCAIXAMOS NO SELECTBOX OS USUARIOS
    //------------------------------------------------------------------

    foreach($_USUARIO as $USER){
             $inner_HTML->CODE=$USER['CODE'];
             $inner_HTML->NOME=$USER['NOME'];
             $inner_HTML->block('SELECT_USERS');
    }
    //------------------------------------------------------------------
    //	STATUS
    //------------------------------------------------------------------
    $_SELECT = new mariaDB();
    $_SELECT->connect();
    $_SELECT->set_table('PEDIDOS_STATUS'); 
    $_SELECT->select();   
    $_STATUS = $_SELECT->fetch_array()['response'];

    //------------------------------------------------------------------
    //	ENCAIXAMOS NO SELECTBOX OS STATUS
    //------------------------------------------------------------------

    foreach($_STATUS as $USER){
             $inner_HTML->CODE=$USER['CODE'];
             $inner_HTML->STATUS=$USER['STATUS'];
             $inner_HTML->block('SELECT_STATUS');
    }