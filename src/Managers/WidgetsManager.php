<?php

namespace Shemi\Laradmin\Managers;

use Shemi\Laradmin\Contracts\Managers\ManagerContract;
use Shemi\Laradmin\Exceptions\InvalidArgumentException;
use Shemi\Laradmin\Widgets\Widget;

class WidgetsManager implements ManagerContract
{
    protected $widgets = [];

    public function registerRow($widgets)
    {
        $row = count($this->widgets);

        if(! isset($this->widgets[$row]) || ! is_array($this->widgets[$row])) {
            $this->widgets[$row] = [];
        }

        foreach ($widgets as $widget) {
            $this->register($widget, $row);
        }

        return $this;
    }

    public function register($widgetClass, $row = 0)
    {
        if(is_string($widgetClass)) {
            $widgetClass = $widgetClass::start();
        }

        if(! $widgetClass instanceof Widget) {
            throw new InvalidArgumentException("All widgets most extent " . Widget::class);
        }

        if($widgetClass->getSize() > Widget::MAX_WIDGETS_WIDTH_SIZE_PER_ROW) {
            throw new InvalidArgumentException("The widget width cannot be greater than: " . Widget::MAX_WIDGETS_WIDTH_SIZE_PER_ROW);
        }

        if(! isset($this->widgets[$row]) || ! is_array($this->widgets[$row])) {
            $this->widgets[$row] = [];
        }

        $rowCount = $this->getRowTotal($row);

        if($rowCount + $widgetClass->getSize() > Widget::MAX_WIDGETS_WIDTH_SIZE_PER_ROW) {
            return $this->register($widgetClass, $row + 1);
        }

        $this->widgets[$row][$widgetClass->getCodename()] = $widgetClass;

        return $this;
    }

    protected function getRowTotal($row)
    {
        $count = 0;

        if(! isset($this->widgets[$row]) || empty($this->widgets[$row])) {
            return $count;
        }

        /** @var Widget $widget */
        foreach ($this->widgets[$row] as $widget) {
            $count += $widget->getSize();
        }

        return $count;
    }

    public function rows()
    {
        return $this->widgets;
    }

    public function getManagerName()
    {
        return 'widgets';
    }
}