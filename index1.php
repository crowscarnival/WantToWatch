<?php



/**
 * Need to operate with array
 */
class Object implements ArrayAccess
{
    protected $_data = array();

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
        return $this;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
        return $this;
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
        return $this;
    }

    public function offsetGet($offset)
    {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }
}

class Render
{
    protected $_ticket;

    public function __construct($ticket = null)
    {
        $this->_ticket = $ticket;
    }

    /**
     * Render string
     * @param Ticket $item
     * @return $this
     */
    public function render($str = null)
    {
        if ($str) {
            echo $str;
        }
    }
}

class AirportbusTicketRender extends Render
{
    protected $_ticket;
    /**
     * Render for bus ticket
     * @param Ticket $item
     * @return $this
     */
    public function render($str = null)
    {
        $ticket = $this->_ticket;
        $seats = ($ticket->seat) ?  "Sit in seat" .  $ticket->seat : "No seat assignment.";
        $transportNumber = $ticket->transportNumber ? ' ' . $ticket->transportNumber . ' ' : ' ';
        echo "Take the airport bus" . $transportNumber . "from {$ticket->from} to {$ticket->to}. $seats \n";
    }
}

class TrainTicketRender extends Render
{
    protected $_ticket;
    /**
     * Render for bus ticket
     * @param Ticket $item
     * @return $this
     */
    public function render($str)
    {
        $ticket = $this->_ticket;
        $seats = $ticket->seat ?  "Sit in seat" .  $ticket->seat : "No seat assignment.";
        echo "Take train {$ticket->transportNumber} from {$ticket->from} to {$ticket->to}. Sit in seat {$ticket->seat}.\n";
    }
}

class AirplaneTicketRender extends Render
{
    protected $_ticket;
    /**
     * Render for bus ticket
     * @param Ticket $item
     * @return $this
     */
    public function render($str)
    {
        $ticket = $this->_ticket;
        $baggage = ($ticket->baggage == 'auto') ? "Baggage will we automatically transferred from your last leg." : "Baggage drop at ticket counter." . $ticket->baggage;
        echo "From {$ticket->from}, take flight {$ticket->transportNumber} to {$ticket->to}. Gate {$ticket->gate}, seat {$ticket->seat}. {$baggage}\n";
    }
}


class Ticket extends Object
{
    protected $_from;
    protected $_to;
    protected $_type;

    protected $_render;

    public function __construct($optionsArray)
    {
        parent::__construct($optionsArray);

        $this->_from = $this->_data['from'];
        $this->_to = $this->_data['to'];

        $this->_type = $this->_data['type'];
    }

    public function getRender($type)
    {
        if (!$this->_render) {
            switch ($type) {
                case "train":
                    $this->_render = new TrainTicketRender($this);
                    break;
                case "airport_bus":
                    $this->_render = new AirportbusTicketRender($this);
                    break;
                case "airplane":
                    $this->_render = new AirplaneTicketRender($this);
                    break;
                default:
                    $this->_render = new Render();
                    break;
            }
            return $this->_render;
        }
    }
    public function render()
    {
        $render = $this->getRender($this->_type);
        $render->render();
    }

}

class TicketCollection implements IteratorAggregate, Countable
{
    /**
     * tickets
     */
    protected $_items;

    protected $_render;

    public function __construct()
    {
        $this->_render = new Render();
    }


    /**
     * Add ticket to collection
     * @param Ticket $item
     * @return $this
     */
    public function addItem(Ticket $item)
    {
        $this->_items[] = $item;
        return $this;
    }

    /**
     * Add tickets to collection
     * @param $itemsArray
     * @return $this
     */
    public function addItems($itemsArray)
    {
        foreach ($itemsArray as $item) {
            if ($item instanceof Ticket) {
                $this->addItem($item);
            } else if (is_array($item)) {
                $ticket = new Ticket($item);
                $this->addItem($ticket);
            }
        }
        return $this;
    }

    /**
     * Render text by items options
     * @return $this
     */
    public function render()
    {
        if (!count($this->_items)) {
            return $this;
        }

        $this->_render->render("<pre>");

        foreach ($this->_items as $item) {
            $item->render();
        }
        $this->_render->render("You have arrived at your final destination.\n");
        return $this;
    }

    /**
     * Render text by items options
     * @return $this
     */
    public function fetchPath($from, $to)
    {
        $items = $this->_items;
        return new TicketCollection($items);
    }

    /**
     * IteratorAggregate::getIterator() implementation
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_items);
    }

    /**
     * Retrieve count of item collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->_items);
    }

}

/* Forming data*/
$items = array(
    array('from' => 'Kiev', 'to' => 'Lviv', 'type' => 'train', 'transportNumber' => 'X12', 'seat' => '18C'),
    array('from' => 'Lviv', 'to' => 'Lviv Danylo Halytskyi International Airport', 'type' => 'airport_bus'),
    array('from' => 'Lviv Danylo Halytskyi International Airport', 'to' => 'Stockholm', 'type' => 'airplane', 'transportNumber' => 'BF134', 'seat' => '3A', 'gate' => '45B', 'baggage' => '344'),
    array('from' => 'Stockholm', 'to' => 'Amsterdam Schiphol', 'type' => 'airplane', 'transportNumber' => 'SK22', 'seat' => '7B', 'gate' => '18', 'baggage' => 'auto'),
    array('from' => 'Stockholm', 'to' => 'Rotterdam', 'type' => 'train', 'transportNumber' => 'T13', 'seat' => '12B'),
);

/* Add data to collection*/
$collection = new TicketCollection();
$collection->addItems($items);

// Sort collection from Kiev to Rotterdam
$collection->fetchPath('Kiev', 'Rotterdam');

// Output items
$collection->render();

/*
 * P. S.
 * All classes should be parted into separated files and included by autoloader
 * or added namespaces.
 * */