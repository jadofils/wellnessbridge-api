<?php

namespace App\Http\Controllers\HealthWorker;

use App\Http\Controllers\Controller;
use App\Models\Cadre;
use Illuminate\Http\Request;
use App\Models\HealthWorker; // Ensure you import the HealthWorker model
use Exception;




/**
 * @OA\Info(
 *      title="WellnessBridge API For 8 Tables",
 *      version="1.0",
 *      description="API documentation for managing health workers"
 * )
 *
 * @OA\Tag(
 *      name="Table 1.0 Health Workers",
 *      description="Operations related to Health Workers and cadres"
 * )
 * 
 */
class HealthWorkerController extends Controller
{
   
   /**
     * @OA\Get(
     *      path="/api/v1/healthworkers",
     *      tags={"Health Workers"},
     *      summary="Get all health workers",
     *      description="Retrieve all health workers from the database",
     *      @OA\Response(response=200, description="Success"),
     *      @OA\Response(response=404, description="No health workers found")
     * )
     */
   
    public function index()
    {
        try {
            // Retrieve all health workers with their cadre relationship
            $healthWorkers = HealthWorker::with('cadre')->get();

            // Check if the response contains data
            if ($healthWorkers->isEmpty()) {
                return response()->json([
                    'message' => 'No health workers found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'message' => 'Health workers retrieved successfully',
                'data' => $healthWorkers
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $th->getMessage()
            ], 500);
        }
    }
   
    /**
     * @OA\Get(
     *      path="/api/v1/healthworkers/{hwID}",
     *      tags={"Health Workers"},
     *      summary="Get a single health worker",
     *      description="Retrieve a specific health worker by ID",
     *      @OA\Parameter(name="hwID", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Success"),
     *      @OA\Response(response=404, description="Health worker not found")
     * )
     */
   public function show($hwID)
{
    try {
        // Attempt to find the health worker with the cadre relationship
        $healthWorker = HealthWorker::where('hwID', $hwID)->with('cadre')->first();

        // Check if health worker exists
        if (!$healthWorker) {
            return response()->json([
                'message' => 'Health worker not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Health worker retrieved successfully',
            'data' => $healthWorker
        ], 200);

    } catch (\Throwable $th) {
        // Catch any unexpected errors and return an appropriate response
        return response()->json([
            'message' => 'Internal Server Error',
            'error' => $th->getMessage()
        ], 500);
    }
}
/**
 * @OA\Post(
 *      path="/api/v1/healthworkers/assign",
 *      tags={"Health Workers"},
 *      summary="Assign a health worker to a cadre",
 *      description="Assigns a health worker to a specific cadre",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"cadID", "hwID"},
 *              @OA\Property(property="cadID", type="integer", example=1),
 *              @OA\Property(property="hwID", type="integer", example=1)
 *          )
 *      ),
 *      @OA\Response(response=200, description="Health worker assigned to cadre successfully"),
 *      @OA\Response(response=404, description="Cadre or health worker not found"),
 *      @OA\Response(response=500, description="Internal server error")
 * )
 */


//assign healthworker to cadre
public function AssignHealthWorkToCadre(Request $request)
{
    try {
        $validatedData = $request->validate([
            'cadID' => 'required|exists:cadres,cadID',
            'hwID' => 'required|exists:health_workers,hwID',
        ]);

        // Check if the cadre exists
        $existCadre = Cadre::where('cadID', $validatedData['cadID'])->first();
        if (!$existCadre) {
            return response()->json([
                'message' => 'Cadre not found'
            ], 404);
        }

        // Find the health worker and assign the cadre
        $healthWorker = HealthWorker::find($validatedData['hwID']);
        if (!$healthWorker) {
            return response()->json(['message' => 'Health worker not found'], 404);
        }

        // Assign the health worker to the cadre
        $healthWorker->cadID = $validatedData['cadID'];
        $healthWorker->save();

        // Reload the health worker with the cadre relationship
        $healthWorker = HealthWorker::with('cadre')->find($validatedData['hwID']);

        return response()->json([
            'message' => 'Health worker assigned to cadre successfully',
            'data' => $healthWorker
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}



/**
 * @OA\Post(
 *      path="/api/v1/healthworkers",
 *      tags={"Health Workers"},
 *      summary="Create a new health worker",
 *      description="Stores a new health worker in the database",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"name", "gender", "dob", "role", "telephone", "email", "address", "cadID"},
 *              @OA\Property(property="name", type="string", example="John Doe"),
 *              @OA\Property(property="gender", type="string", example="Male"),
 *              @OA\Property(property="dob", type="string", format="date", example="1990-01-01"),
 *              @OA\Property(property="role", type="string", example="Nurse"),
 *              @OA\Property(property="telephone", type="string", example="123-456-7890"),
 *              @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *              @OA\Property(property="address", type="string", example="123 Main St, City, Country"),
 *              @OA\Property(property="cadID", type="integer", example=21)
 *          )
 *      ),
 *      @OA\Response(response=201, description="Health worker created successfully"),
 *      @OA\Response(response=422, description="Validation error"),
 *      @OA\Response(response=500, description="Internal server error")
 * )
 */


    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|string|max:255',
                'dob' => 'required|date',
                'role' => 'required|string|max:255',
                'telephone' => 'required|string|max:255|unique:health_workers',
                'email' => 'required|string|email|max:255|unique:health_workers',
                'image' => 'nullable|string',
                'address' => 'required|string',
                'cadID' => 'required|exists:cadres,cadID',
            ]);
    //create the with the image
            $healthWorker = HealthWorker::create($validatedData);
            return response()->json([
                'message' => 'Health worker created successfully',
                'data' => $healthWorker
            ], 201);
      $existCadre = Cadre::where('cadreID', $validatedData['cadreID'])->first();
        if (!$existCadre) {
            return response()->json([
                'message' => 'Cadre not found'
            ], 404);
        }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    


    /**
 * @OA\Put(
 *      path="/api/v1/healthworkers/{hwID}",
 *      tags={"Health Workers"},
 *      summary="Update an existing health worker",
 *      description="Modifies an existing health worker's details",
 *      @OA\Parameter(
 *          name="hwID",
 *          in="path",
 *          required=true,
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="name", type="string", example="Jane Doe"),
 *              @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 *              @OA\Property(property="role", type="string", example="Physician"),
 *              @OA\Property(property="telephone", type="string", example="987-654-3210")
 *          )
 *      ),
 *      @OA\Response(response=200, description="Health worker updated successfully"),
 *      @OA\Response(response=404, description="Health worker not found"),
 *      @OA\Response(response=500, description="Internal server error")
 * )
 */

    // Update an existing health worker
    public function update(Request $request, $hwID)
    {
        $healthWorker = HealthWorker::where("hwID", $hwID)->first();

        if (!$healthWorker) {
            return response()->json(['message' => 'Health worker not found'], 404);
        }

        // Validate and update fields
        $healthWorker->update($request->all());

        return response()->json(['message' => 'Health worker updated successfully', 'data' => $healthWorker]);
    }


    /**
 * @OA\Delete(
 *      path="/api/v1/healthworkers/{hwID}",
 *      tags={"Health Workers"},
 *      summary="Delete a health worker",
 *      description="Removes a health worker from the system",
 *      @OA\Parameter(
 *          name="hwID",
 *          in="path",
 *          required=true,
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(response=200, description="Health worker deleted successfully"),
 *      @OA\Response(response=404, description="Health worker not found"),
 *      @OA\Response(response=500, description="Internal server error")
 * )
 */

    // Delete a health worker
    public function destroy($hwID)
    {
        $healthWorker = HealthWorker::where("hwID", $hwID)->first();

        if (!$healthWorker) {
            return response()->json(['message' => 'Health worker not found'], 404);
        }

        $healthWorker->delete();

        return response()->json(['message' => 'Health worker deleted successfully']);
    }

  

    /**
 * @OA\Get(
 *      path="/api/v1/healthworkers/search",
 *      tags={"Health Workers"},
 *      summary="Search for health workers",
 *      description="Finds health workers based on a search term",
 *      @OA\Parameter(
 *          name="search",
 *          in="query",
 *          required=true,
 *          @OA\Schema(type="string", example="John Doe")
 *      ),
 *      @OA\Response(response=200, description="Search results returned"),
 *      @OA\Response(response=404, description="No matching health workers found"),
 *      @OA\Response(response=500, description="Internal server error")
 * )
 */

    // Search with pagination and error handling'
    public function search(Request $request)
{
    try {
        // Validate that the search term is provided
        $request->validate([
            'search' => 'required|string'
        ]);

        $searchTerm = trim(strtolower($request->input('search')));

        // Perform a case-insensitive search across multiple fields
        $healthWorkers = HealthWorker::whereRaw("LOWER(name) LIKE LOWER(?)", ["%{$searchTerm}%"])
            ->orWhereRaw("LOWER(email) LIKE LOWER(?)", ["%{$searchTerm}%"])
            ->orWhereRaw("LOWER(telephone) LIKE LOWER(?)", ["%{$searchTerm}%"])
            ->get(); // Removed pagination
          

        // Check if results exist
        if ($healthWorkers->isEmpty()) {
            return response()->json(['message' => 'No matching health workers found'], 404);
        }

        return response()->json($healthWorkers);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => 'Validation error', 'details' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An unexpected error occurred'], 500);
    }
}

    
    


 /**
 * @OA\Get(
 *      path="/api/v1/healthworkers/page/{page}",
 *      tags={"Health Workers"},
 *      summary="Get paginated health workers",
 *      description="Retrieve health workers with pagination",
 *      @OA\Parameter(
 *          name="page",
 *          in="path",
 *          required=true,
 *          @OA\Schema(type="integer", example=4)
 *      ),
 *      @OA\Response(response=200, description="Paginated list of health workers"),
 *      @OA\Response(response=404, description="No health workers found"),
 *      @OA\Response(response=500, description="Internal server error")
 * )
 */


    // Paginate health workers
    public function getPage()
    {
        try {
            $healthWorkers = HealthWorker::paginate(5);

            return response()->json($healthWorkers);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }
}