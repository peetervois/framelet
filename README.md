# Framelet Plugin for DokuWiki

Framelet plugin places remote javascript page that acts as custom editor or viewer
for the data.

## Short Description

Framelet plugin does bring up remote html page, loads initial data into `<iframe>`
using specific javascript interface and does save modified data into dokuwiki's 
TXT file. This plugin's behaviour is meant to be pretty generic for variety of 
custom possible data analysis framelets. Dokuwiki does act as JSON data storage
and versioning tool. Also you will see your favorite helper gadget inside of other
dokumentation of your project.

For exchange of JSON data between web server and browser window, framelet plugin
does compress the JSON content. Depending on content, compressed data can be ~5x 
smaller than original content.

## How to Use

```
<framelet width=100% height=300px scale=0.95 href=lib/plugins/framelet/test/index.html >
{
   "text":"This is written on the docuwiki page"
}
</framelet>
```

The index.html page is loaded into inframe window placed into your document where the 
**framelet** tag is specified. You can specify **width** and **height** of the inframe window and
the **scale** factor. Sometimes your framelet requires more space than wiki provides, then you
can specify the width that your page needs and then scale it to fit into page.

The **href** link is prefixed withg docuwiki's root folder.

## The Framelet Interface

The Framelet plugin exchanges javascript object with application.
After the page has been loaded and iframe initialized, the framelet interface makes an attempt
to submit the data object into iframe application. For that the iframe application needs to
have the following function attached to *window*:

```
function database_push( jscriptobject )
{
	// FIXME: place the data reception functions	
}
```
The jscriptobject is serializable with `JSON.stringify()`.

In order to save modified data into dokuwikis page, framelet plugin does call another function
from the application, that has to return javascript object that has to be serializable with 
`JSON.stringify()`

```
function database_pull(){
	var rv = {};
	// FIXME: assemble the data from application
	return rv;
}
```

When framelet discovers the `database_push` method of the application. It will add another method
To the application:

```
function database_submit( commandstring )
```

where the commandstring can be `'save'` to make the framelet to call the `database_pull` method, and
send the returned object as JSON onto the page. Another option is `'cancel'` that will warn you that 
you are about to lose your changes. 

## A Test Page

The test folder under plugin contains application that is capable of exchanging data
with docuwiki's page. The file testcontent.txt contians a docuwiki page source that
will start iframe application.

To see the test document, write onto your page the following:

```
====== Experimental Page ======

Just some text before the framelet.

<framelet width=100% height=300px scale=0.95 href=lib/plugins/framelet/test/index.html >
{
   "text":"This is written on the docuwiki page"
}
</framelet>

And some text after the framelet.

```

## Installation

Place the following link into your 'Extension Manager' > 'Manual Install' > 'Install From URL' the following:

```
https://github.com/peetervois/framelet/archive/master.zip
```

## More Information

All documentation for this plugin can be found at
https://github.com/peetervois/framelet

If you install this plugin manually, make sure it is installed in
lib/plugins/framelet/ - if the folder is called different it
will not work!

Please refer to http://www.dokuwiki.org/plugins for additional info
on how to install plugins in DokuWiki.


## License

Copyright (C) Peeter Vois <peeter@tauria.ee>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the LICENSING file for details
