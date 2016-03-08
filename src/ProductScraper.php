<?php
class ProductScraper {
	protected $ch = null;
	protected $table_fields = null;
	protected $field_values = null;
	protected $currency = null;

	/**
	 *
	 * @param $url  website to scrape
	 * @param $table_fields  fields to display in the response
	 */
	public function __construct($url, $table_fields) {
		$this->ch = curl_init ();
		curl_setopt ( $this->ch, CURLOPT_URL, $url );
		curl_setopt ( $this->ch, CURLOPT_BINARYTRANSFER, true );
		curl_setopt ( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $this->ch, CURLOPT_ENCODING, 'ISO-8859-1' );

		$this->table_fields = $table_fields;
		$this->field_values = array ();
		$this->currency = '';
	}
	/**
	 *
	 * @return Json format string
	 */
	public function getProductsData() {
		$html = curl_exec ( $this->ch );
		$this->checkHtml ( $html, $this->ch );
		$dom = new DOMDocument ();
		@$dom->loadHtml ( $html );
		$xpath = new DOMXPath ( $dom );
		// Get all the products sections (list element)
		$products = $xpath->query ( "//ul[@class='productLister listView']/li" );
		$total = 0;
		$results = array ();
		foreach ( $products as $product ) {
			// Get Product anchor
			$a = $xpath->query ( "div//a", $product );
			$link = trim ( preg_replace ( "/[\r\n]+/", " ", $a->item ( 0 )->getAttribute ( "href" ) ) );
			curl_setopt ( $this->ch, CURLOPT_URL, $link );
			$linked_html = curl_exec ( $this->ch );
			$this->checkHtml ( $linked_html, $this->ch );

			// Get the size in kb of the linked HTML without assets
			$sizeBites = strlen ( $linked_html );
			$size = ($sizeBites > 1024) ? round ( $sizeBites / 1024, 2 ) . "kb" : $sizeBites . "b";
			// Load linked HTML
			$dom_linked_html = new DOMDocument ();
			@$dom_linked_html->loadHtml ( $linked_html );
			// Get XPath
			$xpath_linked_html = new DOMXPath ( $dom_linked_html );

			// Get title value
			$title_node = $xpath_linked_html->query ( '//div[@class="productTitleDescriptionContainer"]/h1' );
			$title = trim ( preg_replace ( "/[\r\n]+/", " ", $title_node->item ( 0 )->nodeValue ) );

			foreach ( $this->table_fields as $key => $field ) { //check all fields set in the array ( by default just 'description' field )

				$node_description = $xpath_linked_html->query ( "//h3[@class='productDataItemHeader' and text()[contains(.,'" . $field . "')]]/following::div[1][@class='productText']/p" );
				if (! empty ( $node_description )) {
					// Get Product Field Description
					$field_description = trim ( preg_replace ( "/[\r\n]+/", " ", $node_description->item ( 0 )->nodeValue ) );
					$this->field_values [$key] = $field_description;
				}
			}

			// Get price unit HTML
			$price_node = $xpath_linked_html->query ( '//div[@class="pricing"]/p[@class="pricePerUnit"]' );

			$price_string = trim ( preg_replace ( "/[\r\n]+/", " ", $price_node->item ( 0 )->nodeValue ) );

			$this->currency = mb_substr ( $price_string, 0, 1, 'UTF-8' ); // get the currency from the

			$price_val = preg_replace ( "/[^0-9\.]/", '', $price_string ); // ditch anything that is not a number

			$total += $price_val;

			// Get the product array
			$product_result = array (
					'title' => $title,
					'size' => $size,
					'unit_price' => $price_val
					//'unit_price' => $this->currency . $price_val
			);
			foreach ( $this->field_values as $key => $value ) {
				$product_result [$key] = $value;
			}
			// Add to the final array
			$results [] = $product_result;
		}
		// Generate JSON
		return json_encode ( array (
				'results' => $results,
				'currency' => $this->currency,
				'total' => number_format ( $total, 2 )
		), JSON_UNESCAPED_UNICODE );
	}
	/**
	 * Common method
	 */
	public function __destruct() {
		if (! empty ( $this->ch )) {
			curl_close ( $this->ch );
		}
	}
	protected function checkHtml($html, $ch) {
		if (! $html) {
			echo "ERROR NUMBER: " . curl_errno ( $ch );
			echo "ERROR: " . curl_error ( $ch );
			exit ();
		}
	}
}
