<?
namespace app;
class mariaDB
{
	public function __construct()
	{
		$this->_conectMySQLi_		= null;
		$this->like					= (object) array();
		$this->InsertVars			= array();
		$this->Insert_like			= array();
		$this->Insert_Update		= array();
		$this->fetch_array			= array();
		$this->obj					= array();
		$this->variaveis			= array();
		$this->setcolum				= '';
		$this->debug				= true;
		$this->decode				= false;
		$this->utf8					= 'none';
		$this->url					= 'none';
		$this->slashes				= 'none';
		$this->rell					= '';
		$this->ignore				= array();
		$this->on_duplicate			= array();
		$this->DISTINCT				= '';
		$this->query				= '';
		$this->replace_isso			= array();
		$this->group				= array();
		$this->replace_porisso		= array();
		$this->where				= null;
		$this->set_where_not_exist	= null;
		$this->primarykey			= null;
		$this->settable				= [];
		$this->setwhere				= null;
		$this->table				= null;
		$this->SP_RETURN			= null;
		$this->sp_response			= [];
		$this->SP					= null;
		$this->SP_PARAMS			= null;
		$this->view_query			= null;
		$this->CONECT_PARAMS		= [getenv('ADMIN_DB_SERVER'),getenv('ADMIN_DB_USER'),getenv('ADMIN_DB_PASS'),getenv('ADMIN_DB_NAME')];
		$this->transactionFn		= false;
		$this->rollbackFn			= false;
		$this->colunmToJson			= [];
		$this->subQueryAlias		= null;
		$this->subQuery				= [];

		return $this;
	}

	#################################################################################################################
	# FUNÇÕES SP
	#################################################################################################################

	public function __call($_name, $arguments){
		if (in_array($_name, get_class_methods('mariaDB'))) {
			return $this->$_name(...$arguments);
		} else {
			if (substr(strtolower($_name), 0, 3) == 'sp_' && strtolower($_name) != 'sp_response') {
				$this->SP			= $_name;
				$this->SP_PARAMS	= $arguments;

				return $this;
			} else {
				throw new RuntimeException('MariaDB error: Função '.$_name.' desconhecida');
			}
		}
	}

    public function preventMySQLInject($string)
    {
      	//	$script = array(' OR ', ' FROM ', ' SELECT ', ' INSERT ', ' DELETE ', ' WHERE ', ' DROP TABLE ', ' SHOW TABLES ', '*', '--', '=');
     	//	$string = (!get_magic_quotes_gpc()) ? addslashes(str_ireplace($script, "", $string)) : str_ireplace($script, "", $string);
        //	return mysqli_real_escape_string($this->_conectMySQLi_, $string);
		return $string;
    }


	
	public function connection_close()
	{
		if (!is_null($this->_conectMySQLi_)) {
				$this->_conectMySQLi_->close();
		}
	}



	public function connect($server=null,$user=null,$pass=null,$name=null){   
		//service mysql status
		//service mysql start
		$this->CONECT_PARAMS[0] = $server	??	getenv('ADMIN_DB_SERVER');
		$this->CONECT_PARAMS[1] = $user		??	getenv('ADMIN_DB_USER');
		$this->CONECT_PARAMS[2] = $pass		??	getenv('ADMIN_DB_PASS');
		$this->CONECT_PARAMS[3] = $name		??	getenv('ADMIN_DB_NAME');

		
		$this->connection_close();
		$this->_conectMySQLi_ = new \mysqli($this->CONECT_PARAMS[0], $this->CONECT_PARAMS[1], $this->CONECT_PARAMS[2], $this->CONECT_PARAMS[3]);
		if ($this->_conectMySQLi_->connect_error) {throw new RuntimeException("Connection failed: " . mysqli_error($this->_conectMySQLi_->connect_error));}
		$this->_conectMySQLi_->query("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;");
		return $this;
	}
	public function clear()
	{
		$this->like					= (object) array();
		$this->InsertVars			= array();
		$this->Insert_like			= array();
		$this->Insert_Update		= array();
		$this->setcolum				= '';
		$this->debug				= true;
		$this->decode				= false;
		$this->utf8					= 'none';
		$this->url					= 'none';
		$this->slashes				= 'none';
		$this->rell					= '';
		$this->ignore				= array();
		$this->on_duplicate			= array();
		$this->DISTINCT				= '';
		$this->replace_isso			= array();
		$this->replace_porisso		= array();
		$this->where				= null;
		$this->set_where_not_exist	= null;
		$this->primarykey			= null;
		$this->settable				= [];
		$this->colum				= '';
		$this->setwhere				= null;
		$this->table				= null;
	}

	
	public function transaction($return='none'){
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		mysqli_begin_transaction($this->_conectMySQLi_,MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT);
		mysqli_autocommit($this->_conectMySQLi_, false);
		$this->transactionFn = true;
		$this->rollbackFn = $return;
	}

	public function rollbackExec($return){
		if($this->rollbackFn!='none'){
			call_user_func_array($this->rollbackFn,array($return));
		}
	}

	public function createSP($name,$params,$sql){
		return 'DELIMITER 
			//
				DROP PROCEDURE IF EXISTS '.$name.';
			//
			CREATE PROCEDURE '.$name.'('.implode(',',$params).')
			BEGIN '.PHP_EOL.$sql.PHP_EOL.'
			END //
			DELIMITER ;';

	}

	public function last_id(){
		return $this->_conectMySQLi_->insert_id;
	}
	public function sp_response($variable = null)
	{
		if ($variable != null) {
			return $this->sp_response[$variable];
		} else {
			return $this->sp_response;
		}
	}

	public function getCollumns($tabela='result'){
		 $this->query = 'SHOW COLUMNS FROM `'.$this->table.'`;';
		$consulta = mysqli_query($this->_conectMySQLi_, $this->query);
		if (mysqli_error($this->_conectMySQLi_)) {throw new RuntimeException(mysqli_error($this->_conectMySQLi_));}
		$this->_num_rows = $this->_num_row = @$consulta->num_rows;
		$array = [];
		if($this->_num_rows>=1){
			while ($row = mysqli_fetch_assoc($consulta)) {
				$array[] = $row;
			}
		}
		$this->fetch_array[$tabela] 	=	$array;
		$this->obj[$tabela] 			= 	(object)$array;
		$this->output[] = $this->query;
		$this->connection_close();
		$this->clear();
		return $this;
	}

	public function fetch_array($variable = null){
		if ($variable != null) {
			return $this->fetch_array[$variable] ??[];
		} else {
			return $this->fetch_array ??[];
		}
	}
	public function fetch_obj($variable = null){
		if ($variable != null) {
			return json_encode($this->fetch_array[$variable],JSON_BIGINT_AS_STRING)??[];
		} else {
			return json_encode($this->fetch_array,JSON_BIGINT_AS_STRING)??[];
		}
	}

	public static function showTables(){
		$tables = array();
		$fire = new mariaDB();
		$fire->connect();
		$fire->select('TABLES','SHOW TABLES');
		
		foreach($fire->fetch_array()['TABLES'] as  $row ){$tables[] = array_values($row)[0];}
		return $tables;
	}


	
	public function getDataTables($tables='*'){
			$return = '';
			mysqli_query($this->_conectMySQLi_, "SET NAMES 'utf8'");
			if($tables == '*'){
				$tables = array();
				$result = mysqli_query($this->_conectMySQLi_, 'SHOW TABLES');
				while($row = mysqli_fetch_row($result)){
					$tables[] = $row[0];
				}
			}else{
				$tables = is_array($tables) ? $tables : explode(',',$tables);
			}
			foreach($tables as $table){
				$result = mysqli_query($this->_conectMySQLi_, 'SELECT * FROM '.$table);
				$num_fields = mysqli_num_fields($result);
				$num_rows = mysqli_num_rows($result);
				$row2 = mysqli_fetch_row(mysqli_query($this->_conectMySQLi_, 'SHOW CREATE TABLE '.$table));
				$return.= "\n\n".$row2[1].";\n\n";
				$counter = 1;
				$return.="\n\n\n";
			}
		return $return;
	}


	public function getInputsData($tables='*'){
		$return = '';
		mysqli_query($this->_conectMySQLi_, "SET NAMES 'utf8'");
		if ($tables == '*') {
			$tables = array();
			$result = mysqli_query($this->_conectMySQLi_, 'SHOW TABLES');
			while ($row = mysqli_fetch_row($result)) {
				$tables[] = $row[0];
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',', $tables);
		}
		foreach ($tables as $table) {
			$result		= mysqli_query($this->_conectMySQLi_, 'SELECT * FROM ' . $table);
			$num_fields = mysqli_num_fields($result);
			$num_rows	= mysqli_num_rows($result);
			$counter = 1;
			for ($i = 0; $i < $num_fields; $i++) { //Over rows
				while ($row = mysqli_fetch_row($result)) {
					if ($counter == 1) {
						$return .= 'INSERT INTO ' . $table . ' VALUES(';
					} else {
						$return .= '(';
					}
					for ($j = 0; $j < $num_fields; $j++) {
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n", "\\n", $row[$j]);
						if (isset($row[$j])) {$return .= '"' . $row[$j] . '"';} else { $return .= '""';}
						if ($j < ($num_fields - 1)) {$return .= ',';}
					}
					if ($num_rows == $counter) {
						$return .= ");\n";
					} else {
						$return .= "),\n";
					}
					++$counter;
				}
			}
			$return .= "\n\n\n";
		}
		return $return;
	}
	public function dataTable(array $oAjaxData=[]){

			// PREPARAMOS OS FILTROS DO DATATABLE

			$draw		= $oAjaxData['draw'];
			$colunas	= $oAjaxData['columns'];
			$start		= $oAjaxData['start'];
			$length		= $oAjaxData['length'];
			$search		= $oAjaxData['search']['value'];
			$order		= $oAjaxData['order'][0]['column'];
			$order		= $colunas[$order]['data'];
			$order		= [$order=>$oAjaxData['order'][0]['dir']];

			// RESGATAMOS O TOTAL DA BASE SEM FILTRO SEM NADA
			$numrow_sem_filtros			= clone $this;
			$numrow_sem_filtros->set_colum('COUNT(*) as TOTAL');
			
			// COLOCAR ISSO LÁ NA CHAMADA PRINCIPAL
			$allQuery = $numrow_sem_filtros->get_query();
			
			$numrow_sem_filtros->select();
			$_fetch_array = $numrow_sem_filtros->fetch_array() ?? [];
			$recordsTotal = ($_fetch_array!=[]) ? $numrow_sem_filtros->fetch_array()['response'][0]['TOTAL'] : 0;


			//INSERE AS PALAVRAS DE SEARCH 
			$busca_por_coluna = false;
			foreach ($colunas as $value) {
				if($value['search']['value']!=""){
					$busca_por_coluna = true;
					$this->like($value['data'],'%'.trim(preg_replace('/[\s]+/mu', '%',trim($value['search']['value']))).'%');
				}
			}
			if(strlen($search)>0){
				foreach($colunas as $value) {
					$this->like($value['data'],'%'.trim(preg_replace('/[\s]+/mu', '%',trim($search))).'%');
				}
			}
			
			//CASO NÃO TENHA WHERES AINDA, ADICIONAMOS 
			if($this->where==null){$this->set_where(' TRUE ');}

			//POSSIVEIS WHERE DE FORMULARIOS
			// $this->set_where('AND 2=2');
			// $this->set_where('AND 3=3');

			//ORDENAMOS PELAS COLUNAS 
			foreach($order as  $key=>$value) {
				$this->set_order($key,$value);
			}

			// TOTAL DE PESQUISA COM AS COLUNAS E PESQUISAS  
			if(strlen($search)>0 || $busca_por_coluna==true){
				$noPage = clone $this;
				$noPage->setcolum = [];
				$noPage->set_colum('COUNT(*) as TOTAL');
				$noPage->select();
				$fetch_array = $noPage->fetch_array()??[];
				$_FILTRADO 	= ($fetch_array!=[])?intVal($noPage->fetch_array()['response'][0]['TOTAL']):0;
			}else{
				$_FILTRADO 	= intVal($recordsTotal);
			}


			// AGORA TOTAL COM A PAGINAÇÃO 
			$this->set_limit($start,$length);
			$query = $this->get_query();
			$fire = new mariaDB();
			$fire->connect();
			$fire->select('DataTable',$query);

			$_TOTAL		= intVal($recordsTotal);
			return [
				"query"				=>	$query,
				"paginate"			=>	$start.' - '.$length,
				"draw"				=>	$draw,
				"recordsFiltered"	=>	$_FILTRADO,
				"recordsTotal"		=>	$_TOTAL,
				"data"				=>	$fire->fetch_array()['DataTable'] ?? []
			];
		}

	public function get_queries($variable = null)
	{
		if ($variable != null) {
			return $this->output[$variable];
		} else {
			return $this->output;
		}
	}
	public function execSP($alias='RESPONSE', $response=null){
		$this->connect();
		#---------------------------------------------------------------
		# TRATAMOS AS ENTRADAS
		#---------------------------------------------------------------
		foreach ($this->SP_PARAMS as $key=>$value) {

			if(is_array($value) || is_object($value)){

				$this->SP_PARAMS[$key] = "'".trim(json_encode($value,JSON_BIGINT_AS_STRING), '[]')."'";

			}elseif(is_numeric($value) || is_int($value) || is_float($value)){

				$this->SP_PARAMS[$key] = trim($value);

			}elseif(is_string($value)){

				$this->SP_PARAMS[$key] = "'".trim($value)."'";

			}elseif($value==null){

				$this->SP_PARAMS[$key] = 'NULL';
			}
		}

		if($response==null){
			$this->query = 'CALL ' . $this->SP . '(' . implode(',',$this->SP_PARAMS). ');';
		}else{
			$this->query = 'CALL ' . $this->SP . '(' . implode(',',$this->SP_PARAMS). ', @' . $response . ');';
		}
		// print_r($this->query);exit;
		
		$consulta = mysqli_query($this->_conectMySQLi_, $this->query);
		if (mysqli_error($this->_conectMySQLi_)) {
			throw new RuntimeException(mysqli_error($this->_conectMySQLi_));
		}
		$this->_num_rows = $this->_num_row = @$consulta->num_rows;
		$array = [];
		if($this->_num_rows>=1){
			while ($row = mysqli_fetch_assoc($consulta)) {
				$array[] = $row;
			}
		}
		//----------------------------------------------------
		$alias= $alias ?? 'RESPONSE';
		$this->fetch_array[$this->SP][$alias] 	=	$array;
		$this->obj[$this->SP][$alias] 			= 	(object)$array;
		$this->output[] = $this->query;

		if ($response != null) {
			$this->_conectMySQLi_->next_result();
			$_query = "SELECT * FROM CODE_LIST WHERE ID= @" . $response;
			$consulta = mysqli_query($this->_conectMySQLi_, $_query);
			if (mysqli_error($this->_conectMySQLi_)) {
				throw new RuntimeException(mysqli_error($this->_conectMySQLi_));
			}
			$array = [];
			while ($row = mysqli_fetch_assoc($consulta)) {
				$array[] = $row;
			}
			$this->fetch_array[$this->SP]['STATUS'] = $array;
			$this->obj[$this->SP]['STATUS'] 		= (object) $array;
			$this->output[] = $_query;
			$this->_conectMySQLi_->next_result();
		}
		$this->connection_close();
		$this->clear();
		return $this;
	}

	public function prepare_execSP($alias='RESPONSE', $response=null){
		$this->connect();
		#---------------------------------------------------------------
		# TRATAMOS AS ENTRADAS
		#---------------------------------------------------------------
		foreach ($this->SP_PARAMS as $key=>$value) {

			if(is_array($value) || is_object($value)){

				$this->SP_PARAMS[$key] = "'".trim(json_encode($value,JSON_BIGINT_AS_STRING), '[]')."'";

			}elseif(is_numeric($value) || is_int($value) || is_float($value)){

				$this->SP_PARAMS[$key] = trim($value);

			}elseif(is_string($value)){

				$this->SP_PARAMS[$key] = "'".trim($value)."'";

			}elseif($value==null){

				$this->SP_PARAMS[$key] = 'NULL';
			}
		}
		$this->query = 'CALL ' . $this->SP . '(' . implode(',',$this->SP_PARAMS). ');';
	}


	public function set_colum($COLUMNS = array(),$JSON=false)
	{
		if (empty($this->setcolum)) 		$this->setcolum = array();
		if (is_array($COLUMNS)) {
			$newColl = array();
			foreach ($COLUMNS as $col) {
				if (isset($col) && $col != "") {
					if (substr($col, 0, 8) == "command:") {

						$newColl[] = substr($value, 8);

						if($JSON==true){
							if(stripos(substr($value, 8),' as ')>-1){
								$this->colunmToJson[]=array_slice(preg_split("/ as /i",substr($value, 8)),-1)[0];
							}else{
								$this->colunmToJson[]=substr($value, 8);
							}
						}
					}else{
						$newColl[] = $col;
						if($JSON==true){
							if(stripos($col,' as ')>-1){
								$this->colunmToJson[]=array_slice(preg_split("/ as /i",$col),-1)[0];
							}else{
								$this->colunmToJson[]=$col;
							}					
						}
					}
				}
			}
			$this->setcolum[] = @implode(',', $newColl);
			 
			 
		} elseif (is_string($COLUMNS) && $COLUMNS != "") {
			if (substr($COLUMNS, 0, 8) == "command:") {
				$this->setcolum[] = substr($COLUMNS, 8);

				if($JSON==true){
					if(stripos(substr($COLUMNS, 8),' as ')>-1){
						$this->colunmToJson[]=array_slice(preg_split("/ as /i",$COLUMNS),-1)[0];
					}else{
						$this->colunmToJson[]= substr($COLUMNS, 8);
					}
				}
			} else {
				$this->setcolum[] = $COLUMNS;
				if($JSON==true){

					if(stripos($COLUMNS,' as ')>-1){


						$this->colunmToJson[]=array_slice(preg_split("/ as /i",$COLUMNS),-1)[0];
					}else{
						$this->colunmToJson[]=$COLUMNS;
					}
				}
			}
		}
		if (is_array($this->setcolum)) {
			$this->colum = @implode(',',$this->setcolum); // coloquei @ não porque da erro, mas aparece um NOTICE chato...
		} else {
			$this->colum = $this->setcolum;
		}
		if ($this->colum == "") {
			$this->colum = "*";
		}
	}

	public function set_table($TABLES, $ALIAS=null){
		if($ALIAS==null){
			$this->table =$TABLES;
		}else{
			$this->table =trim($TABLES).' as '.$ALIAS;
		}
		return $this;
	}

	public function set_tables($TABLES, $ALIAS=null){
		if (empty($this->settable) || $this->settable == []) {
			$this->settable = array();
		}
		if($ALIAS==null){
			array_push($this->settable, trim($TABLES).' ');
		}else{
			array_push($this->settable, trim($TABLES).' as '.$ALIAS);
		}
		$this->table = trim(implode(',',$this->settable));
		return $this;
	}


	public function set_where($WHERES)
	{
		if (empty($this->setwhere) || $this->setwhere == null) {
			$this->setwhere = array();
		}
		$this->setwhere[] = $WHERES;
		$this->where = implode(' ',$this->setwhere);
		return $this;
	}
	public function set_where_not_exist()
	{
		$this->set_where_not_exist = true;
		return $this;
	}
	public function set_order($colum = null, $order = null)
	{
		if ($colum == null && $order == null) {
			throw new RuntimeException('Valor set_order indefinido');
		}

		if (empty($this->setorder)) {
			$this->setorder = array();
		}
		if ($colum != null && $order == null) {
			$this->setorder[] = $colum;
		} else {
			$this->setorder[] = $colum . ' ' . $order;
		}
		$this->order = ' ORDER BY ' . implode(',',$this->setorder);
		return $this;
	}
	public function group_by($colum)
	{
		$this->group[] = $colum;
		return $this;
	}

	public function set_limit($init=null, $finit=null){
		if (is_null($init) && is_null($finit)) {
			throw new RuntimeException('Valor set_limit(?,?) indefinido');
			exit;
		}
		if ($finit == null) {
			$this->limit = ' LIMIT ' . $init ;
		} else {
			$this->limit = ' LIMIT ' . $init . ", " . $finit ;
		}
		return $this;
	}
	public function set_insert($colum, $var){
		if ($this->utf8 == 'encode') {
			$var = utf8_encode($var);
		}

		if ($this->utf8 == 'decode') {
			$var = utf8_decode($var);
		}

		if ($this->url == 'encode') {
			$var = urlencode($var);
		}

		if ($this->url == 'decode') {
			$var = urldecode($var);
		}

		if ($this->slashes == 'strip') {
			$var = stripslashes($var);
		}

		if ($this->slashes == 'add') {
			$var = addslashes($var);
		}
		if (is_null($var)) {
			$var = 'NULL';
		}

		$this->InsertVars[$colum] = $this->preventMySQLInject($var);
		return $this;
	}

	public function set_insert_form($object){
		if(is_array($object)){
			foreach($object as $key => $var){
				$this->set_insert($key,$var);
			}
		}
		return $this;
	}
	public function set_update_form($object){
		if(is_array($object)){
			foreach($object as $key => $var){
				$this->set_update($key,$var);
			}
		}
		return $this;
	}
	public function set_update($colum, $var)
	{
		if ($this->utf8 == 'encode') {
			$var = utf8_encode($var);
		}

		if ($this->utf8 == 'decode') {
			$var = utf8_decode($var);
		}

		if ($this->url == 'encode') {
			$var = urlencode($var);
		}

		if ($this->url == 'decode') {
			$var = urldecode($var);
		}

		if ($this->slashes == 'strip') {
			$var = stripslashes($var);
		}

		if ($this->slashes == 'add') {
			$var = addslashes($var);
		}

		if (is_string($var)) {
			if (substr($var, 0, 8) == "command:") {
				$var = substr($var, 8);
			} else {
				$var = '"' . $this->preventMySQLInject($var) . '"';
			}
		} else {
			$var = $this->preventMySQLInject($var);
		}
		$this->Insert_Update[] =$colum . '=' . $var;
		return $this;
	}
	public function debug($bolean)
	{
		$this->debug = $bolean;
		return $this;
	}
	public function ignore($dados)
	{
		$this->ignore = 'ignore';
		return $this;
	}
	public function utf8($var)
	{
		$this->utf8 = $var;
		return $this;
	}
	public function url($var)
	{
		$this->url = $var;
		return $this;
	}
	public function slashes($var)
	{
		$this->slashes = $var;
		return $this;
	}
	public function on_duplicate($dados)
	{
		$this->on_duplicate = ' ON DUPLICATE KEY UPDATE ' . $dados . ' ';
		return $this;
	}
	public function distinct()
	{
		$this->DISTINCT = ' DISTINCT ';
		return $this;
	}
	public function join($join = "LEFT", $tabela='', $on='')
	{
		$this->rell .= ' '.$join . ' JOIN ' . $tabela . ' ON ' . $on;
		return $this;
	}
	public function like($coluna, $palavra_chave)
	{
		array_push($this->Insert_like, ' LOWER('.$coluna.') LIKE LOWER("'.$palavra_chave.'")');
		return $this;
	}
	public function REGEXP($coluna, $palavra_chave)
	{
		array_push($this->Insert_like, $coluna . ' REGEXP "' . _likeString($palavra_chave) . '"');
		return $this;
	}
	public function primary_key($id)
	{
		$this->primarykey = ' PRIMARY KEY (' . $id . ')';
		return $this;
	}
	public function show_columns($alias=null)
	{
		$this->query = 'SHOW COLUMNS FROM ';
		if ($this->table != null) {
			$this->query .= $this->table;
		}
		if ($this->debug == true) {
			$consulta = mysqli_query($this->_conectMySQLi_, $this->query);
			if (mysqli_error($this->_conectMySQLi_)) {
				throw new RuntimeException(mysqli_error($this->_conectMySQLi_));
			}

		} else { $consulta = @mysqli_query($this->_conectMySQLi_, $this->query);
		}
		$_array = [];
		while ($row = mysqli_fetch_assoc($consulta)) {
			$_array[] = $row;
		}
		if($alias!=null){
			$this->fetch_array['show_columns'][$alias] = $_array;
		}else{
			$this->fetch_array['show_columns'][] = $_array;
		}
		$this->output[] = $this->query;
	}
	public function output(){
		return $this->query;
	}
	public function create_view($name=null){
		if($name==null){
			throw new InvalidArgumentException('create_view(NULL), Error:'.__LINE__);
		}	
		$this->view_query = 'CREATE OR REPLACE ALGORITHM=TEMPTABLE SQL SECURITY DEFINER VIEW ' . $name . ' AS  ('.$this->get_query().')';
		if ($this->debug == true) {
			$consulta = mysqli_query($this->_conectMySQLi_, $this->view_query);
			if (mysqli_error($this->_conectMySQLi_)) {
				throw new RuntimeException(mysqli_error($this->_conectMySQLi_));
			}
		} else {
			$consulta = @mysqli_query($this->_conectMySQLi_,$this->view_query);
		}
		return $this;
	}
	public function verify(){
		if (($this->table != null && $this->table != "") && (!empty($this->setcolum) && $this->setcolum != "")) {
			if (is_array($this->table)) {
				$this->table = implode('',$this->table);
			}
			if (is_array($this->setcolum)) {
				$this->setcolum = implode('',$this->setcolum);
			}
			$result = mysqli_query($this->_conectMySQLi_, "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='" . $this->table . "' AND column_name='" . $this->setcolum . "'");
			$tableExists = mysqli_num_rows($result);
			if ($tableExists > 0) {$tableExists = true;
			} else { $tableExists = false;}return $tableExists;
		} elseif ($this->table != null && $this->table != "") {
			$result = mysqli_query($this->_conectMySQLi_, "SHOW TABLES LIKE '" . $this->table . "' ");
			$tableExists = mysqli_num_rows($result) > 0;return $tableExists;
		} else {
			throw new RuntimeException("Favor setar uma tabela ou tabela + coluna");
			return false;
		}
		return true;
	}
	public function string_replace($value, $key){
		$value = str_replace($this->replace_isso, $this->replace_porisso, $value);
	}
	public function get_query($type = 'SELECT'){
		$this->query = '';
		if(!in_array(trim($type),['SELECT', 'INSERT', 'DELETE','UPDATE'])){
			throw new RuntimeException("->get_query() com parâmetro incorreto. Utilize 'SELECT', 'INSERT', 'DELETE' ou 'UPDATE'");
		}
		$this->query .= $type.' ';
		if (!empty($this->DISTINCT)) {
			$this->query .= $this->DISTINCT;
		}
		if (!empty($this->colum)) {
			$this->query .= $this->colum;
		} else { $this->query .= ' * ';}$this->query .= ' FROM ';
		if ($this->table != null) {
			$this->query .= $this->table;
		}
		if (!empty($this->rell) && !empty($this->rell)) {
			$this->query .= ' '.$this->rell . ' ';
		}
		if (count($this->Insert_like) > 0) {$array_like = implode(' OR ', $this->Insert_like);
		} else { $array_like = "";}
		if ($this->where != null || (count($this->Insert_like) > 0)) {
			if ($this->where != null && $array_like != "") {
				$this->where = $this->where . " AND ";
			}
			if ($this->set_where_not_exist == true) {$not = " NOT EXISTS ";
			} else { $not = "";}
			$this->query .= ' WHERE' . $not . '(' . $this->where . '(' . $array_like . '))';
			$this->query = str_replace('())', ')', $this->query);
		}
		if (count($this->group)>0) {
			$this->query .= ' GROUP BY '.implode(',',$this->group).' ' ;
		}
		if (!empty($this->order)) {
			$this->query .= $this->order . ' ';
		}
		if (!empty($this->limit)) {
			$this->query .= $this->limit;
		}
		return $this->query;
	}


	public function set_tableQuery($subQuery = null){
		if ($subQuery == null) {
			throw new RuntimeException("Parâmetro incorreto ou inexistente: ->subQuery()");
		} else {
			$_suQuery	=trim($subQuery);
			$_suQuery	=str_replace('	',' ',$_suQuery);
			$_suQuery	=preg_replace('/( )+/',' ',$_suQuery);
			$_suQuery	=preg_replace('/(	)+/',' ',$_suQuery);
			$_suQuery	=explode(' ',$_suQuery);
			if(count($_suQuery)==1){
				$this->table ='('.$this->subQuery[$_suQuery[0]].')';
			}elseif(count($_suQuery)==2){
				$this->table ='('.$this->subQuery[$_suQuery[0]].') '.$_suQuery[1];
			}
		}
		return $this;
	}
	
	public function set_subQuery($subQuery = null){
		if ($subQuery == null) {
			throw new RuntimeException("Parâmetro incorreto ou inexistente: ->set_subQuery()");
		} else {
			$this->subQuery[$subQuery]	= $this->get_query();
			$this->clear();
		}
		return $this;
	}
	
	public function select($alias = null, $script = null){
		if ($script != null) {
			$_query = $script;
		} else {
			$_query = $this->get_query();
		}
		

		if ($this->debug == true) {
			$consulta = mysqli_query($this->_conectMySQLi_, $_query);
			if (mysqli_error($this->_conectMySQLi_)) {
				throw new RuntimeException(mysqli_error($this->_conectMySQLi_));
			}
		} else {
			$consulta = @mysqli_query($this->_conectMySQLi_, $_query);
		}


		$this->_num_rows =$this->_num_row = @mysqli_num_rows($consulta);
		$this->output[] = $_query;
		$this->query	= $_query;
		$this->process_result($consulta, $alias);
		$this->clear();

		return $this;
	}

	public function query($script = null){
		if ($script == null) {
			throw new RuntimeException("Parâmetro incorreto ou inexistente");
		} else {
			return $this->select('query', $script);
		}
		return $this;
	}


	public function process_result($result, $alias = null){
		$array = null;
		$obj = null;

		while ($row = mysqli_fetch_assoc($result)) {
			$array[] 	= $row;
			$obj[] 		= $row;
		}
		
		if(count($this->colunmToJson)>0){
			
			foreach (($array??[]) as $array_key => $array_value) {
				
				foreach ($this->colunmToJson as $key=>$value) {

					
					$array[$array_key][$value] = (is_null($array[$array_key][$value])) ? [] : json_decode(stripslashes(str_replace(["\r","\n"],'',trim($array[$array_key][$value]))), true);
					// $array[$array_key][$value] = (is_null($array[$array_key][$value])) ? [] : json_decode($array[$array_key][$value], true);
				}
			}
		}
 
		if ($alias == null) {
			$this->fetch_array['response']	= $array;
			$this->obj['response']			= (object) $obj;
		} else {
			$this->fetch_array[$alias]	 = $array;
			$this->obj[$alias]			 = (object) $obj;
		}

		return $this;
	}	

	public function set_var($key,$value){
		mysqli_query($this->_conectMySQLi_,'SET @'.$key.' := '.$value.';');
	}
	
	public function insert_or_replace(){
		$keyvalue = array();
		foreach ($this->InsertVars as $key => $value) {
			if ($value=='NULL') {
					$keyvalue[] = $key . 'NULL';
				}elseif(is_string($value)) {
				if (substr($value, 0, 8) == "command:") {
					$keyvalue[] = $key . '=' . $this->preventMySQLInject(substr($value, 8));
				} else {
					$keyvalue[] = $key . '="' . $this->preventMySQLInject($value) . '"';
				}
			} else {
				$keyvalue[] = $key . "=" . $this->preventMySQLInject($value);
			}
		}
		$this->on_duplicate = ' ON DUPLICATE KEY UPDATE ' . implode(',', $keyvalue);
		return $this;
	}


	public function prepare_select($alias = null, $script = null){
		$_query = ($script != null) ? $script: $this->get_query();
		$this->query	= $_query;
		$this->output[] = $_query;
		$this->clear();
		return $this;
	}


	public function prepare_insert(){
		
		$queryPrepare = 'INSERT ';
		if (!empty($this->ignore)) {
			$queryPrepare .= $this->ignore;
		}
		$queryPrepare .= ' INTO ';

		if ($this->table != null) {$queryPrepare .= $this->table;} 

		if (count($this->InsertVars) > 0) {
			$keyvalue = array();
			foreach ($this->InsertVars as $key => $value) {
				if ($value=='NULL') {
					$keyvalue[] = 'NULL';
				}elseif (is_string($value)) {
					if ( substr($value, 0, 8) == "command:") {
						$keyvalue[] = 		$this->preventMySQLInject(substr($value, 8));
					} else {
						$keyvalue[] = '"' . $this->preventMySQLInject($value) . '"';
					}
				} else {
					$keyvalue[] 		= $this->preventMySQLInject($value);
				}
			}
			$queryPrepare .= ' ( ' . implode(',', array_keys($this->InsertVars)) . ' ) ';
		} 
		
		$queryPrepare .= ' VALUES ';
		if (count($this->InsertVars) > 0) {
			$queryPrepare .= ' (' . implode(',', $keyvalue) . ') ';
		} else {
			exit;
		};
		if ($this->set_where_not_exist == true) {$not = " NOT EXISTS ";
		} else { $not = "";}
		if (!empty($this->where)) {
			$queryPrepare .= ' WHERE' . $not . '(' . $this->where . ')';
		}
		if (!empty($this->on_duplicate)) {
			$queryPrepare .= $this->on_duplicate;
		}



		if (is_null($this->query)) {
			$this->query = $queryPrepare;
		} else {
			if(!is_array($this->query)){$this->query = [];}
			$this->query[]		= $queryPrepare;
			$this->InsertVars	= [];

		}

	}
	public function insert(){
		$this->query= null;
		$this->prepare_insert();
		$this->execQuery();
		$this->clear();
		return $this;
	}
	public function prepare_replace(){
		
		$queryPrepare = 'REPLACE ';
		if (!empty($this->ignore)) {
			$queryPrepare .= $this->ignore;
		}
		$queryPrepare .= ' INTO ';

		if ($this->table != null) {$queryPrepare .= $this->table;} 

		if (count($this->InsertVars) > 0) {
			$keyvalue = array();
			foreach ($this->InsertVars as $key => $value) {
				if ($value=='NULL') {
					$keyvalue[] = 'NULL';
				}elseif (is_string($value)) {
					if ( substr($value, 0, 8) == "command:") {
						$keyvalue[] = 		$this->preventMySQLInject(substr($value, 8));
					} else {
						$keyvalue[] = '"' . $this->preventMySQLInject($value) . '"';
					}
				} else {
					$keyvalue[] 		= $this->preventMySQLInject($value);
				}
			}
			$queryPrepare .= ' ( ' . implode(',', array_keys($this->InsertVars)) . ' ) ';
		} 
		
		$queryPrepare .= ' VALUES ';
		if (count($this->InsertVars) > 0) {
			$queryPrepare .= ' (' . implode(',', $keyvalue) . ') ';
		} else {
			exit;
		};
		if ($this->set_where_not_exist == true) {$not = " NOT EXISTS ";
		} else { $not = "";}
		if (!empty($this->where)) {
			$queryPrepare .= ' WHERE' . $not . '(' . $this->where . ')';
		}
		if (!empty($this->on_duplicate)) {
			$queryPrepare .= $this->on_duplicate;
		}



		if (is_null($this->query)) {
			$this->query = $queryPrepare;
		} else {
			if(!is_array($this->query)){$this->query = [];}
			$this->query[]		= $queryPrepare;
			$this->InsertVars	= [];

		}

	}
	public function replace(){
		$this->query= null;
		$this->prepare_replace();
		$this->execQuery();
		$this->clear();
		return $this;
	}
	public function prepare_update(){
		$queryPrepare = 'UPDATE ';
		if ($this->table != null) {
			$queryPrepare .= $this->table;
		} else {
			 die('$this->table UNDEFINED, linha:'.__LINE__);
		}
		$queryPrepare .= ' SET ';
		if (count($this->Insert_Update) > 0) {
			$queryPrepare .= implode(',', $this->Insert_Update);
		}else{
			return true;
		};

			if ($this->set_where_not_exist == true) {$not = " NOT EXISTS ";
			} else { $not = "";}

			if (!empty($this->where)) {
				$queryPrepare .= ' WHERE' . $not . '(' . $this->where . ')';
			}

			
			if (is_null($this->query)) {
				$this->query = $queryPrepare;
	
			} else {
				if(!is_array($this->query)){$this->query = [];}
				$this->query[] = $queryPrepare;
				$this->clear();
	
		}

	}
	public function update(){
		$this->query = null;
		$this->prepare_update();
		$this->execQuery();
		$this->clear();
		return $this;
	}
	public function prepare_delete(){
		$queryPrepare = 'DELETE FROM ';
		if (!empty($this->table)) {
			$queryPrepare .= $this->table;
		} 
		if (!empty($this->where)) {
			$queryPrepare .= ' WHERE (' . $this->where . ')';
		} 

		if (is_null($this->query)) {
			$this->query = $queryPrepare;
		} else {
			if(!is_array($this->query)){$this->query = [];}
			$this->query[] = $queryPrepare;
			$this->clear();
		}

	}
	public function delete(){
		$this->query = null;
		$this->prepare_delete();
		$this->execQuery();
		$this->clear();
		return $this;
	}
	public function execQuery(){
		if($this->transactionFn == true){
			try {
				if(is_array($this->query)){
					foreach ($this->query as $query) {
						$result = mysqli_query($this->_conectMySQLi_, $query);
					}
				}elseif($this->query!=''){
					$result = mysqli_query($this->_conectMySQLi_, $this->query);
				}
				mysqli_commit($this->_conectMySQLi_);
				// mysqli_free_result();
				$this->output[] = $this->query;
			} catch (mysqli_sql_exception $exception) {
				mysqli_rollback($this->_conectMySQLi_);
				if($this->rollbackFn!=false){
					$this->rollbackExec($exception->getMessage());
				}else{
					throw new RuntimeException($exception);
				}
			}
		}else{
				if(is_array($this->query)){
					foreach ($this->query as $query) {
						$result = mysqli_query($this->_conectMySQLi_, $query);
						if (mysqli_error($this->_conectMySQLi_)) {
							throw new RuntimeException(mysqli_error($this->_conectMySQLi_));
						}
						// mysqli_free_result($result);
					}
				}else{
					$result = mysqli_query($this->_conectMySQLi_, $this->query);
					if (mysqli_error($this->_conectMySQLi_)) {
						throw new RuntimeException(mysqli_error($this->_conectMySQLi_));
					}
				}
				// mysqli_free_result($result);
				
				
				
		}
	}





}
