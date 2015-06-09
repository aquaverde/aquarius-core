function nodes_select(url, selectedstr, on_select) {
    jQuery('#nodeselect_modal').remove();
    var $this = jQuery(this)
    var $modal = jQuery('<div class="modal" id="nodeselect_modal" style="margin: 20%; min-width: 300px; min-height: 300px; padding: 2em; background-color: white"></div>');
    jQuery('body').append($modal);
    $modal.modal();

    $modal.load(url+'&selected='+selectedstr, function() {
        var $root = $modal.find('.nodetree_root');
        var multi = $root.data('multi') == 1;
        var selected = $root.data('selected');
        var nodetree = new NodeTree($root[0], $root.data('subtree_action'), null, function() { return { selected: ids.join(',') } });

        var ids = [];
        for (var node_id in selected) {
            ids.push(node_id);
        }

        $root.on('change', '.node_select', function() {
            var $this = jQuery(this);
            var id = $this.val();
            var title = $this.data('title');

            if ($this.is(':checked')) {
                if (id === '') {
                    // None selected
                    selected = {};
                } else {
                    if (!multi) {
                        $modal.modal('hide');
                        selected = {};
                    }
                    selected[id] = title;
                }
            } else {
                delete selected[id];
            }

            on_select(selected);
        });
    });
}

