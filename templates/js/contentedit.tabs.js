/* List with functions to execute on every tab change. Right now this is only used by the google maps field. */
var on_tab_init = []

/** hold tab_changes until DOM is loaded, lest we try to show nonexisiting objects */
var dom_loaded = false
var pending_tab_change = false

function load_pending_change() {
    if (!dom_loaded) {
        dom_loaded = true
    }
    if (pending_tab_change) {
        pending_tab_change()
        pending_tab_change = false
    }
}

jQuery(document).ready(load_pending_change)

function show_tab(tabnr, hide, show) {
    var tab_change = function() {
        var tab = jQuery('#tab'+tabnr)
        jQuery('.tabs li.active').removeClass('active')
        tab.addClass('active')
        hide.forEach(function (hide_name) {
            jQuery('#box'+hide_name).hide()
        })
        show.forEach(function (show_name) {
            jQuery('#box'+show_name).show()
        })
        on_tab_init.forEach(function (initializer) { initializer() } )

        jQuery('#active_tab_id').value = tabnr
    }
    if (dom_loaded) tab_change()
    else pending_tab_change = tab_change
}
