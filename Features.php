<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Features extends CI_Controller {
	
	 /* =============================================================================================================================
	 * Author 			: Manoj Kulkarni
	 * Company 			: Aress Software & Education Technologies (P) Ltd.
	 * 
	 * =============================================================================================================================
	 * About Features Controller :
	 * =============================================================================================================================
	 * This controller controls Users related operations.
	 * 
	 * Functions in this controller are :
	 * 1. constructor
	 * 2. index
	 * 3. saveFeatureDetails
	 * 4. checkDuplicateFeature
	 * =============================================================================================================================
	 *
	 * =============================================================================================================================
	 * Function Name : 
	 * __constrct ()
	 * 
	 * Function Description : 
	 * > This function creates a constructor for features controller.
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
		$this->load->model( "User_permissions_model", "UP_Model" );
		$this->load->model( "Features_Model", "F_Model" );
		$this->load->model( "Votingdetails_Model", "VD_Model" );
		
		
		//Check for user is logged In
			checkLogin();	
		
		//Check for user's permissions
			checkpermissions( "Features" );
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
			$message = $this->F_Model->performFeatureOperations( $this->input->post() );
			
			// Set message according to reply from model.
			$this->message->setMessage( $message['message'], $message['class'] );
			
			// give response and break execution.
			echo "true";
			die;
			
		} // if( is_array( $this->input->post()) && $sizePostArray > 0 )
		
		
		//Array for passing data to user's view
		$data 		   = array();
		
		# Get all features data
		$features 		   = $this->F_Model->getAllFeatures();
		
		# Get All voted Features
		$votedFeatures = $this->VD_Model->getVotedFeatures();
		
		//size of user records
		$featuresSize      			= sizeof($features);
		$data['votedFeatures'] 	 	= $votedFeatures;
		$data['features'] 	 		= $features;
		
		# Get the Admin logged in user permision 
		$id = $this->phpsession->get( 'adminUID' );		 
  	 	$data['permissions'] = $this->UP_Model->getPermissionDetails($id);
		
		
		// Load view
	    $this->load->view('features/list', $data);
	} // public function index()
	
	
	
	
	 /* =============================================================================================================================
	 * Function Name : 
	 * saveFeatureDetails( $featureId = 0 )
	 * 
	 * Function Description : 
	 * > This function is used to add / edit feature details.
	 * 
	 * @params : 
	 * $featureId ( int ) ||| Default : 0 ||| User ID
	 * =============================================================================================================================
	 */
	
	 public function saveFeatureDetails( $featureId = 0 ) {
		$data 		    = array();
		$data['id'] 	= $featureId;

		// Create an array for passing posted data.
		$postArray = array();
		$postArray = $this->input->post();
		
		// Check for Recipe ID
		if( (int)$featureId > 0 ) {
		
			// Get recipe details and pass it to view.
			$featureDetails     = $this->F_Model->getFeatureDetails( $featureId );
			$sizeFeatureDetails =  sizeof( $featureDetails );
			if( $sizeFeatureDetails > 0 ) {
				
				// Voting Found.
				// Assign feature Details values to pass it to view.	
				$data['votingOption'] 			= $featureDetails->votingOption;
				$data['description'] 			= $featureDetails->description;
				$data['isEnabled']				= $featureDetails->isEnabled;
				$data['isDeleted']				= $featureDetails->isDeleted;
				$data['createdDateTime']		= $featureDetails->createdDateTime;
				$data['modifiedDateTime']		= $featureDetails->modifiedDateTime;
				$data['createdBy']				= $featureDetails->createdBy;
				$data['modifiedBy']				= $featureDetails->modifiedBy;
			} else {				
				// Voting not found.
				// Set error message and redirect to avoid resubmission.
				$this->message->setMessage( "Feature not found...", "ERROR" );
				redirect( $this->config->item( 'base_url' ).'/features' );
				
			} // else of if( $sizeVotingDetails > 0 )
		} else {
				
				$data['votingOption'] 			= '';
				$data['description'] 			= '';
				$data['isEnabled']				= '';
				$data['isDeleted']				= '';
				$data['createdDateTime']		= '';
				$data['modifiedDateTime']		= '';
				$data['createdBy']				= '';
				$data['modifiedBy']				= '';
				
		} // else of if( (int)$votingId > 0 )
				
		// Check whether data is posted or not.
		$sizePostArray = sizeof( $this->input->post());
		
		if( is_array( $this->input->post() ) && $sizePostArray > 0 ) {
			
			if(!array_key_exists('isEnabled',$postArray))
			{
				$postArray['isEnabled']=0;
			}
			
			// Save feature details using recipe's model. 
			$flag = $this->F_Model->saveFeatureDetails( $postArray, 1 );
			$sizeFlag = sizeof( $flag );
			
			if( is_array( $flag ) && $sizeFlag > 0 ) {
				if( $flag['class'] === "SUCCESS" ) {
					
					// Set Success message and redirect to avoid resubmission
					$this->message->setMessage( $flag['message'], $flag['class'] );
					redirect( $this->config->item( 'base_url' ) . "/features" );
					
				} else {
					
					// Error in updating database
					// Set Success message and redirect to avoid resubmission
					$this->message->setMessage( $flag['message'], $flag['class'] );
					redirect( $this->config->item( 'base_url' ) . "/features" );
					
				} // else of if( $flag['class'] === "SUCCESS" )
				
			} else {
				
				// Error
				$this->message->setMessage( "Error in saving information...", "ERROR" );
				
				// Set redirect URL.
				$redirectURL = $this->config->item( 'base_url' ).'features/saveFeatureDetails';
			} // else of if( is_array( $flag ) && $sizeFlag > 0 )
			
			// Set redirect URL.
			$redirectURL = $this->config->item( 'base_url' ).'/features';
			
		} // 	if( is_array( $this->input->post() ) && $sizePostArray > 0 )
		
		// Load view.
		$this->load->view('features/update', $data);
	  } // public function saveFeatureDetails( $featureId = 0 )
	 
	  
	 /* =============================================================================================================================
	 * Function Name : 
	 * checkDuplicateFeature(  )
	 * 
	 * Function Description : 
	 * > This function used to check duplication of feature exists or not.
	 * 
	 * @params : 
	 * none
	 * =============================================================================================================================
	 */
	 
	 public function checkDuplicateFeature() {
		$votingOption 	= trim($this->input->post( 'votingOption' ));
		$editId = $this->input->post( 'id' );
		echo $this->F_Model->checkDuplicateFeature( $votingOption, $editId );
	} // public function checkDuplicateFeature()
	
	 		
} // class Features extends CI_Controller

/* End of file Votings.php */
/* Location: ./application/controllers/features.php */
?>
