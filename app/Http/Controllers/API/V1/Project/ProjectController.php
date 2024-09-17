<?php

namespace App\Http\Controllers\API\V1\Project;


use Exception;
use RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Response\Fetch\FetchResponse;
use App\Http\Response\Store\StoreResponse;
use App\Service\V1\Project\ProjectInterfaceService;
use App\Http\Response\Delete\DeleteResponse;
use App\Http\Response\Update\UpdateResponse;
use App\Http\Requests\Project\ProjectIdRequest;
use App\Http\Requests\Project\ProjectNameRequest;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\ProjectAssignRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\ProjectTimelineUpdateRequest;
use App\Http\Requests\Project\EditProjectScheduleRequest;

class ProjectController extends Controller
{
    protected $projectService, $deleteResponseHandler, $updateResponseHandler,
        $storeResponseHandler, $fetchResponseHandler, $projectArchiveService;

    public function __construct(
        ProjectInterfaceService $projectService,
        StoreResponse $storeResponseHandler,
        DeleteResponse $deleteResponseHandler,
        UpdateResponse $updateResponseHandler,
        FetchResponse $fetchResponseHandler,
    ) {
        $this->projectService = $projectService;
        $this->storeResponseHandler = $storeResponseHandler;
        $this->deleteResponseHandler = $deleteResponseHandler;
        $this->updateResponseHandler = $updateResponseHandler;
        $this->fetchResponseHandler = $fetchResponseHandler;
    }

    public function userAssignedProject()
    {
        try {
            // Save the new Project using the service
            $storeResponse = $this->projectService->projectsAssigned();
            return $this->storeResponseHandler->handleStoreResponse($storeResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * store proeject info from frontend
     * @param Request $request
     * @var $request
     * @return JsonResponse
     */
    public function store(CreateProjectRequest $request): JsonResponse
    {
        try {
            // Save the new Project using the service
            $storeResponse = $this->projectService->save($request);
            return $this->storeResponseHandler->handleStoreResponse($storeResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * get all projects
     * @param Request $request
     * @var $request
     * @method get
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        try {
            $query = $request->query('query');
            $fetchResponse = $this->projectService->fetch($query);
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * get all projects
     * @param Request $request
     * @var $request
     * @return JsonResponse
     * @method put
     */
    public function update(UpdateProjectRequest $request): JsonResponse
    {
        try {
            $storeResponse = $this->projectService->update($request);
            return $this->updateResponseHandler->handleUpdateResponse($storeResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * delete projects
     * @param ProjectIdRequest $request ,$projectId
     * @var array < $request > , string $projectId
     * @return JsonResponse
     * @method delete
     */
    public function delete(ProjectIdRequest $request): JsonResponse
    {
        try {
            $deleteResponse = $this->projectService->delete($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * search projects by name
     * @param ProjectNameRequest $request
     * @var $request , $projectId
     * @return JsonResponse
     * @method post
     */
    public function search(ProjectNameRequest $request): JsonResponse
    {
        try {
            $fetchResponse = $this->projectService->search($request);
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * search project by id
     * @param Request $request
     * @var $request ,$ProjectId
     * @return JsonResponse
     */
    public function findById(ProjectIdRequest $request): JsonResponse
    {
        try {
            $fetchResponse = $this->projectService->findByProjectId($request);
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**asign users to project
     * @method post
     * @param Request $ReojectAssigmentRequest
     */

    public function assign(ProjectAssignRequest  $request): JsonResponse
    {
        try {
            $assignResponse =  $this->projectService->assign($request);
            return $this->storeResponseHandler->handleStoreResponse($assignResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**unAssign users from project
     * @method post
     * @param Request $ProjectRequest
     */
    public function unAssign(ProjectIdRequest  $request): JsonResponse
    {
        try {
            $assignResponse =  $this->projectService->unAssign($request);
            return $this->storeResponseHandler->handleStoreResponse($assignResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * get all employee projectsby auth
     * @var $request
     * @method get
     * @return JsonResponse
     */
    public function employeeProject(): JsonResponse
    {
        try {
            $fetchResponse = $this->projectService->employeeProject();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**schedule edit users from project
     * @method post
     * @param Request $ProjectRequest
     */
    public function scheduleEdit(EditProjectScheduleRequest  $request): JsonResponse
    {
        try {
            $editResponse =  $this->projectService->scheduleEdit($request);
            return $this->updateResponseHandler->handleUpdateResponse($editResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * fetch all project archives
     * @method postget
     * @param
     */
    public function archivesFetch(): JsonResponse
    {
        try {
            $fetchResponse =  $this->projectService->archivesFetch();
            // handle fetch response
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * unarchive project
     * @param ProjectIdRequest $request
     * @var $request
     */
    public function archivesRestore(ProjectIdRequest $request)
    {
        try {
            $sendResponse = $this->projectService->archivesRestore($request);
            return $this->deleteResponseHandler->handleDeleteResponse($sendResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * delete archived projects
     * @param ProjectIdRequest $request
     * @var $request
     * @return JsonResponse
     */
    public function archivesDelete(ProjectIdRequest $request): JsonResponse
    {
        try {
            $deleteResponse = $this->projectService->archivesDelete($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * search archived projects
     * @param ProjectNameRequest $request
     * @var $request , $ProjectId
     * @return JsonResponse
     */
    public function archiveseSarch(ProjectNameRequest $request): JsonResponse
    {
        try {
            $searchResponse = $this->projectService->archiveseSarch($request);
            return $this->fetchResponseHandler->handlefetchResponse($searchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * extend projects timelines
     * @param ProjectTimelineUpdateRequest $request
     * @var $request
     * @return JsonResponse
     * @method put
     */
    public function extendTimeLine(ProjectTimelineUpdateRequest $request): JsonResponse
    {

        try {
            $storeResponse = $this->projectService->extendTimeLine($request);
            return $this->updateResponseHandler->handleUpdateResponse($storeResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * get all project extentions
     * @param Request $request
     * @var $request
     * @method get
     * @return JsonResponse
     */
    public function extentions(Request $request): JsonResponse
    {
        try {
            $query = $request->query('query');
            $fetchResponse = $this->projectService->extensions($query);
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}