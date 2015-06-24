<?php
/**
 * Media storage model for editability
 *
 * @since 1.0
 */
class CACIE_Editable_Model_Media extends CACIE_Editable_Model {

	/**
	 * @see CACIE_Editable_Model::is_editable()
	 * @since 1.0
	 */
	public function is_editable( $column ) {

		// By default, inherit editability from parent
		$is_editable = parent::is_editable( $column );

		switch ( $column->properties->type ) {

			// Default columns
			case 'title':

			// Custom columns
			case 'column-alternate_text':
			case 'column-caption':
			case 'column-description':
			case 'column-mime_type':
			case 'column-taxonomy':
				$is_editable = true;
			break;
		}

		/**
		 * Filter the editability of a column
		 *
		 * @since 3.4
		 *
		 * @param bool $is_editable Whether the column is editable
		 * @param CPAC_Column $column Colum object
		 * @param CACIE_Editable_Model $model Editability storage model
		 */
		$is_editable = apply_filters( 'cac/editable/is_column_editable', $is_editable, $column, $this );
		$is_editable = apply_filters( 'cac/editable/is_column_editable/column=' . $column->get_type(), $is_editable, $column, $this );

		return $is_editable;
	}

	/**
	 * @see CACIE_Editable_Model::get_column_options()
	 * @since 1.0
	 */
	public function get_column_options( $column ) {

		$options = parent::get_column_options( $column );

		switch ( $column['type'] ) {

			// WP Default
			//case 'author':
				//$options = array();
				//break;
			case 'column-taxonomy':
				$options = $this->get_term_options( $column['taxonomy'] );
				break;

			// Custom columns
			case 'column-mime_type':
				$mime_types = wp_get_mime_types();
				$options = array_combine( $mime_types, $mime_types );
				break;

		}

		return $options;
	}

	/**
	 * @see CACIE_Editable_Model::get_editables_data()
	 * @since 1.0
	 */
	public function get_editables_data() {

		$data = array(

			/**
			 * Default columns
			 *
			 */
			'title' => array(
				'type' 		=> 'text',
				'property' 	=> 'post_title',
				'js' 		=> array(
					'selector' => 'strong > a',
				),
				'display_ajax' => false
			),

			/**
			 * Custom columns
			 *
			 */
			'column-alternate_text' => array(
				'type' => 'text'
			),
			'column-caption' => array(
				'type' => 'textarea',
				'property' => 'post_excerpt'
			),
			'column-description' => array(
				'type' => 'textarea',
				'property' => 'post_content'
			),
			'column-mime_type' => array(
				'type' => 'select',
				'property' => 'post_mime_type'
			),
			'column-taxonomy' => array(
				'type' => 'select2_tags'
			),
		);

		// Handle capabilities for editing post status
		$post_type_object = get_post_type_object( $this->storage_model->post_type );

		if ( ! current_user_can( $post_type_object->cap->publish_posts ) ) {
			unset( $data['column-status'] );
		}

		/**
		 * Filter the editability settings for a column
		 *
		 * @since 3.4
		 *
		 * @param array $data {
		 *     Editability settings.
		 *
		 *     @type string		$type		Editability type. Accepts 'text', 'select', 'textarea', 'media', 'float',
		 *			 						'togglable', 'select', 'select2_dropdown' and 'select2_tags'
		 *     @type array		$options	Optional. Options for dropdown ([value] => [label]), only used when $type is "select"
		 * }
		 * @param CACIE_Editable_Model $model Editability storage model
		 */
		$data = apply_filters( 'cac/editable/editables_data', $data, $this );
		$data = apply_filters( 'cac/editable/editables_data/type=' . $this->storage_model->get_type(), $data, $this );

		return $data;
	}

	/**
	 * @see CACIE_Editable_Model::get_items()
	 * @since 1.0
	 */
	public function get_items() {

		global $wp_query;

		$items = array();

		foreach ( (array) $wp_query->posts as $post ) {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				continue;
			}

			$columndata = array();

			foreach ( $this->storage_model->columns as $column_name => $column ) {

				// Edit enabled for this column?
				if ( ! $this->is_edit_enabled( $column ) ) {
					continue;
				}

				// Set current value
				$value = '';

				// WP Default column
				if ( $column->properties->default ) {

					switch ( $column_name ) {
						case 'title':
							$value = $post->post_title;
							break;
					}
				}

				// Custom column
				else {
					$raw_value = $this->get_column_editability_value( $column, $post->ID );

					if ( $raw_value === NULL ) {
						continue;
					}

					$value = $raw_value;
				}

				/**
				 * Filter the raw value, used for editability, for a column
				 *
				 * @since 3.4
				 *
				 * @param mixed $value Column value used for editability
				 * @param CPAC_Column $column Colum object
				 * @param int $id Post ID to get the column editability for
				 * @param CACIE_Editable_Model $model Editability storage model
				 */
				$value = apply_filters( 'cac/editable/column_value', $value, $column, $post->ID, $this );
				$value = apply_filters( 'cac/editable/column_value/column=' . $column->get_type(), $value, $column, $post->ID, $this );

				// Get item data
				$itemdata = array();

				if ( method_exists( $column, 'get_item_data' ) ) {
					$itemdata = $column->get_item_data( $post->ID );
				}

				// Add data
				$columndata[ $column_name ] = array(
					'revisions' => array( $value ),
					'current_revision' => 0,
					'itemdata' => $itemdata,
					'editable' => array(
						'formattedvalue' => $this->get_formatted_value( $column, $value )
					)
				);
			}

			$items[ $post->ID ] = array(
				'ID' 			=> $post->ID,
				'object' 		=> get_object_vars( $post ),
				'columndata' 	=> $columndata
			);
		}

		return $items;
	}

	/**
	 * @see CACIE_Editable_Model::manage_value()
	 * @since 1.0
	 */
	public function manage_value( $column, $id ){

		global $post;

		$post = get_post( $id );
		setup_postdata( $post );

		switch ( $column->properties->type ) {

			case 'title':
				// @todo; currently is be set in DOM only by xeditable
				// best option would be to give all of them a return value
				// example: when using a post-status column you want to refresh
				// the title column aswell as it contains the post status aswell.
				// this can only be done if title has it's own manage_value.
				break;
		}

		wp_reset_postdata();
	}

	/**
	 * @see CACIE_Editable_Model::column_save()
	 * @since 1.0
	 */
	public function column_save( $id, $column, $value ) {
		if ( ! ( $post = get_post( $id ) ) ) {
			exit;
		}
		if ( ! current_user_can( 'edit_post', $id ) ) {
			exit;
		}

		// Third party columns can use the save() method as a callback for inline-editing
		if ( method_exists( $column, 'save' ) ) {
			$column->save( $id, $value );
			return;
		}

		$editable = $this->get_editable( $column->properties->name );

		switch ( $column->properties->type ) {

			/**
			 * Default Columns
			 *
			 */

			/**
			 * Custom Columns
			 *
			 */
			case 'column-alternate_text':
				$this->update_meta( $post->ID, '_wp_attachment_image_alt', $value );
				break;
			case 'column-acf_field':
				if ( function_exists( 'update_field' ) ) {
					update_field( $column->get_field_key(), $value, $post->ID );
				}
				break;
			case 'column-meta':
				$this->update_meta( $post->ID, $column->get_field_key(), $value );
				break;
			case 'column-taxonomy':
				if ( ! empty( $column->options->taxonomy ) && taxonomy_exists( $column->options->taxonomy ) ) {
					$this->set_post_terms( $id, $value, $column->options->taxonomy );
				}
				break;

			// Save basic property such as title or description (data that is available in WP_Post)
			default:
				if ( ! empty( $editable['property'] ) ) {
					$property = $editable['property'];

					if ( isset( $post->{$property} ) ) {
						wp_update_post( array(
							'ID' => $post->ID,
							$property => $value
						) );
					}
				}
		}
	}
}