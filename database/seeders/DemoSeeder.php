<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\V1\Department\UserDepartment;
use App\Models\V1\Employee\Employee;
use App\Models\V1\Holiday\Holiday;
use App\Models\V1\Notification\Notification;
use App\Models\V1\Project\EmployeeProject;
use App\Models\V1\Project\Project;
use App\Models\V1\Skill\Skill;
use App\Models\V1\Specialization\Specialization;
use App\Models\V1\Specialization\UserSpecialization;
use App\Models\V1\TimeOff\TimeOffRequests;
use App\Models\V1\TimeOff\TimeOffType;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DemoSeeder extends Seeder
{
    /**
     * The Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->faker = Factory::create();

        $this->createEmployees();
        $this->createProjects();
        $this->timeOffRequest();
        $this->seedHoliday();
    }

    private function seedHoliday()
    {
        $countries = [
            'Africa/Accra' => 'GH',
            'Europe/Berlin' => 'DE',
            'Africa/Kigali' => 'RW'
        ];

        $currentYear =  date('Y');

        foreach ($countries as $timezone => $countryCode) {
            $response = Http::get(env('HOLIDAY_ACCESS_API_URL'), [
                'api_key' => 'XQLurAzlqnyIQ7X3sqzihUCDGhNUTsZr',
                'country' => $countryCode,
                'year' => $currentYear,
            ]);

            $holidays = $response->json('response.holidays');

            if (!empty($holidays)) {
                foreach ($holidays as $holiday) {
                    Holiday::firstOrcreate([
                        'holiday' => $holiday['name'],
                        'date' => $holiday['date']['iso'],
                        'timeZone' => $timezone,
                    ]);
                }
            }
        }
    }
    private function createEmployees()
    {
        $this->faker = Factory::create();

        $roleIds = [
            Roles::getRoleIdByValue(Roles::BU->value),
            Roles::getRoleIdByValue(Roles::MGT->value)
        ];

        foreach (range(1, 100) as $index) {
            $employee = Employee::factory()->create();
            $randomRoleId = Arr::random($roleIds);
            $randomEmployee = Employee::where('roleId', $randomRoleId)->inRandomOrder()->first();

            $employee->update([
                'roleId' => $randomRoleId,
                'addedBy' => $randomEmployee ? $randomEmployee->id : null
            ]);

            UserDepartment::factory()->create(['employee_id' => $employee->id]);
            $spec = UserSpecialization::create([
                'employee_id' => $employee->id,
                'specialization_id' => Specialization::all()->random()->id,
            ]);

            $this->skills(ucwords($spec->specializationInfo->name), $employee->id);
        }
    }

    public function skills($name, $employeeId)
    {
        $skillsList = [
            'Frontend Developer' => [
                'HTML5',
                'CSS3',
                'JavaScript',
                'React.js',
                'Vue.js',
                'AngularJS',
                'Responsive Design',
                'CSS Preprocessors (Sass/Less)',
                'Version Control (Git)',
                'Web Performance Optimization'
            ],
            'Backend Developer' => [
                'PHP',
                'Python',
                'Node.js',
                'Ruby on Rails',
                'Express.js',
                'Django',
                'Laravel',
                'Spring Boot',
                'RESTful APIs',
                'Database Management (MySQL/PostgreSQL/MongoDB)'
            ],
            'UI/UX Designer' => [
                'Adobe Photoshop',
                'Adobe Illustrator',
                'Sketch',
                'Figma',
                'User Research',
                'Wireframing',
                'Prototyping',
                'Interaction Design',
                'Visual Design',
                'Design Thinking'
            ],
            'DevOps' => [
                'Docker',
                'Kubernetes',
                'Jenkins',
                'Ansible',
                'Terraform',
                'Continuous Integration/Continuous Deployment (CI/CD)',
                'Monitoring (Prometheus, Grafana)',
                'Logging (ELK Stack)',
                'Infrastructure as Code (IaC)',
                'Cloud Platforms (AWS, Azure, GCP)'
            ],
            'Data Scientist' => [
                'Python (NumPy, Pandas)',
                'R Programming',
                'Machine Learning Algorithms',
                'Data Visualization (Matplotlib, Seaborn)',
                'Statistical Analysis',
                'Deep Learning (TensorFlow, Keras)',
                'Natural Language Processing (NLP)',
                'Big Data Technologies (Hadoop, Spark)',
                'Data Mining',
                'Time Series Analysis'
            ],
            'Software Tester' => [
                'Test Planning',
                'Test Automation (Selenium, Appium)',
                'Test Case Design',
                'Manual Testing',
                'Regression Testing',
                'Performance Testing',
                'Load Testing',
                'Bug Tracking Tools (Jira, Bugzilla)',
                'Agile Testing Methodologies',
                'Continuous Testing'
            ]
        ];

        $lowercaseName = strtolower($name);
        $selectedSkills = [];
        foreach ($skillsList as $skillCategory => $skills) {
            if (strtolower($skillCategory) === $lowercaseName) {
                $numSkills = rand(1, 4);
                $selectedSkills = Arr::random($skills, $numSkills);

                foreach ($selectedSkills as $skill) {
                    Skill::factory()->create([
                        'employee_id' => $employeeId,
                        'name' => $skill
                    ]);
                }
            }
        }
    }

    private function createProjects()
    {
        $employees = Employee::where('roleId', '!=', Roles::getRoleIdByValue(Roles::ADMIN->value))
            ->orWhere('roleId', Roles::getRoleIdByValue(Roles::MGT->value))
            ->get();

        $projectNames = [
            'TVET Project',
            'Icon',
            'ARMS',
            'ARMS ERP',
            'DevOps Prototyping',
            'DS Internal Project',
            'Learning Management System',
            'Nexum Scaleup',
            'Booking Platform',
            'Image Detector',
            'Tourist Site',
            'Tracer Studies',
            'Research & Development',
            'Resource Manager',
            'Virtual Reality',
            'Transportation Solution',
            'Healthcare System',
            'Analytics Dashboard',
            'Chatbot Platform',
            'Productivity Suite',
            'Agriculture Monitor',
            'Supply Chain Tracker',
            'Translation App',
            'Training Platform',
            'Energy Optimization',
            'Fitness Assistant',
            'Social Media Tool',
            'Marketplace',
            'Health Support',
            'Project Management',
            'Event Planner',
            'Budget Tracker',
            'Recipe App',
            'Task Scheduler',
            'Inventory Manager',
            'Weather Forecast',
            'Document Scanner',
            'Expense Tracker',
            'Language Learning',
            'Music Player',
            'Time Tracker',
            'Password Manager',
            'Calendar App',
            'To-Do List',
            'Note-Taking App',
            'Countdown Timer',
            'QR Code Generator',
            'Flashcard App'
        ];

        foreach ($projectNames as $projectName) {
            $startDate = $this->faker->dateTimeBetween('-1 year', '+1 year');
            $endDate = Carbon::instance($startDate)->add($this->faker->randomElement([
                '+1 month', '+1 week', '+3 months', '+4 months', '+5 months'
            ]));

            $project = Project::factory()->create([
                'name' => $projectName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'createdBy' => $employees->random()->id,
            ]);

            $this->assignEmployeesToProject($employees, $project);
        }
    }



    private function assignEmployeesToProject($employees, $project)
    {
        $projectStartDate = Carbon::parse($project->startDate);
        $projectEndDate = Carbon::parse($project->endDate);

        $employees->shuffle()->take(rand(3, 7))->each(function ($employee) use ($project, $projectStartDate, $projectEndDate) {
            $existingProjectHours = $employee->employeeProjects()->sum('workHours');
            $remainingHours = 8 - $existingProjectHours;

            if ($existingProjectHours < 8) {
                $workHours = min(rand(1, 8), $remainingHours);
                $timestamp = $this->generateRandomTimestamp($projectStartDate, $projectEndDate);

                EmployeeProject::create([
                    'project_id' => $project->id,
                    'employee_id' => $employee->id,
                    'workHours' => $workHours,
                    'created_at' => $timestamp,
                ]);
            }
        });

        $this->deleteExpiredProjects();
    }

    private function generateRandomTimestamp($startDate, $endDate)
    {
        $startTimestamp = $startDate->timestamp;
        $endTimestamp = $endDate->timestamp;

        $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);

        return Carbon::createFromTimestamp($randomTimestamp);
    }



    private function deleteExpiredProjects()
    {
        $expiredProjects = Project::whereDate('endDate', '<', Carbon::today())->get();

        foreach ($expiredProjects as $project) {
            // Delete the project
            $project->delete();

            // Delete related employee projects
            $project->employeeProjects()->delete();
        }
    }



    public function timeOffRequest()
    {
        $timeOffs = TimeOffType::all();
        $proofs = ['public/leave/proof/xJh5ShYEyIbqhg4h2RLReosHUbwDom4VNzggWERb.png', 'public/leave/proof/tGebxczXe6TysBhJvfYA72zEI3StfCWmIZ5GxNTQ.jpg'];

        $employees = Employee::whereIn('roleId', [
            Roles::getRoleIdByValue(Roles::BU->value),
            Roles::getRoleIdByValue(Roles::MGT->value)
        ])->get();

        $notifications = [];

        foreach (range(1, 20) as $index) {
            $employee = $employees->random();
            $startDate = $this->faker->dateTimeBetween('-24 hours', '+1 month');
            $endDate = $this->faker->dateTimeBetween($startDate, '+1 month');

            $timeOffType = $timeOffs->random();

            $request = new TimeOffRequests();
            $request->userId = $employee['id'];
            $request->startDate = $startDate;
            $request->type = $timeOffType->id;

            if ($timeOffType->showProof) {
                $proofPath = Arr::random($proofs);
                $request->proof = $proofPath;
            }

            if ($timeOffType->duration) {
                $endDate = clone $startDate;
                $request->endDate = $endDate;
            } else {
                $request->endDate = $endDate;
            }

            $request->details = $this->generateDetailsSentence($employee, $timeOffType, $startDate, $endDate);

            $addedBy = $employee['addedBy'] ?? 1;
            $notifications[] = [
                'message' => 'Requested for a leave',
                'by' => $employee['id'],
                'employee_id' => $addedBy,
                'created_at' => Carbon::now()
            ];

            $request->save();
        }

        Notification::insert($notifications);
    }

    private function generateDetailsSentence($employee, $timeOffType, $startDate, $endDate)
    {
        $sentences = [
            "Employee {$employee->firstName} is requesting {$timeOffType->name} starting from {$startDate->format('Y-m-d H:i')} to {$endDate->format('Y-m-d H:i')}.",
            "For {$timeOffType->name}, {$employee->name} needs time off from {$startDate->format('Y-m-d H:i')} to {$endDate->format('Y-m-d H:i')}.",
            "Starting {$startDate->format('Y-m-d H:i')}, {$employee->name} will be on {$timeOffType->name} until {$endDate->format('Y-m-d H:i')}.",
        ];

        return $this->faker->randomElement($sentences);
    }
}