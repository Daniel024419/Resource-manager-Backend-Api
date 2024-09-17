<?php

namespace Tests\Unit\Repository\Notifications;

use App\Models\V1\User\User;
use PHPUnit\Framework\TestCase;
use App\Models\V1\Employee\Employee;
use App\Models\V1\Notification\Notification;
use App\Repository\V1\Employee\EmployeeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repository\V1\Notification\NotificationRepository;

class NotificationRepositoryUnitTest extends \Tests\TestCase
{
    use RefreshDatabase;

    protected $notificationRepository;
    protected $employeeRepository;


    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationRepository = new NotificationRepository();
        $this->employeeRepository = new EmployeeRepository();
    }

    public function testsave()
    {
        $employee = Employee::factory()->create();

        $notificationData = [
            'message' => 'Test message sent',
            'by' => $employee->id,
            'employee_id' => $employee->id,
        ];

        $result = $this->notificationRepository->save($notificationData);
        $this->assertTrue($result);
    }

    public function testfetch()
    {
        // Assuming there are notifications in the database
        $result = $this->notificationRepository->fetch();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function testfetchByEmployeeId()
    {
        $employee = Employee::factory()->create();

        // Assuming there are notifications in the database for the given employee
        $result = $this->notificationRepository->fetchByEmployeeId($employee->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function testMarkAllAsRead()
    {
        $employee = Employee::factory()->create();

        // Create unread notifications for the given employee
        Notification::create([
            'message' => 'Test message sent',
            'by' => $employee->id,
            'employee_id' => $employee->id,
        ]);

        $result = $this->notificationRepository->markAllAsRead($employee->id);

        $this->assertTrue($result);
    }

    public function testMarkOneAsRead()
    {
        $employee = Employee::factory()->create();

        $notification = Notification::create([
            'message' => 'Test message sent',
            'by' => $employee->id,
            'employee_id' => $employee->id,
        ]);

        $result = $this->notificationRepository->markOneAsRead($notification->id);

        $this->assertTrue($result);
    }
}