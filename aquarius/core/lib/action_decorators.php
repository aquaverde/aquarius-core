<?

/** Wrap classes transparently into another object
  * Useful for decorators. */
class ActionDecorator extends Wrapper implements BasicAction {
    var $__baseobject;

    function __construct($baseaction) {
        if ( ! $baseaction instanceof BasicAction) throw new Exception("Base object is not an action");
        parent::__construct($baseaction);
    }
}

/** Wrapper class for Action to change icon */
class ActionIconChange extends ActionDecorator {
    function __construct($baseaction , $icon) {
        parent::__construct($baseaction);
        $this->icon = $icon;
    }

    function get_icon() {
        return $this->icon;
    }
}

/** Wrapper class for Action to change title text */
class ActionTitleChange extends ActionDecorator {
    function __construct($baseaction , $title) {
        parent::__construct($baseaction);
        $this->title = $title;
    }

    function get_title() {
        return $this->title;
    }
}
?>