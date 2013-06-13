Rapummies
=========

Rapummies is a PHP plugin to get rap lines and annotation/interpretation from  <a href="http://rapgenius.com" target="_blank">rapgenius</a>. 


Requirements
------------
* PHP 5.2.0 or newer
* <a href="https://github.com/92bondstreet/swisscode" target="_blank">SwissCode</a>


What comes in the package?
--------------------------
1. `rapummies.php` - The Rapummies class functions to get rap lines from artist, singer, producer and save them to database...
2. `example.php` - All Rapummies functions call
3. `token.php` - Token file with Database parameters
4. `sql/`- Directory with SQL schema to save results 


Example.php
-----------

	// Init constructor with false value: no dump log file
	$RapGenius = new Rapummies();

	// Get all lines and annotation/interpretation from song url
	$results = $RapGenius->raplines_song("http://rapgenius.com/Kanye-west-no-church-in-the-wild-lyrics");
	print_r($results);

	// Get all lines and annotation/interpretation of artist/singer/producer
	$results = $RapGenius->raplines_artist("Capone N Noreaga");

	// Save results in Database
	$save = $RapGenius->save($results, "raplines");
	var_dump($save);


To start the demo
-----------------
1. Upload this package to your webserver.
2. In your database manager, browse sql directory import `raplines.sql`.
3. Update the `token.php` file with database host, name, user and password  
4. Open `example.php` in your web browser and check screen output and database. 
5. Enjoy !


Project status
--------------
Astreed is currently maintained by Yassine Azzout.


Authors and contributors
------------------------
### Current
* [Yassine Azzout][] (Creator, Building keeper)

[Yassine Azzout]: http://www.92bondstreet.com


License
-------
[MIT license](http://www.opensource.org/licenses/Mit)

