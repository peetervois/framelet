

/* DOKUWIKI:include base64.js */

/*
 *  Pull the data from iframe application.
 *  The application inside iframe shall have a method:

function database_pull(){
	var rv = {};
	// assemble the data from application
	return rv;
}
 */
function framelet_pull( divid ){
	var dataDiv = document.getElementById( divid + '_data' ); // get the data div
	var myFrame = document.getElementById( divid + '_frame' ); // get the ifram div
	var data = myFrame.contentWindow.database_pull();
	dataDiv.value = Base64.encode( JSON.stringify( data, null, 3 ) );
	// FIXME: we need to trigger the saving of the data into docuwiki
}

/*
 *  Push the data fromw dokuwiki page into iframe application.
 *  The application inside iframe shall have a method:

function database_push( jscriptobject ){
	// place the data reception functions	
}

 */

var iframe_is_ready = false;
function framelet_push( divid ){
	// It appears that this function is called when the iframe is not ready, 
	// therefore wait a little with data initialisation.
	var dataDiv = document.getElementById( divid + '_data' ); // get the data div
	var myFrame = document.getElementById( divid + '_frame' ); // get the ifram div
	var myForm = document.getElementById('dw__editform');
	myFrame.onload = function(){
		iframe_is_ready = true;
		var text = Base64.decode( dataDiv.value );
		// parse the json object inside our section
		var data_base = {};
		try {
			data_base = JSON.parse( text ); 
		}
		catch(err){
			// silently ignore
			alert( "Error in the JSON data field !");
		}
		if( typeof data_base != 'object' )
			data_base = {};
		// set the editable config parameter
		if( typeof data_base.config != 'object' )
			data_base.config = {};
		if( myForm == null )
			data_base.config.editable = false;
		else  
			data_base.config.editable = true;
		data_base.config.container = 'dokuwiki';
		// assign new data base into inframe, that application must have the method
		myFrame.contentWindow.database_push( data_base ); 
		// the framelet can trigger data saving by calling database_submit( javscriptobject )
		myFrame.contentWindow.database_submit = function( finish ){
			var data = myFrame.contentWindow.database_pull();
			try {
				dataDiv.value = Base64.encode( JSON.stringify( data, null, 3 ) );				
			}
			catch( err ){
				// we cathced this error here
			}
			// we need to trigger the saving of the data into docuwiki
			if( finish !== "save" ){
				finish = "cancel";
				if( !confirm("Are you sure, you will lose the changes !") ) return;
			}
			myForm.elements.do.value = finish;
			myForm.submit();
		}
	};
	if( iframe_is_ready === true ){
		myFrame.onload();
	}
	else if( myForm !== null )
	{
		// attach the onsubmit event handler to the form
		myForm.onsubmit = function(){
			framelet_pull( divid );
			return true;
		};
	}	
};

/*
 *  The framelet can call method 

database_submit('save');
database_submit('cancel');

 *  in order to make docuwiki to save the data
 */
 
