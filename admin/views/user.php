<?php
class View_User {

		function admin() {
				global $dict;
				## Include model
				App::includeModel('models/user.php', 'user', true);
				$model = App::initAdminModel('user');

				include_once 'common.php';
		}

}