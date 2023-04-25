<?php

namespace App\Services;
use Carbon\Carbon;
use App\Models\LessonReservation;
use App\Services\TicketService;
use App\Services\RareJobTutorService;

class RareJobEMSService extends RareJobBaseService
{   
    const HOME_LESSON = 'HL';
    const EVENT_CATEGORY = 1;
    const BOOKING_DEADLINE = 1; //temporary
    const CANCEL_DEADLINE = 1; //temporary
    const TUTOR_PREFIX = '02-00-'; 
    private $api_event_list = "/events";
    private $api_flex_range = "/flex/range";
    private $api_create_event_schedule = "/events";
    private $api_get_event_detail = "/events/{event_id}";
    private $api_attendee_booked_event = "/events/{event_id}/booking";
    private $date_format_utc = "Y-m-d\TH:i:s\Z";
    private $eventKeys = [ "from", "to", "limit", "sort", "tags", "assignee_id"];
    private $api_standby_event = "/events/{event_id}/assignees/{assignee_id}/attendance";
    private $api_cancel_event = "/events/{event_id}/attendees/{attendee_id}/booking";
    private $api_get_flex_event = "/flex/range";
    private $api_book_flex_event = "/flex/events/booking";
    const DEFAULT_ACTOR_ID = "00-00-000"; // usually use in running cron job
    private $api_flex_event_booking = "/flex/events/booking";
    private $api_create_flex_event = "/flex/range";

    const CATEGORY_TUTOR = '02';
    const CATEGORY_STUDENT = '01';
    const CBT_TUTOR_COMPANY_ID = 2; // Envision/EN-PH

    public function __construct()
    {
        $this->setBaseUrl(env('RAREJOB_EMS_BASE_URL'));
        parent::__construct();
    }

    function group_by($data) {
        $tutorList = array();
        
        foreach($data as $val) {
            $tutorList[$val->assignee_id] = [];
        }

        foreach($data as $val) {
            if(array_key_exists($val->assignee_id, $tutorList)){
                $tutorList[$val->assignee_id][] = [
                    'id' => $val->id,
                    'start_at' => date('h:i', strtotime($val->start_at))
                ];
            }else{
                $tutorList[''][] = $val;
            }
        }

        return $tutorList;
    }

    public function listEvent($id, 
        $options, 
        $category = self::CATEGORY_TUTOR, 
        $isGroup = true )
    {
        $token = $this->generateToken($id, $category);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        $queryString = "";
        $params = [];

        unset($this->options['json']);
        
        foreach($this->eventKeys as $key) {
            if(array_key_exists($key, $options)) {
                $params[$key] = in_array($key, ["from", "to"]) ? 
                    date($this->date_format_utc, strtotime($options[$key]))
                    : $options[$key];
            }
        }
    
        $queryString = http_build_query($params);
        $result = $this->request('GET', $this->api_event_list.'?'.$queryString);
        $data = (array) $result->records;

        return $isGroup ? $this->group_by($data) : $data;
    }

    public function getEventDetail($studentId, $eventId)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        unset($this->options['json']);
        
        $data = $this->request('GET', str_replace("{event_id}", $eventId, $this->api_get_event_detail));

        return [
            "start_at" => date('h:i', strtotime($data->start_at)),
            "end_at" => date('h:i', strtotime($data->end_at)),
            "assignee_id" => $data->assignee_id,
        ];
    }

    public function saveEvent($data)
    {
        $dateTimeObject1 = date_create($data->start_at); 
        $dateTimeObject2 = date_create($data->end_at); 
        $interval = date_diff($dateTimeObject1, $dateTimeObject2); 
        $duration = $interval->h * 60;
        $duration += $interval->i;

        $lesson = new LessonReservation([
            "lesson_date" => date('Y-m-d', strtotime($data->start_at)),
            "school_reservation_slot_id" => "8888",
            "duration" => $duration,
            "student_id" => $data->attendee_id,
            "class_list_id" => "",
            "tutor_id" => $data->assignee_id,
            "mst_textbook_id" => $data->category,
            "memo" => "N/A",
            "status" => $data->is_opened,
            "updated_by" => "8888"
        ]);

        if($lesson->save()) {
            return true;
        } else {
            return false;
        }
    }

    public function bookEvent($data)
    {
        $eventId = $data['eventId'];
        $studentId = $data['studentId'];
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        unset($this->options['json']);

        $this->options['json'] = [
            "attendee_id" => "01-00-".$studentId,
            "requests" => [
                "lesson_type" => "level5",
                "material" => "text book"
            ]
        ];

        return $this->request('POST', str_replace("{event_id}", $eventId, $this->api_attendee_booked_event));
    }

    public function getFlexRange(
        $tutorId,
        $options, 
        $category = self::CATEGORY_TUTOR, 
        $isGroup = true 
    )
    {
        $queryString = "";
        $params = $this->queryParamsBuilder($options);
        
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        unset($this->options['json']);

        $queryString = http_build_query($params);
        $result = $this->request('GET', $this->api_flex_range.'?'.$queryString);
        
        return $isGroup ? $this->group_by($result) : $result;
    }

    public function createFlexRange($tutorId, $startDateTime, $endDateTime, $productId = null)
    {
        $start = date($this->date_format_utc, strtotime($startDateTime));
        $end = date($this->date_format_utc, strtotime($endDateTime));

        $token = $this->getAuthToken();

        // check if from seeders, token will be null
        if (!$token){
            $this->setToken($tutorId, RareJobBaseService::CATEGORY_TUTOR, $productId);
        } else {
            $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        }

        $this->options['json'] = [
            "assignee_id" => RareJobBaseService::CATEGORY_TUTOR."-00-".$tutorId,
            "start_at" => $start,
            "end_at" => $end
        ];

        return $this->request('POST', $this->api_flex_range);
    }

    public function deleteFlexRange($tutorId, $id)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token; 
        $this->options['headers']['X-Amzn-Trace-Id'] = $tutorId;
        unset($this->options['json']);
        return $this->request("DELETE", $this->api_flex_range . "/" . $id);
    }

    public function queryParamsBuilder($options)
    {
        $params = [];
        foreach ($this->eventKeys as $key) {
            if ($options[$key] && array_key_exists($key, $options)) {
                $params[$key] = in_array($key, ["from", "to"]) ?
                    date($this->date_format_utc, strtotime($options[$key]))
                    : $options[$key];
            }
        }
        return $params;
    }

    public function getTutorTodaysEvents($assigneeId)
    {
        $schedules = [];

        $datetimeFrom = date('Y-m-d') . 'T00:00:00Z';
        $datetimeTo = date('Y-m-d') . 'T23:59:00Z'; 

        // EU Lessons
        $euLessons = $this->getEvents($datetimeFrom, $datetimeTo, config('constants.EU_PRODUCT_CODE'), $assigneeId);

        // Rarejob Lessons
        $rarejobLessons = $this->getEvents($datetimeFrom, $datetimeTo, config('constants.RAREJOB_PRODUCT_CODE'), $assigneeId);

        $schedules = array_merge($rarejobLessons, $euLessons);

        return $schedules ?? [];
    }

    protected function setToken($actorId, $category, $productId)
    {
        $token = $this->generateToken($actorId, $category, $productId);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        unset($this->options['json']);
    }

    protected function setProductId($event)
    {
        if (in_array(parent::RAREJOB_PRODUCT_ID, $event->product_ids)){
            return parent::RAREJOB_PRODUCT_ID;
        }

        return parent::EU_PRODUCT_ID;
    }

    public function standByEvent($event)
    {
        $actorId = $event->assignee_id ?? self::DEFAULT_ACTOR_ID;
        $this->setToken($actorId, RareJobBaseService::CATEGORY_TUTOR, $this->setProductId($event));

        $this->options['json'] = [
            "on_standby" => true
        ];

        $api = str_replace(array("{event_id}", "{assignee_id}"), array($event->ems_event_id, $event->assignee_id), $this->api_standby_event);

        return $this->request('POST', $api);
    }

    public function compensateHomeLessonTicket($event)
    {
        // store student ticket
        $ticketService = new TicketService();

        $ticket = [
            "student_id" => $event->attendee_id,
            "expire_date" => Carbon::now()->addDays(30)
        ];

        return $ticketService->store($ticket);
    }

    public function cancelEvent($event)
    {
        $actorId = $event->assignee_id ?? self::DEFAULT_ACTOR_ID;
        $this->setToken($actorId, RareJobBaseService::CATEGORY_TUTOR, $this->setProductId($event));

        $this->options['json'] = [
            "reason" => config('messages.TUTOR_ABSENT_REASON')
        ];

        $api = str_replace(array("{event_id}", "{attendee_id}"), array($event->ems_event_id, $event->attendee_id), $this->api_cancel_event);
        
        return $this->request('DELETE', $api);
    }

    protected function isFlexEventExistsInEvents($flexEvent, $events){
        foreach ($events as $event) {
            if ($flexEvent->assignee_id == $event->assignee_id && 
                $flexEvent->start_at == $event->start_at &&
                $flexEvent->end_at == $event->end_at) {
                return true;
            }
        }
        return false;
    }

    public function transferLesson($event)
    {
        if ($event->lesson_type == config('constants.SCHOOL_LESSON')){

            // get flex events
            $flexEvents = $this->getFlexEvents($event->start_at, $event->end_at, $this->setProductId($event), $event->assignee_id);
            if (isset($flexEvents->errors)){
                return (object)["error" => $flexEvents->errors];
            }

            // get events
            $events = $this->getEvents($event->start_at, $event->end_at, $this->setProductId($event), $event->assignee_id);
            if (isset($events->errors)){
                return (object)["error" => $events->errors];
            }
            $events = isset($events->records) ? $events->records : $events;

            $openFlexEvents = [];

            // check if flex event is already booked
            foreach($flexEvents as $flexEvent){
                if (!$this->isFlexEventExistsInEvents($flexEvent, $events)){
                    array_push($openFlexEvents, $flexEvent);
                }
            }

            if ($openFlexEvents){
                foreach($openFlexEvents as $openFlexEvent){
                    // extract tutor id
                    $tutorId = $this->extractRarejobTutorId($openFlexEvent->assignee_id);

                    $rarejobTutorService = new RareJobTutorService();
                    $operator = $rarejobTutorService->findTutorByLessonOperator($tutorId);
            
                    // check if tutor is CBT
                    if (isset($operator->tutor->company) && $operator->tutor->company == self::CBT_TUTOR_COMPANY_ID){

                        $jsonOptions = [
                            "category" => (int)$event->category,
                            "product_ids" => $event->product_ids ?? [],
                            "offers" => $event->offers ?? [],
                            "start_at"=> $openFlexEvent->start_at,
                            "end_at" => $openFlexEvent->end_at,
                            "assignee_id" => $openFlexEvent->assignee_id,
                            "attendee_id" => $event->attendee_id,
                            "tags" => $event->tags ? $event->tags : [],
                            "requests" => $event->requests ??  null 
                        ];

                        $result = $this->bookFlexEvent($event, $jsonOptions);

                        // check if transfer was successful
                        if ($result && !isset($result->errors)){
                            return $result;
                            break;
                        }
                    }
                }
            }

            return false;
        }

        return true;
    }

    protected function extractRarejobTutorId($assigneeId)
    {
        $id = explode('-', $assigneeId);
        $tutorId = $id[2] ?? $id[0];

        return $tutorId;
    }

    public function getFlexEvents($datetimeFrom, $datetimeTo, $productId, $assigneeId = null)
    {
        $actorId = $assigneeId ?? self::DEFAULT_ACTOR_ID;
        $this->setToken($actorId, RareJobBaseService::CATEGORY_TUTOR, $productId);

        $params = [
            'from' => $datetimeFrom, 
            'to' => $datetimeTo
        ];

        $queryString = http_build_query($params);
        
        return $this->request('GET',  $this->api_get_flex_event ."?". $queryString);
    }

    public function bookFlexEvent($event, $jsonOptions)
    {
        $actorId = $event->assignee_id ?? self::DEFAULT_ACTOR_ID;
        $this->setToken($actorId, RareJobBaseService::CATEGORY_TUTOR, $this->setProductId($event));
        $this->options['json'] = $jsonOptions;
        
        return $this->request('POST',  $this->api_book_flex_event);
    }

    public function getEvents($datetimeFrom, $datetimeTo, $productId, $assigneeId = null)
    {
        $actorId = $assigneeId ?? self::DEFAULT_ACTOR_ID;
        $this->setToken($actorId, RareJobBaseService::CATEGORY_TUTOR, $productId);

        $params = [
            'product_id' => $productId,
            'from' => $datetimeFrom, 
            'to' => $datetimeTo
        ];

        if ($assigneeId){
            $params['assignee_id'] = $assigneeId;
        }
            
        $queryString = http_build_query($params);

        $events = $this->request('GET',  $this->api_event_list ."?". $queryString);

        return $events->records ?? [];
    }

    public function listTutorHomeLessonEventForCalendar($assigneeId)
    {   
        $token = $this->generateToken(self::TUTOR_PREFIX.$assigneeId, RareJobBaseService::CATEGORY_TUTOR);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        unset($this->options['json']);
       
        $data = $this->request('GET', $this->api_event_list, ['tags' => self::HOME_LESSON]);
        $events = (array) $data->records;
       
       return $events;
    }

    public function getEventDetailTutor($eventId, $assigneeId)
    {   
        $token = $this->generateToken(self::TUTOR_PREFIX.$assigneeId, RareJobBaseService::CATEGORY_TUTOR);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        unset($this->options['json']);
        
        $data = $this->request('GET', str_replace("{event_id}", $eventId, $this->api_get_event_detail));
        return $data;
    }
    
    public function createHomeLessonEvent($data, $assigneeId)
    {  
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $token = $this->generateToken(self::TUTOR_PREFIX.$assigneeId, RareJobBaseService::CATEGORY_TUTOR);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        unset($this->options['json']);
        $this->options['json'] = [
            "category" => self::EVENT_CATEGORY,
            "start_at" => $start_time,
            "end_at" => $end_time,
            "assignee_id" =>  self::TUTOR_PREFIX.$assigneeId,
            "booking_deadline" => self::BOOKING_DEADLINE,
            "cancel_deadline" => self::CANCEL_DEADLINE,
            "tags" => [
              self::HOME_LESSON
              ]
  
        ];

        return $this->request('POST',  $this->api_event_list);
    }

    public function deleteEventforTutor($data, $assigneeId)
    {    
        $eventId = $data['eventId'];
        $token = $this->generateToken(self::TUTOR_PREFIX.$assigneeId, RareJobBaseService::CATEGORY_TUTOR);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        unset($this->options['json']);
        
        $data = $this->request('delete', str_replace("{event_id}", $eventId, $this->api_get_event_detail));

        return $data;
    }

    public function getEventDetailForStudents($studentId, $eventId)
    {
        $token = $this->generateToken($studentId, RareJobBaseService::CATEGORY_STUDENT);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        unset($this->options['json']);
        
        $data = $this->request('GET', str_replace("{event_id}", $eventId, $this->api_get_event_detail));

        return [
            "start_at" => $data->start_at,
            "end_at" => $data->end_at,
            "assignee_id" => $data->assignee_id,
        ];
    }
}
