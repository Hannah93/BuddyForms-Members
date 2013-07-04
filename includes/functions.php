<?php

function bp_4209_remove_nav_items() {
    bp_core_remove_subnav_item( 'buddyforms', 'edit' );
}
//add_action( 'bp_setup_nav', 'bp_4209_remove_nav_items', 100 );


add_filter( 'buddyforms_before_admin_form_render', 'buddyforms_before_admin_form_render');

function buddyforms_before_admin_form_render($form){
	
	// echo '<pre>';
	// print_r($form);
	// echo '</pre>';
	
	return $form;
}

// add the forms to the admin bar
function buddyforms_members_wp_before_admin_bar_render(){
	global $wp_admin_bar, $buddyforms;

	if (empty($buddyforms['selected_post_types']))
		return;

	foreach ($buddyforms['selected_post_types'] as $key => $selected_post_type) {
		if(isset($selected_post_type['selected'])) :
			
			if(isset($selected_post_type['form'])){
				$form = $selected_post_type['form'];
			}
			$slug = $key;
			if(isset($form) && isset($buddyforms['buddyforms'][$form]['slug']))
				$slug = $buddyforms['buddyforms'][$form]['slug'];
			
			$post_type_object = get_post_type_object( $key );
			$name = $post_type_object->labels->name;
			
			if(isset($form) && isset($buddyforms['buddyforms'][$form]['name']))
				$name = $buddyforms['buddyforms'][$form]['name'];
			
		
			if(isset($buddyforms['buddyforms'][$selected_post_type['form']]['admin_bar'][0])){
				$wp_admin_bar->add_menu( array(
					'parent'	=> 'my-account-buddypress',
					'id'		=> 'my-account-buddypress-'.$key,
					'title'		=> __($name, 'buddypress'),
					'href'		=> trailingslashit(bp_loggedin_user_domain() . $slug)
				));
				$wp_admin_bar->add_menu( array(
						'parent'	=> 'my-account-buddypress-'.$key,
						'id'		=> 'my-account-buddypress-'.$key.'-view',
						'title'		=> __('View','buddypress'),
						'href'		=> trailingslashit(bp_loggedin_user_domain() . $slug)
				)); 
				if(isset($form) && $form != 'no-form') {
					 $wp_admin_bar->add_menu( array(
						'parent'	=> 'my-account-buddypress-'.$key,
						'id'		=> 'my-account-buddypress-'.$key.'-new',
						'title'		=> __('New ','buddypress'),
						'href'		=> trailingslashit(bp_loggedin_user_domain() . $slug).'create'
					));  
				}
			}
		endif;
	}
}
add_action('wp_before_admin_bar_render', 'buddyforms_members_wp_before_admin_bar_render',99,1);

function buddyforms_admin_bar_members() {
	
    global $wp_admin_bar, $buddyforms;
	
	// echo '<pre>';
	// print_r($buddyforms);
	// echo '</pre>';
	if(!isset($buddyforms['selected_post_types']))
		return;
	
	foreach ($buddyforms['selected_post_types'] as $key => $selected_post_type) {
		
		$wp_admin_bar->remove_menu('my-account-'.$selected_post_type['form']);
	}

    
}
add_action('wp_before_admin_bar_render', 'buddyforms_admin_bar_members' ,10,1);

function members_form($form, $post_type){
	global $buddyforms;

	$form = $buddyforms['selected_post_types'][$post_type]['form'];

	return $form;
}
add_filter('buddyforms_the_form_to_use','members_form',1,2);


function buddyforms_set_globals_new_slug($buddyforms,$new_slug,$old_slug){
	
	if(!isset($buddyforms['selected_post_types']))
		return $buddyforms;
	
	foreach ($buddyforms['selected_post_types'] as $key => $selected_post_type) {	
		if($selected_post_type['form'] == $old_slug){
			$buddyforms['selected_post_types'][$key]['form'] = $new_slug;
		}
	}
	return $buddyforms;
}
add_filter('buddyforms_set_globals_new_slug','buddyforms_set_globals_new_slug',1,3);

function buddyforms_set_globals_members($buddyforms){
	
	if(!isset($buddyforms['selected_post_types']))
		return $buddyforms;
	
	$theposttypes = Array();
	foreach ($buddyforms['selected_post_types'] as $key => $selected_post_type) {
		
		if(isset($selected_post_type['selected'])){
			$theposttypes[$key] = $selected_post_type;
		}
	}
	$buddyforms['selected_post_types'] = $theposttypes;

	return $buddyforms;
}
add_filter('buddyforms_set_globals','buddyforms_set_globals_members',1,1);

function buddyforms_select_posttypes($form){
	global $buddyforms; 
		// Get all post types
    $args=array(
		'public' => true,
		'show_ui' => true
    ); 
    $output = 'names'; // names or objects, note names is the default
    $operator = 'and'; // 'and' or 'or'
    $post_types=get_post_types($args,$output,$operator); 
	
	if(is_array($buddyforms['buddyforms'])){
		$the_forms[] = 'no-form';
		foreach ($buddyforms['buddyforms'] as $key => $buddyform) {
			$the_forms[] = $buddyform['slug'];
		}
		$form_fields = Array();
		
		
		$form->addElement(new Element_HTML('
 		<div class="accordion-group">
			<div class="accordion-heading"><p class="accordion-toggle" data-toggle="collapse" data-parent="#accordion_buddyforms_general_settings_members" href="#accordion_buddyforms_general_settings_members">BuddyForms Members</p></div>
		    <div id="accordion_buddyforms_general_settings_members" class="accordion-body collapse">
				<div class="accordion-inner"><p>Select the post type you want to use in BuddyPress Profiles.</p>')); 
					$form->addElement( new Element_HTML('<ul class="buddyforms_members">'));
					foreach( $post_types as $key => $post_type) {
						$form->addElement( new Element_HTML('<li>'));
						$selected = '';
						if(isset($buddyforms['selected_post_types'][$post_type]['selected']))
							$selected = $buddyforms['selected_post_types'][$post_type]['selected'];
											
							$form->addElement( new Element_Checkbox("","buddyforms_options[selected_post_types][".$post_type."][selected]",array($post_type),array('id' => 'select_posttype_'.$post_type, 'class' => 'select_posttype', 'value' => $selected)));
						$selected_form = '';
						if(isset($buddyforms['selected_post_types'][$post_type]['form']))
							$selected_form = $buddyforms['selected_post_types'][$post_type]['form'];
							
							$form->addElement( new Element_Select("", "buddyforms_options[selected_post_types][".$post_type."][form]", $the_forms, array('class' => 'select_posttype_'.$post_type.'-0 bf_select','value' => $selected_form)));
						$form->addElement(new Element_HTML('</li>'));
					}
					$form->addElement(new Element_HTML('</ul>'));
					$form->addElement( new Element_HTML('
				</div>
			</div>
		</div>'));	
			
			

					
	}
return $form;	
}
add_filter('buddyforms_general_settings','buddyforms_select_posttypes',1,1);

/**
 * hook the buddypress default single.php hooks into the form display field
 * 
 * this function is support for the bp_default theme and an can be used as example for other theme/plugin developer
 * how to hook their theme or plugin hooks. 
 *
 * @package BuddyForms
 * @since 0.2 beta
*/
function buddyforms_form_element_single_hooks($buddyforms_form_element_hooks,$post_type,$field_id){
	if(get_template() != 'bp-default')
		return $buddyforms_form_element_hooks;
	 
		array_push($buddyforms_form_element_hooks,
			'bp_before_blog_single_post',
			'bp_after_blog_single_post'
		);

	return $buddyforms_form_element_hooks;
}
add_filter('buddyforms_form_element_hooks','buddyforms_form_element_single_hooks',1,3);

/**
 * Get the BuddyForms template directory
 *
 * @package BuddyForms
 * @since 0.1 beta
 *
 * @uses apply_filters()
 * @return string
 */
function buddyforms_members_get_template_directory() {
	return apply_filters('buddyforms_members_get_template_directory', constant('BUDDYFORMS_MEMBERS_TEMPLATE_PATH'));
}

/**
 * Locate a template
 *
 * @package BuddyForms
 * @since 0.1 beta
 */
function buddyforms_members_locate_template($file) {
	if (locate_template(array($file), false)) {
		locate_template(array($file), true);
	} else {
		include (BUDDYFORMS_MEMBERS_TEMPLATE_PATH . $file);
	}
}
?>