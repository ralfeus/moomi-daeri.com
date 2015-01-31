<?php

function _g($o,$k,$v=false) {
	return (isset($o[$k])?$o[$k]:$v);
}

// make URL from $_GET or $this->request->get
// or route and pars
function _route($g) {
	return ;
	}
// make URL from $_GET or $this->request->get
// or route and pars
function _url($get, $pars=false) {
	$g = array_merge($get);
	$route = _g($g,'route',_g($g,'_route_',''));
	unset($g['route']);
	$p = '';
	if (!empty($g)) $p .= '&' . http_build_query($g);
	if ($pars) $p .= '&' . http_build_query($pars);
	return $route . $p;
}

// help make notifications
function _note($note,$type='error') {
	return 	'<div class="' . $type . '">'
			. $note
			.'<img src="catalog/view/theme/default/image/close.png" alt="" class="close"></div>';
}

// выбор шаблона
function _tpl($tpl, $default) {
	if (file_exists(DIR_TEMPLATE . $default . $tpl) ) {
		return $default . $tpl;
	} else {
		return 'default' . $tpl;
	}
}
?>