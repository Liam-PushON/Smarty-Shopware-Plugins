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
 * - tax				->				Used when getting price, should tax be included (default = false)
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
	}else if($return === 3){ //Supplier name
		return getSupplier($conn, $id);
	}else if($return === 4){ //Pseudosales
		return getPseudosales($conn, $id);
	}else if($return === 5){ //Categor(y/ies)
		return getCategory($conn, $id);
	}else if($return === 6){ //Price
		return getPrice($conn, $id, $params['tax']);
	}else if($return === 7){ //Tax
		return getTax($conn, $id);
	}else if($return === 8){ //All
		return getAll($conn, $id);
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

function getSupplier($conn, $id){
	$stm = $conn->prepare('SELECT `name` FROM s_articles_supplier WHERE id = '.getSupplierID($conn, $id));
	$stm->execute();
	return $stm->fetchAll()[0][0];
}
function getSupplierID($conn, $id){
	$stm = $conn->prepare('SELECT supplierID FROM s_articles WHERE id = '.$id);
	$stm->execute();
	return $stm->fetchAll()[0][0];
}

function getPseudosales($conn, $id){
	$stm = $conn->prepare('SELECT pseudosales FROM s_articles WHERE id = '.$id);
	$stm->execute();
	return $stm->fetchAll()[0][0];
}

function getCategory($conn, $id){
	$id = getCategoryID($conn, $id);
	$category = "";
	if(count($id) > 1){
		foreach($id as $i){
			$stm = $conn->prepare('SELECT description FROM s_categories WHERE id = '.$i[0]);
			$stm->execute();
			$category = $category . $stm->fetchAll()[0][0].", ";
		}
		return $category = substr($category, 0, ($category->length-2));
	}else{
		$stm = $conn->prepare('SELECT description FROM s_categories WHERE id = '.$id);
		$stm->execute();
		return $stm->fetchAll()[0][0];
	}

}
function getCategoryID($conn, $id){
	$stm = $conn->prepare('SELECT categoryID FROM s_articles_categories WHERE articleID = '.$id);
	$stm->execute();
	if($stm->rowCount() > 1){
		return $stm->fetchAll();
	}else{
  		return $stm->fetchAll()[0][0];
	}
}

function getPrice($conn, $id, $tax){
	if(isset($tax)){
		if($tax){
			$tax = getTax($conn, $id);
			$tax += 100.00;
		}else{
			$tax = 100.00;
		}
		$tax /= 100;
		$stm = $conn->prepare('SELECT price FROM s_articles_prices WHERE articleID = '.$id);
		$stm->execute();
		return number_format(($stm->fetchAll()[0][0]*$tax), 2, '.', ',');
	}
	$stm = $conn->prepare('SELECT price FROM s_articles_prices WHERE articleID = '.$id);
	$stm->execute();
	return number_format($stm->fetchAll()[0][0], 2, '.', ',');
}
function getTax($conn, $id){
	$stm = $conn->prepare('SELECT tax FROM s_core_tax WHERE id = '.getTaxID($conn, $id));
	$stm->execute();
	return $stm->fetchAll()[0][0];
}
function getTaxID($conn, $id){
	$stm = $conn->prepare('SELECT taxID FROM s_articles WHERE id = '.$id);
	$stm->execute();
	return $stm->fetchAll()[0][0];
}

function getAll($conn, $id){
	return [
		'name'=>getName($conn, $id),
		'link'=>getLink($id),
		'image'=>getImg($conn, $id),
		'supplier'=>getSupplier($conn, $id),
		'sales'=>getPseudosales($conn, $id),
		'category'=>getCategory($conn, $id),
		'price'=>getPrice($conn, $id, false),
		'tax'=>getTax($conn, $id),
		'price-tax'=>getPrice($conn, $id, true),
	];
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
		}else if(strtolower($params['return']) === 'supplier'){
			$params['return'] = 3;
		}else if(strtolower($params['return']) === 'sales' || strtolower($params['return']) === 'pseudosales'){
			$params['return'] = 4;
		}else if(strtolower($params['return']) === 'category' || strtolower($params['return']) === 'categories'){
			$params['return'] = 5;
		}else if(strtolower($params['return']) === 'price'){
			$params['return'] = 6;
		}else if(strtolower($params['return']) === 'tax') {
			$params['return'] = 7;
		}else if(strtolower($params['return']) === 'all') {
			$params['return'] = 8;
		}else{
			$params['return'] = 0;
		}
	}

	//**** INITIALISE INDEX ****//
	if(!is_int($params['index'])){$params['index'] = (int)$params['index'];
	}
	//**** INITIALISE INDEX ****//
	if(!isset($params['tax'])){
		$params['tax'] = false;
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