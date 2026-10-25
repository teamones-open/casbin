<?php
error_reporting(E_ALL);
set_error_handler(static function($severity,$message,$file,$line){if((error_reporting()&$severity)||in_array($severity,[E_DEPRECATED,E_USER_DEPRECATED],true))throw new ErrorException($message,0,$severity,$file,$line);return false;});
$root=dirname(__DIR__,2);
require $argv[1];
spl_autoload_register(static function($class)use($root){
    if(str_starts_with($class,'Casbin\\WebmanPermission\\'))$file=$root.'/src/'.str_replace('\\','/',substr($class,24)).'.php';
    elseif(str_starts_with($class,'Casbin\\'))$file=$root.'/third_party/php-casbin/src/'.str_replace('\\','/',substr($class,7)).'.php';
    elseif(str_starts_with($class,'IPTools\\'))$file=$root.'/third_party/iptools/src/'.str_replace('\\','/',substr($class,8)).'.php';
    else return;
    if(is_file($file))require $file;
},true,true);
$assertions=0;
$check=static function($actual,$expected,$name)use(&$assertions){++$assertions;if($actual!==$expected)throw new RuntimeException($name);};
$model=Casbin\Model\Model::newModel();
$model->addDef('r','r','sub, obj, act');$model->addDef('p','p','sub, obj, act');$model->addDef('g','g','_, _');
$model->addDef('e','e','some(where (p.eft == allow))');$model->addDef('m','m','g(r.sub, p.sub) && keyMatch(r.obj, p.obj) && r.act == p.act');
$enforcer=new Casbin\Enforcer($model);
$enforcer->addPolicy('reader','/data/*','read');$enforcer->addRoleForUser('alice','reader');
foreach(['alice','bob']as$user)foreach(['/data/one','/data/two','/other']as$obj)foreach(['read','write']as$act)$check($enforcer->enforce($user,$obj,$act),$user==='alice'&&str_starts_with($obj,'/data/')&&$act==='read','RBAC allow/deny');
$check($enforcer->removePolicy('reader','/data/*','read'),true,'Policy removal');$check($enforcer->enforce('alice','/data/one','read'),false,'Revocation');
foreach([
 ['192.0.2.5','192.0.2.0/24',true],['192.0.3.5','192.0.2.0/24',false],['192.0.2.5','192.0.2.5',true],
 ['2001:db8::1','2001:db8::/32',true],['2001:db9::1','2001:db8::/32',false]
]as[$ip,$network,$expected])$check(Casbin\Util\BuiltinOperations::ipMatch($ip,$network),$expected,'IP matching');
$check(count(IPTools\Network::parse('192.0.2.0/30')),4,'Network count');
$check(count(IPTools\Range::parse('192.0.2.1-192.0.2.4')),4,'Range count');
echo json_encode(['passed'=>true,'assertions'=>$assertions,'error_reporting'=>error_reporting()]),"\n";
