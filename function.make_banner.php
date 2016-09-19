<?php
/*
 *     Smarty plugin
 * -------------------------------------------------------------
 * File:							function.make_banner.php
 * Type:							function
 * Name:							make_banner
 * Description:				Creates a product banner of top sellers or new products
 * -------------------------------------------------------------
 * @license GNU Public License (GPL)
 * -------------------------------------------------------------
 * Parameter:
 * - amount						= How many products should be loaded (default = 1)
 * - type						= Should the banner display 'new' or 'top' products (default='top')
 * -------------------------------------------------------------
 * Example usage:
 * {make_banner type='top' amount=5}
 * {make_banner type='new' amount=6}
 */

include('function.get_article.php');

function smarty_function_make_banner($params, &$smarty){
	//**** INIT PARAMETERS ****//
	if(!isset($params['type'])){
		$params['type'] = 0;
	}else if($params['type'] === 'top'){
		$params['type'] = 0;
	}else if($params['type'] === 'new'){
		$params['type'] = 1;
	}else{
		$params['type'] = 0;
	}
	if(!isset($params['amount'])){
		$params['amount'] = 1;
	}
	$type = $params['type'];
	$amount = $params['amount'];
	$amount = 6;
	$conn = new PDO('mysql:host=localhost;dbname=shopwaredemo', 'shopware', 'root');

	//**** CREATE BANNER ****//
	echo"
		<div class='banner-wrapper'>
			<div class='main-slider'>
				<div class='main-slick'>";
					getMainSliderBlocks($type, $amount, $conn);
				echo"
				</div>
				<div class='main-slider-overlay'>
				";
				if($type === 0){
					echo "Our Top Sellers!";
				}else if($type === 1){
					echo "Our Newest Products";
				}else{
					echo "Error: Unknown Type;";
				}
				echo"
				</div>
			</div>
			<div class='sub-slider'>
			";
				getSubSliderBlocks($type, $amount, $conn);
			echo"
			</div>
		</div>
	";
}

function getMainSliderBlocks($type, $amount, $conn){
	$i = 0;
	while($i < $amount) {
		echo '<img class="banner-image" src="';echo getImg($conn, getArticleID($conn, $i, $type));echo'">';
		$i++;
	}
}

function getSubSliderBlocks($type, $amount, $conn){
	$i = 0;
	while($i < $amount) {
		echo'
			<div class="sub-slider-block">
			   <img class="sub-slider-image" src="';echo getImg($conn, getArticleID($conn, $i, $type));echo'">
				<a href="';echo getLink(getArticleID($conn, $i, $type));	echo'">
					<div class="sub-slider-name-overlay">';echo getName($conn, getArticleID($conn, $i, $type));	echo'</div>
				</a>
				<div class="sub-slider-promotion-overlay"  onclick="slideTo('.$i.')">View!</div>
			</div>';
		$i++;
	}
}
?>