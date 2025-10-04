<?php
/**
 * Example Abilities Registration
 *
 * Provides example abilities to demonstrate the plugin functionality.
 * These can be used as templates for creating custom abilities.
 *
 * @package mcp-adapter-plugin
 */

// Register example abilities on the abilities_api_init hook
add_action( 'abilities_api_init', 'mcp_adapter_plugin_register_example_abilities' );

/**
 * Register example abilities for demonstration purposes.
 *
 * These abilities showcase different patterns and use cases:
 * - Tool: Create Post (action with parameters)
 * - Resource: Site Info (data retrieval)
 * - Prompt: Content Strategy (advisory content)
 */
function mcp_adapter_plugin_register_example_abilities() {

	// Only register examples if explicitly enabled
	$register_examples = apply_filters( 'mcp_adapter_plugin_register_example_abilities', get_option( 'mcp_adapter_enable_examples', false ) );
	if ( ! $register_examples ) {
		return;
	}

	// Example 1: Site Information (good for Resource)
	wp_register_ability(
		'mcp-adapter-plugin/site-info',
		array(
			'label'       => __( 'Get Site Information', 'mcp-adapter-plugin' ),
			'description' => __( 'Retrieves comprehensive information about the WordPress site including name, URL, version, theme, and plugin details.', 'mcp-adapter-plugin' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'include_plugins' => array(
						'type'        => 'boolean',
						'description' => 'Include list of active plugins',
						'default'     => false,
					),
					'include_theme' => array(
						'type'        => 'boolean',
						'description' => 'Include active theme information',
						'default'     => true,
					),
				),
				'additionalProperties' => false,
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'site_name' => array(
						'type'        => 'string',
						'description' => 'The name of the WordPress site',
					),
					'site_url' => array(
						'type'        => 'string',
						'format'      => 'uri',
						'description' => 'The URL of the WordPress site',
					),
					'site_description' => array(
						'type'        => 'string',
						'description' => 'The site tagline',
					),
					'wordpress_version' => array(
						'type'        => 'string',
						'description' => 'WordPress version',
					),
					'php_version' => array(
						'type'        => 'string',
						'description' => 'PHP version',
					),
					'active_theme' => array(
						'type'        => 'object',
						'description' => 'Active theme information',
					),
					'active_plugins' => array(
						'type'        => 'array',
						'description' => 'List of active plugins',
						'items'       => array( 'type' => 'string' ),
					),
				),
			),
			'execute_callback' => function( $input ) {
				$include_plugins = isset( $input['include_plugins'] ) ? (bool) $input['include_plugins'] : false;
				$include_theme   = isset( $input['include_theme'] ) ? (bool) $input['include_theme'] : true;

				$result = array(
					'site_name'         => get_bloginfo( 'name' ),
					'site_url'          => get_site_url(),
					'site_description'  => get_bloginfo( 'description' ),
					'wordpress_version' => get_bloginfo( 'version' ),
					'php_version'       => PHP_VERSION,
				);

				// Add theme information if requested
				if ( $include_theme ) {
					$theme = wp_get_theme();
					$result['active_theme'] = array(
						'name'    => $theme->get( 'Name' ),
						'version' => $theme->get( 'Version' ),
						'author'  => $theme->get( 'Author' ),
					);
				}

				// Add plugins information if requested
				if ( $include_plugins ) {
					if ( ! function_exists( 'get_plugins' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					$all_plugins    = get_plugins();
					$active_plugins = get_option( 'active_plugins', array() );

					$result['active_plugins'] = array();
					foreach ( $active_plugins as $plugin_path ) {
						if ( isset( $all_plugins[ $plugin_path ] ) ) {
							$result['active_plugins'][] = $all_plugins[ $plugin_path ]['Name'];
						}
					}
				}

				return $result;
			},
			'permission_callback' => function() {
				return current_user_can( 'read' );
			},
		)
	);

	// Example 2: Create Draft Post (good for Tool)
	wp_register_ability(
		'mcp-adapter-plugin/create-draft',
		array(
			'label'       => __( 'Create Draft Post', 'mcp-adapter-plugin' ),
			'description' => __( 'Creates a new draft post with the specified title and content. The post will be saved as a draft and can be edited later.', 'mcp-adapter-plugin' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'title' => array(
						'type'        => 'string',
						'description' => 'The post title',
						'minLength'   => 1,
						'maxLength'   => 200,
					),
					'content' => array(
						'type'        => 'string',
						'description' => 'The post content (HTML allowed)',
						'minLength'   => 1,
					),
					'excerpt' => array(
						'type'        => 'string',
						'description' => 'Optional post excerpt',
					),
				),
				'required'   => array( 'title', 'content' ),
				'additionalProperties' => false,
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'post_id' => array(
						'type'        => 'integer',
						'description' => 'The ID of the created post',
					),
					'post_url' => array(
						'type'        => 'string',
						'format'      => 'uri',
						'description' => 'The URL to view the post',
					),
					'edit_url' => array(
						'type'        => 'string',
						'format'      => 'uri',
						'description' => 'The admin edit URL',
					),
				),
			),
			'execute_callback' => function( $input ) {
				$post_data = array(
					'post_title'   => sanitize_text_field( $input['title'] ),
					'post_content' => wp_kses_post( $input['content'] ),
					'post_status'  => 'draft',
					'post_type'    => 'post',
				);

				if ( ! empty( $input['excerpt'] ) ) {
					$post_data['post_excerpt'] = sanitize_text_field( $input['excerpt'] );
				}

				$post_id = wp_insert_post( $post_data );

				if ( is_wp_error( $post_id ) ) {
					throw new Exception( 'Failed to create post: ' . $post_id->get_error_message() );
				}

				return array(
					'post_id'  => $post_id,
					'post_url' => get_permalink( $post_id ),
					'edit_url' => get_edit_post_link( $post_id, 'raw' ),
				);
			},
			'permission_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	// Example 3: Content Strategy Advice (good for Prompt)
	wp_register_ability(
		'mcp-adapter-plugin/content-strategy',
		array(
			'label'       => __( 'Content Strategy Recommendations', 'mcp-adapter-plugin' ),
			'description' => __( 'Analyzes the site and provides strategic recommendations for content creation based on existing content, gaps, and best practices.', 'mcp-adapter-plugin' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'focus_area' => array(
						'type'        => 'string',
						'description' => 'Area to focus analysis on',
						'enum'        => array( 'all', 'frequency', 'topics', 'engagement' ),
						'default'     => 'all',
					),
				),
				'additionalProperties' => false,
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'analysis' => array(
						'type'        => 'string',
						'description' => 'Overall content analysis',
					),
					'recommendations' => array(
						'type'        => 'array',
						'description' => 'List of strategic recommendations',
						'items'       => array(
							'type'       => 'object',
							'properties' => array(
								'title'          => array( 'type' => 'string' ),
								'description'    => array( 'type' => 'string' ),
								'priority'       => array( 'type' => 'string' ),
								'implementation' => array( 'type' => 'string' ),
							),
						),
					),
					'next_steps' => array(
						'type'        => 'string',
						'description' => 'Suggested next steps',
					),
				),
			),
			'execute_callback' => function( $input ) {
				$focus_area = isset( $input['focus_area'] ) ? $input['focus_area'] : 'all';

				// Analyze content
				$post_count = wp_count_posts( 'post' );
				$page_count = wp_count_posts( 'page' );

				// Get recent posts to analyze frequency
				$recent_posts = get_posts(
					array(
						'numberposts' => 10,
						'post_status' => 'publish',
						'orderby'     => 'date',
						'order'       => 'DESC',
					)
				);

				$recommendations = array();

				// Frequency analysis
				if ( in_array( $focus_area, array( 'all', 'frequency' ), true ) ) {
					if ( $post_count->publish < 10 ) {
						$recommendations[] = array(
							'title'          => 'Build Content Foundation',
							'description'    => 'Your site has fewer than 10 published posts. Building a solid content foundation is crucial for SEO and audience engagement.',
							'priority'       => 'High',
							'implementation' => 'Create a content calendar to publish at least 2-3 quality posts per week for the next month.',
						);
					}

					// Check posting frequency
					if ( count( $recent_posts ) > 0 ) {
						$latest_post = $recent_posts[0];
						$days_since  = floor( ( time() - strtotime( $latest_post->post_date ) ) / DAY_IN_SECONDS );

						if ( $days_since > 30 ) {
							$recommendations[] = array(
								'title'          => 'Increase Publishing Frequency',
								'description'    => sprintf( 'It has been %d days since your last post. Regular publishing is important for maintaining audience engagement.', $days_since ),
								'priority'       => 'Medium',
								'implementation' => 'Establish a consistent publishing schedule, even if it\'s just once per week.',
							);
						}
					}
				}

				// Topics analysis
				if ( in_array( $focus_area, array( 'all', 'topics' ), true ) ) {
					$categories = get_categories( array( 'hide_empty' => true ) );
					if ( count( $categories ) < 3 ) {
						$recommendations[] = array(
							'title'          => 'Diversify Content Topics',
							'description'    => 'Your content covers a limited number of topics. Diversifying can help reach a broader audience.',
							'priority'       => 'Medium',
							'implementation' => 'Identify 3-5 core topic areas relevant to your audience and create content pillars for each.',
						);
					}
				}

				// Default recommendation if none found
				if ( empty( $recommendations ) ) {
					$recommendations[] = array(
						'title'          => 'Continue Current Strategy',
						'description'    => 'Your content strategy appears to be on track. Focus on maintaining quality and consistency.',
						'priority'       => 'Low',
						'implementation' => 'Keep monitoring your content performance and adjust based on analytics.',
					);
				}

				$analysis = sprintf(
					'Your site currently has %d published posts and %d pages. Analysis focused on: %s',
					$post_count->publish,
					$page_count->publish,
					$focus_area
				);

				return array(
					'analysis'        => $analysis,
					'recommendations' => $recommendations,
					'next_steps'      => 'Review these recommendations and prioritize implementation based on your resources and goals. Start with high-priority items first.',
				);
			},
			'permission_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
