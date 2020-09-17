
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
	dataDiv.innerText = btoa( JSON.stringify( data, null, 3 ) );
	// FIXME: we need to trigger the saving of the data into docuwiki
}

/*
 *  Push the data fromw dokuwiki page into iframe application.
 *  The application inside iframe shall have a method:

function database_push( jscriptobject ){
	// place the data reception functions	
}

 */
function framelet_push( divid ){
	// It appears that this function is called when the iframe is not ready, 
	// therefore wait a little with data initialisation.
	var dataDiv = document.getElementById( divid + '_data' ); // get the data div
	var myFrame = document.getElementById( divid + '_frame' ); // get the ifram div
	myFrame.onload = function(){
		var text = atob(dataDiv.innerText);
		var data_base = JSON.parse( text ); // parse the json object inside our section
		myFrame.contentWindow.database_push( data_base ); // assign new data base into inframe, that application must have the method
		// the framelet can trigger data saving by calling database_submit( javscriptobject )
		myFrame.contentWindow.database_submit = function(){
			var data = myFrame.contentWindow.database_pull();
			dataDiv.innerText = btoa( JSON.stringify( data, null, 3 ) );
			// FIXME: we need to trigger the saving of the data into docuwiki
		}
	};
};

/*
 *  The framelet can call method 

database_submit();

 *  in order to make docuwiki to save the data
 */
 
