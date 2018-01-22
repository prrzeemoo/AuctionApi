<?php

namespace AppBundle\Service;

class DateService
{
    public function getDay(\DateTime $date)
    {
        return $date->format("d");
    }
}