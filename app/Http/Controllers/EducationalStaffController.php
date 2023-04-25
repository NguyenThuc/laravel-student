<?php

namespace App\Http\Controllers;

use App\Models\EducationalStaff;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\EducationalStaffService;
use App\Http\Requests\EducationalStaff\ShowRequest;

class EducationalStaffController extends Controller
{

    protected $educationalStaffService;
    const TITLE = '教育機関スタッフ管理 ';


    public function __construct(EducationalStaffService $educationalStaffService)
    {
        $this->educationalStaffService = $educationalStaffService;

    }//end __construct()


    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $data = $this->getData($request);
            return $this->responseSuccess($data);
        } else {
            $roles = EducationalStaff::ROLE_NAME;
            $title = self::TITLE;
            return view('educational_staff.index', compact('roles', 'title'));
        }

    }//end index()


    public function show(ShowRequest $request, $id)
    {
        $educationalStaff = $this->educationalStaffService->findById($id);
        if ($educationalStaff) {
            $isChange = $this->educationalStaffService->checkConditionChangeOwner($educationalStaff);
            $educationalStaff->educationalInstitution = $educationalStaff->educationalInstitution()->first();
            $educationalStaff->classrooms = $educationalStaff->classrooms()->get();
            $educationalStaff->roleName = EducationalStaff::getRoleName($educationalStaff->role);
            $title = self::TITLE;
            return view('educational_staff.detail', compact('educationalStaff', 'title', 'isChange'));
        }

        return abort(404);

    }//end show()


    public function getData($request)
    {
        return $this->educationalStaffService->getData($request);

    }//end getData()


    public function getListClassroom($id)
    {
        return $this->educationalStaffService->getListClassroom($id);

    }//end getListClassroom()


    public function getListEducationInstitution()
    {
        return $this->educationalStaffService->getListSchool();

    }//end getListEducationInstitution()


    public function changeOwner(Request $request)
    {
        $staff = $this->educationalStaffService->findById($request->id);
        $isChange = $this->educationalStaffService->checkConditionChangeOwner($staff);
        if ($staff && $isChange) {
            $owner = $this->educationalStaffService
                ->findByEduInstitutionIdAndRoles($staff->educational_institution_id, [EducationalStaff::ROLES['OWNER']])->first();// phpcs:ignore
            $changeOwner = $this->educationalStaffService->changeOwnerRole($staff, $owner);
            if ($changeOwner) {
                return $this->responseSuccess();
            }

            return $this->responseError();
        }

        return $this->responseError(null, Response::HTTP_NOT_FOUND);

    }//end changeOwner()


}//end class
