<?php

namespace App\Services;
use GuzzleHttp\Client;

class BellbirdApiService extends BellbirdBaseService
{
   
    const CREATE_MEETING_BOOKING ='/meeting_bookings';
    const LESSONS_API = '/lesson_headers';
    const CATEGORIES_API = "/categories";
    const COURSES_API = "/courses";
    const UNLISTED_VISIBILITY = "Unlisted";   
    const PUBLISHED_LESSON_TYPE = "Published";
    const LANG_EN = "en";

    public function createMeetingWithReferences($access_token, $data)
    {   

        $this->client = new Client;

     
        $this->options['json'] = 
        [
            'data' => [
              '_new' => true,
              '_type' => 'MeetingBooking',
              '_version' => $data['meeting_booking_version'],
              'time' => [
                'min' =>  $data['start_time'],
                'max' => $data['end_time'],
              ],
              'metadata' => [
                'school_id' => $data['school_id'],
                'student_id' => $data['student_id'],
              ],
              'join_password' => $data['join_password'],
              'brand' => [
                '_ref' => 'brand-ref',
              ],
              'lesson' => [
                '_ref' => 'lesson-ref',
              ],
              'category' => [
                '_ref' => 'category-ref',
              ],
              'course' => [
                '_ref' => 'course-ref',
              ],
              'open' => true,
            ],
            'references' => [
              'brand-ref' => [
                '_type' => 'BrandReference',
                '_version' => $data['brand_version'],
                'id' => env('BELLBIRD_BRAND_ID'),
              ],
              'lesson-ref' => [
                '_type' => 'LessonHeader',
                '_version' => $data['lesson_version'],
                'id' => $data['lesson_id'],
              ],
              'course-ref' => [
                '_type' => 'Course',
                '_version' => $data['course_version'],
                'id' => $data['course_id'],
              ],
              'category-ref' => [
                '_type' => 'Category',
                '_version' => $data['category_version'],
                'id' => $data['category_id'],
              ],
            ],
            'versions' => [
              'MeetingBooking' => $data['meeting_booking_version'],
              'BrandReference' => $data['brand_version'],
              'LessonHeader' => $data['lesson_version'],
              'Course' => $data['course_version'],
              'Category' => $data['category_version'],
            ]
          ];


        $this->options['headers'] = [
        'Authorization' => 'Bearer '.$access_token,
        'Content-Type' => 'application/json'
        ];
        
        $request =$this->client->request('POST', env('BELLBIRD_API_BASE_URL').self::CREATE_MEETING_BOOKING,$this->options); 
        $res = json_decode($request->getBody());

        return $res;
        
    }

    public function createMeetingWithPassword($access_token, $data)
    {   

        $this->client = new Client;

     
        $this->options['json'] = 
        [
            'data' => [
              '_new' => true,
              '_type' => 'MeetingBooking',
              '_version' => $data['meeting_booking_version'],
              'time' => [
                'min' =>  $data['start_time'],
                'max' => $data['end_time'],
              ],
              'metadata' => [
                'school_id' => $data['school_id'],
                'student_id' => $data['student_id'],
              ],
              'join_password' => $data['join_password'],
              'brand' => [
                '_ref' => 'brand-ref',
              ],
              'open' => true,
            ],
            'references' => [
              'brand-ref' => [
                '_type' => 'BrandReference',
                '_version' => $data['brand_version'],
                'id' => env('BELLBIRD_BRAND_ID'),
              ]
            ],
            'versions' => [
              'MeetingBooking' => $data['meeting_booking_version'],
              'BrandReference' => $data['brand_version']
            ]
          ];


        $this->options['headers'] = [
        'Authorization' => 'Bearer '.$access_token,
        'Content-Type' => 'application/json'
        ];
        
        $request =$this->client->request('POST', env('BELLBIRD_API_BASE_URL').self::CREATE_MEETING_BOOKING,$this->options); 
        $res = json_decode($request->getBody());

        return $res;
        
    }

    public function getCategoriesByOrganization()
    {
        $result = $this->generateToken();
        $this->client = new Client;

        if ($result->access_token){
          
            $this->options['headers'] = [
              'Authorization' => 'Bearer '. $result->access_token,
              'Content-Type' => 'application/json'
            ];

            $this->options['json'] = [
                "owner" => env('BELLBIRD_ORGANIZATION_ID'),
                "language" => self::LANG_EN,
                "visibility" => self::UNLISTED_VISIBILITY,
            ];
          
            $request = $this->client->request('GET',  env('BELLBIRD_API_BASE_URL'). self::CATEGORIES_API ,  $this->options); 
            $res = json_decode($request->getBody());

            return $res->data; 
        }

        return $result;
    }

    public function getCourseByCategory($categoryId)
    {
        
        $result = $this->generateToken();
        $this->client = new Client;

        if ($result->access_token){
          
            $this->options['headers'] = [
                'Authorization' => 'Bearer '. $result->access_token,
                'Content-Type' => 'application/json'
            ];

            $this->options['json'] = [
                "category" => $categoryId,
                "owner" => env('BELLBIRD_ORGANIZATION_ID'),
                "language" => self::LANG_EN
            ];
            
            $request = $this->client->request('GET', env('BELLBIRD_API_BASE_URL'). self::COURSES_API, $this->options); 
            $res = json_decode($request->getBody());

            return $res->data; 
        }

        return $result;
    }

    public function getLessonByCourse($courseId)
    {
        $result = $this->generateToken();
        $this->client = new Client;
        
        if ($result->access_token){
          
            $this->options['headers'] = [
                'Authorization' => 'Bearer '. $result->access_token,
                'Content-Type' => 'application/json'
            ];

            $this->options['json'] = [
                "course" => $courseId,
                "lesson_type" => self::PUBLISHED_LESSON_TYPE,
                "published_latest" => true,
                "language" => self::LANG_EN
            ];
            
            $request = $this->client->request('GET', env('BELLBIRD_API_BASE_URL'). self::LESSONS_API, $this->options); 
            $res = json_decode($request->getBody());

            return $res->data; 
        }

        return $result;
    }

    public function udpateMaterial($meetingId, $lessonId, $courseId, $categoryId)
    {
        $token = $this->generateToken()->access_token;

        $this->options['headers'] = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ];

        $this->options['json'] = [
            'data' => [
                '_type' => 'MeetingBooking',
                '_version' => 8,
                'id' => $meetingId,
                'brand' => ['_ref' => 'brand-ref'],
                'lesson' => ['_ref' => 'lesson-ref'],
                'category' => ['_ref' => 'category-ref'],
                'course' => ['_ref' => 'course-ref'],
            ],
            'references' => [
                'brand-ref' => [
                    '_type' => 'BrandReference',
                    '_version' => 2,
                    'id' => env('BELLBIRD_BRAND_ID')
                ],
                'lesson-ref' => [
                    '_type' => 'LessonHeader',
                    '_version' => 5,
                    'id' => $lessonId
                ],
                'course-ref' => [
                    '_type' => 'Course',
                    '_version' => 8,
                    'id' => $courseId
                ],
                'category-ref' => [
                    '_type' => 'Category',
                    '_version' => 9,
                    'id' => $categoryId
                ]
            ],
            'versions' => [
                'MeetingBooking' => 8
            ]
        ];

        $request = $this->client->request(
            'POST',
            env('BELLBIRD_API_BASE_URL') . self::CREATE_MEETING_BOOKING, $this->options
        );

        return json_decode($request->getBody());
    }
}