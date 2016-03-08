<?php
	include_once 'ProductScraper.php';


	$url = 'http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/5_products.html';

	// Comment/Uncomment to show the fields in the result Json
	$table_fields = array(
			'description' => 'Description',
			//'size' => 'Size',
			//'packaging' => 'Packaging',
			//'manufacturer' => 'Manufacturer',
	);


	$scraper = new ProductScraper($url,$table_fields);
	$response = $scraper->getProductsData();
	print_r($response);
