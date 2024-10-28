<?php
/*
Plugin Name: Attachment Meta
Description: Put meta fields in attachment settings meta box
Version: 1.1
Author: Nick Walke
Author URI: http://www.nickwalke.com
*/

require_once( plugin_dir_path( __FILE__ ) . '/custom_attachment_fields.php' );

class Attachment_Meta {
	private $attachment_fields = array();

	function __construct( $fields = array() ){
		$this->attachment_fields = $fields;

		add_filter( 'attachment_fields_to_edit', array( $this, 'addFields' ), 11, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'saveFields' ), 11, 2);
	}

	public function addFields( $form_fields, $post = null) {
		if ( ! empty( $this->attachment_fields ) ) {
			foreach ( $this->attachment_fields as $field => $values ) {
				if ( preg_match( "/" . $values['application'] . "/", $post->post_mime_type) && ! in_array( $post->post_mime_type, $values['exclusions'] ) ) {
					$meta = get_post_meta( $post->ID, '_' . $field, true );
					switch ( $values['input'] ) {
						default:
						case 'text':
							$values['input'] = 'text';
							break;
						case 'textarea':
							$values['input'] = 'textarea';
							break;
						case 'select':
							$values['input'] = 'html';
							$html = '<select name="attachments[' . $post->ID . '][' . $field . ']">';
							if ( isset( $values['options'] ) ) {
								foreach ( $values['options'] as $key => $value ) {
									if ( $meta == $key )
										$selected = ' selected="selected"';
									else
										$selected = '';
									$html .= '<option' . $selected . ' value="' . $key . '">' . $value . '</option>';
								}
							}
							$html .= '</select>';
							$values['html'] = $html;
							break;
						case 'checkbox':
							$values['input'] = 'html';
							if ( $meta == 'on' )
								$checked = ' checked="checked"';
							else
								$checked = '';
							$html = '<input' . $checked . ' type="checkbox" name="attachmets[' . $post->ID . '][' . $field . ']" id="attachments-' . $post->ID . '-' . $field . '" />';
							$values['html'] = $html;
							break;
						case 'radio':
							$values['input'] = 'html';
							$html = '';
							if ( ! empty( $values['options'] ) ) {
								$i = 0;
								foreach ( $values['options'] as $key => $value ) {
									if ( $meta == $key )
										$checked = ' checked="checked"';
									else
										$checked = '';
									$html .= '<input' . $checked . ' value="' . $k . '" type="radio" name="attachments[' . $post->ID . '][' . $field . ']" id="' . sanitize_key( $field . '_' . $post->ID . '_' . $i ) . '" /> <label for="' . sanitize_key( $field . '_' . $post->ID . '_' . $i ) . '">' . $v . '</label><br />';
									$i++;
								}
							}
							$values['html'] = $html;
							break;
					}
					$values['value'] = $meta;
					$form_fields[$field] = $values;
				}
			}
		}
		return $form_fields;
	}

	function saveFields( $post, $attachment ) {
		if ( ! empty( $this->attachment_fields ) ) {
			foreach ( $this->attachment_fields as $field => $values ) {
				if ( isset( $attachment[$field] ) ) {
					if ( strlen( trim( $attachment[$field] ) ) == 0 )
						$post['errors'][$field]['error'][] = __( $values['error_text'] );
					else
						update_post_meta( $post['ID'], '_' . $field, $attachment[$field] );
				}
				else {
					delete_post_meta( $post['ID'], $field );
				}
			}
		}
		return $post;
	}
}

$am = new Attachment_Meta( $additional_fields );
