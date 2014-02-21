/* Manage a nodetree page.
 * Currently, only one tree per page is supported because the containers are loaded directly by id.
 * Construction parameter 'request_url' is used to request an update for a section of the tree.
 * On construction, and after each update, the class 'odd' is added to every second element of class 'nodetree_row' (and removed from the others)
 */
function NodeTree(request_url, move_url) {
    /* Load subtree via HTTP
     * node_id: the node to be updated
     * params: Request parameters to send. If 'node' parameter is undefined, the node_id is assigned to it. If 'open' is undefined, the current state is assigned.
     * The returned content replaces everything in the element having id 'nodetree_entry_<node_id>'
     */
    this.update = function(node_id, params) {
        var container = $('nodetree_entry_'+node_id)
        if (!container) return; // ignore request to update node that is not shown

        if (params.node === undefined) {
            params.node = node_id
        }
            
        // If open is not specified, use current state
        if (params.open === undefined) {
            // The inner div wrapper has class open if the branch is indeed open
            params.open = container.firstDescendant().hasClassName('open') ? 1 : 0
        }
        
        // Request update of branch attached at node_id
        new Ajax.WaitingUpdater(
            container,
            container,
            request_url,
            {   method: 'get'
            ,   parameters: params
            ,   onComplete: function(transport) {
                    this.fix_row_oddities()
                }.bind(this)
            }
        )
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
    
    
    // Add class 'odd' to every second row of the nodetree
    // This not only allows us to alternate row colors, it also forces IE6 to rebuild the layout (Fixing browser issues by adding functionality!).
    this.fix_row_oddities = function() {
        var odd = false
        $$('.nodetree_row').each(function(row) {
            if (odd) row.addClassName('odd')
            else row.removeClassName('odd')
            odd = !odd
        })

    }


    this.fix_row_oddities();
}

/* An AJAX updater subclass that shows a 'wait' cursor as long as the request is going on
 * Same interface as Ajax.Updater with additional constructor parameter wait_area where the cursor will be changed.
 * Example: new Ajax.WaitingUpdater(field_to_update, document.body, 'http://whereIGetMyUpdates', params)
 */
Ajax.WaitingUpdater = Class.create(Ajax.Updater, {
  initialize: function($super, container, wait_area, url, options) {
    options = Object.clone(options);
    var original_onCreate = options.onCreate;
    options.onCreate = (function(response, json) {
      wait_area.style.cursor = 'wait'
      if (Object.isFunction(original_onCreate)) original_onCreate(response, json);
    }).bind(this);
    var original_onComplete = options.onComplete;
    options.onComplete = (function(response, json) {
      wait_area.style.cursor = 'default'
      if (Object.isFunction(original_onComplete)) original_onComplete(response, json);
    }).bind(this);

    $super(container, url, options);
  }
});