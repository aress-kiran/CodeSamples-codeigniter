<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Include the SDK using the Composer autoloader
	require(FCPATH.'api/vendor/autoload.php');
// Include AWS
	use Aws\S3\S3Client;
	use Aws\Common\Credentials\Credentials;
	
class Recipes extends CI_Controller {
	
	 /* =============================================================================================================================
	 * Author 			: Manoj Kulkarni
	 * Company 			: Aress Software & Education Technologies (P) Ltd.
	 * 
	 * =============================================================================================================================
	 * About Recipes Controller :
	 * =============================================================================================================================
	 * This controller controls Recipes related operations.
	 * 
	 * Functions in this controller are :
	 * 1. constructor
	 * 2. index
	 * 3. saveRecipeDetails
	 * 4. saveRecipeImage
	 * 5. checkDuplicateRecipe	
	 * 6. checkDuplicateRecipeIngredients
	 * 7. getRecipeIngredients
	 * 8. saveIngredients
	 * 9. deleteIngredients
	 * 10. saveCalorieIntake
	 * 11. saveRecipeInstructions
	 * 12. deleteRecipeInstructions
	 * 13. checkDuplicateRecipeInstructions
	 * 14. getRecipeInstructions
	 * 15. checkAllIngredientsIncluded
	 * 16. defaultRecipes
	 * 17. checkDuplicateDefaultRecipe
	 * 18. saveDefaultRecipe
	 * 19. getDefaultRecipes
	 * 20. getRecipes
	 * =============================================================================================================================
	 *
	 * =============================================================================================================================
	 * Function Name : 
	 * __constrct ()
	 * 
	 * Function Description : 
	 * > This function creates a constructor for users controller.
	 * > Loads necessary helpers, libraries and models which will be available throughout the controller.
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */

	 public function __construct () {
		
		parent :: __construct();
		
		// Load Database
		$this->load->database();
		
		// Load required helpers
		$this->load->helper( array( 'url', 'config' ) );
		$this->load->helper('string');
		
		// Load necessary libraries
		$this->load->library( 'phpsession' ); // for accessing sessions throughout admin
		$this->load->library( 'message' ); // to set success / error message
		
		// Load necessary models
		$this->load->model( "Users_Model", "U_Model" );
		$this->load->model( "SystemEmails_Model", "M_Model" );
		$this->load->model( "User_permissions_model", "UP_Model" );
		$this->load->model( "Recipes_Model", "R_Model" );
		$this->load->model( "Recipeingredients_model", "RI_Model" );
		$this->load->model( "Recipeinstructions_model", "RIN_Model" );
		$this->load->model( "Fooditems_Model", "FM_Model" );
		$this->load->model( "Units_Model", "UN_Model" );
				
		//Check for user is logged In
			checkLogin();	
		
		//Check for user's permissions
			checkpermissions( "Recipes" );
	} // public function __construct ()

	 /* =============================================================================================================================
	 * Function Name : 
	 * index( )
	 * 
	 * Function Description : 
	 * > This function lists all business users.
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	
	 public function index( ) {
		
		
		// Check whether data is posted or not for performin some operation.
		$sizePostArray = sizeof( $this->input->post());
		if( is_array( $this->input->post()) && $sizePostArray > 0 ) {
			
			// Perform requested operation using Category Model.
			$message = $this->R_Model->performRecipeOperations( $this->input->post() );
			
			// Set message according to reply from model.
			$this->message->setMessage( $message['message'], $message['class'] );
			
			// give response and break execution.
			echo "true";
			die;
			
		} // if( is_array( $this->input->post()) && $sizePostArray > 0 )
		
		
		//Array for passing data to user's view
		$data 		   = array();
		
		//Get all recipes data
		$recipes 		   = $this->R_Model->getAllRecipes();
		
		
		//size of user records
		$recipesSize      			= sizeof($recipes);
		$data['recipes'] 	 		= $recipes;
		//$data['userType'] 		= $userType;
		
		# Get the Admin logged in user permision 
		$id = $this->phpsession->get( 'adminUID' );		 
  	 	$data['permissions'] = $this->UP_Model->getPermissionDetails($id);
		
		
		// Load view
	    $this->load->view('recipes/list', $data);
	} // public function index()
	
	
	/* =============================================================================================================================
	 * Function Name :
	* getRecipesData( )
	*
	* Function Description :
	* > This function retrives Ingredient Categories data
	*
	* @params :
	* none
	* =============================================================================================================================
	*/
	public function getRecipesData()
	{
			
		/* Array of database columns which should be read and sent back to DataTables. Use a space where
		 * you want to insert a non-database field (for example a counter or static image)
		*/
		//$aColumns = array('id','recipeTitle','recipeImage','difficulty','recipeTime','noOfServings','containsAlcohol');
		$aColumns = array('id','recipeTitle','recipeImage','recipeTime','noOfServings');
		
		// DB table to use
		$sTable = 'recipes';
		//
	
		$iDisplayStart 		= $this->input->get_post('iDisplayStart', true);
		$iDisplayLength 	= $this->input->get_post('iDisplayLength', true);
		$iSortCol_0 		= $this->input->get_post('iSortCol_0', true);
		$iSortingCols 		= $this->input->get_post('iSortingCols', true);
		$sSearch 			= $this->input->get_post('sSearch', true);
		$sEcho 				= $this->input->get_post('sEcho', true);
	
		// Paging
		if(isset($iDisplayStart) && $iDisplayLength != '-1')
		{
			$this->db->limit($this->db->escape_str($iDisplayLength), $this->db->escape_str($iDisplayStart));
		}
	
		// Ordering
		if(isset($iSortCol_0))
		{
			for($i=0; $i<intval($iSortingCols); $i++)
			{
				$iSortCol 	= $this->input->get_post('iSortCol_'.$i, true);
				$bSortable 	= $this->input->get_post('bSortable_'.intval($iSortCol), true);
				$sSortDir 	= $this->input->get_post('sSortDir_'.$i, true);
								
				
				if($bSortable == 'true')
				{
					
					if($iSortCol < 5 ) // # Columns from $aColumns array
					{
						$this->db->order_by($aColumns[intval($this->db->escape_str($iSortCol))], $this->db->escape_str($sSortDir));
					}else if($iSortCol == 5 ) { // Difficulty column
						$this->db->order_by(" CASE difficulty WHEN 1 THEN 'Beginner' WHEN 2 THEN 'Expert' END ", $this->db->escape_str($sSortDir));
					}else if($iSortCol == 6 ) { // containsAlcohol column
						$this->db->order_by(" CASE containsAlcohol WHEN 0 THEN 'Non Alcoholic' WHEN 1 THEN 'Alcoholic' END ", $this->db->escape_str($sSortDir));
					}
				} // if($bSortable == 'true')
			} // for($i=0; $i<intval($iSortingCols); $i++)
		}// if(isset($iSortCol_0))
	
		/*
		* Filtering
			* NOTE this does not match the built-in DataTables filtering which does it
			* word by word on any field. It's possible to do here, but concerned about efficiency
			* on very large tables, and MySQL's regex functionality is very limited
		*/
		if(isset($sSearch) && !empty($sSearch))
		{
			for($i=0; $i<count($aColumns); $i++)
			{
				$bSearchable = $this->input->get_post('bSearchable_'.$i, true);
	
						// Individual column filtering
				if(isset($bSearchable) && $bSearchable == 'true')
				{
					$this->db->or_like($aColumns[$i], $this->db->escape_like_str($sSearch));
				} // if(isset($bSearchable) && $bSearchable == 'true')
			}// for($i=0; $i<count($aColumns); $i++)
			
			# Columns other than in $aColumns array
			$this->db->or_like(" CASE difficulty WHEN 1 THEN 'Beginner' WHEN 2 THEN 'Expert' END ", $this->db->escape_like_str($sSearch));
			$this->db->or_like(" CASE containsAlcohol WHEN 0 THEN 'Non Alcoholic' WHEN 1 THEN 'Alcoholic' END ", $this->db->escape_like_str($sSearch));
		}
				
		// Select Data
		//$this->db->select('SQL_CALC_FOUND_ROWS '.str_replace(' , ', ' ', implode(', ', $aColumns)), false);
		$this->db->select('SQL_CALC_FOUND_ROWS '.str_replace(' , ', ' ', implode(', ', $aColumns)).", CASE difficulty WHEN 1 THEN 'Beginner' WHEN 2 THEN 'Expert' END as difficulty_level , CASE containsAlcohol WHEN 0 THEN 'Non Alcoholic' WHEN 1 THEN 'Alcoholic' END as containsAlcohol ", false);
		$this->db->where( "isDeleted", 0 );
		$rResult = $this->db->get($sTable);
						
		// Data set length after filtering
		$this->db->select('FOUND_ROWS() AS found_rows');
		$iFilteredTotal = $this->db->get()->row()->found_rows;
	
		// Total data set length
		$this->db->where("isDeleted", 0);
		$this->db->from($sTable);
		$iTotal = $this->db->count_all_results();
	
		// Output
		$output = array(
			'sEcho' => intval($sEcho),
			'iTotalRecords' => $iTotal,
			'iTotalDisplayRecords' => $iFilteredTotal,
			'aaData' => array()
		);
		
		# Build output
		foreach($rResult->result_array() as $aRow)
		{
			$row = array();
				
			foreach($aColumns as $col)
			{
				if($col == 'id')
				{
					$row[]  = $aRow[$col];
					/*
					$field  = '<a class="btn-view" href="'.$this->config->item( 'base_url' ).'/recipes/saveRecipeDetails/'.$aRow[$col].'" title="Edit recipe - '.ucfirst($aRow['recipeTitle']).'" data-rel="tooltip">  </a>';
					$field .= '<button type="button" id="'.$aRow[$col].'" onclick="deleteRow('.$aRow[$col].')" class="btn-delete delete" data-rel="tooltip" title="Delete recipe - '.ucfirst($aRow['recipeTitle']).' "> </button>';
					*/
				}else if( $col =='recipeTitle'){
					$row[] = ucfirst($aRow[$col]);
				}else if( $col =='recipeImage'){
					if($aRow['recipeImage']!='')
					{
						$recipeImgPath = $aRow['recipeImage'];
						$thumbNailPath = str_replace('/recipes/','/recipes/thumbnail/',$recipeImgPath);
						
						# Check image exists!
						if(@getimagesize($thumbNailPath)){
							$recipeImgSource = $thumbNailPath;
						}else{
							$recipeImgSource = $this->config->item( 'base_url' ).'/assets/images/core/default_recipe.jpg';
						}
					}else{
						$recipeImgSource = $this->config->item( 'base_url' ).'/assets/images/core/default_recipe.jpg';
					}
					//$row[] = '<img src="'.$recipeImgSource.'" style="width: 50%;" />';
					//$row[] = '<img src="'.$recipeImgSource.'" style="width:75px;height=75px;" />';
					$row[] = $recipeImgSource;
				}else if( $col =='recipeTime'){
					$row[] =	date('H:i',strtotime($aRow[$col]));
				}else if( $col =='noOfServings'){
					$row[] =$aRow[$col];
				}
						
			} //  foreach($aColumns as $col)

			$row[] =	$aRow['difficulty_level'];
			$row[] =	$aRow['containsAlcohol'];
			$row[] = 	'';
			$output['aaData'][] = $row;
			
		} // foreach($rResult->result_array() as $aRow)
		
		echo json_encode($output);
	
	} // public function getRecipesData()
				
	
	 /* =============================================================================================================================
	 * Function Name : 
	 * saveRecipeDetails( $recipeId = 0 )
	 * 
	 * Function Description : 
	 * > This function is used to add / edit recipe details.
	 * 
	 * @params : 
	 * $userId ( int ) ||| Default : 0 ||| User ID
	 * =============================================================================================================================
	 */
	
	 public function saveRecipeDetails( $recipeId = 0 ) {
		$data 		    = array();
		$data['id'] 	= $recipeId;

		// Create an array for passing posted data.
		$postArray = array();
		$postArray = $this->input->post();
		
		# Get foodItemns units 
		$units = (array) $this->UN_Model->getUnits();
		
		// Check for Recipe ID
		if( (int)$recipeId > 0 ) {
		
			// Get recipe details and pass it to view.
			$recipeDetails     = $this->R_Model->getRecipeDetails( $recipeId );
			$sizeRecipeDetails =  sizeof( $recipeDetails );
			if( $sizeRecipeDetails > 0 ) {
				
				// Reciepe Found.
				// Assign user Details values to pass it to view.	
				$data['recipeTitle'] 			= $recipeDetails->recipeTitle;
				$data['recipeImage'] 			= $recipeDetails->recipeImage;
				$data['containsAlcohol']		= $recipeDetails->containsAlcohol;
				$data['caloriesForMale']		= $recipeDetails->caloriesForMale;
				$data['caloriesForFemale']		= $recipeDetails->caloriesForFemale;
				$data['difficulty']				= $recipeDetails->difficulty;
				$data['recipeTime']				= $recipeDetails->recipeTime;
				$data['noOfServings']			= $recipeDetails->noOfServings;
				$data['recommendedPercentageMale']	= $recipeDetails->recommendedPercentageMale;
				$data['recommendedPercentageFemale']= $recipeDetails->recommendedPercentageFemale;
				$data['isDeleted']				= $recipeDetails->isDeleted;
				$data['createdDateTime']		= $recipeDetails->createdDateTime;
				$data['modifiedDateTime']		= $recipeDetails->modifiedDateTime;
				$data['createdBy']				= $recipeDetails->createdBy;
				$data['modifiedBy']				= $recipeDetails->modifiedBy;
				
				$timeArray= explode(':', $data['recipeTime']);
				$data['recipeHour']				= $timeArray[0];
				$data['recipeMinute']			= $timeArray[1];
				
				
				$recipeIngredients = $this->RI_Model->getAllRecipeIngredients( $recipeId );
				$data['recipeIngredients'] = json_encode($recipeIngredients);
				
				$recipeInstructions = $this->RIN_Model->getAllRecipeInstructions( $recipeId );
				$data['recipeInstructions'] = json_encode($recipeInstructions);
				
				$foodItems = $this->FM_Model->getAllFoodItemsForAutocomplete( );
				$data['foodItems'] = json_encode($foodItems);
				
				$data['units'] = json_encode($units);
			} else {				
				// Recipe not found.
				// Set error message and redirect to avoid resubmission.
				$this->message->setMessage( "Recipe not found...", "ERROR" );
				redirect( $this->config->item( 'base_url' ).'/recipes' );
				
			} // else of if( $sizeUserDetails > 0 )
		} else {
				$data['recipeTitle'] 		= '';
				$data['recipeImage'] 		= '';
				$data['containsAlcohol']	= '';
				$data['caloriesForMale']	= '';
				$data['caloriesForFemale']	= '';
				$data['noOfServings']		= '';
				$data['difficulty']			= '';
				$data['recipeTime']			= '';
				$data['recipeHour']			= '';
				$data['recipeMinute']		= '';
				$data['recommendedPercentageMale']	= '';
				$data['recommendedPercentageFemale']= '';
				$data['isDeleted']			= '';
				$data['createdDateTime']	= '';
				$data['modifiedDateTime']	= '';
				$data['createdBy']			= '';
				$data['modifiedBy']			= '';
				$data['recipeIngredients']  = '';
				$data['recipeInstructions'] = '';
				$data['foodItems'] = '';
				$data['units'] = json_encode($units);
				
		} // else of if( (int)$userId > 0 )
				
		// Check whether data is posted or not.
		$sizePostArray = sizeof( $this->input->post());
		
		if( is_array( $this->input->post() ) && $sizePostArray > 0  && array_key_exists('securityValue',$this->input->post() )) {
			
			if( isset( $postArray['securityValue'] ) ) {
				unset( $postArray['securityValue'] );
			} // if( isset( $postArray['securityValue'] ) )
				
			if(!array_key_exists('containsAlcohol',$postArray))
			{
				$postArray['containsAlcohol']=0;
			}
			
			// Save recipe details using recipe's model. 
			$flag = $this->R_Model->saveRecipeDetails( $postArray, 1 );
			$sizeFlag = sizeof( $flag );
			
			if( is_array( $flag ) && $sizeFlag > 0 ) {
				if( $flag['class'] === "SUCCESS" ) {
						
					# Upload Recipe Image 
					$this->saveRecipeImage( $flag['recipeId'], $_FILES, $postArray['hiddenRecipeImage'] );
					
					// Set Success message and redirect to avoid resubmission
					$this->message->setMessage( $flag['message'], $flag['class'] );
					//redirect( $this->config->item( 'base_url' ) . "/recipes" );
					redirect( $this->config->item( 'base_url' ) . "/recipes/saveRecipeDetails/" . $flag['recipeId'] );
					
				} else {
					
					// Error in updating database
					// Set Success message and redirect to avoid resubmission
					$this->message->setMessage( $flag['message'], $flag['class'] );
					
					if( (int) $recipeId > 0 ) {
						redirect( $this->config->item( 'base_url' ) . "/recipes/saveRecipeDetails/" . $recipeId );
					} else {
						redirect( $this->config->item( 'base_url' ) . "/recipes/saveRecipeDetails" );
					} // else of if( (int) $userId > 0 )
					
				} // else of if( $flag['class'] === "SUCCESS" )
				
			} else {
				
				// Error
				$this->message->setMessage( "Error in saving information...", "ERROR" );
				
				// Set redirect URL.
				$redirectURL = $this->config->item( 'base_url' ).'/recipes/saveRecipeDetails';
			} // else of if( is_array( $flag ) && $sizeFlag > 0 )
			
			// Set redirect URL.
			$redirectURL = $this->config->item( 'base_url' ).'/recipes';
			
		} // 	if( is_array( $this->input->post() ) && $sizePostArray > 0 )
		
		// Load view.
		$this->load->view('recipes/update', $data);
	  } // public function saveUserDetails( $userId = 0 )
	  
	  
	  /* =============================================================================================================================
	 * Function Name : 
	 * saveRecipeImage( $recipeId = 0, $filesArray = array(), $hiddenRecipeImage='' )
	 * 
	 * Function Description : 
	 * > This function uploads advertisement image
	 * 
	 * @params : 
	 * $recipeId (int) 	||| Default : 0 || Recipe Id to update respective advertisement
	 * files array
	 * $hiddenRecipeImage (String) ||| Default : Blank || Previously uploaded recipe image
	 * =============================================================================================================================
	 */
	function saveRecipeImage( $recipeId = 0, $filesArray = array(), $hiddenRecipeImage='' ) {
		# Testing Details
		// Instantiate the S3 client with your AWS credentials
		$credentials = new Credentials(AWS_ACCESS_KEY, AWS_SECRET_KEY);
		$client = S3Client::factory(array(
				'credentials' => $credentials
		));
		
		$bucket 		= 'snappetite';
		$bucketPath 	= $bucket.'/uploads/recipes';
		$thumbnailPath  = $bucket.'/uploads/recipes/thumbnail';
		
		/*
		# Client Details
		// Instantiate the S3 client with your AWS credentials
		$credentials = new Credentials(AWS_ACCESS_KEY, AWS_SECRET_KEY);
		$client = S3Client::factory(array(
				'credentials' => $credentials,
				'region' => 'eu-west-1'
		));
		
		$bucket 	     = 'appitized-customers';
		$bucketPath      = $bucket.'/snappetite/prod/images/recipes';
		$thumbnailPath   = $bucket.'/snappetite/prod/images/recipes/thumbnail';
		*/
		
		if($filesArray['recipeImage']['name'] != ''){
			
			# Get Image information
			$imageInfo = pathinfo($filesArray['recipeImage']['name']);
			$ext = $imageInfo['extension'];
		
			//Rename image name.
			//$actual_image_name = $imageInfo['filename'].time().".".$ext;
			$actual_image_name = 'recipe_'.$recipeId.'_'.time().".".$ext;
			
			# Upload Image on Amazon S3 bucket
			$fileContent 	= file_get_contents($filesArray['recipeImage']['tmp_name']);
			
			// Upload a file.
			$result = $client->putObject(array(
					'Bucket'       => $bucketPath,
					'Key'          => $actual_image_name,
					'Body'   	   => $fileContent,
					'ContentType'  => 'image/jpeg',
					'ACL'          => 'public-read',
					'StorageClass' => 'STANDARD'
			));
								
			
			if($result['ObjectURL'] !=""){
				$s3file = str_replace("%2F", "/", $result['ObjectURL']);
			}else{
				$s3file='';
			}	
			
			if( $s3file){
				# Resize images
				//$thumbNail 	= "https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?url=".$result['ObjectURL']."&container=focus&resize_w=75&resize_h=75&refresh=2592000";
				
				$thumbNail 	= "https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?url=".$s3file."&container=focus&resize_w=75&resize_h=75&refresh=2592000";
				
				$thumbFileContent 	= file_get_contents($thumbNail);
				
				// Upload a file.
				$result = $client->putObject(array(
						'Bucket'       => $thumbnailPath,
						'Key'          => $actual_image_name,
						'Body'   	   => $thumbFileContent,
						'ContentType'  => 'image/jpeg',
						'ACL'          => 'public-read',
						'StorageClass' => 'STANDARD'
				));
					
				if($result['ObjectURL'] !=""){
					$s3fileThumb = str_replace("%2F", "/", $result['ObjectURL']);
				}else{
					$s3fileThumb='';
				}
			} // if( $s3file){
			
			/*
			# Delete previously uploaded image at the time of edit record if new image uploaded
			if($hiddenRecipeImage!='' && $s3file!='')
			{
				# Check file exitsts
				$checkFileExists = (bool)preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($hiddenRecipeImage)));
				
				if( $checkFileExists == 1){
					# Get previously uploaded image name
					$last_occurance = strrpos($hiddenRecipeImage, '/');
					$previousImageName = substr($hiddenRecipeImage, $last_occurance);
					$previousImageName = str_replace("/", "", $previousImageName);
					
					# Delete image from bucket
					$result = $client->deleteObject(array(
							'Bucket' => $bucketPath,
							'Key'    => $previousImageName
					));
				} // if( $checkFileExists == 1)
			}// if($hiddenRecipeImage!='' && $s3file!='')
			*/
			
			# Save Image Name
			$this->R_Model->saveImageName( $recipeId, $s3file);
		
		} // if($filesArray['recipeImage']['name'] != '')
		
	} // function saveRecipeImage( $recipeId = 0, $filesArray = array(), $hiddenRecipeImage='' )
	
	  
	 /* =============================================================================================================================
	 * Function Name : 
	 * checkDuplicateRecipe(  )
	 * 
	 * Function Description : 
	 * > This function used to check duplication of Recipe exists or not.
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	 
	 public function checkDuplicateRecipe() {
		$recipeTitle 	= trim($this->input->post( 'recipeTitle' ));
		$editId = $this->input->post( 'id' );
		echo $this->R_Model->checkDuplicateRecipe( $recipeTitle, $editId );
	} // public function checkDuplicateRecipe()
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * checkDuplicateRecipeIngredients(  )
	 * 
	 * Function Description : 
	 * > This function used to check duplication of Recipe Ingredients exists or not.
	 * 
	 * @params : $postArray() [ editId, recipeId, ingredientId ]
	 * =============================================================================================================================
	 */
	 
	 public function checkDuplicateRecipeIngredients() {
		
		$editId = $this->input->post( 'editId' );
		$recipeId = $this->input->post( 'recipeId' );
		$ingredientId = $this->input->post( 'ingredientId' );
		echo $this->RI_Model->checkDuplicateRecipeIngredients( $editId, $recipeId, $ingredientId );
	} // public function checkDuplicateRecipeIngredients()
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * getRecipeIngredients(  )
	 * 
	 * Function Description : 
	 * > This function used to get ingredient for the particular recipe
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	 
	 public function getRecipeIngredients() {
	 	$postArray = $this->input->post();
		$recipeId = $postArray['recipeId'];
		$output = $this->RI_Model->getAllRecipeIngredients( $recipeId );
		echo json_encode($output);
	} // public function getRecipeIngredients()
	
	 /* =============================================================================================================================
	 * Function Name : 
	 * saveIngredients(  )
	 * 
	 * Function Description : 
	 * > This function used to save ingredient for the particular recipe
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	 
	 public function saveIngredients() {
	 	$postArray = $this->input->post();
	 	
		$editId  = $postArray['editId'];
		$recipeId = $postArray['recipeId'];
		$ingredientId = $postArray['ingredientId'];
		
		// Save recipe details using recipe's model. 
		$flag = $this->RI_Model->saveRecipeIngredients( $postArray );
		$sizeFlag = sizeof( $flag );
		if( is_array( $flag ) && $sizeFlag > 0 ) {
				if( $flag['class'] === "SUCCESS" ) {
					$output = $this->RI_Model->getAllRecipeIngredients( $recipeId );
					echo json_encode($output);
					
				} else {
					// Error in updating database
					
				} // else of if( $flag['class'] === "SUCCESS" )
		} else {
			// Error
		} // else of if( is_array( $flag ) && $sizeFlag > 0 )
	} // public function saveIngredients()
	
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * deleteIngredients(  )
	 * 
	 * Function Description : 
	 * > This function used to save ingredient for the particular recipe
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	 
	 public function deleteIngredients() {
	 	$postArray = $this->input->post();
		$deleteId = $postArray['deleteId'];
		$recipeId = $postArray['recipeId'];
		// Save recipe details using recipe's model. 
		$flag = $this->RI_Model->deleteRecipeIngredients( $deleteId );
		$sizeFlag = sizeof( $flag );
		if( is_array( $flag ) && $sizeFlag > 0 ) {
				if( $flag['class'] === "SUCCESS" ) {
					$output = $this->RI_Model->getAllRecipeIngredients( $recipeId );
					echo json_encode($output);
					
				} else {
					// Error in updating database
					
				} // else of if( $flag['class'] === "SUCCESS" )
		} else {
			// Error
		} // else of if( is_array( $flag ) && $sizeFlag > 0 )
	} // public function saveIngredients()
	
	
	 /* =============================================================================================================================
	 * Function Name : 
	 * saveCalorieIntake(  )
	 * 
	 * Function Description : 
	 * > This function used to save Calorie Intake for the specific Recipe for the particular gender
	 * 
	 * @params : 
	 * $postArray()
	 * =============================================================================================================================
	 */
	 
	 public function saveCalorieIntake() {
	 	$postArray = $this->input->post();
		$recipeId = $postArray['recipeId'];
		// Save recipe details using recipe's model. 
		$flag = $this->R_Model->saveCalorieIntake( $postArray );
		$sizeFlag = sizeof( $flag );
		if( is_array( $flag ) && $sizeFlag > 0 ) {
				if( $flag['class'] === "SUCCESS" ) {
					//$output = $this->RI_Model->getAllRecipeIngredients( $recipeId );
					//echo json_encode($output);
					echo "success";
					
				} else {
					// Error in updating database
					
				} // else of if( $flag['class'] === "SUCCESS" )
		} else {
			// Error
		} // else of if( is_array( $flag ) && $sizeFlag > 0 )
	} // public function saveIngredients()
	
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * saveRecipeInstructions(  )
	 * 
	 * Function Description : 
	 * > This function used to save instructions for the particular recipe
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	 
	 public function saveRecipeInstructions() {
	 	$postArray = $this->input->post();
		
		$recipeId = $postArray['recipeId'];
		// Save recipe details using recipe's model. 
		$flag = $this->RIN_Model->saveRecipeInstructions( $postArray );
		$sizeFlag = sizeof( $flag );
		if( is_array( $flag ) && $sizeFlag > 0 ) {
				if( $flag['class'] === "SUCCESS" ) {
					$output = $this->RIN_Model->getAllRecipeInstructions( $recipeId );
					echo json_encode($output);
					
				} else {
					// Error in updating database
					
				} // else of if( $flag['class'] === "SUCCESS" )
		} else {
			// Error
		} // else of if( is_array( $flag ) && $sizeFlag > 0 )
	} // public function saveRecipeInstructions()
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * deleteRecipeInstructions(  )
	 * 
	 * Function Description : 
	 * > This function used to save ingredient for the particular recipe
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	 
	 public function deleteRecipeInstructions() {
	 	$postArray = $this->input->post();
		$deleteId = $postArray['deleteId'];
		$recipeId = $postArray['recipeId'];
		// Save recipe details using recipe's model. 
		$flag = $this->RIN_Model->deleteRecipeInstructions( $deleteId );
		$sizeFlag = sizeof( $flag );
		if( is_array( $flag ) && $sizeFlag > 0 ) {
				if( $flag['class'] === "SUCCESS" ) {
					$output = $this->RIN_Model->getAllRecipeInstructions( $recipeId );
					echo json_encode($output);
					
				} else {
					// Error in updating database
					
				} // else of if( $flag['class'] === "SUCCESS" )
		} else {
			// Error
		} // else of if( is_array( $flag ) && $sizeFlag > 0 )
	} // public function deleteRecipeInstructions()
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * checkDuplicateRecipeInstructions(  )
	 * 
	 * Function Description : 
	 * > This function used to check duplication of Recipe Instructions exists or not.
	 * 
	 * @params : $postArray() [ editId, recipeId, ingredientId ]
	 * =============================================================================================================================
	 */
	 
	 public function checkDuplicateRecipeInstructions() {
		
		$editId = $this->input->post( 'editId' );
		$recipeId = $this->input->post( 'recipeId' );
		$instructions = $this->input->post( 'instructions' );
		echo $this->RIN_Model->checkDuplicateRecipeInstructions( $editId, $recipeId, $instructions );
	} // public function checkDuplicateRecipeInstructions()
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * getRecipeInstructions(  )
	 * 
	 * Function Description : 
	 * > This function used to get Instructions for the particular recipe
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	 
	 public function getRecipeInstructions() {
	 	$postArray = $this->input->post();
		$recipeId = $postArray['recipeId'];
		$output = $this->RIN_Model->getAllRecipeInstructions( $recipeId );
		echo json_encode($output);
	} // public function getRecipeInstructions()
	
	
	
	/* =============================================================================================================================
	 * Function Name :
	* checkAllIngredientsIncluded(  )
	*
	* Function Description :
	* > This function used to check whether all ingredients are covered in the instrction.
	*
	* @params : $recipeId
	* =============================================================================================================================
	*/
	
	public function checkAllIngredientsIncluded() {
		$recipeId = $this->input->post( 'recipeId' );
		
		# Get recipe ingredients
		$ingredients = $this->RI_Model->getAllRecipeIngredients($recipeId);
		
		# Make ingredient array with initial count zero [ Count : No of occurance of the ingredient in instructions]
		$index=0;
		foreach( $ingredients as $ingredient)
		{
			$ingArr[$index]['ingredientTitle']=$ingredient['ingredientTitle'];
			$ingArr[$index]['count'] = 0;
			$index++;
		}
		
		# Get recipe instructions
		$instructions = $this->RIN_Model->getAllRecipeInstructions($recipeId);
		
		# Check ingredient is present in the instruction
		$index=0;
		foreach( $ingArr as $singleIng)
		{
			foreach( $instructions as $instruction)
			{
				if (strpos( strtolower($instruction['instructions']),strtolower($singleIng['ingredientTitle'])) !== false) {
					$ingArr[$index]['count'] = $ingArr[$index]['count'] + 1;
				}
			}
			$index++;
		}
		
		# if any ingredient count is 0 [ Zero] means this ingredient is not included in instruction.
		$result = "";
		foreach( $ingArr as $singleIng)
		{
			if($singleIng['count'] < 1)
			{
				if($result)
				{
					$result = $result.", ".$singleIng['ingredientTitle'];
				}else{
					$result = $singleIng['ingredientTitle'];
				}
			}
		}
		
		echo $result;
	} // public function checkAllIngredientsIncluded()
	
	
	
	/* =============================================================================================================================
	 * Function Name :
	* defaultRecipes( )
	*
	* Function Description :
	* > This function lists all business users.
	*
	* @params :
	* none
	* =============================================================================================================================
	*/
	
	public function defaultRecipes( ) {
		$this->load->view('recipes/defaultRecipes');
	} // public function defaultRecipes()
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * checkDuplicateDefaultRecipe(  )
	 * 
	 * Function Description : 
	 * > This function used to check duplication of Recipe exists or not.
	 * 
	 * @params : 
	 * $recipeId
	 * =============================================================================================================================
	 */
	 
	 public function checkDuplicateDefaultRecipe() {
		$recipeId 	= trim($this->input->post( 'recipeId' ));
		echo $this->R_Model->checkDuplicateDefaultRecipe( $recipeId );
	} // public function checkDuplicateRecipe()
	
	
	/* =============================================================================================================================
	 * Function Name :
	* saveIngredients(  )
	*
	* Function Description :
	* > This function used to save ingredient for the particular recipe
	*
	* @params :
	* none
	* =============================================================================================================================
	*/
	
	public function saveDefaultRecipe() {
		$postArray   = $this->input->post();
		
		// Save recipe details using recipe's model.
		$flag = $this->R_Model->saveDefaultRecipe( $postArray );
		$sizeFlag = sizeof( $flag );
		if( is_array( $flag ) && $sizeFlag > 0 ) {
			if( $flag['class'] === "SUCCESS" ) {
				$output = $this->R_Model->getAllDefaultRecipes();
				echo json_encode($output);
			} else {
				// Error in updating database
					
			} // else of if( $flag['class'] === "SUCCESS" )
		} else {
			// Error
		} // else of if( is_array( $flag ) && $sizeFlag > 0 )
	} // public function saveRecipeInstructions()
	
	
	
	/* =============================================================================================================================
	 * Function Name :
	* getDefaultRecipes(  )
	*
	* Function Description :
	* > This function used to get default recipes
	*
	* @params :
	* none
	* =============================================================================================================================
	*/
	
	public function getDefaultRecipes() {
		$output = $this->R_Model->getAllDefaultRecipes( );
		echo json_encode($output);
	} // public function getDefaultRecipes()
	
	
	
	/* =============================================================================================================================
	 * Function Name :
	* getRecipes( )
	*
	* Function Description :
	* > This function lists recipes.
	*
	* @params :
	* none
	* =============================================================================================================================
	*/
	
	public function getRecipes( ) {
		
		$postArray = $this->input->post();
		$term      = $postArray['term'];
		$recipes   = '';
		
		if( isset( $postArray['term'] )) {
			$term = $postArray['term'];
			$recipes = $this->R_Model->getRecipes($term);
			
			unset( $postArray['term'] );
		} // if( isset( $postArray['term'] ))
			
		echo json_encode($recipes);		
	} // public function getRecipes()
	
	
	
} // class Recipes extends CI_Controller

/* End of file Recipes.php */
/* Location: ./application/controllers/Recipes.php */
?>
