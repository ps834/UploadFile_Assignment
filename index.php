
<?php

	//turn on debugging message
	ini_set('display errors','on');
	error_reporting(E_ALL);

	//Class to load  classes it finds the file when the program starts to fail for calling a missing class
	class Manage {
		public static function autoload($class){
			include $class . 'php';
		}
	}

	spl_autoload_register(array('Manage','autoload'));


	//Instantiate the program object
	$obj = new main(); 

	class main {

		public function __construct(){

			//setting default page Request to homepage
			$pageRequest = 'uploadCsv';

			//check the parameter of pageRequest
			if(isset($_REQUEST['page'])){
				$pageRequest = $_REQUEST['page'];
			}

			//instantiate the class that has been called
			$page = new $pageRequest;


			//Call get or post as requested
			if($_SERVER['REQUEST_METHOD'] == 'GET'){
				$page->get();
			}else{
				$page->post();
			}
		}
	}

	abstract class page{

		protected $html;

		//Open html 
		public function __construct(){

			$this->html .= '<html><body>';
		}

		//Closing HTML 
		public function __destruct(){
        	$this->html .= '</body></html>';

        	//printing the results appended to HTML 
        	printText::results($this->html);

    }

	}


	//Class to Upload a CSV file
	class uploadCsv extends page {

    public function get() {

    	$createForm = '<form action = "index.php?page=uploadCsv" method = "post" enctype="multipart/form-data">';
		$createForm .= '<input type = "file" name = "uploadedFile" id = "uploadedFile">';
		$createForm .= '<input type = "submit" value = "Upload File" name = "submitFile">';
		$createForm .= '</form>';
        $this->html .= $createForm;
    }

    //Uploading file to AFS directory
    public function post(){

    	$directory = "/afs/cad/u/p/s/ps834/public_html/uploadFile/uploadedCsvFiles/";
    	$fileToSave = $directory . basename($_FILES["uploadedFile"]["name"]);
    	$fileSize = $_FILES["uploadedFile"]["size"];

    	//Check if Filesize is greater than Zero
    	if($fileSize>0){

    		//Upload the file to AFS
    		if(move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $fileToSave)){

    			//Redirecting the page to displayTable class and passing the file path as parameter
    			header('Location: https://web.njit.edu/~ps834/uploadFile/index.php?page=displayTable&filename=' . $fileToSave);
    		}else{
    			printText::results("Error while uploading File");
    		}
    	}else{
    		printText::results("Please upload a CSV file");
    	}
    }
	}


	//Diplaying CSV as HTML Table
	class displayTable extends page{


		public function get(){

			//Open Table 
			$this->html .= '<table border="1">';

			//Get the filepath from the header passed as URL parameter 
			$fileName = $_GET['filename'];

			//Open the file present in that path
			$fileToOpen = fopen($fileName, "r") or die("Unable to read the file");
			$i=1;

			//Looping till the end of the file
   			 while (!feof($fileToOpen)) {

   			 	//Read a line from the file
       			 $line_of_text = fgets($fileToOpen);

       			 //Divide the line at commas and save it as an array
       			 $data = explode(",", $line_of_text);
       			 $size = sizeof($data);

       			 //Start of Row
				 $this->html .= '<tr>';

				 //Loop through the array to extract table cell values 
       			 foreach ($data as $value) {

       			 	//If first row then set it as Table header
       			 	if($i<=$size){	
       			 		$this->html .= '<th>' . $value . '</th>';
 					}else{

 					//else set as table definitions 
 						$this->html .= '<td>' . $value . '</td>';
 					}
 					$i++;
       			}	

       		//End of Row
       		$this->html .= '</tr>';

    		}

    		//Close file
			fclose($fileToOpen);


			//End of Table
			$this->html .= '</table>';
    	}
		}


		//Static function to print values
		class printText{
			public static function results($text){
				print($text);
			}
		}

?>