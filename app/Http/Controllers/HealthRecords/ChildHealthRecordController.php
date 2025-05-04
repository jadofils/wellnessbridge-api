<?php

namespace App\Http\Controllers\HealthRecords;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChildHealthRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Child Health Records",
 *     description="Endpoints for managing child health records"
 * )
 */
class ChildHealthRecordController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/child-health-records",
     *     tags={"Child Health Records"},
     *     summary="Get paginated list of child health records",
     *     description="Retrieve child health records with pagination.",
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", description="Number of items per page", required=false, @OA\Schema(type="integer", default=10)),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object",
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *     )),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->per_page ?? 10;
        $records = ChildHealthRecord::paginate($perPage);
          

        return response()->json([
            'success' => true,
            'data' => $records
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/child-health-records/{recordID}",
     *     tags={"Child Health Records"},
     *     summary="Get child health record by ID",
     *     description="Retrieve a specific child health record.",
     *     @OA\Parameter(name="recordID", in="path", description="Record ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Record not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show($recordID): JsonResponse
    {
        $record = ChildHealthRecord::find($recordID)
            ->with(['child', 'healthWorker'])
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $record
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/child-health-records",
     *     tags={"Child Health Records"},
     *     summary="Create a new child health record",
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="childID", type="integer", example=1),
     *         @OA\Property(property="healthWorkerID", type="integer", example=10),
     *         @OA\Property(property="checkupDate", type="string", format="date", example="2025-05-04"),
     *         @OA\Property(property="height", type="float", example=120.5),
     *         @OA\Property(property="weight", type="float", example=30.2),
     *         @OA\Property(property="vaccination", type="string", example="Polio"),
     *         @OA\Property(property="diagnosis", type="string", example="Healthy"),
     *         @OA\Property(property="treatment", type="string", example="None")
     *     )),
     *     @OA\Response(response=201, description="Record created successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'childID' => 'required|integer|exists:children,childID',
            'healthWorkerID' => 'required|integer|exists:health_workers,hwID',
            'checkupDate' => 'required|date',
            'height' => 'required|numeric|min:10|max:250',
            'weight' => 'required|numeric|min:1|max:100',
            'vaccination' => 'required|string|max:255',
            'diagnosis' => 'required|string|max:500',
            'treatment' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        try {
            // Check if the child exists
            $child = \App\Models\Child::find($request->childID);

            if (!$child) {
                
                return response()->json(['success' => false, 'message' => 'Child not found.'], 404);
            }
            $record = ChildHealthRecord::create($request->all())
                ->with(['child', 'healthWorker'])
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Record created successfully.',
                'data' => $record
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/child-health-records/{recordID}",
     *     tags={"Child Health Records"},
     *     summary="Delete a child health record",
     *     @OA\Parameter(name="recordID", in="path", description="Record ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Record deleted successfully"),
     *     @OA\Response(response=404, description="Record not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy($recordID): JsonResponse
    {
        $record = ChildHealthRecord::find($recordID);

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        try {
            $record->delete();

            return response()->json(['success' => true, 'message' => 'Record deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }
/**
 * @OA\Tag(
 *     name="Child Health Records",
 *     description="Endpoints for managing child health records"
 * )
 */

    /**
     * @OA\Get(
     *     path="/api/v1/child-health-records/by-child/{childID}",
     *     tags={"Child Health Records"},
     *     summary="Get all health records for a specific child",
     *     @OA\Parameter(name="childID", in="path", description="Child ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Records not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getByChildID($childID): JsonResponse
    {
        $records = ChildHealthRecord::where('childID', $childID)->get()
            ->with(['child', 'healthWorker'])
            ->first();

        if ($records->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No health records found for this child.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $records
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/child-health-records/by-child/{childID}",
     *     tags={"Child Health Records"},
     *     summary="Add a health record for a specific child",
     *     @OA\Parameter(name="childID", in="path", description="Child ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="healthWorkerID", type="integer", example=10),
     *         @OA\Property(property="checkupDate", type="string", format="date", example="2025-05-04"),
     *         @OA\Property(property="height", type="float", example=120.5),
     *         @OA\Property(property="weight", type="float", example=30.2),
     *         @OA\Property(property="vaccination", type="string", example="Polio"),
     *         @OA\Property(property="diagnosis", type="string", example="Healthy"),
     *         @OA\Property(property="treatment", type="string", example="None")
     *     )),
     *     @OA\Response(response=201, description="Record added successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function addByChildID(Request $request, $childID): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'healthWorkerID' => 'required|integer|exists:health_workers,hwID',
            'checkupDate' => 'required|date',
            'height' => 'required|numeric|min:10|max:250',
            'weight' => 'required|numeric|min:1|max:100',
            'vaccination' => 'required|string|max:255',
            'diagnosis' => 'required|string|max:500',
            'treatment' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        try {
            // Check if the child exists
            $child = \App\Models\Child::find($childID);
            if (!$child) {
                return response()->json(['success' => false, 'message' => 'Child not found.'], 404);
            }
            // Check if the health worker exists
            $healthWorker = \App\Models\HealthWorker::find($request->healthWorkerID);
            if (!$healthWorker) {
                
                return response()->json(['success' => false, 'message' => 'Health worker not found.'], 404);

            }
            // Check if the record already exists
            $existingRecord = ChildHealthRecord::where('childID', $childID)
                ->where('healthWorkerID', $request->healthWorkerID)
                ->first();
            if ($existingRecord) {
                return response()->json(['success' => false, 'message' => 'Health record already exists for this child and health worker.'], 422);
            }
            $record = ChildHealthRecord::create(array_merge($request->all(), ['childID' => $childID]))
                ->with(['child', 'healthWorker'])
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Health record added successfully.',
                'data' => $record
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/child-health-records/by-child/{childID}",
     *     tags={"Child Health Records"},
     *     summary="Update health records by child ID",
     *     @OA\Parameter(name="childID", in="path", description="Child ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="healthWorkerID", type="integer", example=10),
     *         @OA\Property(property="checkupDate", type="string", format="date", example="2025-05-04"),
     *         @OA\Property(property="height", type="float", example=120.5),
     *         @OA\Property(property="weight", type="float", example=30.2),
     *         @OA\Property(property="vaccination", type="string", example="Polio"),
     *         @OA\Property(property="diagnosis", type="string", example="Healthy"),
     *         @OA\Property(property="treatment", type="string", example="None")
     *     )),
     *     @OA\Response(response=200, description="Record updated successfully"),
     *     @OA\Response(response=404, description="Record not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function updateByChildID(Request $request, $childID): JsonResponse
    {
        $record = ChildHealthRecord::where('childID', $childID)->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'healthWorkerID' => 'sometimes|integer|exists:health_workers,hwID',
            'checkupDate' => 'sometimes|date',
            'height' => 'sometimes|numeric|min:10|max:250',
            'weight' => 'sometimes|numeric|min:1|max:100',
            'vaccination' => 'sometimes|string|max:255',
            'diagnosis' => 'sometimes|string|max:500',
            'treatment' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        try {
            $record->update($request->all())
                ->with(['child', 'healthWorker'])
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Health record updated successfully.',
                'data' => $record
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }


     /**
     * @OA\Get(
     *     path="/api/v1/child-health-records/by-health-worker/{hwID}",
     *     tags={"Child Health Records"},
     *     summary="Get all health records assigned to a specific health worker",
     *     @OA\Parameter(name="hwID", in="path", description="Health Worker ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Records not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getByHealthWorkerID($hwID): JsonResponse
    {
        $records = ChildHealthRecord::where('healthWorkerID', $hwID)->get()
            ->with(['child', 'healthWorker'])
            ->first();

        if ($records->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No health records found for this health worker.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $records
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/child-health-records/by-health-worker/{hwID}",
     *     tags={"Child Health Records"},
     *     summary="Add a health record assigned to a specific health worker",
     *     @OA\Parameter(name="hwID", in="path", description="Health Worker ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="childID", type="integer", example=1),
     *         @OA\Property(property="checkupDate", type="string", format="date", example="2025-05-04"),
     *         @OA\Property(property="height", type="float", example=120.5),
     *         @OA\Property(property="weight", type="float", example=30.2),
     *         @OA\Property(property="vaccination", type="string", example="Polio"),
     *         @OA\Property(property="diagnosis", type="string", example="Healthy"),
     *         @OA\Property(property="treatment", type="string", example="None")
     *     )),
     *     @OA\Response(response=201, description="Record added successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function addByHealthWorkerID(Request $request, $hwID): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'childID' => 'required|integer|exists:children,childID',
            'checkupDate' => 'required|date',
            'height' => 'required|numeric|min:10|max:250',
            'weight' => 'required|numeric|min:1|max:100',
            'vaccination' => 'required|string|max:255',
            'diagnosis' => 'required|string|max:500',
            'treatment' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        try {
            // Check if the health worker exists
            $healthWorker = \App\Models\HealthWorker::find($hwID);
            if (!$healthWorker) {
                return response()->json(['success' => false, 'message' => 'Health worker not found.'], 404);
            }
            // Check if the child exists
            $child = \App\Models\Child::find($request->childID);
            if (!$child) {
                return response()->json(['success' => false, 'message' => 'Child not found.'], 404);
            }
            // Create the health record
            //IF ALREADY
            $existingRecord = ChildHealthRecord::where('childID', $request->childID)
                ->where('healthWorkerID', $hwID)
                ->first();
            if ($existingRecord) {
                return response()->json(['success' => false, 'message' => 'Health record already exists for this child and health worker.',
                $existingRecord], 
                422);
            }
            $record = ChildHealthRecord::create(array_merge($request->all(), ['healthWorkerID' => $hwID]))
                ->with(['child', 'healthWorker'])
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Health record assigned successfully.',
                'data' => $record
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/child-health-records/by-health-worker/{hwID}",
     *     tags={"Child Health Records"},
     *     summary="Update health records assigned to a specific health worker",
     *     @OA\Parameter(name="hwID", in="path", description="Health Worker ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="childID", type="integer", example=1),
     *         @OA\Property(property="checkupDate", type="string", format="date", example="2025-05-04"),
     *         @OA\Property(property="height", type="float", example=120.5),
     *         @OA\Property(property="weight", type="float", example=30.2),
     *         @OA\Property(property="vaccination", type="string", example="Polio"),
     *         @OA\Property(property="diagnosis", type="string", example="Healthy"),
     *         @OA\Property(property="treatment", type="string", example="None")
     *     )),
     *     @OA\Response(response=200, description="Record updated successfully"),
     *     @OA\Response(response=404, description="Record not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function updateByHealthWorkerID(Request $request, $hwID): JsonResponse
    {
        $record = ChildHealthRecord::where('healthWorkerID', $hwID)->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'childID' => 'sometimes|integer|exists:children,childID',
            'checkupDate' => 'sometimes|date',
            'height' => 'sometimes|numeric|min:10|max:250',
            'weight' => 'sometimes|numeric|min:1|max:100',
            'vaccination' => 'sometimes|string|max:255',
            'diagnosis' => 'sometimes|string|max:500',
            'treatment' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        try {
            $record->update($request->all())
                ->with(['child', 'healthWorker'])
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Health record updated successfully.',
                'data' => $record
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }

}
