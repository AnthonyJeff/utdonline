<?php

	class Manager extends Connect{


		# Metódo de INSERÇÃO normal, apenas uma tabela...
		public function insert_common($table, $data, $query_extra){

			//criando o objeto pdo...
			$pdo = parent::get_instance();

			//pegando apenas os nomes dos campos, apartir das chaves dos arrays...
			$fields = implode(", ", array_keys($data));

			//pegando os nomes dos campos para usar pra substituição de valores
			$values = ":".implode(", :", array_keys($data));

			//preparando query apartir dos campos($fields) e os parametros de valores nomeados($values)
			$query = "INSERT INTO $table ($fields) VALUES ($values)";

			//se a consulta precisar de algo mais..
			if($query_extra != ""){
				$query .= $query_extra;
			}

			# apenas para testar a query (descomente o echo) - PADRÃO- COMENTADO
			//echo $query;

			//continuação da preparação da query...
			$statement = $pdo->prepare($query);

			# Tratamento de Erros do PDO
			if (!$statement) {
			    echo "\PDO::errorInfo():\n";
			    print_r($dbh->errorInfo());
			}

			//filtrando valores para serem inseridos, tecnica segura para evitar SQL Injection..
			foreach ($data as $key => $value) {
				$data[$key] = filter_var($value);
			}

			//substituindo os parametros nomeados pelos verdadeiros valores, ex: ":name" por "Anthony"
			foreach ($data as $key => $value){
				//$parameters[":$key"] = $value;
				$statement->bindValue(":$key", $value, PDO::PARAM_STR);
			}

			//executando a query já com seus valores
			if($statement->execute()){
				//se der certo retorna o id do elemento inserido...
				return $pdo->lastInsertId();
			}else{
				//se não der certo, retornará false...
				return false;
			}

		}//Fechando o método insert_common


		# Metódo de SELEÇÃO normal, apenas uma tabela...
		public function select_common($table, $fields, $filters, $query_extra){

			//criando o objeto pdo...
			$pdo = parent::get_instance();

			//Iniciando a query
			$query = "SELECT ";

			# Se os campos array forem diferentes de NULL, ele pegará os campos. Caso contrário, o select assumirá o '*' (todos os campos);
			if($fields != null){
				$query .= implode(", ", $fields);
			}else{
				$query .= "*";
			}

			//Continuando a query
			$query .= " FROM $table";

			# Para os filtros: Se existirem filtros, eles serão os valores do array. Caso eles forem diferentes de NULL, ele pegará esses valores. 
			if($filters != null){
				$query .= " WHERE ";
				foreach ($filters as $key => $value) {
					$query .= "$key=:$key AND ";
				}

				//Removendo o ultimo AND da query
				$query = substr($query, 0, -4);
			}

			//se a consulta precisar de algo mais..
			if($query_extra != ""){
				$query .= $query_extra;
			}

			# apenas para testar a query (descomente o echo) - PADRÃO- COMENTADO
			//echo $query;

			# preparando consulta
			$statement = $pdo->prepare($query);

			# Caso o pdo não prepare a query, ele mostrará os erros
			if(!$statement) {
			    echo "\PDO::errorInfo():\n";
			    print_r($dbh->errorInfo());
			}

			//substituindo os parametros pelos reais valores dos filtros, caso haja...
			if($filters != null){

				//filtrando valores para serem inseridos, tecnica segura para evitar SQL Injection...
				foreach ($filters as $key => $value) {
					$filters[$key] = filter_var($value);
				}

				//substituindo os parametros nomeados pelos verdadeiros valores, ex: ":name" por "Anthony"
				foreach ($filters as $key => $value) {
					$statement->bindValue(":$key", $value, PDO::PARAM_STR);
				}
			}//fechando o if (teste de filtros)

			# executando consulta
			$statement->execute();

			# preparando resultado
			$data;
			if($statement->rowCount()){
				while($result = $statement->fetch(PDO::FETCH_ASSOC)){
					$data[] = $result;
				}
			}else{
				return false;
			}

			# retornando resultado da busca
			return $data;

		}//Fechando o método select_common

		# Metódo de SELEÇÃO RELACIONADA, com relacionamentos e chaves estrangeiras...
		public function select_special($tables, $relationships, $filters, $query_extra){

			//criando o objeto pdo...
			$pdo = parent::get_instance();

			# Iniciando a Query
			$query = "SELECT ";

			//informando colunas a serem selecionadas
			foreach ($tables as $table=>$fields){
				if(!empty($fields)){
					foreach ($fields as $each_field){
						$query .= "$table.$each_field, ";
					}
				}else{
					$query .= "$table.*, "; //quando as colunas nao forem informadas
				}
			}
			
			//removendo ultima "," 
			$query = substr($query, 0, -2);
			
			//inner join's
			$tables_names = array_keys($tables);

			# Continuando a query, e informando com o INNER JOIN as tabelas a serem relacionadas
			$query .= " FROM ".implode(" INNER JOIN ", $tables_names);
			
			# Colocando os relacionamentos na query
			$query .= " ON ";
			foreach($relationships as $foreign=>$primary){
				$query .= "$foreign=$primary AND "; 
			}

			//removendo ultimo "AND" dos RELACIONAMENTOS
			$query = substr($query, 0, -4);

			# Setando os filtros na query
			if(isset($filters)){
				$query .= " WHERE ";
				foreach($filters as $field=>$value){
					$query .= "$field=? AND ";
				}
				//removendo ultimo "AND"...
				$query = substr($query, 0, -4);
			}

			//se a consulta precisar de algo mais..
			if($query_extra != ""){
				$query .= $query_extra;
			}

			# apenas para testar a query (descomente o echo) - PADRÃO - COMENTADO
			//echo $query;

			//preparando consulta
			$statement = $pdo->prepare($query);

			# Caso o pdo não prepare a query, ele mostrará os erros
			if(!$statement) {
			    echo "\PDO::errorInfo():\n";
			    print_r($dbh->errorInfo());
			}


			# Se os filtros estiverem setados
			if(isset($filters)){
				//filtrando valores para serem inseridos, tecnica segura para evitar SQL Injection...
				foreach ($filters as $key => $value) {
					$filters[$key] = filter_var($value);
				}

				//substituindo os parametros nomeados pelos verdadeiros valores, ex: "?" por "Anthony", com a ajuda da variável auxiliar
				$i = 1;
				foreach ($filters as $key => $value){
					//$parameters[":$key"] = $value;
					$statement->bindValue($i, $value, PDO::PARAM_STR);
					$i++;
				}
			}

			# executando consulta
			$statement->execute();

			# preparando o resultado
			$data;
			if($statement->rowCount()){
				while($result = $statement->fetch(PDO::FETCH_ASSOC)){
					$data[] = $result;
				}
			}else{
				return false;
			}

			# retornando resultado da busca
			return $data;


		}//fechar o método SELECT SPECIAL

		//Metódo de atualização modo normal, apenas uma tabela...
		public function update_common($table, $data, $filters, $query_extra){

			# criando o objeto pdo...
			$pdo = parent::get_instance();

			# valores a serem atualizados
			$new_values = "";
			foreach ($data as $key => $value) {
				$new_values .= "$key=:$key, ";
			}

			# removendo ultima "," da query
			$new_values = substr($new_values, 0, -2);

			# filtros
			$filters_up="";
			foreach ($filters as $key => $value) {
				$filters_up .= "$key=:$key AND ";
			}

			# removendo ultimo "AND";
			$filters_up = substr($filters_up, 0, -4);

			# preparando query apartir dos campos($fields) e os parametros de valores nomeados($values)
			$query = "UPDATE $table SET $new_values WHERE $filters_up;";

			//se a consulta precisar de algo mais..
			if($query_extra != ""){
				$query .= $query_extra;
			}

			# apenas para testar a query (descomente o echo) - PADRÃO - COMENTADO
			//echo $query;

			# continuação da preparação da query...
			$statement = $pdo->prepare($query);

			if (!$statement) {
			    echo "\PDO::errorInfo():\n";
			    print_r($dbh->errorInfo());
			}

			# filtrando valores para serem inseridos, tecnica segura para evitar SQL Injection...
			foreach ($data as $key => $value) {
				$data[$key] = filter_var($value);
			}

			# substituindo os parametros nomeados pelos verdadeiros valores, ex: ":name" por "Anthony"
			foreach ($data as $key => $value){
				//$parameters[":$key"] = $value;
				$statement->bindValue(":$key", $value, PDO::PARAM_STR);
			}

			//substituindo os parametros dos filtros nomeados pelos verdadeiros valores, ex: ":name" por "Anthony"
			foreach ($filters as $key => $value){
				//$parameters[":$key"] = $value;
				$statement->bindValue(":$key", $value, PDO::PARAM_STR);
			}

			//executando a query já com seus valores
			if($statement->execute()){
				//se der certo retorna true...
				return true;
			}else{
				//se não der certo, retornará false...
				return false;
			}

		}//Fechando o método UPDATE COMMON

		//Metódo de EXCLUSÃO modo normal, apenas uma tabela...
		public function delete_common($table, $filters, $query_extra){

			//criando o objeto pdo...
			$pdo = parent::get_instance();

						
			//filtros
			$filters_delete="";
			foreach ($filters as $key => $value) {
				$filters_delete .= "$key=:$key AND ";
			}

			//removendo ultimo "AND";
			$filters_delete = substr($filters_delete, 0, -4);

			# preparando query apartir dos campos($fields) e os parametros de valores nomeados($values)
			$query = "DELETE FROM $table WHERE $filters_delete;";

			# se a consulta precisar de algo mais..
			if($query_extra != ""){
				$query .= $query_extra;
			}

			# apenas para testar a query (descomente o echo) - PADRÃO - COMENTADO
			//echo $query;

			//continuação da preparação da query...
			$statement = $pdo->prepare($query);

			if (!$statement) {
			    echo "\PDO::errorInfo():\n";
			    print_r($dbh->errorInfo());
			}

			//substituindo os parametros dos filtros nomeados pelos verdadeiros valores, ex: ":name" por "Anthony"
			foreach ($filters as $key => $value){
				//$parameters[":$key"] = $value;
				$statement->bindValue(":$key", $value, PDO::PARAM_STR);
			}

			//executando a query já com seus valores
			if($statement->execute()){
				//se der certo retorna true...
				return true;
			}else{
				//se não der certo, retornará false...
				return false;
			}


		}//Fechando o método DELETE COMMOM


	}//Fechando a Class Manager


?>	