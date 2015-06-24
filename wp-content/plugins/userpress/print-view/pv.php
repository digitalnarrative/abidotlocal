<?php

class up546E_PrintView
{
	const VERSION = '0.1';
	private $plugin_name = 'Print View';
	private $plugin_slug = 'print-view';
	private $options;

	public function __construct()
	{
		add_action( 'template_redirect', array( $this, 'getTemplate' ), 5 );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		$options = get_option('wiki_default');
		if ($options['modal_links'] == TRUE)
			add_filter( 'the_content', array( $this, 'ModalLink' ),1);
	}




	public function getTemplate()
	{
		if ( $this->checkPrintTemplate() ) {
			include( plugin_dir_path( __FILE__ ) . 'print.php' );
			exit();
		}
		
		if ( $this->checkModalTemplate() ) {
			include( plugin_dir_path( __FILE__ ) . 'modal.php' );
			exit();
		}
	}

	private function checkPrintTemplate()
	{
		return isset( $_GET['view'] ) && $_GET['view'] == "print";
	}

	private function checkModalTemplate()
	{
		return isset( $_GET['view'] ) && $_GET['view'] == "modal";
	}

	public function activate( $network_wide )
	{
		$data = array(
			'plugin_name' => $this->plugin_name,
			'version' => self::VERSION,
			'url' => get_home_url(),
			'sitename' => get_option( 'blogname' )
		);

	}
	public function ModalLink($content = NULL)
	{

		if (is_archive() || !in_the_loop() || get_post_type() != 'userpress_wiki') {
			return $content;
		}
		if ($content == '' || $content == NULL) {
			return $content;
		}


		$wrapper = "".utf8_encode(html_entity_decode($content))."";
		$dom = new DOMDocument();
		
		
 		$dom->strictErrorChecking = false;
		@$dom->loadHTML($wrapper);

		
		
		//Evaluate Anchor tag in HTML
		$xpath = new DOMXPath($dom);
		$hrefs = $dom->getElementsByTagName('a');

		for ($i = 0; $i < $hrefs->length; $i++) {

				$href = $hrefs->item($i);
				$url = $href->getAttribute('href');
				
				if ($url == NULL) continue;
				$postid = url_to_postid($url);
				if ($postid == 0) continue;

				$posttype = get_post_type($postid);
				if ($posttype != 'userpress_wiki') continue;
				
				if ($posttype == 'userpress_wiki') {
				
				//remove and set target attribute        
					$href->setAttribute("data-reveal-id", "PrintViewModal");
					$href->setAttribute("data-reveal-ajax", "true");
			
					$newURL=$url."?view=modal";
			
					//remove and set href attribute        
					$href->removeAttribute('href');
					$href->setAttribute("href", $newURL);
				}
		}
		
		// save html
		$out = $dom->saveHTML();
		//$out = $dom->saveHTML($dom->getElementsByTagName('body')->item(0)->firstChild);
		return $result = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $out));
		
	
	}
	
}

new up546E_PrintView;

function up546E_PrintView_link()
{
	$print_view = new up546E_PrintView;
}



