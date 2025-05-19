<?php

namespace App\Http\Controllers\project;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller; // Make sure to extend from the base Controller

use Exception;
/**
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *     title="Project",
 *     required={"cadID", "name", "description", "startDate", "status"},
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="cadID", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="startDate", type="string", format="date"),
 *     @OA\Property(property="endDate", type="string", format="date"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(
 *         property="cadre",
 *         type="object",
 *         @OA\Property(property="cadID", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     )
 * )
 */
class ProjectController extends Controller
{
   /**
 * @OA\Get(
 *     path="/api/v1/projects",
 *     summary="Get all projects",
 *     tags={"Projects"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Project"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No projects found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to fetch projects"
 *     )
 * )
 */

    public function index()
    {
        try {
            $projects = Project::with('cadre')->get();
            if ($projects->isEmpty()) {
                return response()->json(['message' => 'No projects found'], 404);
            }
            // Check if the user is authenticated
            return response()->json(['data' => $projects], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch projects', 'message' => $e->getMessage()], 500);
        }
    }
    // Get a single project
    /**
 * @OA\Get(
 *     path="/api/v1/projects/{id}",
 *     summary="Get a single project",
 *     tags={"Projects"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Project retrieved successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Project")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Project not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to fetch project"
 *     )
 * )
 */

    public function show($prjID)
    {
        try {
            $project = Project::with('cadre')->findOrFail($prjID);
            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            return response()->json(['data' => $project], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Project not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch project', 'message' => $e->getMessage()], 500);
        }
    }

/**
 * @OA\Post(
 *     path="/api/v1/projects",
 *     summary="Create a new project",
 *     tags={"Projects"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"cadID", "name", "description", "startDate", "status"},
 *             @OA\Property(property="cadID", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Project Alpha"),
 *             @OA\Property(property="description", type="string", example="This is a sample project."),
 *             @OA\Property(property="startDate", type="string", format="date", example="2025-05-01"),
 *             @OA\Property(property="endDate", type="string", format="date", example="2025-06-01"),
 *             @OA\Property(property="status", type="string", example="ongoing")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Project created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Project")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to create project"
 *     )
 * )
 */

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cadID' => 'required|exists:cadres,cadID',
            'name' => 'required|string',
            'description' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $project = Project::create($request->all());
            //check if the project was created successfully
            if (!$project) {
                return response()->json(['message' => 'Failed to create project'], 500);
            }
            // Check if the project was created successfully

            // Load the associated cadre
            $project->load('cadre');
            // Return the created project with its associated cadre

            return response()->json(['message' => 'Project created successfully', 'data' => $project->load('cadre')], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create project', 'message' => $e->getMessage()], 500);
        }
    }

   
    /**
 * @OA\Put(
 *     path="/api/v1/projects/{id}",
 *     summary="Update an existing project",
 *     tags={"Projects"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="cadID", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Updated Project Name"),
 *             @OA\Property(property="description", type="string", example="Updated description"),
 *             @OA\Property(property="startDate", type="string", format="date", example="2025-06-01"),
 *             @OA\Property(property="endDate", type="string", format="date", example="2025-07-01"),
 *             @OA\Property(property="status", type="string", example="completed")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Project updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Project")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Project not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update project"
 *     )
 * )
 */


    public function update(Request $request, $prjID)
    {
        try {
            $project = Project::findOrFail($prjID);

            $validator = Validator::make($request->all(), [
                'cadID' => 'sometimes|required|exists:cadres,cadID',
                'name' => 'sometimes|required|string',
                'description' => 'sometimes|required|string',
                'startDate' => 'sometimes|required|date',
                'endDate' => 'nullable|date|after_or_equal:startDate',
                'status' => 'sometimes|required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $project->update($request->all());
            return response()->json(['message' => 'Project updated successfully', 'data' => $project->load('cadre')], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Project not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update project', 'message' => $e->getMessage()], 500);
        }
    }

    // Delete a project
   /**
 * @OA\Delete(
 *     path="/api/v1/projects/{id}",
 *     summary="Delete a project",
 *     tags={"Projects"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Project deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Project not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to delete project"
 *     )
 * )
 */


    public function destroy($prjID)
    {
        try {
            $project = Project::findOrFail($prjID);
            $project->delete();
            return response()->json(['message' => 'Project deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Project not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete project', 'message' => $e->getMessage()], 500);
        }
    }
}
