<?php

	require_once __DIR__ . '/vendor/autoload.php';
	use app\template;
	use app\mariaDB;

	//--------------------------------------------------------------
    // URL A QUAL ESTAMOS ACESSANDO
	//--------------------------------------------------------------
		$REQUEST_URI = trim($_SERVER['REQUEST_URI'],'/');
		$PATH= ($REQUEST_URI=="") ? "home" : $_SERVER['REQUEST_URI'];
		define('URL_PATH',$PATH);
		
    //--------------------------------------------------------------
    // CARREGAMOS NA MEMORIA O TEMPLATE DO DESKTOP
    //--------------------------------------------------------------
		$_DESKTOP = new template(__DIR__ . '/assets/html/desktop.html');
	
    //--------------------------------------------------------------
    // BUSCAMOS AGORA OS ARQUIVOS REFERENTES A URL
    //--------------------------------------------------------------
		$_TEMPLATE	= __DIR__ .'/assets/html/'.URL_PATH . '.html';
		$_PAGE		= __DIR__ .'/assets/php/'.URL_PATH . '.php';

		if (file_exists($_TEMPLATE)) {
			$inner_CSS  = new Template($_TEMPLATE, true);
			$inner_JS   = new Template($_TEMPLATE, true);
			$inner_HTML = new Template($_TEMPLATE, true);
		} else {
			$inner_JS   = new Template('<!-- inner JS -->', true);
			$inner_CSS  = new Template('<!-- inner CSS -->', true);
			$inner_HTML = new Template('<!-- inner HTML -->', true);
		}

    //--------------------------------------------------------------
    // CASO EXISTA, CARREGAMOS O PHP REFERENTE DA URL
    //--------------------------------------------------------------

		if (file_exists($_PAGE)) include_once $_PAGE;

	//--------------------------------------------------------------
	// PROCESSA OS BLOCOS SEPARADAMENTE
	//--------------------------------------------------------------

		$inner_HTML	->block('TEMPLATE');
		$inner_HTML	->block('CONTENT');
		$inner_CSS	->block('STYLES');
		$inner_JS	->block('SCRIPTS');

	//--------------------------------------------------------------
	// VAMOS SETAR O CONTEUDO DO MIOLO DENTRO DO OBJETO DESKTOP
	//--------------------------------------------------------------

		if ($_DESKTOP->exists("CONTENT"))	$_DESKTOP->CONTENT	= $inner_HTML->parse();
		if ($_DESKTOP->exists("SCRIPTS"))	$_DESKTOP->SCRIPTS	= $inner_JS->parse();
		if ($_DESKTOP->exists("STYLES"))	$_DESKTOP->STYLES	= $inner_CSS->parse();

	//------------------------------------------------------------------
	//	CUSPIMOS NA TELA O CONTEÚDO INTEIRO RANDERIZADO
	//------------------------------------------------------------------
		$_DESKTOP->show();