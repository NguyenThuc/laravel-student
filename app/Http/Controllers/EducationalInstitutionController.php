<?php

namespace App\Http\Controllers;

use App\Http\Requests\EduInstitution\EditRequest;
use App\Http\Requests\EduInstitution\ShowRequest;
use App\Services\SellerService;
use Illuminate\Http\Request;
use App\Services\EducationalInstitutionService;
use App\Services\EducationalStaffService;
use App\Services\MstTextbookCourseService;
use App\Services\MstService;
use App\Services\AgencyService;
use App\Http\Requests\EduInstitution\StoreRequest;
use App\Http\Requests\EduInstitution\UpdateRequest;
use Illuminate\Support\Arr;
use Exception;
use Illuminate\Http\Response;

class EducationalInstitutionController extends Controller
{

    private $educationalInstitutionService;

    private $educationalStaffService;

    private $mstTextbookCourseService;

    private $agencyService;

    private $mstService;

    private $sellerService;

    const TITLE = '教育機関管理 | Edule';


    public function __construct(
        EducationalInstitutionService $educationalInstitutionService,
        EducationalStaffService $educationalStaffService,
        MstTextbookCourseService $mstTextbookCourseService,
        AgencyService $agencyService,
        MstService $mstService,
        SellerService $sellerService,
    ) {
        $this->educationalInstitutionService = $educationalInstitutionService;
        $this->educationalStaffService = $educationalStaffService;
        $this->mstTextbookCourseService = $mstTextbookCourseService;
        $this->agencyService = $agencyService;
        $this->mstService = $mstService;
        $this->sellerService = $sellerService;

    }//end __construct()


    public function show($id, ShowRequest $request)
    {
        $eduInstitution = $this->educationalInstitutionService->findById($id);
        if (!$eduInstitution) {
            return redirect()->route('educational_institution.list');
        }

        $eduInstitution->contractInfo = $eduInstitution->contract->last();
        $eduInstitution->staff = $this->educationalStaffService->getStaffByEduInstitutionId($eduInstitution->id);
        $courseIds = explode(',', $eduInstitution->mstTextbookCourseIds);
        $eduInstitution->courses = $this->mstTextbookCourseService->getCourseByIds($courseIds);
        $eduInstitution->sellers = $eduInstitution->sellers;
        $title = self::TITLE;
        return view('educational_institution.show', compact('eduInstitution', 'title'));

    }//end show()


    public function getEducationalInstitutionsByAgency($id)
    {
        $educationalInstitutions = $this->agencyService->findById($id)?->educationalInstitutions()->get();
        return $this->responseSuccess($educationalInstitutions);

    }//end getEducationalInstitutionsByAgency()


    public function getAll()
    {
        $schools = $this->educationalInstitutionService->getAll();
        return $this->responseSuccess($schools);

    }//end getAll()


    public function getList()
    {
        $eduInstitutionList = $this->educationalInstitutionService->getList();
        return $this->responseSuccess($eduInstitutionList);

    }//end getList()


    public function getListEducationalInstitutionBySeller($id)
    {
        $schools = $this->sellerService->findById($id)->schools()->get();
        return $this->responseSuccess($schools);

    }//end getListEducationalInstitutionBySeller()


    public function index()
    {
        $title = self::TITLE;
        return view("educational_institution.index", compact('title'));

    }//end index()


    public function create()
    {
        $agencyId = auth()->user('seller')->agency_id;
        $data = [
            'categories' => $this->mstService->getAllCategories(),
            'sellers'    => $this->sellerService->getByAgencyId($agencyId),
            'title'      => self::TITLE,
        ];
        return view('educational_institution.save', $data);

    }//end create()


    public function store(StoreRequest $request)
    {
        $seller = auth()->guard('seller')->user()->toArray();
        $request->merge(
            [
                'agencyId' => Arr::get($seller, 'agency_id'),
                'createBy' => Arr::get($seller, 'name'),
                'updateBy' => Arr::get($seller, 'name'),
            ]
        );

        try {
            $this->educationalInstitutionService->create($request->all());
            return $this->responseSuccess(Response::HTTP_OK);
        } catch (Exception $ex) {
            return $this->responseError($ex->getMessage());
        }

    }//end store()


    public function edit($id, EditRequest $request)
    {
        $eduInstitution = $this->educationalInstitutionService->findById($id);
        if (!$eduInstitution) {
            return redirect()->route('educational_institution.list')->withErrors(['message' => trans('educational-institution.not_found')]);
        }

        $eduInstitution->selectedSellerIds = $eduInstitution->sellers->pluck('id');
        $eduInstitution->mstTextbookCourseIds = $eduInstitution->mstTextbookCourseIds ? explode(',', $eduInstitution->mstTextbookCourseIds) : [];
        $eduInstitution->contractInfo = $eduInstitution->contract->last();
        $eduInstitution->staff = $this->educationalStaffService->getStaffByEduInstitutionId($eduInstitution->id);
        $data = [
            'categories'     => $this->mstService->getAllCategories(),
            'sellers'        => $this->sellerService->getByAgencyId(auth()->user('seller')->agency_id),
            'eduInstitution' => $eduInstitution,
            'title'          => self::TITLE,
        ];
        return view('educational_institution.save', $data);

    }//end edit()


    public function update(UpdateRequest $request, $id)
    {
        $seller = auth()->guard('seller')->user()->toArray();
        $request->merge(
            [
                'agencyId' => Arr::get($seller, 'agency_id'),
                'createBy' => Arr::get($seller, 'name'),
                'updateBy' => Arr::get($seller, 'name'),
            ]
        );

        try {
            $this->educationalInstitutionService->update($request->all(), $id);
            return $this->responseSuccess(Response::HTTP_OK);
        } catch (Exception $ex) {
            return $this->responseError($ex->getMessage());
        }

    }//end update()


    public function checkIfStaffEmailNotExists(Request $request)
    {
        $email = $request->email;
        $staff = $this->educationalStaffService->getByEmail($email);
        if (!$staff) {
            return $this->responseSuccess(Response::HTTP_OK);
        }

        if (isset($request->staffId) && ($staff->id == $request->staffId)) {
            return $this->responseSuccess(Response::HTTP_OK);
        }

        return $this->responseError(trans('educational-institution.duplicate_mail_exception'), Response::HTTP_UNPROCESSABLE_ENTITY);

    }//end checkIfStaffEmailNotExists()


}//end class
