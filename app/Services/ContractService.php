<?php

namespace App\Services;

use App\Models\Contract;

class ContractService
{
    
    public function create($attributes)
    {
        return Contract::create($attributes);
    }
    
    public function update($id, array $attributes)
    {
        $result = Contract::find($id);
        if ($result) {
            $result->update($attributes);
            return $result;
        }

        return false;
    }

}
