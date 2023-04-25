<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function buildJson($statusCode, $type='', $content='', $msgErrors=[], $data=null)
    {
        $jsonResult = [];
        // set code
        $jsonResult['code'] = $statusCode;
        // set title
        if ($type) {
            $jsonResult['type'] = $type;
        }

        // set content
        if ($content) {
            $jsonResult['content'] = $content;
        }

        // set error list
        if ($msgErrors && count($msgErrors) >= 0) {
            $jsonResult['content'] = $msgErrors;
        }

        // set data
        if ($data) {
            $jsonResult['data'] = $data;
        }

        return $jsonResult;

    }//end buildJson()


    public function responseJson($code, $type='', $content='', $msgErrors=null, $data=null)
    {
        return response()->json(
            $this->buildJson($code, $type, $content, $msgErrors, $data)
        );

    }//end responseJson()


    public function responseSuccess($data=[])
    {
        return response()->json(
            [
                'status' => 'success',
                'data'   => $data,
            ],
            Response::HTTP_OK
        );

    }//end responseSuccess()


    public function responseError($message=null, $code=Response::HTTP_BAD_REQUEST)
    {
        return response()->json(
            [
                'status'  => 'error',
                'message' => $message,
            ],
            $code
        );

    }//end responseError()


}//end class
