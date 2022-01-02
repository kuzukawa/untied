(function ($) {
	var heatboxOverlays = document.querySelectorAll('.heatbox-overlay');
	var instantPreviewStyleTags = document.querySelectorAll('.udb-instant-preview');

	var brandingCheckbox = document.querySelector('.udb-enable-branding');
	var layoutSelector = document.querySelector('[name="udb_branding[layout]"]');
	var removeWpLogoCheckbox = document.querySelector('.udb-remove-wp-logo');
	var adminBarLogoImageSrcField = document.querySelector('.udb-admin-bar-logo-image-url');
	var adminBarLogoLinkUrlField = document.querySelector('.udb-admin-bar-logo-url');
	var wpLogo = document.querySelector('.udb-wp-logo');
	var wpLogoLink = document.querySelector('.udb-wp-logo a');
	
	var modernLogoWrappers = document.querySelectorAll('.udb-admin-logo-wrapper');
	var inheritedModernLogoWrapper = document.querySelector('.udb-admin-logo-wrapper.udb-inherited-from-blueprint');
	var modernLogoLinks = document.querySelectorAll('.udb-admin-logo-wrapper a');
	var modernLogos = document.querySelectorAll('.udb-admin-logo-wrapper .udb-admin-logo');

	var removeWpIconStyleTag = document.querySelector('.udb-style-remove-wp-icon');
	var adminBarLogoImageUrlStyleTag = document.querySelector('.udb-style-admin-bar-logo-image-url');
	var removeWpIconSubmenuWrapperStyleTag = document.querySelector('.udb-style-remove-wp-icon-submenu-wrapper');

	var inheritedOutputStyleTag = document.querySelector('.udb-admin-colors-output.udb-inherited-from-blueprint');
	var defaultOutputStyleTag = document.querySelector('.udb-admin-colors-preview.udb-default-admin-colors-output');
	var modernOutputStyleTag = document.querySelector('.udb-admin-colors-preview.udb-modern-admin-colors-output');

	function init() {
		$('.udb-branding-admin-bar-logo-upload').click(function (e) {
			e.preventDefault();

			var custom_uploader = wp.media({
				title: 'Admin Bar Logo',
				button: {
					text: 'Upload Image'
				},
				multiple: false  // Set this to true to allow multiple files to be selected
			})
				.on('select', function () {
					var attachment = custom_uploader.state().get('selection').first().toJSON();
					adminBarLogoImageSrcField.value = attachment.url;
					adminBarLogoImageSrcField.dispatchEvent(new Event('change'));
				})
				.open();
		});

		$('.udb-branding-image-remove').click(function (e) {
			e.preventDefault();
			$(this).prev().prev().val('');
		});

		if (wpLogoLink) wpLogoLink.dataset.udbDefaultHref = wpLogoLink.href;

		modernLogoLinks.forEach(function (modernLogoLink) {
			modernLogoLink.dataset.udbDefaultHref = modernLogoLink.href;
		});

		modernLogos.forEach(function (modernLogo) {
			modernLogo.dataset.udbDefaultSrc = modernLogo.src;
		});

		checkBranding();

		brandingCheckbox.addEventListener('change', checkBranding);
		layoutSelector.addEventListener('change', checkLayout);
		removeWpLogoCheckbox.addEventListener('change', checkWpLogo);
		adminBarLogoImageSrcField.addEventListener('change', checkCustomAdminBarLogoImageSrc);
		adminBarLogoLinkUrlField.addEventListener('change', checkCustomAdminBarLogoLinkUrl)
	}

	function checkBranding() {
		if (brandingCheckbox.checked) {
			enableBranding();
			checkLayout();
			checkWpLogo();
			checkCustomAdminBarLogoImageSrc();
			checkCustomAdminBarLogoLinkUrl();
		} else {
			disableBranding();
			disableLayout();
			showWpLogo();
			disableCustomAdminBarLogoImageSrc();
			disableCustomAdminBarLogoLinkUrl();
		}
	}

	function enableBranding() {
		if (layoutSelector) layoutSelector.disabled = false;

		instantPreviewStyleTags.forEach(function (tag) {
			tag.type = 'text/css';
		});

		heatboxOverlays.forEach(function (overlay) {
			overlay.classList.add('is-hidden');
		});
	}

	function disableBranding() {
		if (layoutSelector) layoutSelector.disabled = true;

		instantPreviewStyleTags.forEach(function (tag) {
			tag.type = 'text/udb';
		});

		heatboxOverlays.forEach(function (overlay) {
			overlay.classList.remove('is-hidden');
		});
	}

	function checkLayout() {
		if (inheritedOutputStyleTag) inheritedOutputStyleTag.type = 'text/udb';

		if ('modern' === layoutSelector.value) {
			defaultOutputStyleTag.type = 'text/udb';
			modernOutputStyleTag.type = 'text/css';

			modernLogoWrappers.forEach(function (modernLogoWrapper) {
				modernLogoWrapper.classList.remove('udb-is-hidden');
			});

			if (wpLogo) wpLogo.classList.add('udb-is-hidden');
		} else {
			defaultOutputStyleTag.type = 'text/css';
			modernOutputStyleTag.type = 'text/udb';

			modernLogoWrappers.forEach(function (modernLogoWrapper) {
				modernLogoWrapper.classList.add('udb-is-hidden');
			});

			if (wpLogo) wpLogo.classList.remove('udb-is-hidden');
		}

		if (inheritedModernLogoWrapper) inheritedModernLogoWrapper.classList.add('udb-is-hidden');
	}

	function disableLayout() {
		if (inheritedOutputStyleTag) inheritedOutputStyleTag.type = 'text/css';
		defaultOutputStyleTag.type = 'text/udb';
		modernOutputStyleTag.type = 'text/udb';

		modernLogoWrappers.forEach(function (modernLogoWrapper) {
			modernLogoWrapper.classList.add('udb-is-hidden');
		});

		if (inheritedModernLogoWrapper) inheritedModernLogoWrapper.classList.remove('udb-is-hidden');
		if (wpLogo) wpLogo.classList.remove('udb-is-hidden');
	}

	function checkWpLogo() {
		if (removeWpLogoCheckbox.checked) {
			hideWpLogo();
		} else {
			if ('default' === layoutSelector.value) {
				showWpLogo();
			}
		}
	}

	function hideWpLogo() {
		if (wpLogo) wpLogo.classList.add('udb-is-hidden');
	}

	function showWpLogo() {
		if (wpLogo) wpLogo.classList.remove('udb-is-hidden');
	}

	function checkCustomAdminBarLogoImageSrc() {
		if (!adminBarLogoImageSrcField.value || '' === adminBarLogoImageSrcField.value) {
			removeWpIconStyleTag.innerHTML = buildCssContent(removeWpIconStyleTag.innerHTML, '');
			removeWpIconSubmenuWrapperStyleTag.innerHTML = buildCssContent(removeWpIconSubmenuWrapperStyleTag.innerHTML, '');
			adminBarLogoImageUrlStyleTag.innerHTML = buildCssContent(adminBarLogoImageUrlStyleTag.innerHTML, '');

			modernLogos.forEach(function (modernLogo) {
				modernLogo.src = modernLogo.dataset.udbDefaultSrc;
			});
		} else {
			removeWpIconStyleTag.innerHTML = buildCssContent(removeWpIconStyleTag.innerHTML, 'display: none;');
			removeWpIconSubmenuWrapperStyleTag.innerHTML = buildCssContent(removeWpIconSubmenuWrapperStyleTag.innerHTML, 'display: none;');

			adminBarLogoImageUrlStyleTag.innerHTML = buildCssContent(
				adminBarLogoImageUrlStyleTag.innerHTML,
				'background-image: url(' + adminBarLogoImageSrcField.value + ');'
			);

			modernLogos.forEach(function (modernLogo) {
				modernLogo.src = adminBarLogoImageSrcField.value;
			});

		}
	}

	function disableCustomAdminBarLogoImageSrc() {
		removeWpIconStyleTag.innerHTML = buildCssContent(removeWpIconStyleTag.innerHTML, 'display: inline;');
		removeWpIconSubmenuWrapperStyleTag.innerHTML = buildCssContent(removeWpIconSubmenuWrapperStyleTag.innerHTML, '');
		adminBarLogoImageUrlStyleTag.innerHTML = buildCssContent(adminBarLogoImageUrlStyleTag.innerHTML, '');

		modernLogos.forEach(function (modernLogo) {
			modernLogo.src = modernLogo.dataset.udbDefaultSrc;
		});
	}

	function checkCustomAdminBarLogoLinkUrl() {
		if (!adminBarLogoLinkUrlField.value || '' === adminBarLogoLinkUrlField.value) {
			disableCustomAdminBarLogoLinkUrl();
		} else {
			if (wpLogoLink) wpLogoLink.href = adminBarLogoLinkUrlField.value;

			modernLogoLinks.forEach(function (modernLogoLink) {
				modernLogoLink.href = adminBarLogoLinkUrlField.value;
			});
		}
	}

	function disableCustomAdminBarLogoLinkUrl() {
		if (wpLogoLink) wpLogoLink.href = wpLogoLink.dataset.udbDefaultHref;

		modernLogoLinks.forEach(function (modernLogoLink) {
			modernLogoLink.href = modernLogoLink.dataset.udbDefaultHref;
		});
	}

	function buildCssContent(content, cssRule) {
		var str = content.split('{');

		return str[0] + '{' + cssRule + '}';
	}

	init();

})(jQuery);
