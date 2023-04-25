<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\ClassList;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

use App\Services\CMCEmailService;
use App\Services\SchoolService;
use App\Models\Email;
use View;

class TeacherService
{
    use SoftDeletes;

    const ROLE_ADMIN = 1;
    const ROLE_TEACHER = 2;
    const ROLE_ASSISTANT = 3;

    private $cmcEmailService;
    private $schoolService;

    public function __construct()
    {
        $this->cmcEmailService = new CMCEmailService();
        $this->schoolService = new SchoolService();
    }

    public function newsNotif()
    {
        $news = [
            "label" => "2021/11/30",
            "subject" => "ようこそ先生",
            "content" => "伊椅子太郎が新規先生として登録されました。"
        ];

        if(!empty($news)) {
            return $news['label']." ".$news['content'];
        } else {
            return "新着のお知らせはありません。";
        }
    }

    public function listAll($schoolId)
    {
        return Teacher::where('school_id', $schoolId)->get();
    }

    public function classList($schoolId)
    {
        return ClassList::where('school_id', $schoolId)->get();
    }

    public function find($id)
    {
        return Teacher::find($id);
    }

    public function castRole($roleId)
    {
        $role = '';

        if($roleId == self::ROLE_ADMIN) {
            $role = config('constants.ADMIN');
        } elseif($roleId == self::ROLE_TEACHER) {
            $role = config('constants.TEACHER');
        } elseif($roleId == self::ROLE_ASSISTANT) {
            $role = config('constants.ASSISTANT');
        }

        return $role;
    }

    public function create($data)
    {
        $receiver = $data['email'];
        $randPassword = Str::random(15);

        $teacher = new Teacher([
            "school_id" => $data['schooId'],
            "first_name" => $data['name'],
            "last_name" => $data['surname'],
            "email" => $receiver,
            "password" => Hash::make($randPassword),
            "role" => $data['authority']
        ]);

        $details = [
            'content' => '先生の新規登録',
            'password' => $randPassword
        ];

        if($teacher->save()) {
            $content = View::make('email.registration')->render();
            $title = "{school_name}【edule - Online英会話】アカウント発行のご案内";
            $school = $this->schoolService->find($teacher->school_id);
            $content = str_replace("{URL}", env("APP_URL") . "/login", $content);
            $content = str_replace("{school_name}", $school->name, $content);
            $content = str_replace("{school_id}", $teacher->school_id, $content);
            $content = str_replace("{teacher_email}", $teacher->email, $content);
            $content = str_replace("{password}", $randPassword, $content);

            $email = new Email(
                array($receiver),
                str_replace("{school_name}", $school->name, $title),
                $content
            );
            $result = $this->cmcEmailService->sendEmail($email->getObjectProperties());

            return true;
        } else {
            return false;
        }
    }

    public function update($data)
    {
        $teacher = Teacher::where('id', $data['id'])
            ->update([
                "school_id" => $data['schooId'],
                "first_name" => $data['name'],
                "last_name" => $data['surname'],
                "email" => $data['email'],
                "role" => $data['authority'],
                "updated_by" => $data['updateBy']
            ]);

        if (!$teacher) {
            return false;
        }

        return true;
    }

    public function delete($id)
    {
        $teacher = $this->find($id);
        Teacher::where('id', $id)->
            update([
                "email" => "DELETED-". $teacher->email
            ]);

        return Teacher::where('id', $id)->delete();
    }

    public function getByEmail($email)
    {
        return Teacher::where('email', $email)->first();
    }

    public function isTeacherBelongSchool($teacherId) {
        $schoolId = auth()->guard('teacher')->user()->school_id;
        $teacher = Teacher::where('id', $teacherId)->first();
        if($teacher->school_id == $schoolId) {
            return true;
        }

        return false;
    }

    public function createWithoutSendingEmail($data)
    {
        $teacher = new Teacher([
            "school_id" => $data['school_id'],
            "first_name" => $data['name'],
            "last_name" => $data['name'],
            "email" => $data['email'],
            "password" => $data['password'],
            "role" => self::ROLE_ADMIN,
        ]);
        $teacher->save();
        return $teacher;
    }
}
