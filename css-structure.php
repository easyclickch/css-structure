<?php

require_once('class.css-maker.php');

/* to create selector name general */
function get_class_name($name, $count, $separator = ', '){
	$grid = '';
	$i=1;while($i<=$count){
		$grid .= '.' . $name . '_' . $i . $separator;
		$i++;
	}

	return rtrim($grid, $separator);
}

function calculate_grid( $wrapperWidth, $column, $gridMargin, $grid ){
	$real_width = $wrapperWidth / $column;
	$margin = is_array($gridMargin) ? $gridMargin[1] + $gridMargin[3] : $gridMargin * 2;
	$width = ($real_width * $grid) - $margin;
	$width = floor($width);
	
	return array($real_width, $width);
}

function calculate_css_grid( $wrapperWidth, $column, $gridMargin, $args=array() ){
	$defaults = array('grid' => 0, 'prefix' => 0, 'suffix' => 0, 'first' => false, 'last' => false, 'css' => array());
	$data = (object) array_merge($defaults, $args);
	
	$one_grid_width = $wrapperWidth / $column;
	$margin = is_array($gridMargin) ? $gridMargin[1] + $gridMargin[3] : $gridMargin * 2;
	$grid_width = ($one_grid_width * $data->grid );
	
	$output = array();
	$output['width'] = floor($grid_width - $margin) . 'px';
		
		if ($data->prefix)
			$output['padding-left'] = floor($one_grid_width * $data->prefix) . 'px';
		if ($data->suffix)
			$output['padding-right'] = floor($one_grid_width  * $data->suffix) . 'px';
		if ($data->first)
			$output['margin-left'] = 0 . 'px';
		if ($data->last)
			$output['margin-right'] = 0 . 'px';
/* 		if (!empty($data->css))
			$output = array_merge($output, $data->css); */
		if ($data->css['margin-left']){
			$output['width'] = sprintf('%d', $output['width']) - sprintf('%d', $data->css['margin-left']) . 'px';
			$output['margin-left'] = sprintf('%d', $data->css['margin-left']) . 'px';
		}
		if ($data->css['margin-right']){
			$output['width'] = sprintf('%d', $output['width']) - sprintf('%d', $data->css['margin-right']) . 'px';
			$output['margin-right'] = sprintf('%d', $data->css['margin-right']) . 'px';
		}			
		if ($data->css['padding-left']){
			$output['width'] = sprintf('%d', $output['width']) - sprintf('%d', $data->css['padding-left']) . 'px';
			$output['padding-left'] = sprintf('%d', $data->css['padding-left']) . 'px';
		}if ($data->css['padding-right']){
			$output['width'] = sprintf('%d', $output['width']) - sprintf('%d', $data->css['padding-right']) . 'px';
			$output['padding-right'] = sprintf('%d', $data->css['padding-right']) . 'px';
		}
	return $output;
}

function calculate_column_css( $args=array() ){
	$defaults = array('wrapperWidth' => 960, 'column' => 12, 'gridMargin' => 10, 'columnSelector' => array() );
	
	$r = array_merge($defaults, $args);
	
	extract($r, EXTR_SKIP);
	
	$results = array();
	$i = 0;
	foreach ($columnSelector as $_selector => $grid){
		$count = $i++;
		
		if (!is_array($grid))
			$grid = array('grid' => $grid);
			
		$select = strval($_selector);
		
		$results[$select] = calculate_css_grid($wrapperWidth, $column, $gridMargin, $grid);
	}
	
	return $results;
}

function css_pseudo($strings, $pclass ='after'){

	if ( is_array($strings) ){
		foreach ($strings as $k)
			$o .=  $k . ':' . $pclass . ', ';
	}else{
		$o = $strings . ':' . $pclass;
	}
	
	return rtrim($o, ', ');
}

function css_for_ie($strings){
	if ( is_array($strings) ){
		foreach ($strings as $k){
			$o .=  '* html '. $k . ', ';
			$o .=  '*:first-child+html '. $k . ', ';
		}
	}else{
		$o =  '* html '. $k . ', ';
		$o .=  '*:first-child+html '. $k . ', ';
	}
	
	return rtrim($o, ', ');
}

function create_css_grid($args=array()){
	$defaults = array(
		'column' => 16,
		'gridMargin' => array(0, 10, 0, 10),
		'wrapperWidth' => 960,
		'wrapperMargin' => array(0, 'auto', 0, 'auto'),
		'wrapperSelector' => '#wrapper',
		'columnSelector' => array(
			//'#container' => array('grid' => 10, 'prefix' => 0, 'suffix' => 1, 'clearfix' => false),
			//'#sidebar' => array('grid' => 5, 'prefix' => 0, 'suffix' => 0, 'clearfix' => false),
		),
		'clearfix' => array(),
		'prefix' => '',
		'suffix' => '',
		'first' => '',
		'last' => '',
		'MinWidthWrapperSelector' => '',
	);
	
	$r = array_merge($defaults, $args);
	extract($r, EXTR_SKIP);	
	
	$css = new DilhamsoftCSSMaker(array('minify' => $minify));	
	
	$px = 'px';
	
	$columns = implode(', ', array_keys($columnSelector) );
	
	if ($MinWidthWrapperSelector)
		$css->add($MinWidthWrapperSelector, 'min-width', $wrapperWidth . 'px');
		
	/* core wrapper */
	if ($wrapperSelector)
		$css->adds($wrapperSelector, array('margin' => $wrapperMargin, 'width' => $wrapperWidth . $px));
	
	if ($gridType == 'column')
		$css->adds($columns, array('display' => 'inline', 'float' => 'left', 'margin-left' => 0 . $px, 'margin-right' => $gridMargin[1] + $gridMargin[3] . $px ) );
	else
		$css->adds($columns, array('display' => 'inline', 'float' => 'left', 'margin-left' => $gridMargin[1] . $px, 'margin-right' => $gridMargin[3] . $px ) );
	
	foreach ( calculate_column_css($r) as $selector => $results ){
		$css->adds($selector,  $results);
	}
		
/* 	$alpha[] = '.alpha';
	$omega[] = '.omega';
	$css->add(implode(', ', $alpha), 'margin-left', 0);
	$css->add(implode(', ', $omega), 'margin-right', 0); */
	
	
	$clearfix = (!is_array($clearfix) && !empty($clearfix)) ? explode(',', $clearfix) : $clearfix;
	$clearfix[] = '.clearfix';
	
	$clears = array('clear' => 'both', 'content' => "' '", 'display' => 'block', 'font-size'=> 0, 'line-height' => 0, 'visibility' => 'hidden', 'width' => 0, 'height' => 0);
	$css->adds(css_pseudo($clearfix, 'after'), $clears);
	
	$css->adds(css_for_ie($clearfix),  array('zoom' => 1));
	
	/* results */
	$css->print_css();
}

?>