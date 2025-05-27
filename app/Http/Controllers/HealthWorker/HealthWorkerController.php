<?php

namespace App\Http\Controllers\HealthWorker;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Cadre;
use Illuminate\Http\Request;
use App\Models\HealthWorker; // Ensure you import the HealthWorker model
use Exception;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;




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
 *      path="/api/v1/healthworkers/{hwID}/assign",
 *      tags={"Health Workers"},
 *      summary="Assign a health worker to a cadre",
 *      description="Assigns a health worker to a specific cadre",
 *      @OA\Parameter(name="hwID", in="path", required=true, @OA\Schema(type="integer")),
 *      @OA\RequestBody(
 *          @OA\JsonContent(
 *              required={"cadID"},
 *              @OA\Property(property="cadID", type="integer", example=21)
 *          )
 *      ),
 *      @OA\Response(response=200, description="Health worker assigned to cadre successfully"),
 *      @OA\Response(response=404, description="Health worker or cadre not found"),
 *      @OA\Response(response=422, description="Validation error"),
 *      @OA\Response(response=500, description="Internal server error")
 * )
 */
 
//assign healthworker to cadre
public function AssignHealthWorkToCadre(Request $request, $hwID)
{
    try {
        // Validate only cadID, since hwID comes from URL parameter
        $validatedData = $request->validate([
            'cadID' => 'required|exists:cadres,cadID',
        ]);

        // Check if the cadre exists
        $existCadre = Cadre::where('cadID', $validatedData['cadID'])->first();
        if (!$existCadre) {
            return response()->json([
                'message' => 'Cadre not found'
            ], 404);
        }

        // Find the health worker using the route parameter
        $healthWorker = HealthWorker::find($hwID);
        if (!$healthWorker) {
            return response()->json([
                'message' => 'Health worker not found'
            ], 404);
        }

        // Check if health worker is already assigned to this cadre
        if ($healthWorker->cadID == $validatedData['cadID']) {
            return response()->json([
                'message' => 'Health worker already assigned to this cadre'
            ], 422);
        }

        // Assign the health worker to the cadre
        $healthWorker->cadID = $validatedData['cadID'];
        $healthWorker->save();

        // Reload the health worker with the cadre relationship
        $healthWorker->load('cadre');

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
                'dob' => 'date',
                'role' => 'required|string|max:255',
                'telephone' => 'string|max:255|unique:health_workers',
                'email' => 'required|string|email|max:255|unique:health_workers',
                'image' => 'nullable|string',
                'address' => 'string',
                'password' => 'required|string|min:6', // 'confirmed' checks for password_confirmation field
                'cadID' => 'required|exists:cadres,cadID', // Using cadID to match the model relationships
            ]);

            // Check if cadre exists first
            $existCadre = Cadre::where('cadID', $validatedData['cadID'])->first();
            if (!$existCadre) {
                return response()->json([
                    'message' => 'Cadre not found'
                ], 404);
            }

            // --- IMPORTANT: Hash the password before creating the health worker ---
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Create the health worker
            $healthWorker = HealthWorker::create($validatedData);
            
            return response()->json([
                'message' => 'Health worker created successfully',
                'data' => $healthWorker
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // This specific catch block is good for validation errors
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    } //update the health worker by param id to update profile
/**
 * @OA\Put(
 *      path="/api/v1/healthworkers/{hwID}/update-profile",
 *      tags={"Health Workers"},
 *      summary="Update health worker profile",
 *      description="Updates the profile of a health worker, including the image",
 *      @OA\Parameter(
 *          name="hwID",
 *          in="path",
 *          required=true,
 *          description="Health worker ID",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="image", type="string", format="binary", description="Profile image (jpeg/png/gif)"),
 *          )
 *      ),
 *      @OA\Response(response=200, description="Health worker updated successfully"),
 *      @OA\Response(response=404, description="Health worker not found"),
 *      @OA\Response(response=422, description="Invalid image format"),
 *      @OA\Response(response=500, description="Internal server error")
 * )
 */

 public function updateProfile(Request $request, $hwID)
    {
        try {
            // 1. Find the health worker
            $healthWorker = HealthWorker::where("hwID", $hwID)->first();
            if (!$healthWorker) {
                return response()->json(['message' => 'Health worker not found'], 404);
            }

            // 2. Validate all incoming data, including non-file fields
            // Use 'sometimes' for optional fields, 'nullable' if they can be cleared,
            // or 'required' if they must always be present.
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:health_workers,email,' . $healthWorker->hwID . ',hwID', // 'unique' rule needs exception for current user
                'telephone' => 'sometimes|string|max:20',
                'role' => 'sometimes|string|max:255', // Assuming role can be updated
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // Image is now optional
            ]);

            // 3. Handle image update separately (as it has specific logic)
            if ($request->hasFile('image')) {
                // If health worker already has an image, delete the old one
                if ($healthWorker->image && Storage::disk('public')->exists($healthWorker->image)) {
                    Storage::disk('public')->delete($healthWorker->image);
                }

                // Store the new image
                $imagePath = $request->file('image')->store('images', 'public');
                $healthWorker->image = $imagePath;
            }

            // 4. Update other fields only if they are present in the request
            // This prevents overwriting existing data with nulls if a field isn't sent.
            if (isset($validatedData['name'])) {
                $healthWorker->name = $validatedData['name'];
            }
            if (isset($validatedData['email'])) {
                $healthWorker->email = $validatedData['email'];
            }
            if (isset($validatedData['telephone'])) {
                $healthWorker->telephone = $validatedData['telephone'];
            }
            if (isset($validatedData['role'])) {
                $healthWorker->role = $validatedData['role'];
            }

            // 5. Save the health worker instance to persist all changes
            // This is now outside the image-specific block, so it always saves if data is present.
            $healthWorker->save();

            // 6. Return a successful response with the updated data
            return response()->json([
                'message' => 'Health worker profile updated successfully',
                'data' => $healthWorker // Return the updated model instance
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch validation exceptions specifically to return 422
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            // Catch other general exceptions
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

       /**
     * @OA\Post(
     *     path="/api/v1/healthworkers/login",
     *     tags={"Authentication"},
     *     summary="User Login",
     *     description="Logs in a user and returns a token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "role"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="role", type="string", example="health_worker")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login Successful"),
     *     @OA\Response(response=401, description="Invalid Credentials"),
     *     @OA\Response(response=403, description="Role Mismatch"),
     *     @OA\Response(response=404, description="User Not Found"),
     *     @OA\Response(response=429, description="Too Many Requests")
     * )
     */
    public function login(Request $request)
{
    try {
        // Rate limiter to prevent brute-force attacks
        if (RateLimiter::tooManyAttempts('login:' . $request->email, 5)) {
            return response()->json(['message' => 'Too many login attempts. Please try again later.'], 429);
        }

        // Normalize the role to lowercase for consistent validation
        // This makes 'admin', 'Admin', 'ADMIN' all treated as 'admin'
        $request->merge(['role' => strtolower($request->input('role'))]);

        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            // Now, list all roles in lowercase. Remove the empty string.
            'role' => ['required', 'string', 'in:health_worker,admin,parent,umunyabuzima'],
            'remember_me' => 'boolean',
        ]);

        // Find user
        // Consider if the email should also be case-insensitive in lookup,
        // though standard practice is emails are case-insensitive.
        $user = HealthWorker::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Verify password using Laravel's Hash facade
        if (!Hash::check($request->password, $user->password)) {
            // Clear rate limiter on failed login to avoid locking out legitimate users due to password typo
            RateLimiter::hit('login:' . $request->email); // Increment failed attempts
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Check role match - Ensure the role stored in the database is also consistent (e.g., lowercase)
        // Or, convert the database role to lowercase for comparison here.
        if (strtolower($user->role) !== $request->role) { // Compare normalized roles
            // Increment rate limiter on role mismatch too, as it's a failed attempt
            RateLimiter::hit('login:' . $request->email);
            return response()->json(['message' => 'Role mismatch'], 403);
        }

        // Generate API token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Reset rate limiter on successful login
        RateLimiter::clear('login:' . $request->email);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Importantly, increment rate limiter for failed validation too
        RateLimiter::hit('login:' . $request->email);
        return response()->json([
            'message' => 'Validation Error',
            'errors' => $e->errors()
        ], 422);
    } catch (\Throwable $th) {
        // For general errors, also consider hitting the rate limiter if it's a critical section
        return response()->json(['message' => 'An error occurred: ' . $th->getMessage()], 500);
    }
}
}