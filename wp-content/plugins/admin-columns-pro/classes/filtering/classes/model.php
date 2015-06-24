<?php

/**
 * Addon class
 *
 * @since 1.0
 */
abstract class CAC_Filtering_Model {

	protected $storage_model;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct( $storage_model ) {

		$this->storage_model = $storage_model;

		// enable filtering per column
		add_action( "cac/columns/storage_key={$this->storage_model->key}", array( $this, 'enable_filtering' ) );
	}

	/**
	 * Indents any object as long as it has a unique id and that of its parent.
	 *
	 * @since 1.0
	 *
	 * @param type $array
	 * @param type $parentId
	 * @param type $parentKey
	 * @param type $selfKey
	 * @param type $childrenKey
	 * @return array Indented Array
	 */
	protected function indent( $array, $parentId = 0, $parentKey = 'post_parent', $selfKey = 'ID', $childrenKey = 'children' ) {
		$indent = array();

		$i = 0;
		foreach( $array as $v ) {

			if ( $v->$parentKey == $parentId ) {
				$indent[$i] = $v;
				$indent[$i]->$childrenKey = $this->indent( $array, $v->$selfKey, $parentKey, $selfKey );

				$i++;
			}
		}

		return $indent;
	}

	/**
	 * Applies indenting markup for taxonomy dropdown
	 *
	 * @since 1.0
	 *
	 * @param array $array
	 * @param int $level
	 * @param array $ouput
	 * @return array Output
	 */
	protected function apply_indenting_markup( $array, $level = 0, $output = array() ) {
		foreach ( $array as $v ) {

			$prefix = '';
			for( $i=0; $i<$level; $i++ ) {
				$prefix .= '&nbsp;&nbsp;';
			}

			$output[ $v->slug ] = $prefix . $v->name;

			if ( ! empty( $v->children ) ) {
				$output = $this->apply_indenting_markup( $v->children, ( $level + 1 ), $output );
			}
		}

		return $output;
	}

	/**
	 * Create dropdown
	 *
	 * @since 1.0
	 *
	 * @param string $name Attribute Name
	 * @param string $label Label
	 * @param array $options Array with options
	 * @param string $selected Current item
	 * @param bool $add_empty_option Add two options for filtering on 'EMPTY' and 'NON EMPTY' values
	 * @return string Dropdown HTML select element
	 */
	protected function dropdown( $column, $options, $add_empty_option = false ) {

		/**
		 * Filter all dropdown options
		 *
		 * @since 3.0.8.5
		 * @param array $options All the filtering options: value => label
		 * @param CPAC_Column $column_instance Column class instance
		 */
		$options = apply_filters( 'cac/addon/filtering/options', $options, $column );

		if ( empty( $options ) ) {
			return false;
		}

		$current = isset( $_GET['cpac_filter'] ) && isset( $_GET['cpac_filter'][ $column->properties->name ] ) ? $_GET['cpac_filter'][ $column->properties->name ] : '';
		?>

		<select class="postform" name="cpac_filter[<?php echo $column->properties->name; ?>]">

		<?php
		/**
		 * Filter the top label of the dropdown menu
		 *
		 * @param string $label
		 * @param CPAC_Column $column_instance Column class instance
		 */
		if ( $top_label = apply_filters( 'cac/addon/filtering/dropdown_top_label', sprintf( __( 'All %s', 'cpac' ), $column->options->label ), $column ) ) : ?>
			<option value="">
				<?php echo $top_label; ?>
			</option>
		<?php endif; ?>
			<?php foreach ( $options as $value => $label ) : ?>
				<?php

				// deprecated filter, use 'cac/addon/filtering/options'
				$label = apply_filters( 'cac/addon/filtering/dropdown_label', $label, $value, $column->options, $column->storage_model->key, $column );
				?>
				<option value="<?php echo esc_attr( urlencode( $value ) ); ?>" <?php selected( $value, $current ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
			<?php
			/**
			 * Filter empty option
			 *
			 * @param bool True / False
			 * @param CPAC_Column $column_instance Column class instance
			 */
			if ( apply_filters( 'cac/addon/filtering/dropdown_empty_option', $add_empty_option, $column ) ) : ?>
				<option disabled>──────────</option>
				<option value="cpac_empty" <?php selected( 'cpac_empty', $current ); ?>><?php _e( 'Empty', 'cpac' ); ?></option>
				<option value="cpac_not_empty" <?php selected( 'cpac_not_empty', $current ); ?>><?php _e( 'Not empty', 'cpac' ); ?></option>
			<?php endif; ?>
		</select>
		<?php
	}
}