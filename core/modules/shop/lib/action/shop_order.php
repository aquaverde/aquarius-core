<?
class action_shop_order extends ModuleAction {
    var $modname = "shop";
    var $props = array('class', 'op');

    function valid() {
        return true;
    }

    /** Remove time portion of timestamp */
    function _start_of_day($timestamp) {
        $time = getdate($timestamp);
        return mktime(0, 0, 0, $time['mon'], $time['mday'], $time['year']);
    }

    /** Try to parse a string as date
      * Expected format is day.month.year, anything that's not numeric is considered as seperator
      * Missing values are substituted with current values
      */
    function _parsedate($string) {
        $now = getdate();
        @list($day, $month, $year) = split('[^0-9]+', $string);
        if (empty($year)) $year = $now['year'];
        if (empty($month)) $month = $now['mon'];
        if (empty($day)) $day = $now['mday'];
        return mktime(0,0,0, $month, $day, $year);
    }

    function _load_order_from_param() {
        $id = $this->params[0];
        $order = new Shop_order();
        $found = $order->get($id);
        if (!$found) throw new Exception("Invalid order ID '$id'");
        return $order;
    }

    function execute() {
        global $aquarius;
        $smarty = false;
        $messages = array();
        $action = false;

        switch($this->op) {
        case "selected_orders":
            $order_ids = requestvar('selected_order', array());
            $command = requestvar('selected_orders_command');
            $orders = array();
            foreach($order_ids as $order_id) {
                $order = new Shop_order();
                flush();
                $found = $order->get($order_id);
                if ($found) {
                    $orders[] = $order;
                } else {
                    throw new Exception("Order id '$order_id' not in DB");
                }
            }

            $template = false;
            
            switch ($command) {
            case "set_paid":
                foreach ($orders as $order) {
                    $order->paid = true;
                    $order->update();
                }
                break;
            case "delete":
                foreach ($orders as $order) {
                    $order->delete();
                }
                break;
            default:
                throw new Exception("Command unknown: '".$command."'");
                break;
            }
            break;
            
        case "changepaydate":
            $order = $this->_load_order_from_param();
            $order->pay_date = time();
            $order->update();
            break;
            
        case "changepaid":
            $order = $this->_load_order_from_param();
            $order->paid = !$order->paid;
            $order->update();
            break;
            
        case "confirm":
            $order = $this->_load_order_from_param();
            $shop_frontend = $aquarius->modules['shop']->frontend_interface();
            Log::warn("Manually confirming order $order->id");
            $shop_frontend->order($order);
            break;
            
        case "show_orders":
                // Read filter settings from action
                $today = $this->_start_of_day(time());
                $filters = array();
                $filters['from'] = get($this->params, 0, $today - SHOP_ORDER_LIST_DEFAULT_TIMESPAN);
                $filters['to'] = get($this->params, 1, $today);
                $filters['status'] = get($this->params, 2, 'pending');
                $filters['paid'] = get($this->params, 3, 'all');
                
                // Update filter settings with data in request
                if (isset($_REQUEST['filter_from'])) {
                    $from = requestvar('filter_from');
                    if (!empty($from)) {
                        $filters['from'] = $this->_parsedate($from);
                    } else {
                        $filters['from'] = false;
                    }
                    $filters['to'] = $this->_parsedate(requestvar('filter_to'));
                
                    $filters['status'] = requestvar('filter_status', 'all');
                    $filters['paid'] = requestvar('filter_paid', 'all');
                }
                
                $order = new Shop_order();
                
                if ($filters['from']) $order->whereAdd("order_date >= ".$filters['from']);
                if ($filters['to']) $order->whereAdd("order_date < ".($filters['to']+60*60*24));
                if ($filters['status'] != 'all') $order->status = $filters['status'];
                if ($filters['paid'] != 'all') $order->paid = $filters['paid'];

                $order->orderBy('order_date DESC');
                $order->find();
                
                $smarty = $aquarius->get_smarty_backend_container();
                $smarty->assign('order',$order);
                $smarty->assign('filters', $filters);

                $smarty->assign('last_filter_action', Action::make('shop_order', 'show_orders', $filters['from'], $filters['to'], $filters['status'], $filters['paid']));

                $smarty->tmplname = "action.shop_show_orders.tpl";
                break;

        case "print":
            $order = $this->_load_order_from_param();
            $smarty = $aquarius->get_smarty_frontend_container();
            $smarty->assign('order', $order);
            $smarty->assign('cart_total', $order->cart_total());
            $smarty->assign('charset', 'utf-8');
            $smarty->assign('print', true);
            $smarty->tmplname = 'shop.mail.shop.tpl';
            break;
            
        default:
            throw new Exception("Operation unknown: '$this->op'");
        }
        return compact('messages', 'smarty','action');
    }
}

?>

