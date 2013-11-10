<?php


/**
 * Collect, sort and render ticket information
 */
class TicketCollection implements IteratorAggregate, Countable
{
    /**
     * tickets
     */
    protected $_items;

    protected $_render;

    public function __construct($items = null)
    {
        if ($items) {
            $this->_items = $items;
        }
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
            $this->_render->render("No route.\n");
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
     * Sorting items, get items from $from and to $from
     * @param $from
     * @param $to
     * @return TicketCollection
     */
    public function fetchPath($from, $to)
    {
        $items = array();
        $oldItems =  $this->_items;

        $counter = 0;
        $newFrom = $from;

        while (count($oldItems)) {
            $shiftArray = true;
            foreach ($oldItems as $key => $item) {
                if ($item->from == $newFrom) {
                    $shiftArray = false;
                    $items[$counter] = $item;
                    unset($oldItems[$key]);
                    $newFrom = $item->to;

                    // return $items if we found path
                    if ($item->to == $to) {
                        return new TicketCollection($items);
                    }
                    break;
                }

            }
            // if we cant find current path
            if ($shiftArray) {
                return new TicketCollection();
            }
            $counter++;
        }

        return new TicketCollection();
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