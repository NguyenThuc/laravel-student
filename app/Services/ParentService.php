<?php

namespace App\Services;
use Carbon\Carbon;

use App\Models\ParentList;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class ParentService
{

    public function findById($id)
    {
        return ParentList::find($id);
    }

    public function checkPassword($password, $id) : bool
    {
        $parent = ParentList::find($id)->makeVisible(['password']);
        return password_verify($password, $parent->password);
    }

    public function create($data)
    {   
        
        $parentSave = new ParentList([
            "email" => $data['email'],
            "first_name" => $data['name'],
            "last_name" => $data['surname'],
            "password" => $data['password'],
            "agree_terms" => $data['agree_terms'],
            "password" => Hash::make($data['password'])
        ]);
        if (!$parentSave->save()) {
            return false;
        }

        $student = Student::find($data['studentID']);
        if($student) {
            $student->parent_id = $parentSave->id;
            $student->save();
        }

        return $parentSave;
    }

    public function getById($id)
    {
        return ParentList::find($id);
    }

    public function findByEmail($email)
    {
        return ParentList::where("email", $email)->first();
    }

    public function updateParentInfo($request, $id)
    {
        $parentList = new ParentList();
        $mappedParent = $parentList->dataMapper($request);
        $keys = array_keys($mappedParent->toArray());
        $parent = $this->getById($id);
        $parent['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $isPasswordMatched = $this->checkPassword($request->password, $id);

        foreach($keys as $key) {
            if($parent[$key] && $mappedParent[$key] != $parent[$key]) {
                $parent[$key] = $mappedParent[$key];
            }
        }
        if(!$isPasswordMatched) {
            $parent['password'] = Hash::make($request->password);
        }
        $parent->save();
        return $parent;
    }

    
}