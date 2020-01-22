<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Recipes_Model extends CI_Model {
	
	 /* =============================================================================================================================
	 * Author 			: Manoj Kulkarni
	 * Company 			: Aress Software & Education Technologies (P) Ltd.
	 * 
	 * =============================================================================================================================
	 * About Recipes Model :
	 * =============================================================================================================================
	 * This model performs all the operations on users table.
	 * 
	 * Functions in this Model are :
	 * 1. getAllRecipes
	 * 2. getAllRecipeTitles
	 * 3. saveRecipeDetails
	 * 4. checkDuplicateRecipe
	 * 5. getRecipeDetails
	 * 6. getRecipeDetailsToExport
	 * 7. saveImageName
	 * 8. saveCalorieIntake
	 * 9. performRecipeOperations
	 * 10. getRecipeInformation
	 * 11. getRecipiesCount
	 * 12. remoteFileExists
	 * 13. getRecipeId
	 * 14. deleteInvalidRecipes
	 * 15. getDefaultRecipes
	 * 16. getRecipesByIngredientId
	 * 17. checkDuplicateDefaultRecipe
	 * 18. saveDefaultRecipe
	 * 19. getAllDefaultRecipes
	 * 20. getRecipes
	/* =============================================================================================================================
	 * Function Name : 
	 * getAllRecipes( )
	 * 
	 * Function Description : 
	 * > This function returns all users list.
	 * 
	 * @params : 
	 * $isAdmin ( int ) ||| Default = 0 ||| Is Admin ||| This paramater specifies the user type.
	 * =============================================================================================================================
	 */
	 
	  /* Define class variables */
     var $tableName = "recipes";
	
	function getAllRecipes() {
		
		$this->db->select( 'id, recipeTitle, recipeImage, containsAlcohol, caloriesForMale, caloriesForFemale, noOfServings,difficulty, recipeTime, recommendedPercentageMale, recommendedPercentageFemale, isDeleted,  createdDateTime,  modifiedDateTime, createdBy, modifiedBy' );
		$this->db->where( "isDeleted", 0 );
		$this->db->order_by( "createdDateTime DESC" );
		$query = $this->db->get( $this->tableName );
		
		if( sizeof( $query ) > 0 ) {
			return $query->result();
		} else {
			return array();
		} // else of if( sizeof( $query ) > 0 )
	} // function getAllUsers( isAdmin = 0 )
	
	/* =============================================================================================================================
	 * Function Name :
	* getAllRecipeTitles( )
	*
	* Function Description :
	* > This function gets all recipe titles in ascending order
	*
	* @params :
	* No parameters
	* =============================================================================================================================
	*/
	
	function getAllRecipeTitles() {
	
		$this->db->select( 'id, recipeTitle' );
		$this->db->where( "isDeleted", 0 );
		$this->db->order_by( "recipeTitle ASC" );
		$query = $this->db->get( $this->tableName );
	
		if( sizeof( $query ) > 0 ) {
			return $query->result();
		} else {
			return array();
		} // else of if( sizeof( $query ) > 0 )
	} // function getAllRecipeTitles()
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * saveRecipeDetails( $recipeId )
	 * 
	 * Function Description : 
	 * > This function saves recipe details and returns newly inserted id.
	 * 
	 * @params : 
	 * $postArray ( array ) ||| Default = array() ||| Posted information array.
	 * =============================================================================================================================
	 */
	
	function saveRecipeDetails( $postArray = array() ) {
		# unset unneeded values.
		if( isset( $postArray['btnSubmit'] ) ) {
			unset( $postArray['btnSubmit'] );
		} // if( isset( $postArray['btnSubmit'] ) )
		
		if( isset( $postArray['hiddenRecipeImage'] ) ) {
			unset( $postArray['hiddenRecipeImage'] );
		} // if( isset( $postArray['hiddenRecipeImage'] ) )
		
		
		$postArray['recipeTime'] =  $postArray['recipeHour'] .":" . $postArray['recipeMinute'] . ":00";
		
		if( isset( $postArray['recipeHour'] ) ) {
			unset( $postArray['recipeHour'] );
		} // if( isset( $postArray['recipeHour'] ) )
		
		if( isset( $postArray['recipeMinute'] ) ) {
			unset( $postArray['recipeMinute'] );
		} // if( isset( $postArray['recipeMinute'] ) )
		
		if( isset( $postArray['ingredientId'] ) ) {
			unset( $postArray['ingredientId'] );
		} // if( isset( $postArray['recipeMinute'] ) )
		
		if( isset( $postArray['ingredientTitle'] ) ) {
			unset( $postArray['ingredientTitle'] );
		} // if( isset( $postArray['ingredientTitle'] ) )
			
		
		# Validate user form
		$error				= '';
		$setErrorFlag = true;
		
		# Fields of posted data
		$id 			= $postArray['id'];
		$recipeTitle 	= trim( $postArray['recipeTitle'] );
		$difficulty		= trim( $postArray['difficulty'] );
		$noOfServings	= trim( $postArray['noOfServings'] );
		$containsAlcohol= trim( $postArray['containsAlcohol'] );
		
		#SQL Injection Validation Start
		
		# Validate recipe title
		if( !$this->form_validation->required( $recipeTitle )) {
			$error .= "<br />Please enter recipe name.";
			$setErrorFlag = false;
		} else if ( !$this->form_validation->min_length( $recipeTitle, 2 )) {
			$error .= "<br />Enter minimum 2 characters for recipe name.";
			$setErrorFlag = false;
		} else if ( !$this->form_validation->max_length( $recipeTitle, 32 ) ) {
			$error .= "<br />Enter maximum 32 characters for recipe name.";
			$setErrorFlag = false;
		} 
		
		# Validate difficulty
		if( !$this->form_validation->required( $difficulty )) {
			$error .= "<br />Please select difficulty.";
			$setErrorFlag = false;
		}
		
		# Validate noOfServings
		if( !$this->form_validation->required( $noOfServings )) {
			$error .= "<br />Please enter no Of servings.";
			$setErrorFlag = false;
		} 
				
		if( !$setErrorFlag ) {
			$flag['message'] 	= "".$error;
			$flag['class']		= "ERROR";
			
			return $flag;
		} // if( !$setErrorFlag )
		
		#SQL Injection Validation Ends
		
		if( (int)$id > 0 && $setErrorFlag ) {
			// set modified date time.
			$postArray['modifiedDateTime'] = date( "Y-m-d H:i:s" );

			// Add user id who modified this record
			$postArray['modifiedBy'] = (int) $this->phpsession->get( 'adminUID' );
									
			// Update Record
			$this->db->where( 'id', $id );
			$query = $this->db->update( $this->tableName, $postArray );
			
			$flag['message'] 	= "Recipe saved successfully.";
			$flag['class']		= "SUCCESS";
			$flag['recipeId']		= $id;
			return $flag;
				
		} // if( (int)$isEdit > 0 && $setErrorFlag )
		
		if( (int) $id == 0 && $setErrorFlag ) {
			// Set isDeleted value - Default 0
			$postArray['isDeleted'] = 0;
			
			// set registration date time.
			$postArray['createdDateTime'] = date( "Y-m-d H:i:s" );
			// set modified date time.
			$postArray['modifiedDateTime'] = date( "Y-m-d H:i:s" );
	
			// Add user id who created this record
			$postArray['createdBy'] = (int) $this->phpsession->get( 'adminUID' );
			
			// Add user id who modified this record
			$postArray['modifiedBy'] = (int) $this->phpsession->get( 'adminUID' );
	
			// Insert Record
			$this->db->insert( $this->tableName, $postArray );
			
			if( (int)$this->db->insert_id() > 0 ) {
				$flag['message'] 	= "Recipe saved successfully.";
				$flag['class']		= "SUCCESS";
				$flag['recipeId']	= (int)$this->db->insert_id();
				return $flag;
			} else {
				$flag['message'] 	= "Unable to insert recipe.";
				$flag['class']		= "ERROR";
				return $flag;
			} // else of if( (int)$this->db->insert_id() > 0 )
		
		} // if( (int) $editId == 0 && $setErrorFlag )
		
		// Insert Record
		$this->db->insert( $this->tableName , $postArray );
	} // function getUserDetails( $userId )
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * function checkDuplicateRecipe( $recipeTitle = '', $editId = 0 ) {
	 * 
	 * Function Description : 
	 * > This function is used to check whether recipe title exist in recipe table or not.
	 * > This function returns success / error messages with class after performing operation.
	 * 
	 * @params : 
	 * $recipeTitle ( '' ) 		||| Default : '' 			||| Post recipeTitle from add/edit form.
	 * $editId ( int )			||| Default : 0				||| To specify insert / update.
	 * =============================================================================================================================
	 */
	 
	 function checkDuplicateRecipe( $recipeTitle = '', $editId = 0 ) {
	 
		$this->db->select( "id" );
		$this->db->where( "recipeTitle", $recipeTitle );
		$this->db->where( "isDeleted", 0 );
		
		if( (int)$editId > 0 ) {
			$this->db->where( "id != " . $editId );
		} // if( (int)$editId > 0 )
		
		$query = $this->db->get( $this->tableName );
		
		if( $query->num_rows() > 0 ) {
			return "false";
		} else {
			return "true";
		} // if( $query->num_rows() > 0 )
		
	} // function checkDuplicateRecipe( $recipeTitle = '', $editId = 0 )
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * getRecipeDetails( $recipeId )
	 * 
	 * Function Description : 
	 * > This function returns selected recipe's details.
	 * > If details not found, it returns empty array.
	 * 
	 * @params : 
	 * $recipeId ( int ) ||| Default = 0 ||| User ID ||| This paramater specifies the user selected.
	 * =============================================================================================================================
	 */
	
	function getRecipeDetails( $recipeId = 0 ) {
		$this->db->select('id, recipeTitle, recipeImage, containsAlcohol, caloriesForMale, caloriesForFemale, difficulty,recipeTime, noOfServings, recommendedPercentageMale, recommendedPercentageFemale, isDeleted, createdDateTime,  modifiedDateTime, createdBy,modifiedBy');
  
		$this->db->where( "id", $recipeId );
		$query = $this->db->get( $this->tableName );
		
		if( sizeof( $query ) > 0 ) {
			return $query->row();
		} else {
			return array();
		} // else of if( sizeof( $query ) > 0 )
	} // function getRecipeDetails( $recipeId )
	
	
	
	/* =============================================================================================================================
	 * Function Name :
	* getRecipeDetails( $recipeId )
	*
	* Function Description :
	* > This function returns selected recipe's details.
	* > If details not found, it returns empty array.
	*
	* @params :
	* $recipeId ( int ) ||| Default = 0 ||| User ID ||| This paramater specifies the user selected.
	* =============================================================================================================================
	*/
	
	function getRecipeDetailsToExport( $recipeId = 0 ) {
		
		$this->db->select('id, recipeTitle, recipeImage, containsAlcohol, caloriesForMale, caloriesForFemale, difficulty,recipeTime, noOfServings, recommendedPercentageMale, recommendedPercentageFemale, isDeleted, createdDateTime,  modifiedDateTime, createdBy,modifiedBy');
		
		# Select all active recipes if recipeId = all
		if((int)$recipeId > 0 )
		{	
			$this->db->where( "id", $recipeId );
		}
			
		$this->db->where( "isDeleted", 0 );
		$this->db->order_by( "recipeTitle ASC" );
		$query = $this->db->get( $this->tableName );
	
		if( sizeof( $query ) > 0 ) {
			return $query->result_array();
		} else {
			return array();
		} // else of if( sizeof( $query ) > 0 )
	} // function getRecipeDetails( $recipeId )
		
	
	 /* =============================================================================================================================
	 * Function Name : 
	 * saveImageName()
	 * 
	 * Function Description : 
	 * > This function save image name
	 * 
	 * @params :
	 * $recipeId :  Recipe id
	 * $imageName: image name
	 * =============================================================================================================================
	 */
	function saveImageName( $recipeId = 0, $imageName = '' ) {
		$data['recipeImage'] = $imageName;
		$this->db->where( "id", $recipeId );
		$this->db->update(  $this->tableName, $data );
	} // function saveImageName( $userId = 0, $imageName = '' )	
	
	
	
	/* =============================================================================================================================
    * Function Name :
    * saveCalorieIntake( $postArray = array())
    *
    * Function Description :
    * > This function is used to update record in database.
    * > This function returns success / error messages with class after performing operation.
    *
    * @params :
    * $postArray ( Array ) 	||| Default : array() ||| Post Array from listing form.
    * =============================================================================================================================
    */

    function saveCalorieIntake( $postArray = array() ) {
				
        $flag     = array();
        $sizePost = sizeof( $postArray );
		$recipeId   = $postArray['recipeId'];
		$gender   = $postArray['gender']; 
		
        if( $sizePost > 0 ) {
            // unset ID
            if( isset( $postArray['recipeId'] )) {
                $recipeId = $postArray['recipeId'];
                unset( $postArray['recipeId'] );
            } // if( isset( $postArray['edit_id'] ))
			
            if( $gender == 'male'  ) {
                $postArray['caloriesForMale'] = $postArray['cal'];
				$postArray['recommendedPercentageMale'] = $postArray['cal_percentage'];
			}else if( $gender == 'female'  ) {
				$postArray['caloriesForFemale'] = $postArray['cal'];
				$postArray['recommendedPercentageFemale'] = $postArray['cal_percentage'];
			}
			
			if( isset( $postArray['gender'] )) {
                unset( $postArray['gender'] );
            } // if( isset( $postArray['gender'] ))
			
			if( isset( $postArray['cal'] )) {
                unset( $postArray['cal'] );
            } // if( isset( $postArray['gender'] ))
			
			if( isset( $postArray['cal_percentage'] )) {
                unset( $postArray['cal_percentage'] );
            } // if( isset( $postArray['gender'] ))
               
                // Update Record
                $this->db->where( 'id', $recipeId );
                $query = $this->db->update( $this->tableName, $postArray );

                $flag['message'] = "Recipe calorie intake saved successfully.";
				$flag['id'] 	= $recipeId;
            	$flag['class']  = 'SUCCESS';

           		 return $flag;
         } else {
            $flag['message'] = 'Something went wrong...'.$error;
            $flag['class']   = 'ERROR';
            return $flag;
        } // else of if( $sizePost > 0 )

    } // function saveFoodItem( $postArray = array(), $isEdit = 0 )
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * performRecipeOperations( $postArray = array() )
	 * 
	 * Function Description : 
	 * > This function is used to delete  recipes from database.
	 * > This function returns success / error messages with class after performing operation.
	 * 
	 * @params : 
	 * $postArray ( Array ) ||| Default : array() ||| Post Array from listing form.
	 * =============================================================================================================================
	 */
	
	 function performRecipeOperations( $postArray = array() ) {
	 
		$message 	= array();
		$size 	 	= sizeof( $postArray );
		
		if( $size > 0 ) {
		
			// Specify action
			$action = $postArray['action'];
			
			// Specify records on which operations to be carried out.
			$idList = $postArray['ids'];
			
			// Set default error message and class.
			$message['message'] = "Something went wrong...";
			$message['class'] 	= "ERROR";
			
			// Perform operation.
			switch( $action ) {
				case "delete"  :	
									// Delete Recipe Ingredients
									$this->db->where( "recipeId in (".$idList.")");
									$this->db->delete('recipeIngredients');
									
									// Delete Recipe Instructions
									$this->db->where( "recipeId in (".$idList.")");
									$this->db->delete('recipeInstructions'); 

									// Delete recipe
									$this->db->where( "id in (".$idList.")"  );
									$this->db->update( $this->tableName, array( "isDeleted" => 1 ));
									$message['message'] = " Recipe deleted successfully.";
									$message['class'] 	= "SUCCESS";
									break;
				
				default : $message['message'] = "Invalid action...";
									$message['class'] = "ERROR";
									break;
				
			} // switch( $action )
			
			return $message;
		} else {
			$message['message'] = "Something went wrong...";
			$message['class'] = "ERROR";
			return $message;
		} // else of if( sizeof( $postArray ) > 0 )
		
	 } // function performCategoryOperations( $postArray = array() )
	 
	 
	/* =============================================================================================================================
	 * Function Name : 
	 * getRecipeDetails( $recipeId )
	 * 
	 * Function Description : 
	 * > This function returns all recipe's details.
	 * 
	 * 
	 * @params : 
	 * $data 
	 * =============================================================================================================================
	 */
	
	function getRecipeInformation($fields ) {
		
		$fields = str_replace("caloriesForMale", "recipes.caloriesForMale", $fields);
		$fields = str_replace("caloriesForFemale", "recipes.caloriesForFemale", $fields);
		$fields = str_replace("quantity", "recipeIngredients.quantity", $fields);
		$this->db->distinct();
		$this->db->select($fields);	
		$this->db->from('recipes,ingredients');
		$this->db->join( 'recipeIngredients', 'recipeIngredients.recipeId = recipes.id ','left outer' );
		$this->db->where( "recipes.isDeleted", 0 );
		$this->db->group_by( "recipes.id" );
		$result = $this->db->get();
		
		return $result->result_array();
	
	} // function getRecipeInformation( $recipeId )
	
	
	
	/* =============================================================================================================================
	 * Function Name : 
	 * getRecipiesCount
	 * 
	 * Function Description : 
	 * > This function returns recipies count
	 * > If details not found, it returns empty array.
	 * 
	 * @params : 
	 * $recipeId ( int ) ||| Default = 0 ||| User ID ||| This paramater specifies the user selected.
	 * =============================================================================================================================
	 */
	function getRecipiesCount() {
		$this->db->select( 'id' );
		$this->db->where( "isDeleted", 0 );
		$query = $this->db->get( $this->tableName );
		return (int)$query->num_rows();
	} // function getAllUsers( isAdmin = 0 )
	
	
	/* =============================================================================================================================
	 * Function Name :
	* remoteFileExists
	*
	* Function Description :
	* > This function check the particular file exists at the remote server
	* > If details not found, it returns empty array.
	*
	* @params :
	* $url ( browser furl of the file )
	* =============================================================================================================================
	*/
	function remoteFileExists($url){
		if(@getimagesize($url)){
			return true;
		}else{
			return false;
		}
	}
	
	
	/* =============================================================================================================================
	 * Function Name :
	* getRecipeId( $recipeTitle )
	*
	* Function Description :
	* > This function returns selected recipe's id from recipe Title.
	* > If details not found, means recipe not exists then insert new recipe and get recipe Id.
	*
	* @params :
	* $recipeTitle ( string ) ||| Default = '' 
	* =============================================================================================================================
	*/
	
	function getRecipeId( $recipeTitle = '' ) {
		$this->db->select('id');
		$this->db->where( "recipeTitle", $recipeTitle );
		$this->db->where( "isDeleted", 0 );
		$query = $this->db->get( $this->tableName );
		$data = $query->row();
	
		if( sizeof( $data ) > 0 ) {
			$data = $query->row();
			return $data->id; 
		} else {
			# insert new recipe and get recipe Id
			$insertData['recipeTitle'] = $recipeTitle;
			$insertData['isDeleted'] = 0;
			$insertData['createdDateTime'] = date( "Y-m-d H:i:s" );
			$insertData['modifiedDateTime'] = date( "Y-m-d H:i:s" );
			$insertData['createdBy'] = (int) $this->phpsession->get( 'adminUID' );
			$insertData['modifiedBy'] = (int) $this->phpsession->get( 'adminUID' );
			
			// Insert Record
			$this->db->insert( $this->tableName, $insertData );
			return $this->db->insert_id();
		} // else of if( sizeof( $data ) > 0 )
	} // function getRecipeId( $recipeTitle )
	
	
	
	/* =============================================================================================================================
	 * Function Name :
	* deleteInvalidRecipes( )
	*
	* Function Description :
	* > This function get recipeId from recipeIngredients which ingredientId is 0.Delete recipe from recipe table , respective recipe Ingredients, Instructions 
	* > Returns count of Distinct recipe Id's 
	*
	* @params :
	* $recipeTitle ( string ) ||| Default = ''
	* =============================================================================================================================
	*/
	
	function deleteInvalidRecipes() {
		# Get distinct recipe Id's which IngredientId's are 0 in "recipeIngredients" table.
		
		$this->db->distinct();
		$this->db->select('recipeId');
		$this->db->where( "ingredientId", 0 );
		$query = $this->db->get( "recipeIngredients" );
		$results = $query->result_array();
		
		# Get Distinct Recipes Count
		$recipeCount = count($results);
		$recipeIds = '';
		foreach($results as $result)
		{
			if($recipeIds)
			{
				$recipeIds = $recipeIds.",".$result['recipeId'];
			}else{
				$recipeIds = $result['recipeId'];
			}
		}
		
		if( $recipeIds ){
			// Delete Recipe Instructions 
			$this->db->where( "recipeId in (".$recipeIds.")");
			$this->db->delete("recipeInstructions");
			
			// Delete Recipe Ingredients
			$this->db->where( "recipeId in (".$recipeIds.")");
			$this->db->delete("recipeIngredients");
			
			// Delete Recipes
			$this->db->where( "id in (".$recipeIds.")");
			$this->db->delete("recipes");
		}
		
		return $recipeCount;
		
	} // function deleteInvalidRecipes()
	
	/* =============================================================================================================================
	 * Function Name :
	* getDefaultRecipes
	*
	* Function Description :
	* > This function get recipes listing with all ingredient and brand details used to show as default recipes. 
	*
	* @params :
	* $recipeCount ( int ) ||| Display ( $recipeCount ) no of recipes.
	* =============================================================================================================================
	*/
	
	function getDefaultRecipes($limit=0) {
	
		$this->db->select( 'id, recipeTitle, recipeImage, containsAlcohol, caloriesForMale, caloriesForFemale, noOfServings,difficulty, recipeTime, recommendedPercentageMale, recommendedPercentageFemale, isDeleted,  createdDateTime,  modifiedDateTime, createdBy, modifiedBy,isDefault' );
		$this->db->where( "isDeleted", 0 );
		$this->db->where( "isDefault", 1 );
		$this->db->order_by( "createdDateTime DESC" );
		if($limit > 0)
		{
			$this->db->limit($limit, 0);
		}			
		$query = $this->db->get( $this->tableName );
	
		if( sizeof( $query ) > 0 ) {
			return $query->result();
		} else {
			return array();
		} // else of if( sizeof( $query ) > 0 )
	} // function getDefaultRecipes($limit) 
	
	
	/* =============================================================================================================================
	 * Function Name :
	* getRecipesByIngredientId($ingredientIds,$excludeRecipes,$inInventory)
	*
	* Function Description :
	* > This function get recipes listing by ingredient ids.
	*
	* @params :
	* $ingredientIds ( string )  ||| comma separated $ingredientIds
	* $excludeRecipes ( string ) ||| comma separated $excludeRecipes
	* $inInventory ( string)     ||| 'true' => Recipe ingredients are from user's inventory or 'false' 
	* =============================================================================================================================
	*/
	
	function getRecipesByIngredientId($ingredientIds,$excludeRecipes,$inInventory) {
		$results  = array();
		if($ingredientIds)
		{
			$cond  = $excludeRecipes!='' ? ' and r.id NOT IN ( '.$excludeRecipes .') ' : '';
			$qry   = 'SELECT r.*,i.id as ingredientId , i.ingredientTitle, "'.$inInventory.'" as inInventory
					  FROM recipes r
					  LEFT JOIN `recipeIngredients` ri ON r.id = ri.recipeId
					  LEFT JOIN `ingredients` i ON i.id = ri.ingredientId
					  WHERE ri.ingredientId IN ( '.$ingredientIds.' ) ' . $cond. '
					  GROUP BY r.id ';
			
			$results  =  $this->db->query($qry)->result_array();
		}
		
		return $results;
		
	} // function getRecipesByIngredientId($ingredientIds,$excludeRecipes,$inInventory)
	
	
	
	/* =============================================================================================================================
	 * Function Name :
	* function checkDuplicateDefaultRecipe( $recipeId)
	*
	* Function Description :
	* > This function is used to check whether recipe is alredy set as default recipe.
	* > This function returns success / error messages with class after performing operation.
	*
	* @params :
	* * $recipeId ( int )			||| Default : 0				||| recipe id to set as default recipe
	* =============================================================================================================================
	*/
	
	function checkDuplicateDefaultRecipe( $recipeId) {
	
		$this->db->select( "id" );
		$this->db->where( "id", $recipeId );
		$this->db->where( "isDeleted", 0 );
		$this->db->where( "isDefault", 1 );
	
		$query = $this->db->get( $this->tableName );
	
		if( $query->num_rows() > 0 ) {
			return "false";
		} else {
			return "true";
		} // if( $query->num_rows() > 0 )
	
	} // function checkDuplicateDefaultRecipe( $recipeId)
	
	
	
	/* =============================================================================================================================
	 * Function Name :
	* saveRecipeIngredients( $postArray = array(), $isEdit = 0 )
	*
	* Function Description :
	* > This function is used to insert / update record in database.
	* > This function returns success / error messages with class after performing operation.
	*
	* @params :
	* $postArray ( Array ) 	||| Default : array() ||| Post Array from listing form.
	* $isEdit ( int )			||| Default : 0				||| To specify insert / update.
	* =============================================================================================================================
	*/
	
	function saveDefaultRecipe( $postArray = array() ) {
		/*
		echo "<pre>";
		print_r($postArray);
		exit;
		*/
		
		$flag     = array();
		$sizePost = sizeof( $postArray );
	
		if( $sizePost > 0 ) {
			// unset ID
			if( isset( $postArray['recipeId'] )) {
				$recipeId = $postArray['recipeId'];
				unset( $postArray['recipeId'] );
			} // if( isset( $postArray['recipeId'] ))
				
			if( isset( $postArray['oldRecipeID'] )) {
				$oldRecipeID = $postArray['oldRecipeID'];
				unset( $postArray['oldRecipeID'] );
			} // if( isset( $postArray['oldRecipeID'] ))
	
				
			if( (int)$recipeId > 0  ) {
				
				# Set value to update
				$postArray['modifiedDateTime'] = date( "Y-m-d H:i:s" );
				$postArray['modifiedBy'] = (int) $this->phpsession->get( 'adminUID' );
	
				// Update Record
				$this->db->where( 'id', $recipeId );
				$query = $this->db->update( $this->tableName, $postArray );
				
				# Unmap old recipeid as default recipe
				if($recipeId != $oldRecipeID && $oldRecipeID > 0 )
				{
					$updateArray['isdefault'] = 0;
					$updateArray['modifiedDateTime'] = date( "Y-m-d H:i:s" );
					$updateArray['modifiedBy'] = (int) $this->phpsession->get( 'adminUID' );
					
					// Update Record
					$this->db->where( 'id', $oldRecipeID );
					$query = $this->db->update( $this->tableName, $updateArray );
				}
				
				$flag['id'] 	= $recipeId;
				$flag['class']  = 'SUCCESS';
				$flag['message'] = "Recipe successfully set as default recipe.";
				return $flag;
			}else{
				$flag['message'] = 'Something went wrong...';
				$flag['class']   = 'ERROR';
				return $flag;
			} // if( (int)$recipeId > 0  ) {
	
		} //if( $sizePost > 0 )
		else {
			$flag['message'] = 'Something went wrong...';
			$flag['class']   = 'ERROR';
			return $flag;
		} // else of if( $sizePost > 0 )
	
	} // function saveDefaultRecipe( $postArray = array() )
	
	
	/* =============================================================================================================================
	 * Function Name :
	* getAllDefaultRecipes( )
	*
	* Function Description :
	* > This function gets all getAllDefaultRecipes in ascending order
	*
	* @params :
	* No parameters
	* =============================================================================================================================
	*/
	
	function getAllDefaultRecipes() {
	
		$this->db->select( 'id, recipeTitle' );
		$this->db->where( "isDeleted", 0 );
		$this->db->where( "isDefault", 1 );
		$this->db->order_by( "recipeTitle ASC" );
		$query = $this->db->get( $this->tableName );
	
		if( sizeof( $query ) > 0 ) {
			return $query->result();
		} else {
			return array();
		} // else of if( sizeof( $query ) > 0 )
	} // function getAllDefaultRecipes()
	
	
	/* =============================================================================================================================
	 * Function Name :
	* getAllRecipeTitles( )
	*
	* Function Description :
	* > This function gets all recipe titles in ascending order
	*
	* @params :
	* No parameters
	* =============================================================================================================================
	*/
	
	function getRecipes($term) {
	
		$this->db->select( 'id, recipeTitle' );
		$this->db->where( "isDeleted", 0 );
		$this->db->where('recipeTitle LIKE "'.$term.'%"');
		$this->db->order_by( "recipeTitle ASC" );
		$query = $this->db->get( $this->tableName );
	
		if( sizeof( $query ) > 0 ) {
			return $query->result();
		} else {
			return array();
		} // else of if( sizeof( $query ) > 0 )
	} // function getAllRecipeTitles()
	
	
			
} // class Recipes_Model extends CI_Model

/* End of file Recipes_Model.php */
/* Location: ./application/models/Recipes_Model.php */
?>