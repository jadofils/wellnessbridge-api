<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Children",
 *     description="API/v1 Endpoints for Child Management"
 * )
 */
class ChildController extends Controller
{
    /**
     * Display a listing of children resources
     * 
     * @OA\Get(
     *     path="/api/v1/children",
     *     tags={"Children"},
     *     summary="Get paginated list of children",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter records",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Children retrieved successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Build query
            $query = Child::with(['birthProperty', 'childHealthRecords']);
            
            // Search functionality
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('gender', 'like', "%{$searchTerm}%")
                      ->orWhere('address', 'like', "%{$searchTerm}%")
                      ->orWhere('parentName', 'like', "%{$searchTerm}%")
                      ->orWhere('parentContact', 'like', "%{$searchTerm}%");
                });
            }

            // Pagination with default of 10 items per page
            $perPage = $request->query('per_page', 10);
            $children = $query->paginate($perPage);
    
            return response()->json([
                'success' => true,
                'message' => 'Children retrieved successfully.',
                'data' => $children
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve children.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
      
    /**
     * Store a newly created child resource
     * 
     * @OA\Post(
     *     path="/api/v1/children",
     *     tags={"Children"},
     *     summary="Create a new child record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "gender", "dob", "address", "parentName", "parentContact"},
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
     *                 @OA\Property(property="dob", type="string", format="date", example="2018-01-01"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="parentName", type="string", example="Jane Doe"),
     *                 @OA\Property(property="parentContact", type="string", example="555-1234")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Child created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'required|string',
            'parentName' => 'required|string|max:255',
            'parentContact' => 'required|string|max:20',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('children', 'public');
        }
//check if user already exist
        $child = Child::where('name', $validated['name'])->first();
        if ($child) {
            return response()->json([
                
                'success' => false,
                'message' => 'Child already exists.',
                'data' => $child
            ]);
        }
        $child = Child::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Child record created successfully.',
            'data' => $child
        ], 201);
    }

    /**
     * Display the specified child resource
     * 
     * @OA\Get(
     *     path="/api/v1/children/{id}",
     *     tags={"Children"},
     *     summary="Get child by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Child ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Child not found"
     *     )
     * )
     */
    public function show($child): JsonResponse
{
    try {
        $child = Child::with(['birthProperty', 'childHealthRecords'])
                      ->where('childID', $child)
                      ->first();

        if (!$child) {
            return response()->json([
                'success' => false,
                'message' => 'Child not found.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $child->load(['birthProperty', 'childHealthRecords'])
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    

    /**
     * Update the specified child resource
     * 
     * @OA\Put(
     *     path="/api/v1/children/{id}",
     *     tags={"Children"},
     *     summary="Update child record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Child ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
     *                 @OA\Property(property="dob", type="string", format="date", example="2018-01-01"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="parentName", type="string", example="Jane Doe"),
     *                 @OA\Property(property="parentContact", type="string", example="555-1234")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Child updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Child not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $childID): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'gender' => 'sometimes|in:male,female,other',
                'dob' => 'sometimes|date',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'address' => 'sometimes|string',
                'parentName' => 'sometimes|string|max:255',
                'parentContact' => 'sometimes|string|max:20',
            ]);
    
            // Find the child record using childID from parameters
            $child = Child::where('childID', $childID)->first();
    
            if (!$child) {
                return response()->json([
                    'success' => false,
                    'message' => 'Child not found.',
                    'data' => null
                ], 404);
            }
    
            // Handle image update if a new file is uploaded
            if ($request->hasFile('image')) {
                if (!empty($child->image)) {
                    Storage::disk('public')->delete($child->image);
                }
                $validated['image'] = $request->file('image')->store('children', 'public');
            }
    
            // Assign new values manually
            foreach ($validated as $key => $value) {
                if (!empty($value)) {
                    $child->$key = $value;
                }
            }
    
            // Save the record explicitly
            $child->update();
    
            return response()->json([
                'success' => true,
                'message' => 'Child record updated successfully.',
                'data' => $child
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    

    /**
     * Remove the specified child resource
     * 
     * @OA\Delete(
     *     path="/api/v1/children/{id}",
     *     tags={"Children"},
     *     summary="Delete child record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Child ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Child deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Child not found"
     *     )
     * )
     */
    public function destroy(Child $child): JsonResponse
    {
        if ($child->image) {
            Storage::disk('public')->delete($child->image);
        }

        $child->delete();

        return response()->json([
            'success' => true,
            'message' => 'Child record deleted successfully.'
        ]);
    }

 

 
}