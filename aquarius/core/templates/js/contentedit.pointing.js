function nodes_selected(target_id, selected_nodes) {
    var ids = []
    var titles = []
    for (node_id in selected_nodes) {
        ids.push(node_id)
        titles.push(selected_nodes[node_id])
    }
    $(target_id+'_selected').value = ids.join(',')

    $(target_id+'_titles').update(titles.join(' | '))
    var titlebox = $(target_id+'_titlebox')
    titlebox.style.display = ids.length == 0 ? 'none' : 'block'

    // Jig a class so that IE renders our changes
    titlebox.toggleClassName('idefix')
}
