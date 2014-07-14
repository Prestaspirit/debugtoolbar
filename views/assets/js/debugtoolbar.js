var debugtoolbar = {
	// Sandbox a jQuery instance for the profiler.
	//jq: jQuery.noConflict(true)
	jq: jQuery
};

debugtoolbar.jq.extend(debugtoolbar, {

	// BOUND ELEMENTS
	// -------------------------------------------------------------
	// Binding these elements early, stops jQuery from "querying"
	// the DOM every time they are used.

	el: {
		replace: debugtoolbar.jq('#debugtoolbar_replace'),
		main: debugtoolbar.jq('.debugtoolbar'),
		close: debugtoolbar.jq('#debugtoolbar-close'),
		zoom: debugtoolbar.jq('#debugtoolbar-zoom'),
		hide: debugtoolbar.jq('#debugtoolbar-hide'),
		show: debugtoolbar.jq('#debugtoolbar-show'),
		tab_pane: debugtoolbar.jq('.debugtoolbar-tab-pane'),
		hidden_tab_pane: debugtoolbar.jq('.debugtoolbar-tab-pane:visible'),
		tab: debugtoolbar.jq('.debugtoolbar-tab'),
		tabs: debugtoolbar.jq('.debugtoolbar-tabs'),
		tab_links: debugtoolbar.jq('.debugtoolbar-tabs a'),
		window: debugtoolbar.jq('.debugtoolbar-window'),
		closed_tabs: debugtoolbar.jq('#debugtoolbar-closed-tabs'),
		open_tabs: debugtoolbar.jq('#debugtoolbar-open-tabs'),
		content_area: debugtoolbar.jq('.debugtoolbar-content-area')
	},

	// CLASS ATTRIBUTES
	// -------------------------------------------------------------
	// Useful variable for debugtoolbar.

	// is debugtoolbar in full screen mode
	is_zoomed: false,

	// initial height of content area
	small_height: debugtoolbar.jq('.debugtoolbar-content-area').height(),

	// the name of the active tab css
	active_tab: 'debugtoolbar-active-tab',

	// the data attribute of the tab link
	tab_data: 'data-debugtoolbar-tab',

	// size of debugtoolbar when compact
	mini_button_width: '2.6em',

	// is the top window open?
	window_open: false,

	// current active pane
	active_pane: '',

	// START()
	// -------------------------------------------------------------
	// Sets up all the binds for debugtoolbar!

	start: function() {

		// hide initial elements
		debugtoolbar.el.close.css('visibility', 'visible').hide();
		debugtoolbar.el.zoom.css('visibility', 'visible').hide();
		debugtoolbar.el.tab_pane.css('visibility', 'visible').hide();

		// bind all click events
		debugtoolbar.el.close.click(function(event) {
			debugtoolbar.close_window();
			event.preventDefault();
		});
		debugtoolbar.el.hide.click(function(event) {
			debugtoolbar.hide();
			event.preventDefault();
		});
		debugtoolbar.el.show.click(function(event) {
			debugtoolbar.show();
			event.preventDefault();
		});
		debugtoolbar.el.zoom.click(function(event) {
			debugtoolbar.zoom();
			event.preventDefault();
		});
		debugtoolbar.el.tab.click(function(event) {
			debugtoolbar.clicked_tab(debugtoolbar.jq(this));
			event.preventDefault();
		});

	},

	// CLICKED_TAB()
	// -------------------------------------------------------------
	// A tab has been clicked, decide what to do.

	clicked_tab: function(tab) {

		// if the tab is closed
		if (debugtoolbar.window_open && debugtoolbar.active_pane == tab.attr(debugtoolbar.tab_data)) {
			debugtoolbar.close_window();
		} else {
			debugtoolbar.open_window(tab);
		}

	},

	// OPEN_WINDOW()
	// -------------------------------------------------------------
	// Animate open the top window to the appropriate tab.

	open_window: function(tab) {

		// can't directly assign this line, but it works
		debugtoolbar.jq('.debugtoolbar-tab-pane:visible').fadeOut(200);
		debugtoolbar.jq('.' + tab.attr(debugtoolbar.tab_data)).delay(220).fadeIn(300);
		debugtoolbar.el.tab_links.removeClass(debugtoolbar.active_tab);
		tab.addClass(debugtoolbar.active_tab);
		debugtoolbar.el.window.slideDown(300);
		debugtoolbar.el.close.fadeIn(300);
		debugtoolbar.el.zoom.fadeIn(300);
		debugtoolbar.active_pane = tab.attr(debugtoolbar.tab_data);
		debugtoolbar.window_open = true;

	},

	// CLOSE_WINDOW()
	// -------------------------------------------------------------
	// Animate closed the top window hiding all tabs.

	close_window: function() {

		debugtoolbar.el.tab_pane.fadeOut(100);
		debugtoolbar.el.window.slideUp(300);
		debugtoolbar.el.close.fadeOut(300);
		debugtoolbar.el.zoom.fadeOut(300);
		debugtoolbar.el.tab_links.removeClass(debugtoolbar.active_tab);
		debugtoolbar.active_pane = '';
		debugtoolbar.window_open = false;

	},

	// SHOW()
	// -------------------------------------------------------------
	// Show the debugtoolbar toolbar when it has been compacted.

	show: function() {

		debugtoolbar.el.closed_tabs.fadeOut(600, function () {
			debugtoolbar.el.main.removeClass('debugtoolbar-hidden');
			debugtoolbar.el.open_tabs.fadeIn(200);
		});
		debugtoolbar.el.main.animate({width: '100%'}, 700);

	},

	// HIDE()
	// -------------------------------------------------------------
	// Hide the debugtoolbar toolbar, show a tiny re-open button.

	hide: function() {

		debugtoolbar.close_window();

		setTimeout(function() {
			debugtoolbar.el.window.slideUp(400, function () {
				debugtoolbar.close_window();
				debugtoolbar.el.main.addClass('debugtoolbar-hidden');
				debugtoolbar.el.open_tabs.fadeOut(200, function () {
					debugtoolbar.el.closed_tabs.fadeIn(200);
				});
				debugtoolbar.el.main.animate({width: debugtoolbar.mini_button_width}, 700);
			});
		}, 100);

	},

	// TOGGLEZOOM()
	// -------------------------------------------------------------
	// Toggle the zoomed mode of the top window.

	zoom: function() {
		var height;
		if (debugtoolbar.is_zoomed) {
			height = debugtoolbar.small_height;
			debugtoolbar.is_zoomed = false;
		} else {
			// the 6px is padding on the top of the window
			height = (debugtoolbar.jq(window).height() - debugtoolbar.el.tabs.height() - 6) + 'px';
			debugtoolbar.is_zoomed = true;
		}

		debugtoolbar.el.content_area.animate({height: height}, 700);

	}

});

// launch debugtoolbar on jquery dom ready
debugtoolbar.jq(debugtoolbar.start);
