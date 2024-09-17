<?php

namespace App\Service\V1\Specialization;

use Exception;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\Specialization\FetchSpecialization;
use App\Http\Resources\Specialization\SingleSpecializationResource;
use App\Repository\V1\Specialization\SpecializationInterfaceRepository;

class SpecializationService implements SpecializationInterfaceService
{

    /**
     * @var SpecializationInterfaceRepository
     */
    public $specializationRepository;


    /**
     * SpecializationService constructor.
     * @param SpecializationInterfaceRepository $specializationRepository
     */

    public function __construct(
        SpecializationInterfaceRepository $specializationRepository,
    ) {
        $this->specializationRepository = $specializationRepository;
    }

    public function getASpecilization(int $specialization)
    {
        try {
            $specialization = $this->specializationRepository->getASpecilization($specialization);

            return [
                'specialization' => new SingleSpecializationResource($specialization),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {
            // Other exceptions
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
    /**
     * ftech specializations
     * @param $request
     * @return mixed spacializations
     */
    public function fetch()
    {
        try {
            //pass the data for query
            $specializations = $this->specializationRepository->fetch();

            return [
                'specializations' => FetchSpecialization::collection(collect($specializations)->unique('name')),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {
            // Other exceptions
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * store specialization
     * @param $request
     * @return mixed
     */
    public function store($request)
    {
        try {
            $cleanData = $request->validated();

            $specializationsData = [
                'name' => strtoupper($cleanData['name']),
            ];

            //pass the data for query
            $specializations = $this->specializationRepository->save($specializationsData);

            if (empty($specializations)) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'Specializations created successfully.',
                'specializations' => new FetchSpecialization($specializations),
                'status' => JsonResponse::HTTP_CREATED,
            ];
        } catch (ModelNotFoundException $e) {

            return [
                'message' => 'Specializations creation was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * updte specialization
     * @param $request
     * @return mixed
     */
    public function update($request)
    {
        try {
            $cleanData = $request->validated();
            $specializationsData = [
                'id' => $cleanData['specialization_id'],
                'name' => $cleanData['name'],
            ];

            $specializations = $this->specializationRepository->updateById($specializationsData);
            if (!$specializations) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'Specializations updated successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Specialisation update was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * delete specializations
     * @param $request
     * @return mixed
     */
    public function delete($request)
    {
        try {
            $cleanData = $request->validated();
            $specializations = $this->specializationRepository->deleteById($cleanData['specialization_id']);

            if (!$specializations) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'Specialisation deleted successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Department deleting was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
}
