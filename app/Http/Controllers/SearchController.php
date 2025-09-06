<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SearchService;
class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

   public function search(Request $request, SearchService $searchService)
{
    $request->validate([
        'query' => 'required|string',
        'modelType' => 'nullable|string'
    ]);

    $results = $searchService->search(
        $request->input('query'),
        $request->input('modelType')
    );

    if ($results->isEmpty()) {
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'لم يتم العثور على نتائج مطابقة'
        ]);
    }

    return response()->json([
        'success' => true,
        'data' => $results,
        'message' => 'تم العثور على نتائج'
    ]);
}

   
}
