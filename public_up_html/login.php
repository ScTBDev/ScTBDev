<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2011 ScTBDev.ca
 *
 *	This file is part of ScTBDev.
 *
 *	ScTBDev is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	ScTBDev is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with ScTBDev.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(CLASS_PATH.'bt_session.php');
require_once(CLASS_PATH.'bt_security.php');

$returnto = isset($_GET['returnto']) ? trim($_GET['returnto']) : '';;
$returnto = stripos($returnto, 'login.php') === false ? $returnto : '';
$use_ssl = !((bool)0 + $_GET['http']);

if (!bt_vars::$ssl) {
	$redirectbase = bt_security::redirect_base(true);
	header('Location: '.$redirectbase.'/login.php?http=1'.($returnto ? '&returnto='.rawurlencode($returnto) : ''));
	die();
}

bt_loginout::db_connect(false);

if (bt_user::$current) {
	$redirectbase = bt_security::redirect_base(($use_ssl || (bt_user::$current['flags'] & bt_options::USER_SSL_SITE)));
	$url = $returnto ? $returnto : '/my.php';
	header('Location: '.$redirectbase.$url);
	die;
}

bt_theme::head('Login', false, 1800);
$errormsg = '';
if (!empty($returnto) && !isset($_GET['nowarn'])) {
	$errmsg = 'The page you tried to view can only be used when you\'re logged in!';
	$errormsg_vars = array(
		'ERRMSG'	=> $errmsg,
	);
	$errormsg = bt_theme_engine::load_tpl('login_error', $errormsg_vars);
}
$session = new bt_session(true, 300);
$form_hash = $session->create('login');
$sslchecked = $use_ssl ? 'checked="checked" ' : '';

$return_to = !empty($returnto) ? '<input type="hidden" name="returnto" value="'.bt_security::html_safe($returnto).'" />' : '';

$loginvars = array(
	'ERROR_MSG'		=> $errormsg,
	'FORM_HASH'		=> $form_hash,
	'RETURN_TO'		=> $return_to,
	'SSL_CHECKED'	=> $sslchecked,
);

echo bt_theme_engine::load_tpl('login', $loginvars);
bt_theme::foot();
?>
