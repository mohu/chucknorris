## Quick Start

Update the template folder files to reflect the latest html5boilerplate release (if required)

Don't forget to move the __*404.html*__ file to /templates

## Database

Create a MySQL database for the project and add the name to the __*$db*__ variable in:

/includes/common/dbconnector.php

```
$db   = 'database_name';
```

Install __*initial.sql*__ from /database

## Folder permissions

Ensure /cache, admin/cache and /mobile/cache all have write permissions.

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

Previously, you had to add frontend and backend model and view files when adding new URLs. Chuck Norris now auto-generates these files and installs the basic requirements to load the pages.

Models need to be built to include data to send to the template files (documentation coming soon) but this should be enough to enable the creation of static templates ready to be made dynamic by the backend team.

## Mobile

The mobile site follows the same setup as above (but if the mobile site is hosted in the same directory as the main site, the database is shared).

However, you still need to edit:

/mobile/includes/common/dbconnector.php

With your database name as below...

```
$db   = 'database_name';
```