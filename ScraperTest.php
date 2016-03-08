<?php
require_once 'ProductScraper.php';


class SumFinderClassTest extends PHPUnit_Framework_TestCase
{
	/* Check the sum of the products is the same as in total */
	public function testSum()
	{
		$url = 'http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/5_products.html';

		// Comment/Uncomment to show the fields in the result Json
		$table_fields = array(
				'description' => 'Description',
				//'size' => 'Size',
				//'packaging' => 'Packaging',
				//'manufacturer' => 'Manufacturer',
		);
		$scraper = new ProductScraper($url,$table_fields);
		$json = $scraper->getProductsData();
		$array = json_decode($json, true);
		$input = $array['results'];
		$total = $array['total'];
		$this->assertEquals($total, $this->productSum($input));
	}
	/* Check the sum of the products is the same as in total */
	public function testJson()
	{
		$url = 'http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/5_products.html';

		// Comment/Uncomment to show the fields in the result Json
		$table_fields = array(
				'description' => 'Description',
				//'size' => 'Size',
				//'packaging' => 'Packaging',
				//'manufacturer' => 'Manufacturer',
		);
		$scraper = new ProductScraper($url,$table_fields);
		$json = $scraper->getProductsData();
		$this->assertJSON($json);
		//$this->assertTrue($this->isValidJSON($json));
		$responseArray = json_decode($json, true);
		$results = $responseArray['results'];
		$this->assertArrayHasKey('results', $responseArray);
		$this->assertArrayHasKey('total', $responseArray);
		$this->assertArrayHasKey('title', $results[0]);
		$this->assertArrayHasKey('size', $results[0]);
		$this->assertArrayHasKey('unit_price', $results[0]);
		$this->assertArrayHasKey('description', $results[0]);
// 		$this->assertArrayHasKey('size', $results[0]);
// 		$this->assertArrayHasKey('packaging', $results[0]);
// 		$this->assertArrayHasKey('manufacturer', $results[0]);
	}
	/**
	 * Returns sum of products
	 * @return array
	 */
	public function productSum($productArray)
	{
		$sum = 0;
		foreach ($productArray as $element) {
			$sum += $element['unit_price'];
		}
		return $sum;
	}
}