<?php
defined('_SECURE_') or die('Forbidden');

function adminlte_hook_themes_apply($content) {
	global $core_config, $user_config, $icon_config;
	
	$themes_lang = strtolower(substr($user_config['language_module'], 0, 2));
	
	if ($themes_layout = trim($_SESSION['tmp']['themes']['layout'])) {
		$themes_layout = 'themes_layout_' . $themes_layout;
		unset($_SESSION['tmp']['themes']['layout']);
	} else {
		$themes_layout = 'themes_layout';
	}
	
	$tpl = array(
		'name' => $themes_layout,
		'vars' => array(
			'CONTENT' => $content,
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'HTTP_PATH_THEMES' => $core_config['http_path']['themes'],
			'LOGOUT_URL' => _u("index.php?app=main&inc=core_auth&route=logout"),
			'THEMES_MODULE' => core_themes_get(),
			'THEMES_MENU_TREE' => themes_menu_tree(),
			'THEMES_SUBMENU' => themes_submenu(),
			'THEMES_LANG' => ($themes_lang ? $themes_lang : 'en'),
			'CREDIT_SHOW_URL' => _u('index.php?app=ws&op=credit'),
			'NAME' => $user_config['name'],
			'USERNAME' => $user_config['username'],
			'GRAVATAR' => $user_config['opt']['gravatar'],
			'LAYOUT_FOOTER' => $core_config['main']['layout_footer'],
			'Logout' => _('Logout'),
			'Home' => _('Home'),
			'About Us' => _('About Us'),
			'MENU' => _('MENU'),
		),
		'ifs' => array(
			'valid' => auth_isvalid() 
		) 
	);
	$content = tpl_apply($tpl, array(
		'core_config',
		'user_config',
		'icon_config'
	));
	
	return $content;
}

function adminlte_hook_themes_submenu($content = '') {
	global $user_config, $icon_config;
	
	$separator = "<span class='ml-2' />";
	
	$logged_in = $user_config['name'];
	$tooltips_logged_in = _('Logged in as') . ' ' . $logged_in;
	
	$credit = core_display_credit(rate_getusercredit($user_config['username']));
	$tooltips_credit = _('Your credit');
	
	$ret = '<div class="playsms-submenu">';
	$ret .= '<span class="playsms-icon fas fa-user" alt="' . $tooltips_logged_in . '" title="' . $tooltips_logged_in . '"></span><span id="submenu-user">' . $logged_in . '</span>';
	$ret .= $separator . '<span class="playsms-icon fas fa-credit-card" alt="' . $tooltips_credit . '" title="' . $tooltips_credit . '"></span><span id="submenu-credit-show">' . $credit . '</span>';
	
	if (auth_login_as_check()) {
		$ret .= $separator . _a('index.php?app=main&inc=core_auth&route=logout', _('return'));
	}
	
	$ret .= $content;
	$ret .= '</div>';
	
	return $ret;
}

function adminlte_hook_themes_menu_tree($menu_config) {
    global $core_config, $user_config, $icon_config;

    $main_menu = "";
    foreach ($menu_config as $menu_title => $array_menu) {
        foreach ($array_menu as $sub_menu) {
            $sub_menu_url = $sub_menu[0];
            $sub_menu_title = $sub_menu[1];
            $sub_menu_index = (int) ($sub_menu[2] ? $sub_menu[2] : 10) + 100;

            // devider or valid entry
            if (($sub_menu_url == '#') && ($sub_menu_title == '-')) {
                $m[$sub_menu_index . '.' . $sub_menu_title] = "";
            } else if ($sub_menu_url == '#') {
                $m[$sub_menu_index . '.' . $sub_menu_title] = "";
            } else if ($sub_menu_url && $sub_menu_title) {
                if (acl_checkurl($sub_menu_url)) {
                    $m[$sub_menu_index . '.' . $sub_menu_title] = "
                        <li class='nav-item'>
                            <a href='" . _u($sub_menu_url) . "' class='nav-link'><i class='far fa-circle nav-icon'></i><p>" . $sub_menu_title . "</p></a>
                        </li>";
                }
            }
        }

		$found = false;
        if (isset($m) && is_array($m) && count($m)) {
            $menu_tree = "
            	<li class='nav-item has-treeview'>
        	        <a href='#' class='nav-link'>
            	        <i class='nav-icon far fa-folder-open'></i>
                	    <p>" . $menu_title . "<i class='right fas fa-angle-left'></i></p>
	                </a>
    	            <ul class='nav nav-treeview bg-dark' style='background-color: #c0c0c0'>";

            ksort($m);
            foreach ($m as $mm) {
            	if ($menu_tree_item = trim($mm)) {
	                $menu_tree .= $menu_tree_item;
    	            $found = true;
    	        }
            }
            unset($m);

            $menu_tree .= "
	            	</ul>
            	</li>";
        }
        
        if ($found) {
        	$main_menu .= $menu_tree;
        }
    }
    
    if (auth_isvalid()) {
    	$main_menu .= "
			<li class='nav-item'>
       			<a href='" . _u('index.php?app=main&inc=core_auth&route=logout') . "' class='nav-link'><i class='nav-icon fas fa-sign-out-alt'></i><p>"  . _('Logout') . "</p><a/>
        	</li>";
    } else {
    	$main_menu .= "
			<li class='nav-item'>
       			<a href='#' class='nav-link'><i class='nav-icon fas fa-sign-in-alt'></i><p>"  . _('Login') . "</p><a/>
        	</li>";
	}
	
    $content = $main_menu;

    return $content;
}
