function pointing_selection_setup(button, on_select) {
    var $button = jQuery(button);

    // The URL to open the node selection
    var url = $button.data('url');

    var target_id = $button.data('target');

    // The 'selected' field is a hidden form-field that contains the comma
    // separated list of the currently selected node ID
    var selected_id = $button.data('selected_field') || target_id+'_selected';
    var selected_id = '#'+selected_id;
    var $selected_field = jQuery(selected_id);
    if ($selected_field.length == 0) console.log("Pointing selection missing 'selected' field "+selected_id);
    var selected = $selected_field.val();

    // The 'titles' field is a DOM node whose text content shows the titles
    // if the currently selected nodes
    var titles_id = '#'+target_id+'_titles';
    var $titles_field = jQuery(titles_id);
    if ($titles_field.length == 0) console.log("Pointing selection missing 'title' field "+titles_id);
    var titlebox_id = '#'+target_id+'_titlebox';
    var $titlebox = jQuery(titlebox_id);

    var $modal = jQuery('<div></div>');
    $modal.load(url+'&selected='+selected);
    $modal.width(500);
    $modal.height(600);
   // jQuery('body').append($modal);
    //$modal.modal('show');

    window.select_nodes = function(selected) {
        var ids = []
        var titles = []
        for (node_id in selected) {
            ids.push(node_id)
            titles.push(selected[node_id])
        }

        $selected_field.val(ids.join(','));
        $titles_field.html(titles.join(' | '));

        // Show title container when there are nodes selected
        $titlebox.toggle(ids.length > 0);

        if (on_select) on_select(target_id, selected);
    }
    return false;
}

jQuery(function() {
    jQuery('.pointing_selection').click(function() { pointing_selection_setup(this); });
});
