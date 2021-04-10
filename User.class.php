<?php  

	class User{
		
		# Atributos da classe
		private $data = array();

		# Método Construtor
		public function __construct($name, $email){
			$this->data['name'] = $name;
			$this->data['email'] = $email;
		}

		# Método Mágico Set, recebendo os valores 
		public function __set($key, $value){
			$this->data[$key] = $value;
 		}

 		# Método Mágico Get, mostrando os valores
		public function __get($key){
			return $this->data[$key];
		}
	}

?>
