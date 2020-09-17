

function database_pull(){
	var rv = {};
	// assemble the data from application
	var dataDiv = document.getElementById( 'textinput' ); // get the data div
	rv.text = dataDiv.value;
	return rv;
}

function database_push( jscriptobject ){
	// place the data reception functions
	var dataDiv = document.getElementById( 'textinput' ); // get the data div
	dataDiv.value = jscriptobject.text;
	if( jscriptobject.config.editable == false ){
		var buttons = document.getElementsByTagName( 'input' );
		for( var i=0; i< buttons.length; i++ ){
			buttons[i].style.cssText = "display:none;";
		}
	}
	
}
