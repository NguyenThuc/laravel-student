<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ActivityLogService {

    /**
     * Log info
     *
     * @param $action
     * @param $causedBy
     * @param $description
     * @param array $oldData
     * @param array $newData
     *
     * @return void
     */
    public static function info($action, $causedBy, $description = '', $oldData = [], $newData = [])
    {
        $properties = ActivityLogService::getProperties($oldData, $newData);

        Log::channel('app')->info($action, [
            'caused_by' => $causedBy,
            'description' => $description,
            'properties' => $properties
        ]);
    }//end info()


    /**
     * Log warning
     *
     * @param $action
     * @param $causedBy
     * @param $description
     * @param array $oldData
     * @param array $newData
     *
     * @return void
     */
    public static function warning($action, $causedBy, $description = '', $oldData = [], $newData = [])
    {
        $properties = ActivityLogService::getProperties($oldData, $newData);
        
        Log::channel('app')->warning($action, [
            'caused_by' => $causedBy,
            'description' => $description,
            'properties' => $properties
        ]);
    }//end warning()


    /**
     * Log error
     *
     * @param $action
     * @param $causedBy
     * @param $description
     * @param array $oldData
     * @param array $newData
     *
     * @return void
     */
    public static function error($action, $causedBy, $description, $oldData = [], $newData = [])
    {
        $properties = ActivityLogService::getProperties($oldData, $newData);
        
        Log::channel('app')->warning($action, [
            'caused_by' => $causedBy,
            'description' => $description,
            'properties' => $properties
        ]);
    }//end error()


    /**
     * Get properties data
     *
     * @param array $oldData
     * @param array $newData
     *
     * @return array
     */
    private static function getProperties($oldData, $newData)
    {
        $old = [];

        foreach ($newData as $key => $value) {
            $old[$key] = $oldData[$key];
        }

        $properties = [
            'old' => $old,
            'new' => $newData
        ];

        if (count($properties['old']) === 0 && count($properties['new']) === 0) {
            $properties = null;
        }

        return $properties;

    }//end getProperties()

}

?>