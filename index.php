<?php
// 5 minutes max.
$maxTimeExec = 1500;
set_time_limit($maxTimeExec);
/**
* Change extToGetNbLine to count more/less files by extension.
* Change ignore to use a specific regex to ignore files.
*/
class Count{
	public $nbFiles = 0;
	public $nbLines = 0;
	public $listDirectory = array();
	public static $extToGetNbLine = array('php', 'js', 'css', 'html', 'less', 'sql', 'xml', 'java', 'cpp', 'h', 'rb', 'json', 'erb', 'haml');

	public function __construct($dir, $extToGetNbLine = false){
		$this->count_files($dir);
		Count::$extToGetNbLine = ($extToGetNbLine && is_array($extToGetNbLine))? $extToGetNbLine : Count::$extToGetNbLine ;
	}

	public function count_files($dir) {
		$ignore = '/^\.|^node_modules|(.+)(.min)/i';# À modifier selon vos besoins. Ignore tous les fichiers commencant par un point.

	   	$nbFiles = 0;
	   	$dir_handle = opendir($dir);

	   	while($entry = readdir($dir_handle)) {
	      	if(!in_array($entry, array('.', '..')) && !preg_match($ignore, $entry)) {
	      		$path = "$dir/$entry";

	      		if(is_dir($path)){
	      			$this->listDirectory[] = array('path' => $path);
	      			$nbFiles += $this->count_files($path);
	      		}else{
	      			$nbFiles++;
	      			// If the file is
	      			if(in_array(pathinfo($path, PATHINFO_EXTENSION), Count::$extToGetNbLine)){
	      				$contenu_fichier = file_get_contents($path);
						$nbLines = substr_count($contenu_fichier, "\n");
						$this->nbLines += $nbLines;
						$this->listDirectory[count($this->listDirectory)-1][] = array('path' => $path, 'lines' => $nbLines);
	      			}else{
	      				//echo pathinfo($path, PATHINFO_EXTENSION).'<br >';
	      			}
	      		}
	  		}
	   }
	   closedir($dir_handle);

	   // Update value.
	   $this->nbFiles = $nbFiles;
	   return $nbFiles;
	}
}

// Calculate results.
if(isset($_GET['pathDirectory'])){

	if(is_dir($_GET['pathDirectory']) && !empty($_GET['pathDirectory'])){
		$directory = $_GET['pathDirectory'];
		$o = new Count($directory, (isset($extToGetNbLine))? $extToGetNbLine : false);
	}
}
?>


<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<div style="float: left;">
			<u>Extensions à calculer</u>:<br />
			<ul>
				<?php
					foreach(Count::$extToGetNbLine as $ext){
				?>		
				<li>
					<?php echo $ext;?>
				</li>
				<?php
					}
				?>
			</ul>
		</div>
		<div style="text-align: center; margin: auto;">
			<form id="formDirectory" action="index.php" method="get">
				Chemin du dossier à calculer:<br />
				<input name="pathDirectory" type="text" value="C:/wamp/www/" />

				<input type="submit" />
			</form>	

			<u>Nb</u>: Temps maximal accordé au script: <b><?php echo $maxTimeExec;?> secondes</b>. (~<?php echo $maxTimeExec/60;?> minutes)
		</div>
		<div class="float: left;"></div>
	</<body>
</html>

<?php
// Display results.
if(isset($_GET['pathDirectory'])){

	if(is_dir($_GET['pathDirectory']) && !empty($_GET['pathDirectory'])){
		echo "Il y a ".count($o->listDirectory)." dossiers dans le dossier $directory et ses sous-dossiers.<br />";
		echo "Il y a ".$o->nbFiles." fichiers dans le dossier $directory et ses sous-dossier.<br />";
		echo "Il y a ".$o->nbLines." lignes de code dans tous les fichiers ".implode(", ", Count::$extToGetNbLine)." de tous les dossiers de $directory.<br /><br />";

		echo "<pre>";
		var_dump($o->listDirectory);
		echo "</pre>";
	}else{
		echo "Le chemin entré est invalide. Il ne correspond pas à un dossier.";
	}
}
?>