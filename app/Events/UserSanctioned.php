<?php

namespace App\Events;

use App\Models\Sanction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserSanctioned
{
    use Dispatchable, SerializesModels;

    public $sanction;

    public function __construct(Sanction $sanction){
        $this->sanction = $sanction;
    }
}
