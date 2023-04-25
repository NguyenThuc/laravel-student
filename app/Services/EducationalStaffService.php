<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\EducationalInstitution;
use App\Models\EducationalStaff;
use Illuminate\Support\Facades\Auth;
use App\Models\Seller;
use App\Services\SellerService;
use App\Services\EducationalInstitutionService;

use Illuminate\Support\Facades\DB;

class EducationalStaffService
{

    const ROLE_OWNER = 1;
    const ROLE_STAFF = 2;
    const ROLE_FACULITY = 3;
    const ROLE_PARTNER = 4;
    const PER_PAGE = 100;


    public function getStaffByEduInstitutionId($eduInstitutionId)
    {
        $filters = [
            ['educational_institution_id', '=', $eduInstitutionId],
            ['role', '=', self::ROLE_OWNER],
        ];
        return EducationalStaff::where($filters)->get()->first();
    }

    public function getData($request)
    {

        $search = json_decode($request->search);
        $sellerInfo = Auth::guard('seller');
        $role = $sellerInfo->user()->role;
        $sellerId = $sellerInfo->user()->id;
        $sellerService = new SellerService();
        $schoolIds = $sellerService->getIdEducationalInstitutions($sellerId);
        if ($role === Seller::ROLE_ADMIN) {
            $agencyId = $sellerInfo->user()->agency_id;
            $educationalInstitutionService = new EducationalInstitutionService();
            $schoolIds = $educationalInstitutionService->getEducationalInstitutionIds($agencyId);
        }
        $teachers = EducationalStaff::with('classrooms', 'educationalInstitution');
        $teachers->whereIn('educational_institution_id', $schoolIds);
        if (isset($search)) {
            if (isset($search->school) && !empty($search->school)) {
                $teachers->where('educational_institution_id', '=', $search->school);
            }
            if (isset($search->role) && !empty($search->role)) {
                $teachers->where('role', '=', $search->role);
            }
        }
        return $teachers->paginate(self::PER_PAGE);
    }

    public function getListClassroom($educational_institution_id)
    {
        return Classroom::where('educational_institution_id', '=', $educational_institution_id)->get()->toArray();
    }

    public function getListSchool()
    {
        $educationalInstitutionService = new EducationalInstitutionService();
        return $educationalInstitutionService->getEducationalInstitutionbySellers();
    }


    public function getByEmail($email)
    {
        return EducationalStaff::where('email', $email)->first();
    }

    public function create($attributes)
    {
        return EducationalStaff::create($attributes);
    }

    public function update($id, array $attributes)
    {
        $result = EducationalStaff::find($id);
        if ($result) {
            $result->update($attributes);
            return $result;
        }

        return false;
    }

    public function findById($id)
    {
        return EducationalStaff::find($id);
    }

    public function findByEduInstitutionIdAndRoles($educationalInstitutionId, array $roles = [])
    {
        if ($roles) {
            return EducationalStaff::where('educational_institution_id', $educationalInstitutionId)
                ->whereIn('role', $roles)
                ->get();
        }
        return EducationalStaff::where('educational_institution_id', $educationalInstitutionId)
            ->get();
    }

    public function changeOwnerRole($staff, $owner)
    {
        try {
            if ($staff && $owner) {
                DB::beginTransaction();
                $owner->role = EducationalStaff::ROLES['STAFF'];
                $owner->save();
                $staff->role = EducationalStaff::ROLES['OWNER'];
                $staff->save();
                DB::commit();
                return true;
            }
            return false;
        } catch (\PDOException $ex) {
            DB::rollBack();
            return false;
        }
    }

    public function checkConditionChangeOwner($staff)
    {
        $rolesCheck = [
            EducationalStaff::ROLES['STAFF'],
            EducationalStaff::ROLES['FACULTY'],
        ];
        if (in_array($staff->role, $rolesCheck) && !empty($staff->email)) {
            return true;
        }
        return false;
    }

}
