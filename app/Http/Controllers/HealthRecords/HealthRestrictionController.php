<?php

namespace App\Http\Controllers\HealthRecords;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HealthRestriction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

/**
 * @OA\Tag(
 *     name="Health Restrictions",
 *     description="Endpoints for managing health restrictions"
 * )
 */
class HealthRestrictionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/health-restrictions",
     *     tags={"Health Restrictions"},
     *     summary="Get paginated list of health restrictions",
     *     description="Retrieve health restrictions with pagination.",
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", description="Number of items per page", required=false, @OA\Schema(type="integer", default=10)),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->per_page ?? 10;
        $restrictions = HealthRestriction::paginate($perPage)
            ->withQueryString();
   
       
//return the json response error not html
        if ($restrictions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No health restrictions found.'
            ], 404);
        }
        //return the json response

        return response()->json([
            'success' => true,
            'data' => $restrictions
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/health-restrictions/{hrID}",
     *     tags={"Health Restrictions"},
     *     summary="Get health restriction by ID",
     *     description="Retrieve a specific health restriction.",
     *     @OA\Parameter(name="hrID", in="path", description="Health Restriction ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Restriction not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show($hrID): JsonResponse
    {
        $restriction = HealthRestriction::find($hrID)
            ->with('childHealthRecord')
            ->first();

        if (!$restriction) {
            return response()->json([
                'success' => false,
                'message' => 'Restriction not found.'
            ], 404);
        }


        return response()->json([
            'success' => true,
            'data' => $restriction
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/health-restrictions/by-health-worker/{hwID}",
     *     tags={"Health Restrictions"},
     *     summary="Add a health restriction assigned to a specific health worker",
     *     @OA\Parameter(name="hwID", in="path", description="Health Worker ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="recordID", type="integer", example=1),
     *         @OA\Property(property="description", type="string", example="No strenuous activity"),
     *         @OA\Property(property="severity", type="string", example="Moderate")
     *     )),
     *     @OA\Response(response=201, description="Restriction added successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function addByhrID(Request $request, $hwID): JsonResponse
    {
       

        try {
             $validator = Validator::make($request->all(), [
            'recordID' => 'required|integer|exists:child_health_records,recordID',
            'description' => 'required|string|max:500',
            'severity' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }
            $restriction = HealthRestriction::create(array_merge($request->all(), ['hrID' => $hwID]))
            ->where('hrID', $hwID)
            ->where('recordID', $request->recordID)
            ->first();
            if (!$restriction) {
                return response()->json(['success' => false, 'message' => 'Failed to assign health restriction.'], 500);
            }
            // Check if the restriction was created successfully
            if (isEmpty($restriction)) {
                return response()->json(['success' => false, 'message' => 'Failed to assign health restriction.'], 500);
            }
            // Return the created restriction

            return response()->json([
                'success' => true,
                'message' => 'Health restriction assigned successfully.',
                'data' => $restriction
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/health-restrictions/by-health-worker/{hwID}",
     *     tags={"Health Restrictions"},
     *     summary="Update health restrictions assigned to a specific health worker",
     *     @OA\Parameter(name="hwID", in="path", description="Health Worker ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="description", type="string", example="No strenuous activity"),
     *         @OA\Property(property="severity", type="string", example="Severe")
     *     )),
     *     @OA\Response(response=200, description="Restriction updated successfully"),
     *     @OA\Response(response=404, description="Restriction not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function updateByhrID(Request $request, $hwID): JsonResponse
    {
        $restriction = HealthRestriction::where('hrID', $hwID)->first();

        if (!$restriction) {
            return response()->json(['success' => false, 'message' => 'Restriction not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|string|max:500',
            'severity' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        try {
            $restriction->update($request->all())
            ->where('hrID', $hwID)
            ->where('recordID', $request->recordID)
            ->first();
            if (!$restriction) {
                return response()->json(['success' => false, 'message' => 'Failed to update health restriction.'], 500);
            }
            // Check if the restriction was updated successfully
            if (isEmpty($restriction)) {
                return response()->json(['success' => false, 'message' => 'Failed to update health restriction.'], 500);
            }
            // Return the updated restriction
                return response()->json([
                'success' => true,
                'message' => 'Health restriction updated successfully.',
                'data' => $restriction
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }

    //add restriction by child id
    /**
     * @OA\Post(
     *     path="/api/v1/health-restrictions/by-child/{childID}",
     *     tags={"Health Restrictions"},
     *     summary="Add a health restriction assigned to a specific child",
     *     @OA\Parameter(name="childID", in="path", description="Child ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="recordID", type="integer", example=1),
     *         @OA\Property(property="description", type="string", example="No strenuous activity"),
     *         @OA\Property(property="severity", type="string", example="Moderate")
     *     )),
     *     @OA\Response(response=201, description="Restriction added successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function addByChildID(Request $request, $childID): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recordID' => 'required|integer',
            'description' => 'required|string|max:500',
            'severity' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        try {
            $restriction = HealthRestriction::create(array_merge($request->all(), ['childID' => $childID]))
            ->where('childID', $childID)
            ->where('recordID', $request->recordID)
            ->first();
            if (!$restriction) {
                return response()->json(['success' => false, 'message' => 'Failed to assign health restriction.'], 500);
            }
            // Check if the restriction was created successfully
            if (isEmpty($restriction)) {
                return response()->json(['success' => false, 'message' => 'Failed to assign health restriction.'], 500);
            }
            // Return the created restriction

            return response()->json([
                'success' => true,
                'message' => 'Health restriction assigned successfully.',
                'data' => $restriction
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }

 
    //add by health worker id
    /**
     * @OA\Delete(
     *     path="/api/v1/health-restrictions/by-health-worker/{hwID}",
     *     tags={"Health Restrictions"},
     *     summary="Delete health restrictions assigned to a specific health worker",
     *     @OA\Parameter(name="hwID", in="path", description="Health Worker ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Restriction deleted successfully"),
     *     @OA\Response(response=404, description="Restriction not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroyByhrID($hwID): JsonResponse
    {
        $restriction = HealthRestriction::where('hrID', $hwID)->first();

        if (!$restriction) {
            return response()->json(['success' => false, 'message' => 'Restriction not found.'], 404);
        }

        try {
            $restriction->delete();
            return response()->json(['success' => true, 'message' => 'Health restriction deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }
    //delete by child id
    /**
     * @OA\Delete(
     *     path="/api/v1/health-restrictions/by-child/{childID}",
     *     tags={"Health Restrictions"},
     *     summary="Delete health restrictions assigned to a specific child",
     *     @OA\Parameter(name="childID", in="path", description="Child ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Restriction deleted successfully"),
     *     @OA\Response(response=404, description="Restriction not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroyByChildID($childID): JsonResponse
    {
        $restriction = HealthRestriction::where('childID', $childID)->first();
        
        if (!$restriction) {
            return response()->json(['success' => false, 'message' => 'Restriction not found.'], 404);
        }

        try {
            $restriction->delete();
            return response()->json(['success' => true, 'message' => 'Health restriction deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
        }
    }


}
