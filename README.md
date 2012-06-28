## Quick Start

Update the template folder files to reflect the latest html5boilerplate release (if required)

Don't forget to move the _404.html_ file to /templates

## Database

Create an empty MySQL database for the project and add the name to the __*$db*__ variable in:

/includes/common/dbconnector.php

```
$db   = 'database_name';
```

## Adding URLs

By default __*"home"*__ is ready to use and edit.

To add further URLs to the site you must add them to the __*index.php*__ file __*$valid_paths*__ array:

```
$valid_paths  = array('home',
                      'blog',
                      'news',
                      );
```

Add corresponding template files for each new page to the /templates folder as __*.html*__ naming the file the same as the route.

This template file should be structured as below:

```
{% extends "base.html" %}

{% block content %}
<body>
  <!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
  <header>

  </header>
  <div role="main">

  </div>
{% endblock %}
```

Next create a model __*php*__ file and add to the /models folder. This for retrieving database information (below is an example of a file that won't get any data but will enable the site to work for a URL called __*blog*__. Note the class and function naming conventions!):

```
<?php
class Model_Blog extends RedBean_SimpleModel {

	function blog() {
                $dict = array();
		return $dict;
	}
}
```

Finally, a view file must me added (again, note each instance and case of __*"blog"*__):

```
<?php
require_once 'models/' . $module . '.php';
$model  = new Model_Blog();

$dict[$module] = $model->blog();

echo $twig->render('blog.html', $dict);
```