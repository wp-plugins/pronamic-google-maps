<?php

/**
 * Title: Pronamic Google Maps mashup
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Google_Maps_Mashup {
	public static function render($q = array(), $arguments = array()) {
		Pronamic_Google_Maps_Site::requireSiteScript();

		$defaults = array(
			'width' => Pronamic_Google_Maps::$defaultWidth ,
			'height' => Pronamic_Google_Maps::$defaultHeight , 
			'latitude' => 0 , 
			'longitude' => 0 , 
			'zoom' => Pronamic_Google_Maps::MAP_ZOOM_DEFAULT , 
			'map_type_id' => Pronamic_Google_Maps::MAP_TYPE_DEFAULT , 
			'hide_list' => true , 
			'fit_bounds' => true , 
			'center_client_location' => false , 
			'marker_options' => array(

			) , 
			'map_options' => array(

			) ,
			'marker_cluster_options' => array(
			
			) , 
			'echo' => true 
		);

		$arguments = wp_parse_args($arguments, $defaults);

		if($q instanceof WP_Query) {
			$query = $q;
		} else {
			$query = new WP_Query($q);
		}

		$options = new stdClass();
		$options->width = $arguments['width'];
		if(is_numeric($options->width)) {
			$options->width = '' . $options->width . 'px'; 
		}
		$options->height = $arguments['height'];
		if(is_numeric($options->height)) {
			$options->height = '' . $options->height . 'px'; 
		}
		$options->latitude = $arguments['latitude'];
		$options->longitude = $arguments['longitude'];
		$options->hideList = $arguments['hide_list'];
		$options->fitBounds = $arguments['fit_bounds'];
		$options->centerClientLocation = $arguments['center_client_location'];
			
		// Map options
		$options->mapOptions = new stdClass();
		$options->mapOptions->mapTypeId = $arguments['map_type_id'];
		$options->mapOptions->zoom = $arguments['zoom'];
		foreach($arguments['map_options'] as $key => $value) {
			$value = apply_filters('pronamic_google_maps_map_options_' . $key, $value);

			$options->mapOptions->$key = $value;
		}
		
		// Marker cluster options
		if(!empty($arguments['marker_clusterer_options'])) {
			wp_enqueue_script('google-maps-markerclustererplus');
			
			$options->markerClustererOptions = new stdClass();
			foreach($arguments['marker_clusterer_options'] as $key => $value) {
				$value = apply_filters('pronamic_google_maps_marker_clusterer_options_' . $key, $value);
	
				$options->markerClustererOptions->$key = $value;
			}
		}

		// HTML
		$content = '<div class="pgmm">';

		$content .= sprintf('<input type="hidden" name="pgmm-info" value="%s" />', esc_attr(json_encode($options)));

		$content .= sprintf('<div class="canvas" style="width: %s; height: %s;">', $options->width, $options->height);
		$content .= sprintf('</div>');

		$items = '';
		while($query->have_posts()) { $query->the_post();
			$pgm = Pronamic_Google_Maps::getMetaData();

			if($pgm->active) {
				$info = new Pronamic_Google_Maps_Info();
				$info->title = $pgm->title;
				$info->description = $pgm->description;
				$info->latitude = $pgm->latitude;
				$info->longitude = $pgm->longitude;
				$info->markerOptions = new stdClass();
				foreach($arguments['marker_options'] as $key => $value) {
					$value = apply_filters('pronamic_google_maps_marker_options_' . $key, $value);
		
					$info->markerOptions->$key = $value;
				}
				
				$items .= '<li>';
				$items .= sprintf('<input type="hidden" name="pgm-info" value="%s" />', esc_attr(json_encode($info)));

				$item = sprintf(
					'<a href="%s" title="%s" rel="bookmark">%s</a>' , 
					get_permalink() , 
					sprintf(esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' )) , 
					get_the_title()
				);

				$items .= apply_filters(Pronamic_Google_Maps_Filters::FILTER_MASHUP_ITEM, $item);
				$items .= '</li>';
			}
		}

		wp_reset_postdata();

		if(!empty($items)) {
			$content .= '<ul>';
			$content .= $items;
			$content .= '</ul>';
		}

		$content .= '</div>';

		if($arguments['echo']) {
			echo $content;
		} else {
			return $content;
		}
	}
}
