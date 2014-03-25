/* Manage a nodetree page.
 * Construction parameter 'request_url' is used to request an update for a section of the tree.
 */
function NodeTree(root, request_url, move_url) {
    var self = this
    
    // Register click handlers on the open/close toggle
    this.register_handlers = function(root) {
        jQuery(root).find('.nodetree_container').addBack('.nodetree_container').each(function() { // addBack() because root could be a container itself
            var container = this
            jQuery(container).children('.nodetree_row').children('.nodetree_toggle').first().click(function() {
                var do_open = jQuery(this).hasClass('open') ? 0 : 1
                self.update(container, do_open )
            })
        })
    }

    this.register_handlers(root)

    /* Load subtree via HTTP
     * container: the node container with a nodetree_row and maybe nodetree_children inside
     * open: whether to include children
     * The contents of container are replaced by the async update
     */
    this.update = function(container, open) {
        var $container = jQuery(container)

        params = {
            node: $container.data('node')
        }
        
        if (open !== undefined) {
            params.open = open ? 1: 0
        }
        
        jQuery.get(request_url, params, function(replacement){
            $container.html(replacement)
            self.register_handlers(container)
        })
    }
    
    this.refresh = function(node_id, open) {
        var $root = jQuery(root)
        var cont_class = '.container_'+node_id
        var container = undefined
        if ($root.is(cont_class)) {
            container = $root.get(0)
            open = true // root is always open (You assumed? You know what happens when you assume!)
        } else {
            container = jQuery(root).find(cont_class).get(0)
        }
        if (container) this.update(container, open)
    }

    this.moveorder = function(node, parent, prev) {
        /* DON'T JUDGE ME <DEITY> WILL */
        /* There has to be a better way than to build and submit a form?! */
        var form = jQuery('<form/>', {
            action: move_url,
            method: 'POST'
        })
        jQuery.each({node: node, node_target: parent, prev: prev}, function(key) {
            form.append(jQuery('<input/>', {
                type: 'hidden',
                name: key,
                value: this
            }));
        });
        form.appendTo('body').submit();
    }
}
