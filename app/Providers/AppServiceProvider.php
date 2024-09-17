<?php

namespace App\Providers;

use App\Service\V1\Otp\OtpService;
use Illuminate\Support\Facades\Log;
use App\Service\V1\Auth\AuthService;
use App\Jobs\SystemUpNotificationJob;
use App\Service\V1\Users\UserService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use App\Models\V1\Department\Department;
use App\Repository\V1\Otp\OTPrepository;
use App\Service\V1\Client\ClientService;
use App\Service\V1\Skills\SkillsService;
use App\Service\V1\Reports\ReportService;
use App\Service\V1\Uploads\UploadService;
use App\Http\Response\Fetch\FetchResponse;
use App\Http\Response\Store\StoreResponse;
use App\Repository\V1\Auth\AuthRepository;
use App\Service\V1\Project\ProjectService;
use App\Service\V1\TimeOff\TimeOffService;
use App\Repository\V1\Users\UserRepository;
use App\Service\V1\Otp\OtpInterfaceService;
use App\Http\Response\Delete\DeleteResponse;
use App\Http\Response\Update\UpdateResponse;
use App\Service\V1\Employee\EmployeeService;
use App\Service\V1\Auth\AuthInterfaceService;
use App\Repository\V1\Client\ClientRepository;
use App\Repository\V1\Skills\SkillsRepository;
use App\Service\V1\UserGroup\UserGroupService;
use App\Service\V1\Users\UserInterfaceService;
use App\Models\V1\Specialization\Specialization;
use App\Repository\V1\Project\ProjectRepository;
use App\Repository\V1\TimeOff\TimeOffRepository;
use App\Service\V1\AutoAssign\AutoAssignService;
use App\Service\V1\Department\DepartmentService;
use App\Repository\V1\Otp\OTPRepositoryinterface;
use App\Service\V1\Client\ClientInterfaceService;
use App\Service\V1\Skills\SkillsInterfaceService;
use App\Repository\V1\Employee\EmployeeRepository;
use App\Service\V1\Uploads\UploadInterfaceService;
use App\Repository\V1\Auth\AuthInterfaceRepository;
use App\Service\V1\Project\ProjectInterfaceService;
use App\Service\V1\TimeOff\TimeOffServiceInterface;
use App\Repository\V1\UserGroup\UserGroupRepository;
use App\Repository\V1\Users\UserInterfaceRepository;
use App\Service\V1\Notification\NotificationService;
use App\Service\V1\TimeTracking\TimeTrackingService;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Service\V1\Employee\EmployeeInterfaceService;
use App\Repository\V1\Department\DepartmentRepository;
use App\Repository\V1\Client\ClientInterfaceRepository;
use App\Repository\V1\Skills\SkillsInterfaceRepository;
use App\Service\V1\UserGroup\UserGroupServiceInterface;
use App\Service\V1\Specialization\SpecializationService;
use App\Repository\V1\Project\ProjectInterfaceRepository;
use App\Repository\V1\TimeOff\TimeOffRepositoryInterface;
use App\Service\V1\AutoAssign\AutoAssignServiceInterface;
use App\Service\V1\Department\DepartmentInterfaceService;
use App\Service\V1\Reports\ReportServiceInterfaceService;
use App\Repository\V1\Notification\NotificationRepository;
use App\Repository\V1\TimeTracking\TimeTrackingRepository;
use App\Repository\V1\Employee\EmployeeInterfaceRepository;
use App\Repository\V1\UserGroup\UserGroupRepositoryInterface;
use App\Service\V1\Notification\NotificationInterfaceService;
use App\Service\V1\TimeTracking\TimeTrackingServiceInterface;
use App\Repository\V1\Specialization\SpecializationRepository;
use App\Repository\V1\Department\DepartmentInterfaceRepository;
use App\Service\V1\Specialization\SpecializationInterfaceService;
use App\Repository\V1\Notification\NotificationInterfaceRepository;
use App\Repository\V1\TimeTracking\TimeTrackingRepositoryInterface;
use App\Repository\V1\Specialization\SpecializationInterfaceRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //Bind the TimeTrackingRepositoryInterface to the TimeTrackingRepository
        $this->app->bind(TimeTrackingRepositoryInterface::class, TimeTrackingRepository::class);

        //Bind the TimeTrackingserviceInterface to the TimeTrackingservice
        $this->app->bind(TimeTrackingServiceInterface::class, TimeTrackingService::class);

        // Bind the AutoAssignServiceInterface to the AutoAssignService class
        $this->app->bind(AutoAssignServiceInterface::class, AutoAssignService::class);

        //Bind the UserGroupRepositoryInterface to the UserGroupRepository
        $this->app->bind(UserGroupRepositoryInterface::class, UserGroupRepository::class);

        //Bind the UserGroupServiceInterface to the UserGroupService
        $this->app->bind(UserGroupServiceInterface::class, UserGroupService::class);

        // Bind the TimeOffServiceInterface with the TimeOffService class.
        $this->app->bind(TimeOffServiceInterface::class, TimeOffService::class);

        // Bind the TimeOffRepositoryInterface with the TimeOffRepository class.
        $this->app->bind(TimeOffRepositoryInterface::class, TimeOffRepository::class);


        // Bind the UserInterfaceRepository interface to the UserRepository class.
        $this->app->bind(UserInterfaceRepository::class, UserRepository::class);

        // Bind the AuthInterfaceRepository interface to the AuthRepository class.
        $this->app->bind(AuthInterfaceRepository::class, AuthRepository::class);

        // Bind the EmployeeInterfaceRepository interface to the EmployeeRepository class.
        $this->app->bind(EmployeeInterfaceRepository::class, EmployeeRepository::class);

        // Bind the ClientInterfaceRepository interface to the ClientRepository class.
        $this->app->bind(ClientInterfaceRepository::class, ClientRepository::class);

        // Bind the ProjectInterfaceRepository interface to the ProjectRepository class.
        $this->app->bind(ProjectInterfaceRepository::class, ProjectRepository::class);

        // Bind the OTPRepositoryinterface interface to the OtpRepository class.
        $this->app->bind(OTPRepositoryinterface::class, OTPrepository::class);

        // Bind the DepartmentInterfaceRepository interface to the DepartmentRepository class.
        $this->app->bind(DepartmentInterfaceRepository::class, DepartmentRepository::class);

        // Bind the SpecializationInterfaceRepository interface to the SpecializationRepository class.
        $this->app->bind(SpecializationInterfaceRepository::class, SpecializationRepository::class);

        // Bind the NotificationInterfaceRepository interface to the NotificationRepository class.
        $this->app->bind(NotificationInterfaceRepository::class, NotificationRepository::class);

        // Bind the NotificationInterfaceService interface to the NotificationService class.
        $this->app->bind(NotificationInterfaceService::class, NotificationService::class);

        // Bind the SkillsInterfaceService interface to the SkillsService class.
        $this->app->bind(SkillsInterfaceService::class, SkillsService::class);

        $this->app->bind(SkillsInterfaceRepository::class, SkillsRepository::class);

        // Bind the UploadInterfaceService interface to the UserArchiveService class.
        $this->app->bind(UploadInterfaceService::class, UploadService::class);

        //bind the
        $this->app->bind(ProjectInterfaceService::class, ProjectService::class);
        // Inside the register method of the service provider
        $this->app->bind(UserInterfaceService::class, UserService::class);
        // bind authServiceInterface to authService
        $this->app->bind(AuthInterfaceService::class, AuthService::class);
        // bind employeeInterface to employeeService
        $this->app->bind(EmployeeInterfaceService::class, EmployeeService::class);
        //bind ClientInterfaceService to clientService
        $this->app->bind(ClientInterfaceService::class, ClientService::class);
        //bind DepartmentInterfaceService to departmentService
        $this->app->bind(DepartmentInterfaceService::class, DepartmentService::class);
        //bind specializationInterfaceService to specializationService
        $this->app->bind(SpecializationInterfaceService::class, SpecializationService::class);

        // Bind the OtpService to the container
        $this->app->bind(OtpInterfaceService::class,OtpService::class);

        // Bind the ReportServiceInterfaceService interface to the ReportService class.
        $this->app->bind(ReportServiceInterfaceService::class, ReportService::class);


        // Bind FetchResponse class to the service container
        $this->app->bind(FetchResponse::class, function ($app) {
            return new FetchResponse();
        });

        // Bind StoreResponse class to the service container
        $this->app->bind(StoreResponse::class, function ($app) {
            return new StoreResponse();
        });

        // Bind DeleteResponse class to the service container
        $this->app->bind(DeleteResponse::class, function ($app) {
            return new DeleteResponse();
        });

        // Bind UpdateResponse class to the service container
        $this->app->bind(UpdateResponse::class, function ($app) {
            return new UpdateResponse();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'specialization' => Specialization::class,
            'department' => Department::class,
        ]);

        // Dispatch the job
        // dispatch(new SystemUpNotificationJob(new NotificationService(
        //     new NotificationRepository(),
        //     new EmployeeRepository()
        // )));
    }
}
