<?php

# MODULE base
$il->modules[] = 'base';
require_once 'mod/base/root.php';

# MODULE dir
$il->modules[] = 'dir';
# a file 'mod/dir/index.dat/'

# MODULE user
$il->modules[] = 'user';
require_once 'mod/user/user.php';

# MODULE blog
$il->modules[] = 'blog';
require_once 'mod/blog/blog.php';
# dir: data/notes/

# MODULE cache
$il->modules[] = 'cache';
require_once 'mod/cache/cache.php';

# MODULE catalog
$il->modules[] = 'catalog';
require_once 'mod/catalog/catalog.php';
# SQL -> mod/catalog/catalog.sql

# MODULE contacto
$il->modules[] = 'contacto';
require_once 'mod/contacto/contact.php';
# use: data/contact/users.db
# use: data/contact/data.html

# MODULE datalog
$il->modules[] = 'datalog';
require_once 'mod/datalog/datalog.php';
require_once 'mod/datalog/item.php';
# SQL -> mod/datalog/datalog.sql
# dir: data/datalog/
# use: data/datalog/stations.db

# MODULE users
$il->modules[] = 'users';
require_once 'mod/users/user.php';
require_once 'mod/users/login.php';
# SQL -> mod/users/user.sql
# use: data/user/passwd.dat

# MODULE groups
$il->modules[] = 'groups';
# use: data/user/groups.dat

# MODULE root
$il->modules[] = 'root';
require_once 'mod/root/display.php';
# SQL -> mod/root/general.sql
# SQL -> mod/root/resource.sql

# MODULE oruga
$il->modules[] = 'oruga';
require_once 'mod/oruga/oruga.php';
# SQL -> mod/oruga/oruga.sql
# SQL -> mod/oruga/colombia.sql
# dir: data/catalog/maquinaria/
# dir: data/catalog/repuestos/
# use: data/catalog/venta.ini
# use: data/catalog/alquiler.ini
# use: data/catalog/repuestos.ini

# PATHS
set_paths('','root');
set_paths('about','root','hiden');
set_paths('alquiler','alias','products');
set_paths('base','base');
set_paths('blog','blog','hiden');
set_paths('contact','contact','hiden');
set_paths('contacto','alias','contact');
set_paths('datalog','datalog');
set_paths('inicio.html','landing');
set_paths('item','item');
set_paths('login','login');
set_paths('nosotros','root');
set_paths('notas','alias','blog');
set_paths('products','catalog');
set_paths('repuestos','alias','products');
set_paths('status','status');
set_paths('user','user');
set_paths('usuario','alias','user');
set_paths('venta','alias','products');

fix_paths();
?>