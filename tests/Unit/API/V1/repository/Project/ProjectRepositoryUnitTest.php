<?php

namespace Tests\Unit\API\V1\repository\Project;

use App\Models\V1\Employee\Employee;
use App\Models\V1\Project\EmployeeProject;
use App\Models\V1\Project\Project;
use App\Repository\V1\Project\ProjectRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRepositoryUnitTest extends TestCase
{
    use RefreshDatabase;

    private $projectRepository;
    // private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRepository = new ProjectRepository();
    }


    // Test for saving a new project
    public function testsave()
    {
        // Create project data using the factory
        $projectData = Project::factory()->make()->toArray();

        // Call the project repository method to save the new project
        $result = $this->projectRepository->save($projectData);

        // Assertions to check if the project is saved correctly
        $this->assertInstanceOf(Project::class, $result);
        $this->assertEquals($projectData['name'], $result->name);
        $this->assertEquals($projectData['projectCode'], $result->projectCode);
    }

    // Test for fetching all projects
    public function testfetchs()
    {
        $project1 = $this->createProject();
        $project2 = $this->createProject();

        // Call the project repository method to fetch all projects
        $result = $this->projectRepository->fetch('');

        // Assertions to check if all projects are fetched
        $this->assertContains($project1->id, $result->pluck('id'));
        $this->assertContains($project2->id, $result->pluck('id'));
    }

    // Test for deleting a project by project ID
    public function testdeleteByProjectId()
    {
        // Create a project
        $project = $this->createProject();

        // Call the project repository method to delete the project by ID
        $result = $this->projectRepository->deleteByProjectId($project->projectId);

        // Assertions to check if the project is deleted correctly
        $this->assertTrue($result);
        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        // Test with non-existing project ID
        $resultNonExisting = $this->projectRepository->deleteByProjectId($project->projectId);
        $this->assertTrue($resultNonExisting);
    }

    // Test for finding a project by project ID
    public function testfindByProjectId()
    {
        // Create a project
        $project = $this->createProject();

        // Call the project repository method to find the project by ID
        $result = $this->projectRepository->findByProjectId($project->projectId);

        // Assertions to check if the project is found correctly
        $this->assertContains($project->name, $result->pluck('name'));
    }

    // Test for finding a project by project auth ID
    public function testfindByAuthId()
    {
        // Create a project
        $project = $this->createProject();

        // Create a dummy employee project associated with the project
        $dummyEmployeeProject = EmployeeProject::factory()->create([
            'project_id' => $project->id,
        ]);

        // Call the project repository method to find the project by auth ID
        $result = $this->projectRepository->findByAuthId($project->id);

        // Assertions to check if the employee project is found correctly
        $this->assertInstanceOf(EmployeeProject::class, $result);
        $this->assertEquals($dummyEmployeeProject->id, $result->id);
    }

    // Test for finding a project by project name
    public function testfindByProjectName()
    {
        // Create a project
        $project = $this->createProject();

        // Call the project repository method to find the project by name
        $result = $this->projectRepository->findByProjectName($project->name);

        // Assertions to check if the project is found correctly
        $this->assertContains($project->id, $result->pluck('id'));
    }

    // Test for updating a project by project ID
    public function testupdateByProjectId()
    {
        // Create a project
        $project = $this->createProject();
        $projectId = $project->projectId;

        // Updated data
        $updatedData = [
            'name' => 'Winter arms',
        ];

        // Call the project repository method to update the project
        $result = $this->projectRepository->updateByProjectId(array_merge($updatedData, ['projectId' => $projectId]));

        // Assertions to check if the project is updated correctly
        $this->assertInstanceOf(Project::class, $result);
        $this->assertEquals($updatedData['name'], $result->name);
        $this->assertDatabaseHas('projects', ['projectId' => $projectId, 'name' => $updatedData['name']]);
    }

    // Test for assigning a project to users
    public function testassign()
    {
        // Create a project and employees
        $project = $this->createProject();
        $employees = Employee::factory()->create();
        $userIds = $employees->pluck('userId')->toArray();

        // Call the project repository method to assign the project to users
        $result = $this->projectRepository->assign($userIds, $project->projectId);

        // Assertions to check if the assignment is successful
        $this->assertTrue($result['success']);

        // Check if users are assigned to the project in the database
        foreach ($userIds as $userId) {
            $employee = Employee::where('userId', $userId)->first();
            $this->assertDatabaseHas('employee_projects', [
                'project_id' => $project->id,
                'employee_id' => $employee->id,
            ]);
        }
    }

    // Test for assigning a project to users with a non-existing user
    public function testassignWithNotFoundUser()
    {
        // Create a project
        $project = $this->createProject();

        // User IDs with a non-existing user
        $userIds = [999];

        // Call the project repository method to assign the project to users
        $result = $this->projectRepository->assign($userIds, $project->projectId);

        // Assertions to check if the assignment is not successful
        $this->assertFalse($result['success']);
        $this->assertEquals('NotFoundException', $result['type']);
    }

    // Test for unassigning a project from users
    public function testunAssign()
    {
        // Create a project and an employee
        $project = $this->createProject();
        $employee = Employee::factory()->create();

        // Assign the project to users
        $assignResult = $this->projectRepository->assign([$employee->id], $project->projectId);
        $this->assertTrue($assignResult['success']);

        // Call the project repository method to unassign the project from users
        $unassignResult = $this->projectRepository->unAssign([$employee->id], $project->projectId);

        // Assertions to check if the unassignment is not successful
        $this->assertTrue($unassignResult['success']);

        // Additional test with a non-existing project
        $nonExistingProjectId = 'non-existing-project-id';
        $unassignNonExistingProjectResult = $this->projectRepository->unAssign([$employee->id], $nonExistingProjectId);

        // Assertions to check if the unassignment is not successful
        $this->assertFalse($unassignNonExistingProjectResult['success']);
        $this->assertEquals('NotFoundException', $unassignNonExistingProjectResult['type']);
    }


    // Test for fetching archived projects
    public function testfetchArchives()
    {
        // Create archived projects
        $archivedProject1 = $this->createProject();
        $archivedProject2 = $this->createProject();

        // Delete the projects (archive them)
        $archivedProject1->delete();
        $archivedProject2->delete();

        // Call the project repository method to fetch archived projects
        $result = $this->projectRepository->fetchArchives();

        // Assertions to check if the archived projects are fetched successfully
        $this->assertNotNull($result);
        $this->assertCount(2, $result);
        $this->assertContains($archivedProject1->id, $result->pluck('id'));
        $this->assertContains($archivedProject2->id, $result->pluck('id'));
    }

    // Test for archiving a project
    public function testArchiveProject()
    {
        // Create a project
        $project = $this->createProject();

        // Call the project repository method to archive the project
        $result = $this->projectRepository->archiveProject($project->projectId);

        // Assertions to check if the project is archived successfully
        $this->assertTrue($result);

        // Check if the project is soft-deleted in the database
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    // Test for removing project archive
    public function testdeleteArchive()
    {
        // Create a project
        $project = $this->createProject();
        $projectId = $project->projectId;

        // Archive the project
        $this->projectRepository->archiveProject($projectId);

        // Call the project repository method to remove the project archive
        $response = $this->projectRepository->deleteArchive($projectId);

        // Assertions to check if the project archive is removed successfully
        $this->assertTrue($response);
    }

    // Test for deleting archived project
    public function testDeleteArchivedProject()
    {
        // Create a project
        $project = $this->createProject();

        // Soft-delete the project
        $project->delete();

        // Call the project repository method to delete the archived project permanently
        $response = $this->projectRepository->deleteArchive($project->projectId);

        // Assertion to check if the archived project is deleted successfully
        $this->assertTrue($response);
    }

    // Test for searching archived projects by name or code
    public function testsearchByNameOrCode()
    {
        // Create an archived project
        $archivedProject = $this->createProject();

        // Call the project repository method to search archived projects by name or code
        $nameSearchResultsUnTrash = $this->projectRepository->searchByNameOrCode($archivedProject->name);
        $codeSearchResultsWithoutTrash = $this->projectRepository->searchByNameOrCode($archivedProject->projectCode);

        // Assertions for unarchived project search
        $this->assertCount(1, $nameSearchResultsUnTrash);
        $this->assertCount(1, $codeSearchResultsWithoutTrash);
        $this->assertEquals($archivedProject->id, $nameSearchResultsUnTrash[0]->id);

        // Archive the project
        $archivedProject->delete();

        // Call the project repository method to search archived projects by name or code
        $nameSearchResultsWithTrash = $this->projectRepository->searchByNameOrCode($archivedProject->name);
        $codeSearchResultsWithTrash = $this->projectRepository->searchByNameOrCode($archivedProject->projectCode);

        // Assertions for archived project search
        $this->assertCount(1, $nameSearchResultsWithTrash);
        $this->assertCount(1, $codeSearchResultsWithTrash);
        $this->assertEquals($archivedProject->id, $nameSearchResultsWithTrash[0]->id);

        // Search for a non-existing project
        $nonExistentSearchResults = $this->projectRepository->searchByNameOrCode('somethingWhichNoDey');

        // Assertion to check if no results are returned for a non-existing project
        $this->assertCount(0, $nonExistentSearchResults);
    }

    // Test for finding an archived project by project ID
    public function testfindArchiveByProjectId()
    {
        // Create an archived project
        $archivedProject = $this->createProject();

        // Archive the project
        $archivedProject->delete();

        // Call the project repository method to find an archived project by project ID
        $foundArchivedProject = $this->projectRepository->findArchiveByProjectId($archivedProject->projectId);
        $nonExistentProject = $this->projectRepository->findArchiveByProjectId('somethingWhichNoDey');

        // Assertions for finding an archived project
        $this->assertInstanceOf(Project::class, $foundArchivedProject);
        $this->assertEquals($archivedProject->projectId, $foundArchivedProject->projectId);

        // Assertion for a non-existing project
        $this->assertNull($nonExistentProject);
    }



    // Helper function to create a project
    private function createProject(): Project
    {
        return Project::factory()->create();
    }
}