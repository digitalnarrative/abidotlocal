<?php

// construct
up546E_WPCommentsQuery::getInstance();


class up546E_WPCommentsQuery {
	
	static function getInstance() {
		static $instance;
		return $instance ? $instance : $instance = new self;
	}
	
	private function __clone() {}
	
	
	protected function __construct() {
		add_filter('query_vars', array(&$this, 'query_vars'));
		add_filter('posts_fields', array(&$this, 'posts_fields'));
		add_filter('posts_where', array(&$this, 'posts_where'));
		add_filter('posts_join', array(&$this, 'posts_join'));
		add_filter('posts_orderby', array(&$this, 'posts_orderby'));
	}
	
	
	function query_vars($vars) {
		$vars[] = 'comment_count';
		$vars[] = 'comment_count_compare';
		$vars[] = 'orderby_last_comment';
		$vars[] = 'orderby_last_activity';
		return $vars;
	}
	
	function posts_fields($sql) {
		global $wpdb;
		if(get_query_var('orderby_last_activity')) {
			// might be faster, but inaccurate when post date is altered, i.e. future-publish posts
			//return $sql.', IFNULL(`last_comment`.`date`, `'.$wpdb->posts.'`.`post_date`) AS `last_activity`';
			
			return $sql.', GREATEST(`'.$wpdb->posts.'`.`post_date`, IFNULL(`last_comment`.`date`, 0)) AS `last_activity`';
		}
		return $sql;
	}
	
	function posts_where($sql) {
		global $wpdb;
		if(is_numeric($count = get_query_var('comment_count'))) {
			$compare = get_query_var('comment_count_compare');
			return ($sql !== '' ? $sql.' AND ' : '').$wpdb->prepare('`'.$wpdb->posts.'`.`comment_count` '.(in_array($compare, array('!=', '>', '>=', '<', '<=')) ? $compare : '=').' %d', $count);
		}
		return $sql;
	}
	
	function posts_join($sql) {
		global $wpdb;
		if(get_query_var('orderby_last_comment') || get_query_var('orderby_last_activity')) {
			return $sql.'
				LEFT JOIN (SELECT `comment_post_ID` AS `post`, MAX(`comment_date`) AS `date` FROM `'.$wpdb->comments.'` WHERE `comment_approved`="1" GROUP BY `post`) AS `last_comment`
					ON `last_comment`.`post` = `'.$wpdb->posts.'`.`ID`
			';
		}
		return $sql;
	}
	
	function posts_orderby($sql) {
		if(get_query_var('orderby_last_comment')) {
			return '`last_comment`.`date` DESC'.($sql !== '' ? ', '.$sql : '');
		}
		elseif(get_query_var('orderby_last_activity')) {
			return '`last_activity` DESC'.($sql !== '' ? ', '.$sql : '');
		}
		return $sql;
	}
	
}



?>