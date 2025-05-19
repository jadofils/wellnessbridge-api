<?php

namespace App\Http\Controllers\assignProject;

use App\Models\ProjectAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
/**
 * @OA\Schema(
 *     schema="ProjectAssignment",
 *     type="object",
 *     title="ProjectAssignment",
 *     required={"hwID", "prjID", "assignedDate", "role"},
 *     
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="hwID", type="integer", example=1, description="Health worker ID"),
 *     @OA\Property(property="prjID", type="integer", example=5, description="Project ID"),
 *     @OA\Property(property="assignedDate", type="string", format="date", example="2025-05-01"),
 *     @OA\Property(property="endDate", type="string", format="date", nullable=true, example="2025-08-01"),
 *     @OA\Property(property="role", type="string", example="Team Lead"),

 *     @OA\Property(
 *         property="healthWorker",
 *         type="object",
 *         @OA\Property(property="hwID", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     ),

 *     @OA\Property(
 *         property="project",
 *         type="object",
 *         @OA\Property(property="prjID", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     )
 * )
 */


class AssignProjectController extends Controller
{

    /**
 * @OA\Get(
 *     path="/api/v1/project-assignments",
 *     summary="Get all project assignments",
 *     tags={"ProjectAssignments"},
 *     @OA\Response(
 *         response=200,
 *         description="List of project assignments",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/ProjectAssignment")
 *         )
 *     ),
 *     @OA\Response(response=404, description="No project assignments found"),
 *     @OA\Response(response=500, description="Server error")
 * )
 */

   
    public function index()
    {
        try {
            $assignments = ProjectAssignment::with(['healthWorker', 'project'])->get();
            return response()->json(['data' => $assignments], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch assignments', 'message' => $e->getMessage()], 500);
        }
    }

  /**
 * @OA\Post(
 *     path="/api/v1/project-assignments",
 *     summary="Create a new project assignment",
 *     tags={"ProjectAssignments"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ProjectAssignment")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Project assignment created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/ProjectAssignment")
 *     ),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=500, description="Failed to create project assignment")
 * )
 */

    public function show($id)
    {
        try {
            $assignment = ProjectAssignment::with(['healthWorker', 'project'])->findOrFail($id);
            return response()->json(['data' => $assignment], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Assignment not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch assignment', 'message' => $e->getMessage()], 500);
        }
    }

  /**
 * @OA\Get(
 *     path="/api/v1/project-assignments/{id}",
 *     summary="Get a specific project assignment",
 *     tags={"ProjectAssignments"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Project assignment found",
 *         @OA\JsonContent(ref="#/components/schemas/ProjectAssignment")
 *     ),
 *     @OA\Response(response=404, description="Project assignment not found"),
 *     @OA\Response(response=500, description="Failed to fetch project assignment")
 * )
 */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hwID' => 'required|exists:health_workers,hwID',
            'prjID' => 'required|exists:projects,prjID',
            'assignedDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:assignedDate',
            'role' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $assignment = ProjectAssignment::create($request->all());
            $assignment->load(['healthWorker', 'project']);
            return response()->json([
                'message' => 'Project assigned successfully',
                'data' => $assignment
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to assign project', 'message' => $e->getMessage()], 500);
        }
    }

 /**
 * @OA\Put(
 *     path="/api/v1/project-assignments/{id}",
 *     summary="Update a specific project assignment",
 *     tags={"ProjectAssignments"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="hwID", type="integer"),
 *             @OA\Property(property="prjID", type="integer"),
 *             @OA\Property(property="assignedDate", type="string", format="date"),
 *             @OA\Property(property="endDate", type="string", format="date"),
 *             @OA\Property(property="role", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Project assignment updated"),
 *     @OA\Response(response=404, description="Project assignment not found"),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=500, description="Failed to update project assignment")
 * )
 */
    public function update(Request $request, $id)
    {
        try {
            $assignment = ProjectAssignment::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'hwID' => 'sometimes|required|exists:health_workers,hwID',
                'prjID' => 'sometimes|required|exists:projects,prjID',
                'assignedDate' => 'sometimes|required|date',
                'endDate' => 'nullable|date|after_or_equal:assignedDate',
                'role' => 'sometimes|required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $assignment->update($request->all());
            $assignment->load(['healthWorker', 'project']);

            return response()->json([
                'message' => 'Assignment updated successfully',
                'data' => $assignment
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Assignment not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update assignment', 'message' => $e->getMessage()], 500);
        }
    }

   /**
 * @OA\Delete(
 *     path="/api/v1/project-assignments/{id}",
 *     summary="Delete a specific project assignment",
 *     tags={"ProjectAssignments"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Project assignment deleted"),
 *     @OA\Response(response=404, description="Project assignment not found"),
 *     @OA\Response(response=500, description="Failed to delete project assignment")
 * )
 */
 public function destroy($id)
    {
        try {
            $assignment = ProjectAssignment::findOrFail($id);
            $assignment->delete();
            return response()->json(['message' => 'Assignment deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Assignment not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete assignment', 'message' => $e->getMessage()], 500);
        }
    }
}
