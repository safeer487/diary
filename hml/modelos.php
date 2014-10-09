<?php  

/**
 * Los modelos llevan la lógica del sistema. Reciben instrucciones
 * y devuelven resultados. Y nada más.
 */
//cada pagina tiene diferente modelo

/**
* Clase de seguridad
*/
class Seguridad
{
	
	function __construct()
	{
		session_start();
		if(!isset($_SESSION['usuario'])){
			header('Location: controlador.php?vista=index');
			exit;
		}
	}
}

/**
 * Clase del panel de identificacion
 */
class ModeloIndex {
	//Variables
	private $sAlias = '';
	private $sPass = '';
	private $bMostrarError = false;
	/**
	 * Constructor
	 */
	public function __construct(){
		if ($_POST) {
			$this->sAlias = trim($_POST['alias']);
			$this->sPass = md5(trim($_POST['password']));
			$this->comprobarIden();
		}
	}
	/**
	 * Get de $bMostrarError
	 * @return boolean 
	 */
	public function isError(){
		return $this->bMostrarError;
	}
	/**
	 * Metodo que comprueba la identificacion de usuarios
	 */
	private function comprobarIden(){
		//comprobamos que existe el usuario
		require_once "BD.class.php";
		$miBD = new DB();
		$sSQL = "SELECT id FROM usuario WHERE alias = '$this->sAlias' AND 
		password = '$this->sPass'";
		$iNumRes = $miBD->contarResultadosQuery($sSQL);
		if($iNumRes >= 1){
			$this->redirigeAAdmin($sSQL, $miBD);
		}else{
			$this->bMostrarError = true;
		}
	}
		/**
		 * metodo que lleva al usuario a pagina de admin
		 * y crea su sesion
		 * @param String $insSQL
		 * @param  BD Class para gestionar la base de datos
		 */
		private function redirigeAAdmin($insSQL, $inBD){
			//Recuperamos el id del usuario y lo guardamos en una sesion para 
			//que siempre sea accesible.
			$aResul = $inBD->obtenerResultado($insSQL);
			session_start();
			$_SESSION['usuario'] = $aResul[0]['id'];
			//Redireccionamos a la pagina de aminstracion
			header('Location: controlador.php?vista=panelAdmin');
			exit;
		}
}	

/**
* Clase para crear nueva página
*/
class ModeloCrear {
	/**
	*Constructor principal
	*/
	public function __construct(){
		new seguridad;
		$this->guardarABase();
	}
	/**
	*Metodo que inserta una nueva página
	*/
	private function guardarABase(){
		if($_POST){
			//obtenemos variable
			$sText = $_POST['crearDiario'];
			/*conectamos a base de datos*/
			require_once "BD.class.php";
			$miBD = new DB();
			$sSQL = 'SELECT id FROM paginas WHERE fecha = CURDATE()';
			//comprobamos si ya existe la pagina
			if($miBD->contarResultadosQuery($sSQL) > 0) {
			//ya existe una pagina en esa fecha, por lo que actualiza
				$aResul = $miBD->obtenerResultado($sSQL);
				$sSQL = "UPDATE paginas SET texto = '$sText' WHERE id = ".$aResul[0]['id'];
			} else{
			$sSQL = "INSERT INTO paginas VALUES(NULL,CURDATE(),'$sText')";
			}
			$miBD->ejecutarQuery($sSQL);
			//Llevamos a admin con un aviso
			header('Location: controlador.php?vista=panelAdmin&nuevo=1');
			exit;

		}
	}
	/**
	* Método que recupera el texto de hoy
	*/
	public function recuperarTexto(){
			require_once "BD.class.php";
			$miBD = new DB();
			$sSQL = 'SELECT id, texto FROM paginas WHERE fecha = CURDATE()';
			//comprobamos si ya existe la pagina
			if($miBD->contarResultadosQuery($sSQL) > 0) {
			//ya existe una pagina en esa fecha, por lo que actualiza
				$aResul = $miBD->obtenerResultado($sSQL);
				return $aResul[0]['texto'];
		}
	}
}
/**
 * Clase para gestionar páginas antiguas
 */
class ModeloVer {
	//variables
	public $sBuscar;
	/**
	 * Constructor principal
	 */
	public function __construct(){
		new Seguridad;
		$this->borrarPagina();
		
	}

	/**
	 * Método que imprime la lista de paginas
	 * @return String HTML
	 */
	public function verLista(){
		$sHTML = '';
		//conexion a base de datos
		require_once 'BD.Class.php';
		$miBD = new DB();
		$sSQL = "SELECT id,fecha FROM paginas";
		if (isset($_POST['search'])) {
			$sBus = $_POST['search'];
			$sSQL .= " WHERE texto LIKE '%$sBus%'";
		}
		$sSQL .= " ORDER BY fecha DESC;"; 
		
		$resul = $miBD->obtenerResultado($sSQL);
		foreach ($resul as $key => $value) {
			$sHTML .='
		<div>
		<a href="controlador.php?vista=ver&borrar='.$value['id'].'"><button type="button" class="eliminar"  value="Eliminar">Eliminar</button></a>
		<a href="controlador.php?vista=modificar&mod='.$value['id'].'"><button type="button"  class="modificar"  value="Modificar">Modificar</button></a>
		
		<span class="date">'.$value['fecha'].'</span>
		</div>
	';
		}
		return $sHTML;
	}
	/**
	 * Metodo que borra la pagina que lleva del $_GET['borrar']
	 */
	private function borrarPagina(){
		if (isset($_GET['borrar'])) {
			$iId = $_GET['borrar'];
			require_once 'BD.class.php';
			$miBD = new DB();
			$sSQL = "DELETE FROM paginas WHERE id =".$iId;
			$miBD->ejecutarQuery($sSQL);
		}
	}
}

	
/**
* Clase del panel de administración
*/
class ModeloPanelAdmin {
	public function __construct(){
		new seguridad();
		$this->cerrarSession();
	}

	private function cerrarSession(){
		if (isset($_GET['cerrar'])) {
			session_destroy();
			header('Location: controlador.php?vista=index');
			exit;
		}
	}
	public function mensajeGuardado(){
		if(isset($_GET['nuevo'])){
			return "Enhorabuena que has guardado";
		}
	}
}
class ModeloModificar{
	//variables
	private $iId = 0;
	private $sFecha = '';
	private $sTexto = '';

	public function __construct(){
		new Seguridad;
		$this->obtenerVariables();
		$this->actualizarPagina();
		
	}
	/*****GET******/
	/**
	 * Get id
	 * @return   [description]
	 */
	public function getIdMod(){
		return $this->iId;
	}
	public function getFecha(){
		return $this->sFecha;
	}
	public function getTexto(){
		return $this->sTexto;
	}
	private function obtenerVariables(){
		if (isset($_GET['mod'])) {
			$this->iId= $_GET['mod'];
		}
		require_once 'BD.class.php';
		$miBD = new DB;
		$sSQL = "SELECT texto, fecha FROM paginas WHERE id=".$this->iId;
		$resul = $miBD->obtenerResultado($sSQL);
		$this->sFecha = $resul[0]['fecha'];
		$this->sTexto = $resul[0]['texto'];
	}

	private function actualizarPagina(){
		if($_POST){
		//Variables
		$this->iId = $_GET['mod'];
		$this->sTexto = $_POST['text'];
		require_once 'BD.class.php';
		$miBD = new DB;

		$sSQL = "UPDATE paginas SET texto = '$this->sTexto' WHERE id=".$this->iId;
		
		$miBD->ejecutarQuery($sSQL);
		header('Location: controlador.php?vista=panelAdmin&mod=1');
		exit;
		}
	}
}
?>