<?php  

	
	#incluinfo os dados necessarios
	include_once 'Connect.class.php';
	include_once 'Manager.class.php';


	# buscando os dados la na tabela
	$manager = new Manager;

	$dados = $manager->select_common("users",null, null, " ORDER BY user_name DESC");	
?>
<table border=2>
	<thead>
	<tr>
		<th>ID</th>
		<th>Nome</th>
		<th>Email</th>
		<th>Telefone</th>
	</tr>
	</thead>

	<tbody>
		<?php foreach($dados as $d): ?>
			<tr>
				<td><?=$d['id_user'];?></td>
				<td><?=$d['user_name'];?></td>
				<td><?=$d['user_email'];?></td>
				<td><?=$d['user_phone'];?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>

</table>
