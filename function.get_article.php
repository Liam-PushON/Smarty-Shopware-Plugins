<?php
/*
 *     Smarty plugin
 * -------------------------------------------------------------
 * File:     		function.get_article.php
 * Type:     		function
 * Name:     		get_article
 * Description: 	Returns specified article data
 * -------------------------------------------------------------
 * @license GNU Public License (GPL)
 * -------------------------------------------------------------
 * Parameter:
 * - type			->				Type of query (0/'new' = New, 1/'top' = Top)(Not used when id is set) (default = 0)
 * - return			->				Type of data returned (0/'name' = Name, 1/'link' = Link, 2/'image' = Image) (default = 0)
 * - index			->				Index of data in id query (0 being the first member) (Not used when id is set) (defualt = 0)
 * - id				->				Id of a specified article (default = null)
 * - name			->				Used to get an id based on an items name in database
 * -------------------------------------------------------------
 * Example usage:
 * {get_article index=2 type='top' return='image'}
 * {get_article id=24 return='name'}
 */

function smarty_function_get_article($params, &$smarty){
	$dbdetails = getDatabaseDetails($smarty);
	$params = initParams($params);
	$type = $params['type'];
	$return = $params['return'];
	$index = $params['index'];

	try {
		$conn = new PDO($dbdetails['dsn'], $dbdetails['user'], $dbdetails['pass']);
	} catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
	}

	if(isset($params['id']) && isset($params['name'])){
		unset($params['name']);
	}
	if(isset($params['id'])){
		$id = $params['id'];
	}else if(isset($params['name'])){
		$id = getIDFromName($conn, $params['name']);
	}else{
		$id = getArticleID($conn, $index, $type);
	}

	if($return === 0){ //Name
		return getName($conn, $id);
	}else if($return === 1){ //Link
		return getLink($id);
	}else if($return === 2){ //Image Link
		return getImg($conn, $id);
	}else{
		return 'Unknown Return';
	}
}

function getImg($conn, $id){
	$stm = $conn->prepare('SELECT path FROM s_media WHERE `name` = "'.getImgName($conn, $id).'"');
	$stm->execute();
	return Shopware()->Container()->get('shopware_media.media_service')->getUrl($stm->fetchAll()[0][0]);
}
function getImgName($conn, $id){
	$std = $conn->prepare('SELECT img FROM s_articles_img WHERE articleID = '.$id.' AND main = 1');
	$std->execute();
	return $std->fetchAll()[0][0];
}

function getLink($id){
	return 'detail/index/sArticle/'.$id;
}

function getName($conn, $id){
	$stm = $conn->prepare('SELECT `name` FROM s_articles WHERE id = '.$id);
	$stm->execute();
	return $stm->fetchAll()[0][0];
}

function getArticleID($conn, $index, $type){
	if($type === 0){
		$stm = $conn->prepare('SELECT id FROM s_articles ORDER BY changetime DESC');
	}else if($type === 1){
		$stm = $conn->prepare('SELECT article_id FROM s_articles_top_seller_ro ORDER BY sales DESC');
	}else{
		return 'Unknown Type';
	}
	try {
		$stm->execute();
	}catch(PDOException $e){
		echo 'Error: ' . $e->getMessage();
	}
	return $stm->fetchAll()[$index][0];
}

function getIDFromName($conn, $name){
	$stm = $conn->prepare('SELECT id FROM s_articles WHERE `name` = "'.$name.'"');
	$stm->execute();
	return $stm->fetchAll()[0][0];
}

function initParams($params){

	if(!isset($params['type'])){
		$params['type'] = 0;
	}
	if(!isset($params['return'])){
		$params['return'] = 0;
	}
	if(!isset($params['index'])){
		$params['index'] = 0;
	}

	//**** INITIALISE TYPE ****//
	if(!is_int($params['type'])){
		if(strtolower($params['type']) === 'new'){
			$params['type'] = 0;
		}else if(strtolower($params['type']) === 'top'){
			$params['type'] = 1;
		}else{
			$params['type'] = 0;
		}
	}

	//**** INITIALISE RETURN ****//
	if(!is_int($params['return'])){
		if(strtolower($params['return']) === 'name'){
			$params['return'] = 0;
		}else if(strtolower($params['return']) === 'link'){
			$params['return'] = 1;
		}else if(strtolower($params['return']) === 'image'){
			$params['return'] = 2;
		}else{
			$params['return'] = 0;
		}
	}

	//**** INITIALISE INDEX ****//
	if(!is_int($params['index'])){
		$params['index'] = (int)$params['index'];
	}
	return $params;
}


function getDatabaseDetails($smarty){
	$smarty->config_read_hidden = true;
	if(file_exists('/smarty.conf')){
		$smarty->configLoad('/smarty.conf', 'Database');
	}else if(file_exists('smarty.conf')){
		$smarty->configLoad('smarty.conf', 'Database');
	}else if(file_exists('/engine/Library/Smarty/smarty.conf')){
		$smarty->configLoad('/engine/Library/Smarty/smarty.conf', 'Database');
	}else if(file_exists('/shopware/engine/Library/Smarty/smarty.conf')){
		$smarty->configLoad('/shopware/engine/Library/Smarty/smarty.conf', 'Database');
	}else{
		echo 'No smarty.conf file found';
	}
	$smarty->config_read_hidden = true;

	$host = $smarty->getConfigVars('host');
	$db = $smarty->getConfigVars('db');
	$user = $smarty->getConfigVars('user');
	$pass = $smarty->getConfigVars('pass');
	return ['dsn'=>'mysql:host='.$host.';dbname='.$db, 'user'=>$user, 'pass'=>$pass];

}

?>