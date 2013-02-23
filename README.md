# Chuck

Chuck is a PHP based open source content management system.

Developed by [Rikki Pitt](http://www.twitter.com/rikkipitt) at [Studio Mohu](http://www.studiomohu.com), London

## Documentation

[View the documentation](http://chuck.studiomohu.com)

## Overview

*Chuck was intended to have a discrete structure that follows a compartmentalised layout with its own standardised conventions.*

####Basic flow

* User initiates a HTTP request to the front end of Chuck
* All requests are handled by .htaccess and ported to an index.php file
* In a similar fashion to Django's urls.py, a urls.php file of valid URL paths is loaded
* If the user request is matched in the urls.php, the corresponding view and sub-function is loaded by the router - if not, a 404 view is parsed
* Models are loaded in and database data is passed to the view which is then rendered in a HTML5 Boilerplate template

Chuck can therefore be considered to be a "MVT" or Model-View-Template framework.

![Framework](http://chuck.studiomohu.com/img/mvt.png)
