<?php
if (!class_exists("wpdreamsDraggable")) {
    /**
     * Class wpdreamsDraggable
     *
     * A draggable selector UI element with custom values.
     *
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsDraggable extends wpdreamsType {
        function getType() {
            parent::getType();
            $this->processData();
            echo "
      <div class='wpdreamsCustomFields'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
            if (isset($this->data['description']))
                echo "<p class='descMsg'>" . $this->data['description'] . "</p>";
            echo '<div class="sortablecontainer" id="sortablecontainer' . self::$_instancenumber . '">
            <ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            foreach ($this->selects as $k => $v) {
                if (!in_array($k, $this->selected))
                    echo '<li class="ui-state-default" key="'.$k.'">' . $v . '</li>';
            }
            echo "</ul></div>";
            echo '<div class="sortablecontainer"><ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->selected != null && is_array($this->selected)) {
                foreach ($this->selected as $k => $v) {
                    echo '<li class="ui-state-default" key="'.$v.'">' . $this->selects[$v] . '</li>';
                }
            }
            echo "</ul></div>";
            echo "
         <input isparam=1 type='hidden' value='" . $this->data['value'] . "' name='" . $this->name . "'>";
            echo "
         <input type='hidden' value='wpdreamsDraggable' name='classname-" . $this->name . "'>";
            ?>
            <script>
                (function ($) {
                    $(document).ready(function () {
                        var selector = "#sortable<?php echo self::$_instancenumber ?>, #sortable_conn<?php echo self::$_instancenumber ?>";
                        $("#sortable<?php echo self::$_instancenumber ?>, #sortable_conn<?php echo self::$_instancenumber ?>").sortable({
                            connectWith: ".connectedSortable"
                        }, {
                            update: function (event, ui) {
                            }
                        }).disableSelection();

                        $(selector).on('sortupdate', function(event, ui) {
                            if (typeof(ui)!='undefined')
                                parent = $(ui.item).parent();
                            else
                                parent = $(event.target);
                            while (!parent.hasClass('wpdreamsCustomFields')) {
                                parent = $(parent).parent();
                            }
                            var items = $('ul[id*=sortable_conn] li', parent);
                            var hidden = $('input[name=<?php echo $this->name; ?>]', parent);
                            var val = "";
                            items.each(function () {
                                val += "|" + $(this).attr('key');
                            });
                            val = val.substring(1);
                            hidden.val(val);
                        });
                    });
                }(jQuery));
            </script>
            <?php
            echo "
        </fieldset>
      </div>";
        }

        function processData() {
            if (is_array($this->data)) {
                if (isset($this->data['selects']) && is_array($this->data['selects']))
                    $this->selects = $this->data['selects'];
                else
                    $this->selects = array();

                if (isset($this->data['value']))
                    $this->selected = explode("|", $this->data['value']);
                else
                    $this->selected = array();
            } else {
                $this->selects = array();
                $this->selected = explode("|", $this->data);
            }
        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }
    }
}