<?
    use app\mariaDB;
    $inner_HTML->HEADER     = "DETALHES DO PEDIDO";
    $inner_HTML->CODE_PEDIDO = $_GET['CODE'];
    $inner_JS->CODE_PEDIDO = $_GET['CODE'];

//------------------------------------------------------------------
//    STATUS
//------------------------------------------------------------------
    $_SELECT = new mariaDB();
    $_SELECT->connect();
    $_SELECT->set_table('PRODUTOS');
    $_SELECT->select();
    $_STATUS = $_SELECT->fetch_array()['response'];

//------------------------------------------------------------------
//    ENCAIXAMOS NO SELECTBOX
//------------------------------------------------------------------

foreach ($_STATUS as $USER) {
    $inner_HTML->VALUE = $USER['CODE'].','.$USER['NOME'].','.$USER['VALOR'];
    $inner_HTML->NOME = $USER['NOME'];
    $inner_HTML->block('SELECT_PRODUTOS');
}

//------------------------------------------------------------------
//	PUXAMOS OS PEDIDOS
//------------------------------------------------------------------

    $_SELECT = new mariaDB();
    $_SELECT->connect();
    $_SELECT->set_table('PEDIDOS_PRODUTOS LINK');
    $_SELECT->set_colum('LINK.*');
    $_SELECT->set_colum('PRODUTOS.NOME');
    $_SELECT->set_colum('PRODUTOS.VALOR');
    $_SELECT->set_colum('LINK.QTDD_PRODUTO');
    $_SELECT->set_colum('(PRODUTOS.VALOR * LINK.QTDD_PRODUTO) AS TOTAL');
    $_SELECT->join('INNER','PEDIDOS',' PEDIDOS.CODE=LINK.CODE_PEDIDO');
    $_SELECT->join('INNER','PRODUTOS',' PRODUTOS.CODE=LINK.CODE_PRODUTO');
    $_SELECT->join('INNER','USUARIOS',' USUARIOS.CODE=PEDIDOS.CODE_USUARIO');
    $_SELECT->set_where('CODE_PEDIDO="'.$_GET['CODE'].'"');
    $_SELECT->select('param');
    $_RESULT = $_SELECT->fetch_array('param');

//------------------------------------------------------------------
//	INSERIMOS OS PRODUTOS DENTRO DA TABELA
//------------------------------------------------------------------
    $_TOTAL = 0;
    foreach ($_RESULT as $PEDIDO){
        $inner_HTML->CODE_PEDIDO    = $PEDIDO['CODE_PEDIDO'];
        $inner_HTML->CODE_PRODUTO   = $PEDIDO['CODE_PRODUTO'];
        $inner_HTML->NOME_PRODUTO   = $PEDIDO['NOME'];
        $inner_HTML->VALOR_PRODUTO  = $PEDIDO['VALOR'];
        $inner_HTML->VALOR_TOTAL    = $PEDIDO['TOTAL'];
        $inner_HTML->QTDD_PRODUTO   = $PEDIDO['QTDD_PRODUTO'];
        $inner_HTML->block('TABELA_PEDIDOS');
        //------------------------------------------------------------------
        //	VAMOS SOMANDO O VALOR TOTAL FINAL
        //------------------------------------------------------------------
        $_TOTAL = $_TOTAL + floatVal($PEDIDO['TOTAL']);
    }

     $inner_HTML->GET_TOTAL =  $_TOTAL;

//------------------------------------------------------------------
//	PUXA OS DADOS DO CLIENTE
//------------------------------------------------------------------


$_SELECT = new mariaDB();
$_SELECT->connect();
$_SELECT->set_table('USUARIOS'); 
$_SELECT->set_colum('USUARIOS.*');
$_SELECT->join('INNER','PEDIDOS',' PEDIDOS.CODE="'.$_GET['CODE'].'"');
$_SELECT->set_where('USUARIOS.CODE=PEDIDOS.CODE_USUARIO');
$_SELECT->select('param');
$_RESULT = $_SELECT->fetch_array('param')[0];

$inner_HTML->USER_CODE          = $_RESULT['CODE'];
$inner_HTML->USER_NOME          = $_RESULT['NOME'];
$inner_HTML->USER_CPF           = $_RESULT['CPF'];
$inner_HTML->USER_ENDERECO      = $_RESULT['ENDERECO'];
$inner_HTML->USER_EMAIL         = $_RESULT['EMAIL'];
$inner_HTML->USER_NASCIMENTO    = $_RESULT['NASCIMENTO'];


$_SELECT = new mariaDB();
$_SELECT->connect();
$_SELECT->set_table('PEDIDOS'); 
$_SELECT->set_where('CODE="'.$_GET['CODE'].'"');
$_SELECT->select('param');
$_RESULT = $_SELECT->fetch_array('param')[0];
$inner_HTML->DATA_PEDIDO = $_RESULT['DATA_PEDIDO'];