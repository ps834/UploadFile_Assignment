
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

		//Open HTML 
		public function __construct(){

			$this->html .= '<html><body>';
		}

		//Closing HTML 
		public function __destruct(){
        	
        	$this->html .= '</body></html>';

        	//printing the results appended to HTML 
        	print($this->html);

    	}

	}


	//Class to Upload a CSV file
	class uploadCsv extends page {

	    public function get() {

	    	//Call function to display Upload form
	    	$this->html .= uploadForm::displayUploadForm();
	    }

	    //Uploading file to AFS directory
	    public function post(){

	    	$directory = "uploadedCsvFiles/";
	    	$fileToSave = $directory . basename($_FILES["uploadedFile"]["name"]);
	    	$fileSize = $_FILES["uploadedFile"]["size"];

	    	//Check if Filesize is greater than Zero
	    	if($fileSize>0){

	    		//Upload the file to AFS
	    		if(move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $fileToSave)){

	    			//Call redirectPage csv file into HTML Table
	    			callHeader::redirectPage($fileToSave);
	    			
	    		}else{
	    			print("Error while uploading File");
	    		}
	    	}else{
	    		print("Please upload a CSV file");
	    	}
	    }

	}


	//Static function to create Upload form
	class uploadForm{

		public static function displayUploadForm(){
				
		$createForm = '<h1>Upload CSV File</h1><br>';
    	$createForm .= '<form action = "index.php?page=uploadCsv" method = "post" enctype="multipart/form-data">';
		$createForm .= '<input type = "file" name = "uploadedFile" id = "uploadedFile">';
		$createForm .= '<input type = "submit" value = "Upload File" name = "submitFile">';
		$createForm .= '</form>';
    	return $createForm;
		}
	}


	//Static function to redirect the page to displayTable class and passing the file path as parameter
	class callHeader{

		public static function redirectPage($fileToSave){

    		header('Location: index.php?page=displayTable&filename=' . $fileToSave);
		}
	}

	//Diplaying CSV as HTML Table
	class displayTable extends page{


		public function get(){

			//Get the filepath from the header passed as URL parameter 
			$fileName = $_GET['filename'];
			$this->html .= displayTable::  readCsv($fileName);
    	  }


    	//Static function to read the CSV file and display it as HTML Table
    	public static function readCsv($fileName){


    		$createTable = "<center><h4>The file " . substr($fileName,17) . " is as follows: </h4></center>";

			//Open Table 
			$createTable .= '<table border="1">';

			//Open the file present in that path
			$fileToOpen = fopen($fileName, "r") or die("Unable to read the file");
			$i=1;

			//Looping till the end of the file
   			 while (!feof($fileToOpen)) {

   			 	//Read a line from the file
       			 $rowData = fgetcsv($fileToOpen);
       			 $size = sizeof($rowData);

       			 //Start of Row
				 $createTable .= '<tr>';

				 //Loop through the array to extract table cell values 
       			 foreach ($rowData as $value) {

       			 	//If first row then set it as Table header
       			 	if($i<=$size){	
       			 		$createTable .= '<th>' . $value . '</th>';
 					}else{

 					//else set as table definitions 
 						$createTable .= '<td>' . $value . '</td>';
 					}
 					$i++;
       			}	

       		//End of Row
       		$createTable .= '</tr>';

    		}

    		//Close file
			fclose($fileToOpen);


			//End of Table
			$createTable .= '</table>';

			return $createTable;
    	}

		}

?>