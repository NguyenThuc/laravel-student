<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\RareJobEMSService;
use App\Services\RareJobTutorService;

use Datetime;
use DateInterval;
use DatePeriod;

class RareJobEMSSeeder extends Seeder
{
    const DATETIME_START_AT = "2022-03-07T06:00:00Z";
    const DATETIME_END_AT = "2022-03-31T17:50:00Z";
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    const DATE_START = '2022-03-07';
    const DATE_END = '2022-04-01';
    const TIME_START = '08:00:00';
    const TIME_END = '17:50:00'; 
    const RAREJOB_PRODUCT_CODE = "004";
    const EU_PRODUCT_CODE = "006";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = "Testing123";

        $slRareJobTutors = [
            ['id' => 32263, "company" => 1, "contract_type" => "SL", "email" => 'marvin.relente@rarejob.net'],
            ['id' => 1558924, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+1@rarejob.co.jp'],
            ['id' => 1558712, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+2@rarejob.co.jp'],
            ['id' => 1558710, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+3@rarejob.co.jp'],
            ['id' => 1558523, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+4@rarejob.co.jp'],
            ['id' => 1557723, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+5@rarejob.co.jp'],
            ['id' => 1557178, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+6@rarejob.co.jp'],
            ['id' => 1557147, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+7@rarejob.co.jp'],
            ['id' => 1556702, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+8@rarejob.co.jp'],
            ['id' => 1556697, "company" => 1, "contract_type" => "SL", "email" => 'kazuki.iwahori+9@rarejob.co.jp'],
        ];

        $hlRareJobTutors = [
            ['id' => 1556633, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+10@rarejob.co.jp'],
            ['id' => 1556223, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+11@rarejob.co.jp'],
            ['id' => 1556151, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+12@rarejob.co.jp'],
            ['id' => 1556038, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+13@rarejob.co.jp'],
            ['id' => 1555861, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+14@rarejob.co.jp'],
            ['id' => 1555855, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+15@rarejob.co.jp'],
            ['id' => 1555773, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+16@rarejob.co.jp'],
            ['id' => 1555685, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+17@rarejob.co.jp'],
            ['id' => 1555615, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+18@rarejob.co.jp'],
            ['id' => 1555468, "company" => 1, "contract_type" => "HL", "email" => 'kazuki.iwahori+19@rarejob.co.jp'],
        ];

        $slENPHTutors = [
            ['id' => 782905, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+20@rarejob.co.jp'],
            ['id' => 686783, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+21@rarejob.co.jp'],
            ['id' => 560462, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+22@rarejob.co.jp'],
            ['id' => 413382, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+23@rarejob.co.jp'],
            ['id' => 817045, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+24@rarejob.co.jp'],
            ['id' => 738140, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+25@rarejob.co.jp'],
            ['id' => 644487, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+26@rarejob.co.jp'],
            ['id' => 493787, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+27@rarejob.co.jp'],
            ['id' => 782896, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+28@rarejob.co.jp'],
            ['id' => 678951, "company" => 2, "contract_type" => "SL", "email" => 'kazuki.iwahori+29@rarejob.co.jp']
        ];

        // seed tutors
        $this->seedTutor($slRareJobTutors);
        $this->seedTutor($hlRareJobTutors);
        $this->seedTutor($slENPHTutors);

        // reset tutor password
        $this->resetTutorPassword($slRareJobTutors, $password);
        $this->resetTutorPassword($hlRareJobTutors, $password);
        $this->resetTutorPassword($slENPHTutors, $password);


        $rarejobEMSService = new RareJobEMSService();
        $rarejobTutorService = new RareJobTutorService();

        // Create RareJob tutors flex events
        foreach($slRareJobTutors as $slRareJobTutor){
            $result = $rarejobEMSService->createFlexRange($slRareJobTutor['id'], self::DATETIME_START_AT, self::DATETIME_END_AT, self::RAREJOB_PRODUCT_CODE);

            if (isset($result->errors)){
                echo $result->errors[0] . " on SL " . $slRareJobTutor['id'] . " tutor ID. \n";
            } else {
                echo "Flex range has been created for tutor ID: " . $slRareJobTutor['id'] . "\n";
            }
        }

        // Create ENPH tutors flex events
        foreach($slENPHTutors as $slENPHTutor){
            $result = $rarejobEMSService->createFlexRange($slENPHTutor['id'] , self::DATETIME_START_AT, self::DATETIME_END_AT, self::EU_PRODUCT_CODE, $slENPHTutor['id']);
            
            if (isset($result->errors)){
                echo $result->errors[0] . " on SL" . $slENPHTutor['id'] . " tutor ID. \n";
            } else {
                echo "Flex range has been created for tutor ID: " . $slENPHTutor['id'] . "\n";
            }
        }

        // Create RareJob tutors HL
        $dateStart = new DateTime(self::DATE_START);
        $dateEnd = new DateTime(self::DATE_END);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($dateStart, $interval, $dateEnd);

        foreach ($period as $dt) {

            $date = $dt->format(self::DATE_FORMAT);
           
            if (!$this->isWeekend($date)){

                $timeStart = new DateTime($date . " " . self::TIME_START);
                $timeEnd =  new DateTime($date . " " . self::TIME_END);

                $interval = DateInterval::createFromDateString('1 hour');
                $period = new DatePeriod($timeStart, $interval, $timeEnd);

                foreach($hlRareJobTutors as $hlRareJobTutor){
                
                    foreach ($period as $dt) {

                        // 00:00 - 00-25
                        $timeStart = $dt->format('Y-m-d') . "T" . $dt->format(self::TIME_FORMAT) . "Z";
                        $timeEnd =  $dt->format('Y-m-d') . "T" . date(self::TIME_FORMAT, strtotime($dt->format(self::TIME_FORMAT) . ' + 25 minutes')) . "Z";

                        $data =[
                            "start_time" => $timeStart,
                            "end_time" => $timeEnd
                        ];

                        $result = $rarejobEMSService->createHomeLessonEvent($data, $hlRareJobTutor['id'], self::RAREJOB_PRODUCT_CODE);
                        if(isset($result->errors)){
                            echo $result->errors[0] . " on HL" . $hlRareJobTutor['id'] . " tutor ID. \n";
                        } else {
                            echo "Normal event has been created tutor id: " . $hlRareJobTutor['id'] . " time: " . $timeStart . "-" . $timeEnd . "\n";
                        }

                         // 00:30 - 00-55
                        $timeStart = $dt->format('Y-m-d') . "T" . date(self::TIME_FORMAT, strtotime($dt->format(self::TIME_FORMAT) . ' + 30 minutes')) . "Z";
                        $timeEnd =  $dt->format('Y-m-d') . "T" .  date(self::TIME_FORMAT, strtotime($timeStart . ' + 25 minutes')) . "Z";
                        
                        $data =[
                            "start_time" => $timeStart,
                            "end_time" => $timeEnd
                        ];

                        $result = $rarejobEMSService->createHomeLessonEvent($data, $hlRareJobTutor['id'], self::RAREJOB_PRODUCT_CODE);
                        if(isset($result->errors)){
                            echo $result->errors[0] . " on HL" . $hlRareJobTutor['id'] . " tutor ID. \n";
                        } else {
                            echo "Normal event has been created tutor id: " . $hlRareJobTutor['id'] . " time: " . $timeStart . "-" . $timeEnd . "\n";
                        }
                    }
                }
            }
        }
    }

    protected function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }

    protected function resetTutorPassword($tutors, $password)
    {
        foreach($tutors as $tutor){
            
            $rarejobTutorService = new RareJobTutorService();
            
            $res = $rarejobTutorService->generateResetPasswordToken($tutor["email"]);

            if (isset($res->errors)){
                echo "Email " . $tutor["email"] . "Error:" . $res->errors[0] . "\n";
            } else {
                $res = $rarejobTutorService->resetPassword($res->token, $password);
                if (isset($res->errors)){
                    echo "Error:" . $res->errors[0] . "\n";
                } else {
                    echo "Password has been reset for " . $tutor["email"] . "\n";
                }
            }
        }
    }

    protected function seedTutor($tutors)
    {
        foreach($tutors as $tutor){
            \DB::table('tutors')->insert([
                ['rarejob_tutor_id' => $tutor['id'], 'name' => 'nickname', 'company' => $tutor['company'], 'contract_type' => $tutor['contract_type'],'created_at' => now()],
            ]);
        }
    }
}
