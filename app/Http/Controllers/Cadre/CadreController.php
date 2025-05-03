<?php

namespace App\Http\Controllers\Cadre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cadre;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Cadres",
 *     description="Operations related to healthcare cadres"
 * )
 */
class CadreController extends Controller
{
    /**
     * Display a listing of all cadres.
     *
     * @OA\Get(
     *     path="/api/v1/cadres",
     *     summary="Get all cadres",
     *     tags={"Cadres"},
     *     @OA\Response(response=200, description="Successful response")
     * )
     */
    public function index()
    {
        $cadres = Cadre::all();

        if ($cadres->isEmpty()) {
            return response()->json([
                'message' => 'No cadres found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'All cadres retrieved successfully',
            'data' => $cadres
        ], 200);
    }

    /**
     * Paginate cadres with a page parameter in the URL.
     *
     * @OA\Get(
     *     path="/api/v1/cadres/page/{page}",
     *     summary="Get paginated list of cadres",
     *     tags={"Cadres"},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         required=true,
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Paginated cadres retrieved")
     * )
     */
    public function paginateByPage($page)
    {
        $perPage = 5;

        $cadres = Cadre::paginate($perPage, ['*'], 'page', $page);

        if ($cadres->isEmpty()) {
            return response()->json([
                'message' => 'No cadres found on this page',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Cadres retrieved successfully',
            'data' => $cadres
        ], 200);
    }

    /**
     * Store a newly created cadre.
     *
     * @OA\Post(
     *     path="/api/v1/cadres",
     *     summary="Create a new cadre",
     *     tags={"Cadres"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "qualification"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="qualification", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Cadre created successfully"),
     *     @OA\Response(response=400, description="ValcadIDation error")
     * )
     */
    public function store(Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'qualification' => 'required|string'
        ]);

        if ($Validator->fails()) {
            return response()->json([
                'message' => 'ValcadIDation failed',
                'errors' => $Validator->errors()
            ], 400);
        }

        $cadre = Cadre::create($request->all());

        return response()->json([
            'message' => 'Cadre created successfully',
            'data' => $cadre
        ], 201);
    }

    /**
     * Display a specific cadre.
     *
     * @OA\Get(
     *     path="/api/v1/cadres/{cadre}",
     *     summary="Get a specific cadre",
     *     tags={"Cadres"},
     *     @OA\Parameter(
     *         name="cadre",
     *         in="path",
     *         required=true,
     *         description="cadID of the cadre",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Cadre found"),
     *     @OA\Response(response=404, description="Cadre not found")
     * )
     */
    public function show($cadID)
    {
        $cadre = Cadre::find($cadID);
        logger()->info('Retrieved Cadre:', ['cadre' => $cadre]);
        

        if (!$cadre) {
            return response()->json([
                'message' => 'Cadre not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Cadre retrieved successfully',
            'data' => $cadre
        ], 200);
    }

    /**
     * Update a specific cadre.
     *
     * @OA\Put(
     *     path="/api/v1/cadres/{cadre}",
     *     summary="Update an existing cadre",
     *     tags={"Cadres"},
     *     @OA\Parameter(
     *         name="cadre",
     *         in="path",
     *         required=true,
     *         description="cadID of the cadre",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="qualification", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Cadre updated successfully"),
     *     @OA\Response(response=404, description="Cadre not found")
     * )
     */
    public function update(Request $request, $cadID)
    {
        $cadre = Cadre::find($cadID);

        if (!$cadre) {
            return response()->json([
                'message' => 'Cadre not found'
            ], 404);
        }

        $cadre->update($request->all());

        return response()->json([
            'message' => 'Cadre updated successfully',
            'data' => $cadre
        ], 200);
    }

    /**
     * Delete a specific cadre.
     *
     * @OA\Delete(
     *     path="/api/v1/cadres/{cadre}",
     *     summary="Delete a cadre",
     *     tags={"Cadres"},
     *     @OA\Parameter(
     *         name="cadre",
     *         in="path",
     *         required=true,
     *         description="cadID of the cadre",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Cadre deleted successfully"),
     *     @OA\Response(response=404, description="Cadre not found")
     * )
     */
    public function destroy($cadID)
    {
        $cadre = Cadre::find($cadID);
    
        if (!$cadre) {
            return response()->json(['message' => 'Cadre not found'], 404);
        }
    
        if ($cadre->healthWorkers()->exists()) {
            return response()->json([
                'message' => 'Cannot delete cadre. It has related health workers.'
            ], 400);
        }
    
        $cadre->delete();
    
        return response()->json(['message' => 'Cadre deleted successfully', 'data' => $cadre], 200);
    }
    
}
