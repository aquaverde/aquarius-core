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
        var tab = $('tab'+tabnr)
        $$('.tabs li.active').invoke('removeClassName', 'active')
        tab.addClassName('active')
        hide.forEach(function (hide_name) {
            var field = $('box'+hide_name)
            if (field) field.style.display = 'None'
        })
        show.forEach(function (show_name) {
            var field = $('box'+show_name)
            if (field) field.style.display = 'Block'
        })
        on_tab_init.forEach(function (initializer) { initializer() } )
        tab.toggleClassName('idefix')

        $('active_tab_id').value = tabnr
    }
    if (dom_loaded) tab_change()
    else pending_tab_change = tab_change
}
