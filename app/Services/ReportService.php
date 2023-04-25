<?php

namespace App\Services;
use App\Models\ProblemReport;

class ReportService
{
    public function store($report)
    {
        $problemReport = ProblemReport::create($report);
        return $problemReport;
    }

    public function getTutorPendingReportList()
    {
        $report = DB::table('problem_reports')->whereNull('tutor_reason')->get();
        return $report;
    }

    public function getTutorPendingReports($tutorId)
    {
        return ProblemReport::whereNull('tutor_reason')->
            where("tutor_id", $tutorId)->get();
    }

    public function updateReportByTutor($data)
    {
        $report = ProblemReport::where('id', $data['reportId'])
                    ->update([
                        "tutor_teaching_minutes" => $data['tutorTime'],
                        "tutor_reason" => $data['reason']
                    ]);
        if (!$report) {
            return false;
        }
        
        return true;
    }
}