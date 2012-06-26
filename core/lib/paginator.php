<?
/** Manage splitting of list of items into pages for display
  *
  */
class Paginator {
    /** Initialize a paginator with required attributes
      * @param $items array of items to paginate
      * @param $items_per_page how many items to include in each page
      * @param $current_page number of the page currently displayed (starting from zero). This may also be the string 'all', to specify that you want one huge page with all items.
      * @param $action action that can be modified to lead to pages
      * @param $pageattr name of the 'page' attribute in $action
      * */
    function __construct(array $items, $items_per_page, $current_page, $action, $pageattr='page') {
        $this->items = $items;
        $this->items_per_page = intval($items_per_page);
        if ($this->items_per_page < 1) throw new Exception("Invalid items_per_page_count: $this->items_per_page");
        $this->action = $action;
        $this->pageattr = $pageattr;
        $this->page_count = intval(ceil(count($items) / $this->items_per_page));
        $this->current_page = $current_page == 'all' ? 'all' : intval(min($this->page_count - 1, max(0, intval($current_page))));
    }

    /** Get the items for the current page
      * @return current chunk from items list */
    function current_items() {
        if ('all' === $this->current_page) return $this->items;
        return array_slice(
            $this->items,
            $this->current_page * $this->items_per_page,
            $this->items_per_page
        );
    }

    function all_pages() {
        return array_chunk($this->items, $this->items_per_page);
    }

    /** Change current page to the page that includes given item
      * If the item does not exist the current page will not be changed
      * @param $item 
      * @return whether item was found */
    function select_page_by_item($item) {
        $index = 0;
        foreach($this->items as $list_item) {
            if ($list_item == $item) {
                $this->current_page = intval(floor($index / $this->items_per_page));
                return true;
            }
            $index++;
        }
        return false;
    }

    /** Previous page: Get an action to the page before the current one
      * @return action or false if there is no previous page */
    function prev_action() {
        if (!is_numeric($this->current_page)) return false;
        return $this->page_action($this->current_page - 1);
    }

    /** Next page: Get an action to the page after the current one
      * @return action */
    function next_action() {
        if (!is_numeric($this->current_page)) return false;
        return  $this->page_action($this->current_page + 1);
    }

    /** All page: Get an action to a page displaying all items
      * @return action or false if there are no items */
    function all_action() {
        return $this->page_action('all');
    }

    /** List of all actions leading to pages
      * @return array with as many actions as there are pages */
    function page_actions() {
        return array_map(array($this, 'page_action'), range(0, $this->page_count - 1));
    }

    /** Get action leading to specified page index
      * @return action or false if page index invalid */
    function page_action($page) {
        $in_range = $page == 'all' || $page >= 0 && $this->page_count > $page;
        if (!$in_range) return false;

        $page_action = clone $this->action;
        $page_action->{$this->pageattr} = $page;
        return $page_action;
    }

    /** Get actions to pages, including 'next' and 'previous'.
      * @return dictionary with entries 'next', 'previous' and 'pages' */
    function all_actions() {
        return array(
            'prev'=>$this->prev_action(),
            'next'=>$this->next_action(),
            'pages'=>$this->page_actions(),
            'all'=>$this->all_action(),
        );
    }
}
?>