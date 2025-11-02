<?php

namespace App\Models\DTO;

use App\Enums\HandoutStatus;

class HandoutDTO
{
    public string $title;
    public string $description;
    public string $start_date;
    public string $end_date;
    public string $priority;
    public HandoutStatus $status;
    public string $link_name;
    public string $link_url;
    public string $image_url;

    public function __construct(string $title, string $description, string $start_date, string $end_date, string $priority, HandoutStatus $status, string $link_name, string $link_url, string $image_url)
    {
        $this->title = $title;
        $this->description = $description;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->priority = $priority;
        $this->status = $status;
        $this->link_name = $link_name;
        $this->link_url = $link_url;
        $this->image_url = $image_url;
    }
}
