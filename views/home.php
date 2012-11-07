<?php
class View_Home {

		function home() {
				global $twig, $dict;
				## Include models
				$app->includeModel('models/example.php', 'example');

				## Add to dictionary
				$dict['example'] = App::initModel('example');

				## Render template
				echo $twig->render('home.twig', $dict);
		}

}