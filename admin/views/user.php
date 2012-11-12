<?php
class View_User {

		function admin() {
				global $dict;
				## Include model
				App::includeModel('models/user.php', 'user', true);
    ## Initialise model
				$model = App::initAdminModel('user');
    ## Initialise default model function - view/add/edit/delete
				App::initAdminCommon($model);
		}

}