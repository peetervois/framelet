/*
 * This is interface javascript between framelet application and dokuwiki page
 *
 * the framelet application needs to have 2 functions:

function database_pull(){
	var rv = {};
	// FIXME: assemble the data from application
	return rv;
}

function database_push( jscriptobject ){
	// FIXME: place the data reception functions	
}

 *
 * The jscriptobject does have dokuwiki framelet reserved attributes:
 *
 {
	config: {
		editable: false, // true or false depending if the data from framelet can be saved into document
		container: 'dokuwiki' // tells to application that it does have docuwiki's framelet interface
	}
 }
 *
 * the iframe window does have method database_submit( string ), where string can be:
 *    'save' - causes framelet interface to call database_pull and saves the modifications to page
 *    'cancel' - makes the modifications in the framelet to be discarded

 *

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
	dataDiv.value = LZString.compressToEncodedURIComponent( JSON.stringify( data, null, 3 ) );
}

/*
 *  Push the data fromw dokuwiki page into iframe application.
 *  The application inside iframe shall have a method:

function database_push( jscriptobject ){
	// place the data reception functions	
}

 */

var iframe_is_ready = {};
var iframe_rescaler = {};
function framelet_push( divid ){
	// It appears that this function is called when the iframe is not ready, 
	// therefore wait a little with data initialisation.
	var dataDiv = document.getElementById( divid + '_data' ); // get the data div
	var myFrame = document.getElementById( divid + '_frame' ); // get the ifram div
	var myForm = document.getElementById('dw__editform');
	// keep track that the iframe with divid has been initializwd
	if( typeof iframe_is_ready[divid] == 'undefined' )
		iframe_is_ready[divid] = false;
	// attache onload event handler to the frame, for initialization load the content into frame
	myFrame.onload = function(){
		// mark the div to be ready for further push actions
		iframe_is_ready[divid] = true;
		//var compr = Base64.decode( dataDiv.value );
		// text = LZString.decompress( compr );
		text = LZString.decompressFromEncodedURIComponent( dataDiv.value );
		// parse the json object inside our section
		var data_base = {};
		try {
			data_base = JSON.parse( text ); 
		}
		catch(err){
			// silently ignore
			alert( "Error in the JSON data field !");
			data_base = {};
		}
		// in case of our data_base object creation failed in some way, then
		if( typeof data_base != 'object' || data_base == null )
			data_base = {}; // make it to be empty object for configuration
		// set the editable config parameter
		if( typeof data_base.config != 'object' )
			data_base.config = {}; // config was not part of the object already
		// tell to the application wether it is in the read-only or in read-write mode
		if( myForm == null )
			data_base.config.editable = false;
		else  
			data_base.config.editable = true;
		// tell to the application that it is inside dokuwiki's container'
		data_base.config.container = 'dokuwiki';
		// assign new data base into inframe, that application must have the method
		try {
			myFrame.contentWindow.database_push( data_base ); 
			// the framelet can trigger data saving by calling database_submit( javscriptobject )
			myFrame.contentWindow.database_submit = function( finish ){
				var data = myFrame.contentWindow.database_pull();
				try {
					dataDiv.value = LZString.compressToEncodedURIComponent( JSON.stringify( data, null, 3 ) );
				}
				catch( err ){
					// we cathced this error here
					alert( "Error in the JSON data field !");
					finish = "cancel";
				}
				// we need to trigger the saving of the data into docuwiki
				if( finish !== "save" ){
					finish = "cancel";
					if( !confirm("Are you sure, you will lose the changes !") ) return;
				}
				myForm.elements.do.value = finish;
				myForm.submit();
			}
		}
		catch( err ){
			// the application seems not to support database_push
			console.log( err );
		}
	};
	if( iframe_is_ready[divid] === true ){
		// from dokuwiki page you can call this method to force the data to be submitted again
		myFrame.onload();
	}
	else{
		// some tasks to perform on very first run
		// attach some handlers to form actions
		if( myForm !== null ){
			// attach the onsubmit event handler to the form, in case somebody clicks on submission buttons
			// on the dokuwiki
			myForm.onsubmit = function(){
				framelet_pull( divid );
				return true;
			};
		}
		// attach the autoscale handler to window.
		var myRefDiv = document.getElementById( divid + '_ifdiv' );
		if( myRefDiv != null ){
			var onresizehandler = function(){
				if( typeof iframe_rescaler[divid] !== 'undefined' ){
					clearTimeout( iframe_rescaler[divid] );
					delete iframe_rescaler[divid];
				}
				iframe_rescaler[divid] = setTimeout( function(){
					var divw = myRefDiv.offsetWidth;
					var divh = myRefDiv.offsetHeight;
					var reqw = myRefDiv.attributes.iframerequestwidth.nodeValue;
					delete iframe_rescaler[divid];
					if( reqw <= 0 ) return;
					var scale = divw / reqw;
					if( scale > 1 )
					{
						scale = 1;
						myFrame.style.width = ''+divw+'px';
					}	
					// look
					myFrame.style.height = ''+ divh/scale +'px';
					myFrame.style.webkitTransform = 'scale('+scale+')';
				}, 300);
			};
			window.onresize = onresizehandler;
			onresizehandler();
		}
	} 	
};

/*
 *  The framelet can call method 

database_submit('save');
database_submit('cancel');

 *  in order to make docuwiki to save the data
 */
 
