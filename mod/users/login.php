<?php

function loadusers($force=false) {
	static $user_db;
	if(isset($user_db) && !$force) return $user_db;
	$users = il_select('user');
	$user_db = array();
	foreach($users as $ud) $user_db[$ud['id']] = $ud;
	return $user_db;
}

function saveusers($user_db) {
	$users = il_select('user');
	foreach($users as $ud) {
		$u = $ud['user_name'];
		$difs=array();
		foreach($ud as $k=>$v) {
			if($v != ($nv=$user_db[$u][$k])) $difs[$k] = $nv;
		}
		il_update('user',$difs,array('user_name'=>$u));
	}
}

function confirm_user($user,$pass) {
	$users = loadusers();
	if(isset($users[$user])) {
		$md = md5("[$user:$pass]");
		return $md==$users[$user]['hatch'];
	}
	return false;
}

function set_user($user,$remember=null) {
	$users = loadusers();
	$nx = time()+7*24*60*60;

	il_put('user',$user);
	il_put2('user','user',$user);
	il_put2('user','name',$users[$user]['name']);
	il_put2('user','avatar',$users[$user]['avatar']);
	il_put2('user','banner',$users[$user]['banner']);
	$_SESSION['user'] = $user;

	if(empty($_COOKIE['user']) || $user != $_COOKIE['user'])
		setcookie('user',$user);

	if($remember) {
		setcookie('user_remember','1');
		$_SESSION['remember'] = true;
		if(empty($_COOKIE['user_expires']) || $nx > (int)$_COOKIE['user_expires'])
			setcookie('user_expires',"$nx");
		il_put2('user','remember',true);
		il_put2('user','expires',$nx);
	} else {
		setcookie('user_remember','');
		unset($_SESSION['remember']);
		il_put2('user','remember',false);
	}
	
	# now, this should go to the user database to get other session parameters such
	# as user main group, user groups and user permitions
	$params = il_select('user_data',array('param','param_idx','value'),array('user'=>"=$user"));
	foreach($params as $pr)
		il_put2('user',$pr['param'].($pr['param_idx']!=1?'-'.$pr['param_idx']:''),$pr['value']);
	$groups = il_select('user_group',array('group','role'),array('user'=>"=$user"));
	foreach($groups as $gr) {
		il_add('queries',"Setting {$gr['group']} as {$gr['role']}\n");
		il_add2('user','group',array($gr['group']=>$gr['role']));
	}
}

function unset_user() {
	global $il;
	$il->clear('user');
	$il->clearr('user');

	foreach(array_keys($_SESSION) as $key)
		if(substr($key,0,5)=='user_') unset($_SESSION[$key]);
	unset($_SESSION['user']);
		
	if(!empty($_COOKIE['user']))
		setcookie('user','');
	if(!empty($_COOKIE['user_expires']))
		setcookie('user_expires','');
}


function make_login($line) {
	set_area('left');
	set_area('right');

	$action = isset($_POST['action'])?$_POST['action']:'';
	$next = isset($_REQUEST['next'])? $_REQUEST['next']: '/';
	switch($action){
	case 'login':
		il_put('title',"Inicio de sesión");
		$name=isset($_POST['name'])?trim($_POST['name']):null;
		$passwd=isset($_POST['passwd'])?trim($_POST['passwd']):"ERROR";
		$remember=isset($_POST['remember'])?trim($_POST['remember']):false;
		if(confirm_user($name,$passwd)) {
			il_put('title',"Inicio de sesión exitoso");
			set_user($name,$remember);
			#loadgroups($name);
			redirect("$next");
			break;
		}
		$count = isset($_POST['count']) ? 1+(int)$_POST['count'] : 99;
		return logincontent($name,$count);
		break;
	case 'register':
		il_put('title',"Nuevo usuario");
		$name=trim($_POST['name']);
		$passwd1=trim($_POST['passwd']);
		$passwd2=trim($_POST['passwd2']);
		$nombre=trim($_POST['nombre']);
		$users = loadusers();
		if(isset($users[$name])) {
			return registercontent($name,$nombre,"Usuario ya existe");
		} elseif($passwd1!=$passwd2) {
			return registercontent($name,$nombre,"No coinciden las contraseñas");
		} elseif(strlen($passwd1)<4 || in_array($passwd1,array('1234','12345'))) {
			return registercontent($name,$nombre,"Contraseña demasiado simple");
		} elseif($passwd1==$name || $passwd1==$nombre) {
			return registercontent($name,$nombre,"Contraseña coincide con el nombre");
		} else {
			$users[$name] = array(1,md5("$name:$passwd1"),0,$nombre);
			saveusers();
			il_put('title',"Usuario creado");
			return logincontent($name,0);
		}
		break;
	default:
		$what = il_line_get('last',$line);
		switch($what) {
		case 'logout':
			unset_user();
			redirect("$next");
		case 'new':
		case 'nuevo':
			il_put('title',"Nuevo usuario");
			return registercontent();
		case 'config':
		case 'configurar':
			il_put('title',$content = "Configurar sesión de ".$_SESSION['user']);
			$content.= "\n<pre>".print_r($GLOBALS['il'],true)."</pre>\n";
			$users = loadusers();
			$content.= "\n<pre>".print_r($users,true)."</pre>\n";
			return $content;
		default:
			if(isset($_SESSION['user'])) {
				redirect("$next");
			}
			il_put('title',"Inicio de sesión");
			return logincontent();
		}
	}
}

function logincontent($user='',$try=0) {
	global $dir;
	$valuser=$user?" value=\"$user\"":"";
	$warning=$try?"\n\t<p class=warning>Usuario o contraseña incorrecta.</p>":"";
	$next=isset($_REQUEST['next'])&&$_REQUEST['next']? htmlentities($_REQUEST['next']): htmlentities("$dir/");
	return <<<AAA
	
	<form method=post>$warning
	<table>
		<tr><td>Nombre de usuario:</td><td><input name=name$valuser></td></tr>
		<tr><td>Contraseña:</td><td><input type=password name=passwd></td></tr>
		<tr><td colspan=2><input type=submit></td></tr>
	</table>
	<input type=hidden name=action value=login>
	<input type=hidden name=count value="$try">
	<input type=hidden name=next value="$next">
	</form>

AAA;
}

function registercontent($user='',$name='',$error='') {
	$warning=$error?"\n\t<p class=warning>$error</p>":"";
	return <<<AAA

	<form method=post>$warning
	<table>
		<tr><td>Nombre de usuario:</td><td><input name=name value="$user"></td></tr>
		<tr><td>Contraseña:</td><td><input type=password name=passwd></td></tr>
		<tr><td>Confirme contraseña:</td><td><input type=password name=passwd2></td></tr>
		<tr><td>Nombre real:</td><td><input name=nombre value="$name"></td></tr>
		<tr><td colspan=2><input type=submit></td></tr>
	</table>
	<input type=hidden name=action value=register>
	</form>
	
AAA;
}

?>
