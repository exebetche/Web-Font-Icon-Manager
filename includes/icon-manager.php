<?php
class WFIM_Icon_Manager {
	/** 
	 * Add icon selector css js
	 *
	 * @return void
	 */
	static public function add_icon_selector_js() {
		wp_enqueue_script( 'wfim_icon_selector', WFIM_PLUGIN_URL . 'js/web-font-icon-manager-icon-selector.js', array( 'jquery', 'thickbox' ), '0.1', true );
		wp_localize_script( 'wfim_icon_selector', 'wfim_cm_i18n', self::js_i18n() );
	}

	/** 
	 * Add icon selector css style
	 *
	 * @return void
	 */
	static public function add_icon_selector_styles() {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'wfim_option_page', WFIM_PLUGIN_URL . 'css/web-font-icon-manager-icon-selector.css', '0.1', true );
	}

	/**
	 * Return javascript localization variable
	 *
	 * @return array 'english message'=>'transrated message'
	 */
	static public function js_i18n() {
		return array(
			'Icon' => __( 'Icon', 'web-font-icon-manager' ),
			'Select Icon' => __( 'Select Icon', 'web-font-icon-manager' ),
			'Delete' => __( 'Delete', 'web-font-icon-manager' ),
			'Please upload font file.' => __( 'Please upload font file.', 'web-font-icon-manager' )
		);
	}

	/**
	 * Pass the code points to javascript as global variable
	 *
	 * @return void
	 */
	static public function pass_the_code_points_to_js() {
		$fonts = WFIM_Option_Manager::get_active_fonts();
		$fonts = array_merge( array_diff( $fonts, array( '' ) ) );
		if ( empty( $fonts ) )
			return;

		$output = '';
		$output .= "<script type=\"text/javascript\">\n";
		$output .= "var wfim_fonts = {\n";
		foreach ( $fonts as $font_name ) {
			$code_points = '';
			$_code_points = WFIM_Font_File_Manager::get_code_points( $font_name );
			if ( ! empty( $_code_points ) && is_array( $_code_points ) ) {
				foreach( $_code_points as $code ) {
					if ( is_numeric( $code ) )
						$code_points .= ! $code_points ? $code :  ',' . $code;
				}
			}
			$output .= "\t" . esc_html( $font_name ) . " : [$code_points]";
			$last = end( $fonts );
			if ( end( $fonts ) != $font_name )
				$output .= ",\n";
		}
		$output .= "\n}\n";
		$output .= "</script>\n";
		
		if ( ! empty( $output ) )
			echo $output;
	}

	/**
	 * Show @font-face
	 *
	 * This method remove WP Multibyte Patch plugin's css from queue.
	 * Because, the css is overide @font-face in admin screen.
	 * 
	 * @param boolen to show all fonts into @font-fase set this true
	 * @return void
	 */
	static public function at_font_face( $show_all = false ) {
		wp_dequeue_style( 'wpmp-admin-custom' );	

		if ( $show_all ) {
			$fonts = WFIM_Font_File_Manager::get_fonts();
			if ( !empty( $fonts ) && is_array( $fonts ) )
				$fonts = array_keys( $fonts );
		} else {
			$fonts = WFIM_Option_Manager::get_active_fonts();
		}
		if ( empty( $fonts ) )
			return;
		
		$output = "<style type=\"text/css\">\n";
		foreach ( $fonts as $font_name ) {
			$urls = WFIM_Font_File_Manager::get_urls( $font_name );
			if ( empty( $urls ) || ! is_array( $urls ) )
				continue;

			$output .= "@font-face {\n";
			$output .= "\tfont-family: \"$font_name\";\n";

			$url_num = $i = count( $urls );

			// eot
			if ( ! empty( $urls['eot'] ) ) {
				$output .= "\tsrc: url('" . esc_url( $urls['eot'] ) . "');\n";
				if ( $i > 1 )
					$output .= "\tsrc: url('" . esc_url( $urls['eot'] ) . "?#iefix') format('embedded-opentype'),\n";
				$i--;
			}

			// woff
			if ( ! empty( $urls['woff'] ) ) {
				if ( $url_num == $i )
					$output .= "\tsrc: ";

				$output .= "\turl('" . esc_url( $urls['woff'] ) . "') format('woff')";
				if ( $i > 1 )
					$output .= ",\n";
				else
					$output .= ";\n";
				$i--;
			}
			
			// tff
			if ( ! empty( $urls['ttf'] ) ) {
				if ( $url_num == $i )
					$output .= "\tsrc: ";

				$output .= "\turl('" . esc_url( $urls['ttf'] ) . "') format('truetype')";
				if ( $i > 1 )
					$output .= ",\n";
				else
					$output .= ";\n";
				$i--;
			}
			
			// otf
			if ( ! empty( $urls['otf'] ) ) {
				if ( $url_num == $i )
					$output .= "\tsrc: ";

				$output .= "\turl('" . esc_url( $urls['otf'] ) . "') format('opentype')";
				if ( $i > 1 )
					$output .= ",\n";
				else
					$output .= ";\n";
				$i--;
			}
			
			// svg
			if ( ! empty( $urls['svg'] ) ) {
				if ( $url_num == $i )
					$output .= "\tsrc: ";

				$output .= "url('" . esc_url( $urls['svg'] ) . "#" . esc_attr( $font_name ) . "') format('svg')";
				if ( $i > 1 )
					$output .= ",\n";
				else
					$output .= ";\n";
				$i--;
			}
			
			$output .= "}\n";
			$output .= ".icon-" . esc_html( $font_name ) . ":before,\n";
			$output .= ".icon-" . esc_html( $font_name ) . " span.i {\n";
			$output .= "\tfont-family: \"" . esc_html( $font_name ) . "\";\n";
			$output .= "\tcontent: attr(data-icon);\n";
			$output .= "}\n";
		}
		$output .= "</style>\n";
		echo $output;
	}
}

