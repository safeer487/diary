<?php  

/**
 * La vista debe hacer lo mínimo para encargarse de mostrar HTML al cliente.
 * Y nada más.
 */

function armar_diccionario($vista, $data) {//Crear diccionario
	$diccionario = array();
	//Dicionario Index
	if ($vista == 'index' && $data->isError()) {
		$diccionario['mensajeError'] = 'Sus datos no son correctos.';
	}elseif($vista == 'index'){
		$diccionario['mensajeError'] = '';
	}
	//Diccionario Crear
	if($vista == 'crear'){
		$diccionario['texto'] = $data->recuperarTexto();
		$diccionario['fecha actual'] = date("j, n, Y");
	}

	//Diccionario panelAdmin
	if($vista == 'panelAdmin'){
		$diccionario['Mensaje guardado'] = $data->mensajeGuardado();
	}

	//Diccionario ver
	if($vista == 'ver'){
		$diccionario['lista'] = $data->verLista();
	}

	//Diccionario modificar
	if($vista == 'modificar'){
		$diccionario['idMod'] = $data->getIdMod();
		$diccionario['fecha'] = $data->getFecha();
		$diccionario['texto'] = $data->getTexto();
	}
	return $diccionario;

}

function render_data($vista, $data) {//$data realmente es una vista
	$html = '';
	if(($vista) && ($data)) {
		$diccionario = armar_diccionario($vista, $data);//vista es un get
			$html = file_get_contents('plantilla_' . $vista . '.html');
			foreach ($diccionario as $clave => $valor) {
				$html = str_replace('{' . $clave . '}', $valor, $html);
		}
	}
	print $html;//print $html es lo que hace imprime todo 
}

?>