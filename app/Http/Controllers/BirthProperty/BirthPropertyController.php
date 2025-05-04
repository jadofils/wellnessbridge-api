<?php

namespace App\Http\Controllers\BirthProperty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BirthProperty;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
/**
 * @OA\Tag(
 *     name="Birth Properties",
 *     description="Endpoints for managing birth properties"
 * )
 */
class BirthPropertyController extends Controller
{

    /**
 * @OA\Get(
 *      path="/api/v1/birth-properties",
 *     tags={"Birth Properties"},
 *     summary="Get paginated list of birth properties",
 *     description="Retrieve birth properties with pagination.",
 *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="per_page", in="query", description="Number of items per page", required=false, @OA\Schema(type="integer", default=10)),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *     @OA\Response(response=500, description="Server error")
 * )
 */
    // GET - Fetch all birth properties with pagination
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->per_page ?? 10; // Default to 10 per page
        $birthProperties = BirthProperty::paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $birthProperties
        ], 200);
    }



    /**
 * @OA\Get(
 *     path="/api/v1/birth-properties/{bID}",
 *     tags={"Birth Properties"},
 *     summary="Get a birth property by ID",
 *     description="Retrieve a specific birth property.",
 *     @OA\Parameter(name="bID", in="path", description="Birth Property ID", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(response=404, description="Birth property not found"),
 *     @OA\Response(response=500, description="Server error")
 * )
 */
    // GET - Fetch a single birth property by ID
    public function show($bID): JsonResponse
    {
        $birthProperty = BirthProperty::find($bID);

        if (!$birthProperty) {
            return response()->json([
                'success' => false,
                'message' => 'Birth property not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $birthProperty
        ], 200);
    }




    /**
 * @OA\Post(
 *     path="/api/v1/birth-properties",
 *     tags={"Birth Properties"},
 *     summary="Create a new birth property",
 *     description="Store a newly created birth property.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(type="object",
 *             @OA\Property(property="childID", type="integer", example=141),
 *             @OA\Property(property="motherAge", type="integer", example=30),
 *             @OA\Property(property="fatherAge", type="integer", example=32),
 *             @OA\Property(property="numberOfChildren", type="integer", example=2),
 *             @OA\Property(property="birthType", type="string", example="Natural"),
 *             @OA\Property(property="birthWeight", type="number", format="float", example=3.2),
 *             @OA\Property(property="childCondition", type="string", example="Healthy")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Birth property created successfully"),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=500, description="Server error")
 * )
 */
    // POST - Create a new birth property
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'childID' => 'required|integer|exists:children,childID',
            'motherAge' => 'required|integer|min:12|max:100',
            'fatherAge' => 'required|integer|min:12|max:100',
            'numberOfChildren' => 'required|integer|min:1',
            'birthType' => 'required|string|max:255',
            'birthWeight' => 'required|numeric|min:0.5|max:10',
            'childCondition' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $birthProperty = BirthProperty::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Birth property created successfully.',
                'data' => $birthProperty
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the birth property.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
 * @OA\Put(
 *     path="/api/v1/birth-properties/{bID}",
 *     tags={"Birth Properties"},
 *     summary="Update an existing birth property",
 *     description="Modify the details of a birth property.",
 *     @OA\Parameter(name="bID", in="path", description="Birth Property ID", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(type="object",
 *             @OA\Property(property="motherAge", type="integer", example=32),
 *             @OA\Property(property="fatherAge", type="integer", example=34),
 *             @OA\Property(property="numberOfChildren", type="integer", example=3),
 *             @OA\Property(property="birthType", type="string", example="C-section"),
 *             @OA\Property(property="birthWeight", type="number", format="float", example=3.5),
 *             @OA\Property(property="childCondition", type="string", example="Premature")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Birth property updated successfully"),
 *     @OA\Response(response=404, description="Birth property not found"),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=500, description="Server error")
 * )
 */
    // PUT - Update an existing birth property
    public function update(Request $request, $bID): JsonResponse
    {
        $birthProperty = BirthProperty::find($bID);

        if (!$birthProperty) {
            return response()->json([
                'success' => false,
                'message' => 'Birth property not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'motherAge' => 'sometimes|integer|min:12|max:100',
            'fatherAge' => 'sometimes|integer|min:12|max:100',
            'numberOfChildren' => 'sometimes|integer|min:1',
            'birthType' => 'sometimes|string|max:255',
            'birthWeight' => 'sometimes|numeric|min:0.5|max:10',
            'childCondition' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $birthProperty->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Birth property updated successfully.',
                'data' => $birthProperty
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the birth property.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * @OA\Delete(
 *     path="/api/v1/birth-properties/{bID}",
 *     tags={"Birth Properties"},
 *     summary="Delete a birth property",
 *     description="Remove a birth property record.",
 *     @OA\Parameter(name="bID", in="path", description="Birth Property ID", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Birth property deleted successfully"),
 *     @OA\Response(response=404, description="Birth property not found"),
 *     @OA\Response(response=500, description="Server error")
 * )
 */
    // DELETE - Remove a birth property
    public function destroy($bID): JsonResponse
    {
        $birthProperty = BirthProperty::find($bID);

        if (!$birthProperty) {
            return response()->json([
                'success' => false,
                'message' => 'Birth property not found.'
            ], 404);
        }

        try {
            $birthProperty->delete();

            return response()->json([
                'success' => true,
                'message' => 'Birth property deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the birth property.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


     /**
     * @OA\Get(
     *     path="/api/v1/birth-properties/by-child/{childID}",
     *     tags={"Birth Properties"},
     *     summary="Get birth property by child ID",
     *     description="Retrieve a birth property associated with a specific child.",
     *     @OA\Parameter(name="childID", in="path", description="Child ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=true), @OA\Property(property="data", type="object"))),
     *     @OA\Response(response=404, description="Birth property not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function showByChildID($childID): JsonResponse
    {
        $birthProperty = BirthProperty::where('childID', $childID)->first();

        if (!$birthProperty) {
            return response()->json([
                'success' => false,
                'message' => 'Birth property not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $birthProperty
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/birth-properties/by-child/{childID}",
     *     tags={"Birth Properties"},
     *     summary="Delete birth property by child ID",
     *     description="Delete a birth property associated with a specific child.",
     *     @OA\Parameter(name="childID", in="path", description="Child ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Birth property deleted successfully"),
     *     @OA\Response(response=404, description="Birth property not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function deleteByChildID($childID): JsonResponse
    {
        $birthProperty = BirthProperty::where('childID', $childID)->first();

        if (!$birthProperty) {
            return response()->json([
                'success' => false,
                'message' => 'Birth property not found.'
            ], 404);
        }

        try {
            $birthProperty->delete();

            return response()->json([
                'success' => true,
                'message' => 'Birth property deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the birth property.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/birth-properties/by-child/{childID}",
     *     tags={"Birth Properties"},
     *     summary="Update birth property by child ID",
     *     description="Modify the details of a birth property associated with a specific child.",
     *     @OA\Parameter(name="childID", in="path", description="Child ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="motherAge", type="integer", example=32),
     *             @OA\Property(property="fatherAge", type="integer", example=34),
     *             @OA\Property(property="numberOfChildren", type="integer", example=3),
     *             @OA\Property(property="birthType", type="string", example="C-section"),
     *             @OA\Property(property="birthWeight", type="number", format="float", example=3.5),
     *             @OA\Property(property="childCondition", type="string", example="Premature")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Birth property updated successfully"),
     *     @OA\Response(response=404, description="Birth property not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function updateByChildID(Request $request, $childID): JsonResponse
    {
        $birthProperty = BirthProperty::where('childID', $childID)->first();

        if (!$birthProperty) {
            return response()->json([
                'success' => false,
                'message' => 'Birth property not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'motherAge' => 'sometimes|integer|min:12|max:100',
            'fatherAge' => 'sometimes|integer|min:12|max:100',
            'numberOfChildren' => 'sometimes|integer|min:1',
            'birthType' => 'sometimes|string|max:255',
            'birthWeight' => 'sometimes|numeric|min:0.5|max:10',
            'childCondition' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $birthProperty->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Birth property updated successfully.',
                'data' => $birthProperty
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the birth property.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
