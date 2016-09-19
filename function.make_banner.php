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
	$dbdetails = getDatabaseDetails($smarty);
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

	try {
		$conn = new PDO($dbdetails['dsn'], $dbdetails['user'], $dbdetails['pass']);
	} catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
		echo $e->getTraceAsString();
	}
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

	echo '
		<script type="text/javascript">
			 $(document).ready(function() {
				  $(\'.main-slick\').slick({
						slidesToShow: 1,
						slidesToScroll: 1,
						autoplay: true,
						autoplaySpeed: 2000,
						arrows: false,
				  });
			 });
			 $(document).ready(function() {
				  $(\'.sub-slider\').slick({
						slidesToShow: 3,
						slidesToScroll: 2,
						arrows: false,
						vertical: true,
						verticalSwiping: true,
						draggable: true,
				  });
			 });
			 function slideTo(slide){
				  $(\'.main-slick\').slick(\'slickGoTo\', parseInt(slide));
			 }
    </script>
	';
}

function getMainSliderBlocks($type, $amount, $conn){
	$i = 0;
	while($i < $amount) {
		echo '<img class="banner-image" src="'; echo getImg($conn, getArticleID($conn, $i, $type)); echo'">';
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