

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
}
