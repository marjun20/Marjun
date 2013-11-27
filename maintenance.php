<?php

/*=============================================================================================================================*/
/*=============================================================================================================================*/
class SubModulesXML{
	public static $_instance;
	private static $dom;
	private static $contents;	
	
	function __construct() {
		
	}
	
	function __destruct(){
		self::$dom = null;
		self::$contents = null;
	}
	
	public static function getInstance() {
		if( ! (self::$_instance instanceof self) ) {
		self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	function getContents($filePath){
		self::$dom = null;
		self::$dom = new DOMDocument;
		self::$dom->validateOnParse = true;
		self::$dom->load($filePath);
		//self::$dom->validate();
	
		$root = self::$dom->documentElement;
		
		self::$contents['Document'] = self::getChild($root);
		self::$contents['Document']['Children'] = self::getChildren($root);
		
		return self::$contents;
		
	}
	
	function getChildren($element){
		$children = $element->childNodes;
		if($children){
			$childrenCount = 0;
			if($children->length>0){
				for($c=0;$c<$children->length;$c++){
					$child = $children->item($c);
					if($child->nodeType==1){
						$childrenCount = $childrenCount + 1;
						$content[$childrenCount-1] = self::getChild($child);						
					}
				}
			}
			$content['Count'] = $childrenCount;
			return $content;
		}
	}
	
	function getChild($element){
		$content['Attributes'] = self::getAttributes($element);
		$content['Id'] = $element->getAttribute('id');
		$content['Name'] = $element->nodeName;
		$content['Value'] = "";
		if ($element->nodeType==1){
			$nodeValue = '';
			$hasChildren = false;
			foreach($element->childNodes as $node){
	 			if ($node->nodeType == 3){
	 				$content['Value'] = $node->nodeValue;
	 			}else{
					$hasChildren=true;
				}
	 		}
	 		$node = null;
	 		
	 		if($hasChildren==true){
				$content['Children'] = self::getChildren($element);
			}else{
				$content['Children']['Count'] = 0;
			}
		}
		return $content;
	}
	
	function getAttributes($element){
		$attributes = $element->attributes;
		if($attributes){
			$content['Count']=$attributes->length;
			for($a=0;$a<$attributes->length;$a++){
				$attribute =  $attributes->item($a);
				$content[$a]['Name'] = $attribute->nodeName;
				$content[$a]['Value'] = $attribute->nodeValue;
				$content[$attribute->nodeName]['Value'] = $attribute->nodeValue;
			}
		}else{
			$content['Count']=0;
		}
		return $content;
	}
}


/*=============================================================================================================================*/
/*=============================================================================================================================*/
class LogHistory{
	public static $_instance;
	
	private static $userName;
	
	
	function __construct() {
		
	}
	
	function __destruct(){
		
	}
	
	public static function getInstance() {
		if( ! (self::$_instance instanceof self) ) {
		self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	function setUserName($userName){
		self::$userName = $userName;
	}
	
	function getUserName(){
		return self::$userName;
	}
	
	function writeToHistory($content, $serialize=false){
		$userName = self::$userName;
		
		if($userName==""){return;}
		
		
		$strTime = date('Y:m:d:H:i:s');
		$timeStamp = explode(":",$strTime);
		
		if($serialize===true){$content = serialize($content);}
		
		if(!file_exists(SETTINGS_USERLOGHISTORY.$timeStamp[0])){mkdir(SETTINGS_USERLOGHISTORY.$timeStamp[0]);}
		if(!file_exists(SETTINGS_USERLOGHISTORY.$timeStamp[0].'/'.$timeStamp[1])){mkdir(SETTINGS_USERLOGHISTORY.$timeStamp[0].'/'.$timeStamp[1]);}
		if(!file_exists(SETTINGS_USERLOGHISTORY.$timeStamp[0].'/'.$timeStamp[1].'/'.$timeStamp[2])){mkdir(SETTINGS_USERLOGHISTORY.$timeStamp[0].'/'.$timeStamp[1].'/'.$timeStamp[2]);}
		
		$filePath = SETTINGS_USERLOGHISTORY.$timeStamp[0].'/'.$timeStamp[1].'/'.$timeStamp[2].'/'.$userName.'.log';
		
		if(!file_exists($filePath)){
			$handle = fopen($filePath,'a+');
			fwrite($handle,"==> ".$strTime." log data created for $userName");
		}else{
			$handle = fopen($filePath,'a+');
		}
		
		fwrite($handle,"\n");
		fwrite($handle,"==> ".$strTime." ".$content);
		
		fclose($handle);
		
		return;

	}	
}


/*=============================================================================================================================*/
/*=============================================================================================================================*/
class AccessPermissions{
	public static $_instance;
	private static $dom;
	private static $permissionFile;
	private static $permissionId;
	private static $permissionName;
	private static $permissionDescription;
	private static $accessLevel;
		
	function __construct() {
		
	}
	
	function __destruct(){
		self::$dom = null;
	}
	
	public static function getInstance() {
		if( ! (self::$_instance instanceof self) ) {
		self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	function setPermissionFile($permissionFile){
		self::$permissionFile = $permissionFile;
	}
	
	function getPermissionFile(){
		return self::$permissionFile;
	}
	
	function getPermissionId(){
		return self::$premissionId;
	}
	
	function getPermissionName(){
		return self::$permissionName;
	}
	
	function getPermissionDescription(){
		return self::$permissionDescription;
	}
	
	function setAccessLevel($accessLevel){
		self::$accessLevel = $accessLevel;
	}
	
	function getAccessLevel(){
		return self::$accessLevel;
	}
	
	function getPermission($permissionId){
		self::$permissionId = $permissionId;
		self::$permissionName = "Unknown Name";
		self::$permissionDescription = "Unknown Description";
		
		$accessLevel = self::$accessLevel;
		if (file_exists(self::$permissionFile)){
			self::$dom = null;
			self::$dom = new DOMDocument();
			self::$dom->load(self::$permissionFile);
			self::$dom->validate();
			
			$e = self::$dom->getElementById($permissionId);
			
			if($e->hasAttribute("name")){
				self::$permissionName = $e->getAttribute("name");
			}
			
			if($e->hasAttribute("description")){
				self::$permissionDescription = $e->getAttribute("description");
			}
			
			if($e->hasAttribute($accessLevel)){
				$val = $e->getAttribute($accessLevel);
			}else{
				$val = "no";
			}
			
			if(strtolower($val)=="yes"){
				$returnVal = true;
			}else{
				$returnVal = false;
			}
			return $returnVal;
		}
	}
	
	function renderRestriction(){
		die();
	}
}


/*=============================================================================================================================*/
/*=============================================================================================================================*/
class UserAccounts{
	public static $_instance;
	private static $objDat;
	private static $result;
	private static $rowCount;
	private static $fieldCount;
	private static $fieldNames;
	
	
	function __construct() {
		self::$objDat = DataConnection::getInstance();
	}
	
	function __destruct(){
		self::$objDat = null;
		self::$result = null;
		self::$rowCount = null;
		self::$fieldCount = null;
		self::$fieldNames = null;
	}
	
	public static function getInstance() {
		if( ! (self::$_instance instanceof self) ) {
		self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	function getRowCount(){
		return self::$rowCount;
	}
	
	function getFieldCount(){
		return self::$fieldCount;
	}
	
	function getFieldNames(){
		return self::$fieldNames;
	}
	
	function getInsertedId(){
		return self::$objDat->getInsertedId();
	}
	
	function insert($userName, $fullName, $level){
		$userName = addslashes(trim($userName));
		$fullName = addslashes(trim($fullName));
		$level = addslashes($level);
		
		if(strlen($userName)==0){return;}
		if(strlen($fullName)==0){return;}
		
		$queryString = "INSERT INTO hr_Users 
						(UserName, FullName, Level) 
						VALUES 
						('$userName','$fullName','$level')";
		return self::$objDat->doUpdateQuery($queryString);
	}
	
	function update($userName, $fullName, $level){
		$userName = addslashes(trim($userName));
		$fullName = addslashes(trim($fullName));
		$level = addslashes($level);
		
		if(strlen($userName)==0){return;}
		if(strlen($fullName)==0){return;}
		
		$queryString = "UPDATE hr_Users SET 
						FullName='$fullName', Level='$level' 
						WHERE UserName='$userName'";
		return self::$objDat->doUpdateQuery($queryString);
	}
	
	function delete($userName){
		$queryString = "DELETE FROM hr_Users WHERE UserName='$userName'";
		return self::$objDat->doUpdateQuery($queryString);
	}
	
	function getResult($sort = "UserName ASC"){
		$queryString = "SELECT UserName, FullName, Level
						FROM hr_Users ORDER BY $sort";
		self::$result = self::$objDat->doSelectQuery($queryString);
		self::$rowCount = self::$objDat->getRowCount();
		self::$fieldCount = self::$objDat->getFieldCount();
		self::$fieldNames = self::$objDat->getFieldNames();
		
		return self::$result;
	}
	
	function getInfo($userName){
		$userName = addslashes($userName);
		$queryString = "SELECT UserName, FullName, Level
						FROM hr_Users WHERE UserName='$userName'";
		self::$result = self::$objDat->doSelectQuery($queryString);
		self::$rowCount = self::$objDat->getRowCount();
		self::$fieldCount = self::$objDat->getFieldCount();
		self::$fieldNames = self::$objDat->getFieldNames();
		
		return self::$result;
	}
	
	function validateUser($userName, $password){
		$userName = addslashes($userName);
		if(strlen($password)>0){$password=md5($password);}
		$queryString = "SELECT UserName, FullName, Level
						FROM hr_Users WHERE UserName='$userName' AND Password='$password'";
		self::$result = self::$objDat->doSelectQuery($queryString);
		return self::$objDat->getRowCount();
	}
	
	function changePassword($userName, $currentPassword, $newPassword){
		$userName = addslashes($userName);
		if(strlen($currentPassword)>0){$currentPassword=md5($currentPassword);}
		if(strlen($newPassword)>0){$newPassword=md5($newPassword);}
		$queryString = "UPDATE hr_Users SET 
						Password='$newPassword' WHERE UserName='$userName' AND Password='$currentPassword'";
		return self::$objDat->doUpdateQuery($queryString);
	}
	
	function getUserAccessLevel($userName){
		$userName = addslashes($userName);
		$queryString = "SELECT Level FROM hr_Users WHERE UserName='$userName'";
		$result = self::$objDat->doSelectQuery($queryString);
		return $result[0][0];
	}
	
}

