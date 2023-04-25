<?php

namespace App\Services;

use App\Models\Agency;

class AgencyService {

    public function findById($id)
    {
        return Agency::find($id);
    }

}
