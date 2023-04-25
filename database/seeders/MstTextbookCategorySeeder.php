<?php

namespace Database\Seeders;

use App\Models\MstTextbookCategory;
use App\Models\MstTextbookCourse;
use App\Models\MstTextbookLesson;
use Illuminate\Database\Seeder;
use App\Services\BellbirdApiService;
use Illuminate\Support\Facades\Log;
use DB;

class MstTextbookCategorySeeder extends Seeder
{
    const JA_LANG = "ja";
    const BY_COLUMN_PREFIX = "batch-MstTextbookCategorySeeder";

    private $bellBirdApiService;

    public function __construct()
    {
        $this->bellBirdApiService = new BellbirdApiService();
    }
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    { 
        $exemptedCategoryIds = [
            "306fc793-afe7-4a9e-941d-97edf9710c7a",
            "3c8acfb8-ba7f-4556-81f6-11edbe3dadcb"
        ];

        DB::beginTransaction();
        
        try {
          
            $bellbirdCategories = $this->bellBirdApiService->getCategoriesByOrganization();

            // delete non existing category
            $this->deleteNonExistingCategory($bellbirdCategories);

            // category
            foreach ($bellbirdCategories as $bellbirdCategory){

                if (!in_array($bellbirdCategory->id, $exemptedCategoryIds)){

                    $mstTextbookCategory = $this->updateCategoryIfExist($bellbirdCategory);
                    $mstTextbookCategoryId = $mstTextbookCategory?->id;

                    if (!$mstTextbookCategory){

                        $name = $this->getJapaneseTranslation($bellbirdCategory->name_text->text_translations);
                        
                        $mstTextbookCategory = new MstTextbookCategory([
                            'name' => $name,
                            'name_en' => $bellbirdCategory->name_text->text,
                            'bellbird_category_id' => $bellbirdCategory->id,
                            'created_by' => self::BY_COLUMN_PREFIX . "-run",
                            'created_at' => now()
                        ]);

                        if ($mstTextbookCategory->save()){
                            $mstTextbookCategoryId = $mstTextbookCategory->id;

                            Log::info('Inserted bellbird catergory ID: ' . $mstTextbookCategory->id);
                        }
                    }

                    $courses = $this->bellBirdApiService->getCourseByCategory($bellbirdCategory->id);

                    $this->processBellbirdCourse($courses, $mstTextbookCategoryId);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ERROR]' . $e->getMessage());
        }
    }

    private function processBellbirdCourse($courses, $mstTextbookCategoryId)
    {
        if ($mstTextbookCategoryId){
            $this->deleteNonExistingCourseByCategory($courses, $mstTextbookCategoryId);
        }

        // course
        foreach($courses as $course){

            $mstTextBookCourse = $this->updateCourseIfExist($course);
            $mstTextbookCourseId = $mstTextBookCourse?->id;
            
            if (!$mstTextBookCourse){
                $name = $this->getJapaneseTranslation($course->name_text->text_translations);

                $mstTextbookCourse = new MstTextbookCourse([ 
                        'name' => $name,
                        'name_en' => $course->name_text->text, 
                        'mst_textbook_category_id' => $mstTextbookCategoryId,
                        "bellbird_course_id" => $course->id, 
                        'created_by' => self::BY_COLUMN_PREFIX . "-processBellbirdCourse",
                        'created_at' => now()
                    ]);

                if ($mstTextbookCourse->save()){
                    $mstTextbookCourseId = $mstTextbookCourse->id;

                    Log::info('Inserted bellbird course ID: ' . $mstTextbookCourse->id);
                }
            }
            
            $lessons = $this->bellBirdApiService->getLessonByCourse($course->id);

            $this->processBellbirdLesson($lessons, $mstTextbookCourseId);
        }  
    }

    private function processBellbirdLesson($lessons, $mstTextbookCourseId)
    {
        if ($mstTextbookCourseId){
            $this->deleteNonExistingLessonByCourse($lessons, $mstTextbookCourseId);
        }

        // lesson
        foreach($lessons as $lesson){

            $mstTextBookLesson = $this->updateLessonIfExist($lesson);

            if (!$mstTextBookLesson){
                $name = $this->getJapaneseTranslation($lesson->title_text->text_translations);

                $mstTextBookLesson = new MstTextbookLesson([
                        'mst_textbook_course_id' => $mstTextbookCourseId,
                        'name' => $name,
                        'name_en' => $lesson->title_text->text,
                        'bellbird_lesson_id' => $lesson->master_id, 
                        'created_by' => self::BY_COLUMN_PREFIX . "-processBellbirdLesson",
                        "created_at" => now()
                    ]);
                
                if ($mstTextBookLesson->save()){
                    Log::info('Inserted bellbird lesson ID: ' . $lesson->master_id);
                }
            }
        }
    }

    private function deleteNonExistingCategory($bellbirdCategories)
    {
        $categories = MstTextbookCategory::all();

        // check if category exists on bellbird
        foreach ($categories as $category){
            if (!$this->checkIfExistOnBellbird($bellbirdCategories, 'id', $category->bellbird_category_id)){

                // get courses by mst_textbook_category_id
                $mstTextBookCourses = MstTextbookCourse::where('mst_textbook_category_id', $category->id)->get();
                
                foreach($mstTextBookCourses as $mstTextBookCourse){
                    $mstTextbookLessons = MstTextbookLesson::where('mst_textbook_course_id', $mstTextBookCourse->id)->get();

                    foreach($mstTextbookLessons as $mstTextbookLesson){
                        // delete lesson
                        MstTextbookLesson::where('id', $mstTextbookLesson->id)->delete();
                        
                        Log::info('Deleted a lesson having an id: ' . $mstTextbookLesson->id);
                    }
                }
                 
                $mstTexbookCourses = MstTextbookCourse::where('mst_textbook_category_id', $category->id)->get();

                foreach($mstTexbookCourses as $mstTexbookCourse){
                    // delete course
                    MstTextbookCourse::where('id', $mstTexbookCourse->id)->delete();

                    Log::info('Deleted a course having an id: ' . $mstTexbookCourse->id);
                }

                // delete category
                MstTextbookCategory::where('id', $category->id)->delete();

                Log::info('Deleted a category having an id: ' . $category->id);
            }
        }
    }

    private function deleteNonExistingCourseByCategory($bellbirdCourses, $mstTextbookCategoryId)
    {
        $courses = MstTextbookCourse::where('mst_textbook_category_id', $mstTextbookCategoryId)->get();

        // check if category exists on bellbird
        foreach ($courses as $course){
            if (!$this->checkIfExistOnBellbird($bellbirdCourses, 'id', $course->bellbird_course_id)){

                // get lessons by mst_textbook_course_id
                $mstTextBookLessons = MstTextbookLesson::where('mst_textbook_course_id', $course->id)->get();

                // delete lesson
                foreach($mstTextBookLessons as $mstTextBookLesson){
                    MstTextbookLesson::where('id', $mstTextBookLesson->id)->delete();

                    Log::info('Deleted a lesson having an id: ' . $mstTextBookLesson->id);
                }

                // delete course 
                MstTextbookCourse::where('id', $course->id)->delete();

                Log::info('Deleted a course having an id: ' . $course->id);
            }
        }
    }

    private function deleteNonExistingLessonByCourse($bellbirdLessons, $mstTextbookCourseId)
    {
        $lessons = MstTextbookLesson::where('mst_textbook_course_id', $mstTextbookCourseId)->get();

        // check if category exists on bellbird
        foreach ($lessons as $lesson){
            if (!$this->checkIfExistOnBellbird($bellbirdLessons, 'master_id', $lesson->bellbird_lesson_id)){

                // delete lesson
                MstTextbookLesson::where('id', $lesson->id)->delete();

                Log::info('Deleted a lesson having an id: ' . $lesson->id);
            }
        }
    }

    private function checkIfExistOnBellbird($bellbirdData, $targetId, $id)
    {
        foreach ($bellbirdData as $bd){
            if ($bd->$targetId == $id){
                return true;
            }
        }

        return false;
    }

    private function updateCategoryIfExist($category)
    {
        $mstTextBookCategory = MstTextbookCategory::where('bellbird_category_id', $category->id)->first();

        if ($mstTextBookCategory){
            $jpNameTranslation = $this->getJapaneseTranslation($category->name_text->text_translations);

            if ($jpNameTranslation != $mstTextBookCategory->name || $mstTextBookCategory->name_en != $category->name_text->text){
                
                MstTextbookCategory::where('id', $mstTextBookCategory->id)
                    ->update([
                        "name" => $jpNameTranslation,
                        "name_en" => $category->name_text->text,
                        "updated_by" => self::BY_COLUMN_PREFIX . "-updateCategoryIfExist",
                        "updated_at" => now()
                    ]);
                
                Log::info('Updated a textbook catergory having an id: ' . $mstTextBookCategory->id);
            }

            return $mstTextBookCategory;
        }

        return null;
    }

    private function updateCourseIfExist($course)
    {
        $mstTextBookCourse = MstTextbookCourse::where('bellbird_course_id', $course->id)->first();

        if ($mstTextBookCourse){
            $jpNameTranslation = $this->getJapaneseTranslation($course->name_text->text_translations);

            if ($jpNameTranslation != $mstTextBookCourse->name || $mstTextBookCourse->name_en != $course->name_text->text){
                
                MstTextbookCourse::where('id', $mstTextBookCourse->id)
                    ->update([
                        "name" => $jpNameTranslation,
                        "name_en" => $course->name_text->text,
                        "updated_by" => self::BY_COLUMN_PREFIX . "-updateCourseIfExist",
                        "updated_at" => now()
                    ]);
                
                Log::info('Updated a textbook course having an id: ' . $mstTextBookCourse->id);
            }

            return $mstTextBookCourse;
        }

        return null;
    }

    private function updateLessonIfExist($lesson)
    {
        $mstTextBookLesson = MstTextbookLesson::where('bellbird_lesson_id', $lesson->master_id)->first();

        if ($mstTextBookLesson){

            $jpNameTranslation = $this->getJapaneseTranslation($lesson->title_text->text_translations);

            if ($jpNameTranslation != $mstTextBookLesson->name || $mstTextBookLesson->name_en != $lesson->title_text->text){
                
                MstTextbookLesson::where('id', $mstTextBookLesson->id)
                    ->update([
                        "name" => $jpNameTranslation,
                        "name_en" => $lesson->title_text->text,
                        "updated_by" => self::BY_COLUMN_PREFIX . "-updateLessonIfExist",
                        "updated_at" => now()
                    ]);
                
                Log::info('Updated a textbook lesson having an id: ' . $mstTextBookLesson->id);
            }

            return $mstTextBookLesson;
        }

        return null;
    }

    private function getJapaneseTranslation($textTranslations)
    {
        if ($textTranslations){
            foreach ($textTranslations as $textTranslation){
                if ($textTranslation->language == self::JA_LANG){
                    return $textTranslation->translation;
                }
            }
        }

        return null;
    }
}
