<?
    use app\mariaDB;
    $inner_HTML->HEADER= "PEDIDOS";

//------------------------------------------------------------------
//    STATUS
//------------------------------------------------------------------
$_SELECT = new mariaDB();
$_SELECT->connect();
$_SELECT->set_table('PEDIDOS_STATUS');
$_SELECT->select();
$_STATUS = $_SELECT->fetch_array()['response'];

//------------------------------------------------------------------
//    ENCAIXAMOS NO SELECTBOX
//------------------------------------------------------------------

foreach ($_STATUS as $USER) {
    $inner_HTML->CODE = $USER['CODE'];
    $inner_HTML->STATUS = $USER['STATUS'];
    $inner_HTML->block('SELECT_STATUS');
}
