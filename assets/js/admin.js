/**
 * MCP Adapter Admin JavaScript
 *
 * @package mcp-adapter-plugin
 */

(function($) {
	'use strict';

	/**
	 * Initialize admin functionality when document is ready.
	 */
	$(document).ready(function() {
		// Add any admin JavaScript functionality here
		console.log('MCP Adapter Admin initialized');

		// Example: Handle settings changes with live preview
		$('#mcp_adapter_enable_examples, #mcp_adapter_enable_example_servers').on('change', function() {
			const isChecked = $(this).is(':checked');
			const setting = $(this).attr('id');
			
			console.log('Setting changed:', setting, isChecked);
			
			// You could add AJAX calls here to preview changes without saving
		});

		// Add tooltips or help text functionality
		$('.mcp-help-icon').on('click', function(e) {
			e.preventDefault();
			$(this).next('.mcp-help-text').toggle();
		});
	});

	/**
	 * Handle ability testing/execution (future feature).
	 */
	window.mcpAdapterTestAbility = function(abilityId) {
		console.log('Testing ability:', abilityId);
		
		// Future implementation: AJAX call to test an ability
		$.ajax({
			url: mcpAdapterAdmin.ajaxUrl,
			method: 'POST',
			data: {
				action: 'mcp_adapter_test_ability',
				nonce: mcpAdapterAdmin.nonce,
				ability_id: abilityId
			},
			success: function(response) {
				console.log('Test result:', response);
			},
			error: function(xhr, status, error) {
				console.error('Test failed:', error);
			}
		});
	};

	/**
	 * Handle server status checks (future feature).
	 */
	window.mcpAdapterCheckServer = function(serverId) {
		console.log('Checking server:', serverId);
		
		// Future implementation: AJAX call to check server status
		$.ajax({
			url: mcpAdapterAdmin.ajaxUrl,
			method: 'POST',
			data: {
				action: 'mcp_adapter_check_server',
				nonce: mcpAdapterAdmin.nonce,
				server_id: serverId
			},
			success: function(response) {
				console.log('Server status:', response);
			},
			error: function(xhr, status, error) {
				console.error('Status check failed:', error);
			}
		});
	};

})(jQuery);
