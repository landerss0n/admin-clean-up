/**
 * Admin Clean Up - Admin Scripts
 *
 * @package Admin_Clean_Up
 */

(function($) {
	'use strict';

	/**
	 * Move any misplaced notices to correct location
	 */
	function moveNotices() {
		var $wrap = $('.acu-settings-wrap');
		var $settings = $wrap.find('.acu-settings');

		// Find any notices inside .acu-settings and move them before it
		$settings.find('.notice, .updated, .error').each(function() {
			$(this).insertBefore($settings);
		});
	}

	// Run on DOM ready
	$(document).ready(function() {
		moveNotices();
	});

	/**
	 * Media uploader for login logo
	 */
	var loginLogoUploader;

	$('#acu-select-login-logo').on('click', function(e) {
		e.preventDefault();

		if (loginLogoUploader) {
			loginLogoUploader.open();
			return;
		}

		loginLogoUploader = wp.media({
			title: acuAdmin.selectImage,
			button: {
				text: acuAdmin.useImage
			},
			multiple: false,
			library: {
				type: 'image'
			}
		});

		loginLogoUploader.on('select', function() {
			var attachment = loginLogoUploader.state().get('selection').first().toJSON();

			$('#acu-custom-login-logo').val(attachment.id);

			var imgUrl = attachment.sizes && attachment.sizes.medium
				? attachment.sizes.medium.url
				: attachment.url;

			$('#acu-login-logo-preview')
				.html('<img src="' + imgUrl + '" alt="">')
				.show();

			$('#acu-select-login-logo').text(acuAdmin.selectImage.replace('Select', 'Change'));
			$('#acu-remove-login-logo').show();
		});

		loginLogoUploader.open();
	});

	$('#acu-remove-login-logo').on('click', function(e) {
		e.preventDefault();

		$('#acu-custom-login-logo').val('');
		$('#acu-login-logo-preview').hide().html('');
		$('#acu-select-login-logo').text(acuAdmin.selectImage);
		$(this).hide();
	});

})(jQuery);
