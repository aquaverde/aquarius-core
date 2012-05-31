/*
	jQuery Coda-Slider v2.0 - http://www.ndoherty.biz/coda-slider
	Copyright (c) 2009 Niall Doherty
	This plugin available for use in all personal or commercial projects under both MIT and GPL licenses.
*/

/* edited 11-08-11: slideText added, thomas@aquaverde.ch */

$(function(){
	// Remove the coda-slider-no-js class from the body
	$("body").removeClass("coda-slider-no-js");
	// Preloader
	$(".coda-slider").children('.panel').hide().end().prepend('<p class="loading">Loading...<br /><img src="images/ajax-loader.gif" alt="loading..." /></p>');
});

var sliderCount = 1;

$.fn.codaSlider = function(settings) {

	settings = $.extend({
		autoHeight: false,
		autoHeightEaseDuration: 1000,
		autoHeightEaseFunction: "easeInOutExpo",
		autoSlide: true,
		autoSlideInterval: 10000,
		autoSlideStopWhenClicked: true,
		crossLinking: false,
		dynamicArrows: true,
		dynamicArrowLeftText: "",
		dynamicArrowRightText: "",
		dynamicTabs: false,
		dynamicTabsAlign: "",
		dynamicTabsPosition: "",
		externalTriggerSelector: "",
		firstPanelToLoad: 1,
		panelTitleSelector: "",
		slideEaseDuration: 1000,
		slideEaseFunction: "easeInOutExpo",
		slideText: ".claim",
		slideTextEaseDuration: 1100
	}, settings);
	
	return this.each(function(){
		
		// Uncomment the line below to test your preloader
		// alert("Testing preloader");
		
		var slider = $(this);
		
		// If we need arrows
		if (settings.dynamicArrows && slider.find(".panel").length > 1) {
			slider.parents("body").append('<div class="arrow left" id="codaNavLeft-' + sliderCount + '"></div>');
			slider.parents("body").append('<div class="arrow right" id="codaNavRight-' + sliderCount + '"></div>');
		};
				
		var panelWidth = slider.find(".panel").width();
		var panelCount = slider.find(".panel").size();
		var panelContainerWidth = panelWidth*panelCount;
		var navClicks = 0; // Used if autoSlideStopWhenClicked = true
		
		// Surround the collection of panel divs with a container div (wide enough for all panels to be lined up end-to-end)
		$('.panel', slider).wrapAll('<div class="panelContainer"></div>');
		// Specify the width of the container div (wide enough for all panels to be lined up end-to-end)
		$(".panelContainer", slider).css({ width: panelContainerWidth });
		
		// Specify the current panel.
		// If the loaded URL has a hash (cross-linking), we're going to use that hash to give the slider a specific starting position...
		if (settings.crossLinking && location.hash && parseInt(location.hash.slice(1)) <= panelCount) {
			var currentPanel = parseInt(location.hash.slice(1));
			var offset = - (panelWidth*(currentPanel - 1));
			$('.panelContainer', slider).css({ marginLeft: offset });
		// If that's not the case, check to see if we're supposed to load a panel other than Panel 1 initially...
		} else if (settings.firstPanelToLoad != 1 && settings.firstPanelToLoad <= panelCount) { 
			var currentPanel = settings.firstPanelToLoad;
			var offset = - (panelWidth*(currentPanel - 1));
			$('.panelContainer', slider).css({ marginLeft: offset });
		// Otherwise, we'll just set the current panel to 1...
		} else { 
			var currentPanel = 1;
		};
			
		// Left arrow click
		$('.arrow.left').bind("click", prev);
		
		function prev(){
			$('.arrow').unbind("click");
			navClicks++;
			if (currentPanel == 1) {
				offset = - (panelWidth*(panelCount - 1));
				alterPanelHeight(panelCount - 1);
				currentPanel = panelCount;
				$(settings.slideText).not(".current "+settings.slideText).css({ left: $(settings.slideText).width() }).animate({ left: 0 }, settings.slideTextEaseDuration, settings.slideEaseFunction);
				$(".current "+settings.slideText).css({ left: 0 }).animate({ left: -$(settings.slideText).width() }, settings.slideTextEaseDuration, settings.slideEaseFunction, function(){
					$('.arrow.left').bind("click", prev);
					$('.arrow.right').bind("click", next);
				});
				slider.find('.current').removeClass('current').parent().find('div:last').addClass('current');
			} else {
				currentPanel -= 1;
				alterPanelHeight(currentPanel - 1);
				offset = - (panelWidth*(currentPanel - 1));
				$(settings.slideText).not(".current "+settings.slideText).css({ left: -$(settings.slideText).width() }).animate({ left: 0 }, settings.slideTextEaseDuration, settings.slideEaseFunction);
				$(".current "+settings.slideText).css({ left: 0 }).animate({ left: $(settings.slideText).width() }, settings.slideTextEaseDuration, settings.slideEaseFunction, function(){
					$('.arrow.left').bind("click", prev);
					$('.arrow.right').bind("click", next);
				});
				slider.find('.current').removeClass('current').prev().addClass('current');
			};
			$('.panelContainer', slider).animate({ marginLeft: offset }, settings.slideEaseDuration, settings.slideEaseFunction);
			if (settings.crossLinking) { location.hash = currentPanel }; // Change the URL hash (cross-linking)
			return false;
		}
			
		// Right arrow click
		$('.arrow.right').bind("click", next);
		
		function next(){
			$('.arrow').unbind("click");
			navClicks++;
			if (currentPanel == panelCount) {
				offset = 0;
				currentPanel = 1;
				alterPanelHeight(0);
				$(settings.slideText).not(".current "+settings.slideText).css({ left: -$(settings.slideText).width() }).animate({ left: 0 }, settings.slideTextEaseDuration, settings.slideEaseFunction);
				$(".current "+settings.slideText).css({ left: 0 }).animate({ left: $(settings.slideText).width() }, settings.slideTextEaseDuration, settings.slideEaseFunction, function(){
					$('.arrow.left').bind("click", prev);
					$('.arrow.right').bind("click", next);
				});
				slider.find('.current').removeClass('current').parent().find('div:eq(0)').addClass('current');
			} else {
				offset = - (panelWidth*currentPanel);
				alterPanelHeight(currentPanel);
				currentPanel += 1;
				$(settings.slideText).not(".current "+settings.slideText).css({ left: $(settings.slideText).width() }).animate({ left: 0 }, settings.slideTextEaseDuration, settings.slideEaseFunction);
				$(".current "+settings.slideText).css({ left: 0 }).animate({ left: -$(settings.slideText).width() }, settings.slideTextEaseDuration, settings.slideEaseFunction, function(){
					$('.arrow.left').bind("click", prev);
					$('.arrow.right').bind("click", next);
				});
				slider.find('.current').removeClass('current').next().addClass('current');
			};
			$('.panelContainer', slider).animate({ marginLeft: offset }, settings.slideEaseDuration, settings.slideEaseFunction);
			if (settings.crossLinking) { location.hash = currentPanel }; // Change the URL hash (cross-linking)
			return false;
		}
					
		// If we need a tabbed nav
		$('#codaNav-' + sliderCount + ' a').each(function(z) {
			// What happens when a nav link is clicked
			$(this).bind("click", function() {
				navClicks++;
				$(this).addClass('current').parents('ul').find('a').not($(this)).removeClass('current');
				offset = - (panelWidth*z);
				alterPanelHeight(z);
				currentPanel = z + 1;
				$('.panelContainer', slider).animate({ marginLeft: offset }, settings.slideEaseDuration, settings.slideEaseFunction);
				if (!settings.crossLinking) { return false }; // Don't change the URL hash unless cross-linking is specified
			});
		});
		
		// External triggers (anywhere on the page)
		$(settings.externalTriggerSelector).each(function() {
			// Make sure this only affects the targeted slider
			if (sliderCount == parseInt($(this).attr("rel").slice(12))) {
				$(this).bind("click", function() {
					navClicks++;
					targetPanel = parseInt($(this).attr("href").slice(1));
					offset = - (panelWidth*(targetPanel - 1));
					alterPanelHeight(targetPanel - 1);
					currentPanel = targetPanel;
					// Switch the current tab:
					slider.siblings('.codaNav').find('a').removeClass('current').parents('ul').find('li:eq(' + (targetPanel - 1) + ') a').addClass('current');
					// Slide
					$('.panelContainer', slider).animate({ marginLeft: offset }, settings.slideEaseDuration, settings.slideEaseFunction);
					if (!settings.crossLinking) { return false }; // Don't change the URL hash unless cross-linking is specified
				});
			};
		});
			
		// Specify which tab is initially set to "current". Depends on if the loaded URL had a hash or not (cross-linking).
		if (settings.crossLinking && location.hash && parseInt(location.hash.slice(1)) <= panelCount) {
			$("#codaNav-" + sliderCount + " a:eq(" + (location.hash.slice(1) - 1) + ")").addClass("current");
		// If there's no cross-linking, check to see if we're supposed to load a panel other than Panel 1 initially...
		} else if (settings.firstPanelToLoad != 1 && settings.firstPanelToLoad <= panelCount) {
			$("#codaNav-" + sliderCount + " a:eq(" + (settings.firstPanelToLoad - 1) + ")").addClass("current");
		// Otherwise we must be loading Panel 1, so make the first tab the current one.
		} else {
			$("#codaNav-" + sliderCount + " a:eq(0)").addClass("current");
		};
		
		// Set the height of the first panel
		if (settings.autoHeight) {
			panelHeight = $('.panel:eq(' + (currentPanel - 1) + ')', slider).height();
			slider.css({ height: panelHeight });
		};
		
		// Trigger autoSlide
		if (settings.autoSlide) {
			slider.ready(function() {
				setTimeout(autoSlide,settings.autoSlideInterval);
			});
		};
		
		function alterPanelHeight(x) {
			if (settings.autoHeight) {
				panelHeight = $('.panel:eq(' + x + ')', slider).height()
				slider.animate({ height: panelHeight }, settings.autoHeightEaseDuration, settings.autoHeightEaseFunction);
			};
		};
		
		function autoSlide() {
			if (slider.find(".panel").length > 1) {
				if (navClicks == 0 || !settings.autoSlideStopWhenClicked) {
					$('.arrow').unbind("click");
					if (currentPanel == panelCount) {
						var offset = 0;
						currentPanel = 1;
						$(settings.slideText).not(".current "+settings.slideText).css({ left: -$(settings.slideText).width() }).animate({ left: 0 }, settings.slideTextEaseDuration, settings.slideEaseFunction);
						$(".current "+settings.slideText).css({ left: 0 }).animate({ left: $(settings.slideText).width() }, settings.slideTextEaseDuration, settings.slideEaseFunction, function(){
							$('.arrow.left').bind("click", prev);
							$('.arrow.right').bind("click", next);
						});
						slider.find('.current').removeClass('current').parent().find('div:eq(0)').addClass('current');
					} else {
						var offset = - (panelWidth*currentPanel);
						currentPanel += 1;
						$(settings.slideText).not(".current "+settings.slideText).css({ left: $(settings.slideText).width() }).animate({ left: 0 }, settings.slideTextEaseDuration, settings.slideEaseFunction);
						$(".current "+settings.slideText).css({ left: 0 }).animate({ left: -$(settings.slideText).width() }, settings.slideTextEaseDuration, settings.slideEaseFunction, function(){
							$('.arrow.left').bind("click", prev);
							$('.arrow.right').bind("click", next);
						});
						slider.find('.current').removeClass('current').next().addClass('current');
					};
					alterPanelHeight(currentPanel - 1);
					// Slide:
					$('.panelContainer', slider).animate({ marginLeft: offset }, settings.slideEaseDuration, settings.slideEaseFunction, function() {
						setTimeout(autoSlide,settings.autoSlideInterval);																													  
					});
					
				};
			};
		};
		// Kill the preloader
		$('.panel', slider).show().end().find("p.loading").remove();
		slider.removeClass("preload");
		
		sliderCount++;
		
	});
};