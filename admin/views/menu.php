<?php
class View_Menu {

		function admin() {
				global $dict;
				## Include model
				App::includeModel('models/menu.php', 'menu', true);
				## Initialise model
				$model = App::initAdminModel('menu');
				## Initialise default model function - view/add/edit/delete
				App::initAdminCommon($model);
		}

}