<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\EducationalInstitution;
use App\Services\ContractService;
use App\Services\EducationalStaffService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Jobs\CreateEduStaffJob;
use App\Models\EducationalStaff;

class EducationalInstitutionService
{
    const PER_PAGE = 100;

    public function findById($id)
    {
        return EducationalInstitution::select('educational_institutions.*',
        'educational_institutions.mst_textbook_course_ids as mstTextbookCourseIds')->find($id);
    }

    public function getList($wheres = [], $perPage = self::PER_PAGE)
    {
        $query = EducationalInstitution::with('contracts');

        $sellerService = new SellerService();

        if (Auth::user()->role !== $sellerService::ROLE_ADMIN) {
            $sellerId = Auth('seller')->user()->id;

            $query->whereHas('sellers', function ($q) use ($sellerId) {
                $q->where('sellers.id', $sellerId);
            });
        }

        $query->join('educational_staffs', 'educational_staffs.educational_institution_id', '=', 'educational_institutions.id')
            ->where('educational_staffs.role', EducationalStaffService::ROLE_OWNER);

        $query->where($wheres);

        $query->select('educational_institutions.*', 'educational_staffs.name as owner_name')
            ->orderBy('created_at', 'desc');

        $paginator = $query->paginate($perPage);
        $pagination = [
            'pageSize' => $paginator->perPage(),
            'total' => $paginator->total(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage()
        ];

        return [
            'data' => $paginator->items(),
            'pagination' => $pagination
        ];
    }

    public function create($data)
    {
        $data = (object)$data;
        try {
            DB::beginTransaction();
            $courseIds = implode(',', $data->mstTextbookCourseIds);
            
            $eduInstitution = [
                'name' => $data->name,
                'agency_id' => $data->agencyId,
                'phone_number' => $data->phoneNumber,
                'mst_textbook_course_ids' => $courseIds,
            ];
            $eduInstitution = EducationalInstitution::create($eduInstitution);
           
            //create contract
            $contractService = new ContractService();
            $contractService->create([
                'educational_institution_id' => $eduInstitution->id,
                'started_at' => Carbon::parse($data->startDate)->format('Y-m-d'),
                'ended_at'   => Carbon::parse($data->endDate)->format('Y-m-d'),
            ]);
            //end create contract
    
            //create educational staff
            $password = rand(0, 9) . Str::random(9);
            $accountId = EducationalStaff::all()->last()->account_id;
            $accountId++;
            $eduStaffData = [
                'educational_institution_id' => $eduInstitution->id,
                'account_id'                 => $accountId,
                'name'                       => $data->teacherName,
                'email'                      => $data->teacherEmail,
                'password'                   => Hash::make($password),
                'role'                       => EducationalStaffService::ROLE_OWNER,
            ];
            $educationalStaffService = new EducationalStaffService();
            $staff = $educationalStaffService->create($eduStaffData);
            //end create educational staff
    
            //sync sellers
            $eduInstitution->sellers()->sync($data->selectedSellerIds);

            //send Mail
            CreateEduStaffJob::dispatch($staff, $password);

            DB::commit();
            return $eduInstitution;
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->responseError($ex->getMessage());
        }
    }

    public function update($data, $id)
    {
        $data = (object)$data;
        try {
            DB::beginTransaction();
            $courseIds = implode(',', $data->mstTextbookCourseIds);
            
            $eduInstitution = EducationalInstitution::find($id);
            $eduInstitutionData = [
                'name' => $data->name,
                'agency_id' => $data->agencyId,
                'phone_number' => $data->phoneNumber,
                'mst_textbook_course_ids' => $courseIds,
            ];
            $eduInstitution->update($eduInstitutionData);
        
            //update contract
            $contract = [
                'started_at' => Carbon::parse($data->startDate)->format('Y-m-d'),
                'ended_at'   => Carbon::parse($data->endDate)->format('Y-m-d'),
            ];
            $contractService = new ContractService();
            $contractService->update($eduInstitution->contracts->last()->id, $contract);
            //end update contract

            //update educational staff
            $eduStaff = [
                'name'                       => $data->teacherName,
                'email'                      => $data->teacherEmail,
            ];
            $educationalStaffService = new EducationalStaffService();
            $staff = $educationalStaffService->getStaffByEduInstitutionId($eduInstitution->id);
            $educationalStaffService->update($staff->id, $eduStaff);
            //end update educational staff

            //sync sellers
            $eduInstitution->sellers()->sync($data->selectedSellerIds);
            DB::commit();
            return $eduInstitution;
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->responseError($ex->getMessage());
        }
    }

    public function getEducationalInstitutionIds($agencyId)
    {
        return EducationalInstitution::where('agency_id', $agencyId)->get()->pluck('id')->toArray();
    }

    public function getEducationalInstitutionbySellers()
    {
        return EducationalInstitution::with(['sellers' => function ($query) {
            $sellerId = Auth::guard('seller')->id();
            $query->where('sellers.id', '=', $sellerId);
        }])->get()->toArray();
    }

}
