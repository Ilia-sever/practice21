<?php
header('Content-type: text/html; charset=utf-8');
include 'oop.php';

$fl = fopen("config.txt", "r");

$conf = array();
$l=0;
$strs = array("directory:","host:","user:",'password:','db_name:');
while (!feof($fl))
{
    $conf[$l]=fgets($fl);
    $conf[$l] = trim(str_replace($strs, '', $conf[$l]));
    $l++;
}
$pd= new Database ($conf[1],$conf[4],$conf[2],$conf[3]);





function setfl($conf,$ava,$tmp)
{
    @mkdir($conf[0],0777);
    $ava = $_FILES['avatar'];
    $tmp = $ava['tmp_name'];
    $info=getimagesize($tmp);
    if (preg_match('{image/(.*)}is',$info['mime'])) {
        $info = pathinfo($ava['name']);
        $filename = basename($ava['name'],'.'.$info['extension']);
        $filename= $filename . rand(0,9) . rand(0,9) . '.' . $info['extension'];
        $name = iconv(mb_detect_encoding(basename($ava['name'])),'windows-1251',"$conf[0]/" . $filename);
        move_uploaded_file($tmp, $name);
        $name=iconv('windows-1251', 'utf-8', $name);
        
        return $name;
    }
    else {die ('Попытка вставки неизображения');}
}

 


function tsk($n,$nam,$pattern)
{
    if (!empty($_POST["$n"])) {
        $p = $_POST["$n"];
        if (!preg_match($pattern, $p)) {
            die('Некорректный ввод: '. $nam);
        }
    } else {
        die('Пустое поле: '. $nam);
    }
    return $p;
}

function protect()
{
	$n = func_num_args(); 
	$ms = func_get_args(); 
	$ms2=array();
	$ms2[0]=0;
	$j=1;

	for($i = 0; $i < $n; $i+=2)
	{
			

		if 	((preg_match('/(and|null|not|union|select|from|where|group|order|having|limit|into|file|case)/i', $ms[$i]) ) or (preg_match('/#=*/',$ms[$i])))
		{
            session_start();
			$_SESSION['time']=time();
			$_SESSION['time-del']=1;

			die ("Попытка SQL-атаки. Доступ закрыт на 3 минуты");
		}
		if 	(preg_match('(\<(/?[^>]+)>)', $ms[$i]))
		{
            session_start();
			$_SESSION['time']=time();
			$_SESSION['time-del']=2;
			die ("Попытка HTML-атаки. Доступ закрыт на 5 минут");
		}

		if (strpos($ms[$i+1], "pas") !== false)
	   	{
	   		$ms[$i]=md5(md5($ms[$i])); 
	   	}
	   	if (strpos($ms[$i+1], 'sql') !== false)
	   	{
	   		$ms[$i]=stripslashes($ms[$i]); 
	   	}
	   	if (strpos($ms[$i+1], 'html') !== false)
	   	{
	   		$ms[$i]=htmlentities($ms[$i], ENT_IGNORE);
	   		$ms[$i] = htmlspecialchars($ms[$i], ENT_IGNORE);
	   		
	   	}
	   	$ms2[$j]=$ms[$i];
	   	$j++;
   } 
   return $ms2;
}


$m = array();
$m[0]=0;


if (isset($_POST['sub2'])) {
    $m = protect( tsk('log','логин', '/((?=^.{8,}$)(?=.*[A-Za-z])(?!.*\W)((?:.*[0-9]){2,}).*)/u'), 'sqlhtml',
    	          tsk('pas','пароль', "((?=^.{7,18}$)(?=.*[A-Z])(?!.*[А-Яа-яЁё])((?:.*[!@#$%^&*\[\]\{\}]){2,}).*)"), 'passqlhtml'
    	          );

    $resu=$pd->count("SELECT count(`log`) FROM  `user` where `log`=? and `pas`=?;",array($m[1],$m[2]));
    
    if ($resu > 0) 
    {
        $buf = $pd->select("SELECT * FROM  `user` where `log`=? and `pas`=?;",array($m[1],$m[2]));
        
        session_start();
        $_SESSION['id']=$buf[0];   
        for ($i=3;$i<11;$i++)
        {
         $m[$i]=$buf[$i];
        }
        $_SESSION['id'] = $m[1];
    	$_SESSION['time'] = 1;
    	$_SESSION['time-del'] = 0;    
        require_once("room.html");
    } 
    else 
    {
        die("Неверный вввод логина или пароля");
    }
}
else
if ((isset($_POST['sub1']))||(isset($_POST['sub3']))) {

    session_start();
    $_SESSION['id'] = 0;

	 $m = protect (
	 	 tsk('log','логин', "/((?=^.{8,}$)(?=.*[A-Za-z])(?!.*\W)((?:.*[0-9]){2,}).*)/u"), 'sqlhtml',
        tsk('pas','пароль', "((?=^.{7,18}$)(?=.*[A-Z])(?!.*[А-Яа-яЁё])((?:.*[!@#$%^&*\[\]\{\}]){2,}).*)"), 'passqlhtml',
	 	tsk('name','имя', "/[0-9A-Za-zа-яА-ЯЁё]{2,14}/u"), 'sql',
        tsk('surname','фамилия', "/[A-Za-zа-яА-ЯЁё]{4,14}/u"), 'sql',
        tsk('patron','отчество', "/[A-Za-zа-яА-ЯЁё]{4,14}/u"), 'sql',
        tsk('date','дата рождения', "/[0-9]+/u"), 'sqlhtml',
        tsk('email','email', "/[@]+/u"), 'sqlhtml',
        tsk('phone','телефон', "/[0-9]+/u"),  'sqlhtml'
        );

	if (isset($_POST['sub1'])) 
	{
        $resu = $pd->count("SELECT count(`log`) FROM  `user` where `log`=?;",array($m[1]));
    	
        if ($resu!=0) 
        {
        	die ('Данный логин существует');
        }
        $resu = $pd->count("SELECT count(`email`) FROM  `user` where `email`=?;",array($m[7]));
    	
        if ($resu!=0) 
        {
        	die ('Данный email существует');
        }  

        if (isset($_FILES['avatar'])) $ava = $_FILES['avatar'];
        if (isset($ava['tmp_name'])) $tmp = $ava['tmp_name'];
        if(is_uploaded_file($tmp)) $m[9] = setfl($conf,$ava,$tmp); else die ("Аватар не загружен ".$ava['error']);

    	$pd->simple_query("INSERT INTO `user` (`log`, `pas`, `name`, `surname`, `patron`, `date`, `email`, `phone`,`link`) VALUES (?,?,?,?,?,?,?,?,?);",array($m[1],$m[2],$m[3],$m[4],$m[5],$m[6],$m[7],$m[8],$m[9]));
        
    	$_SESSION['id'] = $m[1];
    	$_SESSION['time'] = 1;
    	$_SESSION['time-del'] = 0;
    	require_once("room.html");


        
    }

    if (isset($_POST['sub3'])) {

        $resu=$pd->count("SELECT count(`email`) FROM  `user` where `email`=? and not `log` = ?",array($m[7],$m[1]));

        if ($resu!=0) 
        {
        	die ('Данный email существует');
        } 

        $pd->simple_query("UPDATE `user` SET `name`= ?, `surname`=?, `patron`=? , `date`=?,`email`=? , `phone`=?   WHERE `log`=?;",array($m[3],$m[4],$m[5],$m[6],$m[7],$m[8],$m[1]));
               
         if (isset($_FILES['avatar'])) $ava = $_FILES['avatar'];
        if (isset($ava['tmp_name'])) $tmp = $ava['tmp_name'];
        if(is_uploaded_file($tmp)) $m[9] = setfl($conf,$ava,$tmp); 

            $pd->simple_query("UPDATE `user` SET `link`=? WHERE `log`=?;",array($m[9],$m[1]));
            
                
           echo "Изменения приняты";
           $_SESSION['id'] = $m[1];
            $_SESSION['time'] = 1;
            $_SESSION['time-del'] = 0;
       }
   }
        

else
{
    session_start();
    if (isset($_SESSION['id']))
    {
    	if ((time()-$_SESSION['time']<300)&&($_SESSION['time-del']==2))
    	{
    		die ("Попытка HTML-атаки. Доступ закрыт на 5 минут");
    	}
    	if ((time()-$_SESSION['time']<180)&&($_SESSION['time-del']==1))
    	{
    		die ("Попытка SQL-атаки. Доступ закрыт на 3 минуты");
    	}

        $resu = $pd->count("SELECT count(`log`) FROM  `user` where `log`=?;",array($_SESSION['id']));
    	
       
        if ($resu > 0) {
            $buf=$pd->select("SELECT * FROM  `user` where log=?;",array($_SESSION['id']));
            
                             
            for ($i=1;$i<11;$i++)
            {
             $m[$i]=$buf[$i];
            }
            require_once("room.html");
        }
        else  require_once("registration.html");   
    	
    }
    else
    {

    	require_once("registration.html");
        
    }
}

$pd = null;



