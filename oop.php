<?php


trait BD_methods 
{
	public function count($sql,$arr)
	{

		$res = $this->pd->prepare($sql);
    	$res->execute($arr);
    	return $res->fetchColumn(0);
	}
	public function select($sql,$arr) 
	{
		$res = $this->pd->prepare($sql);
    	$res->execute($arr);
    	return $res->fetch(PDO::FETCH_BOTH);
	}
	public function simple_query($sql,$arr)
	{
		$res = $this->pd->prepare($sql);
    	$res->execute($arr);
	}
	public function __construct ($host, $dbname,$pass,$user)
	{
		try 
		{
	    	$this->pd = new PDO("mysql:host=$host;dbname=$dbname;charset=UTF8", $pass,$user);
		} 
		catch (PDOException $e) 
		{
	    die('Подключение не удалось: ' . $e->getMessage());
		}
	}
	public function __destruct()
    {
        $this->pd = null;
    }
}
abstract class FieldsContainer {
	protected $pd;
}

class Database extends FieldsContainer
{
	use BD_methods;
	

}