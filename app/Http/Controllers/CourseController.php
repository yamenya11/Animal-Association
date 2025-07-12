<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CourseService;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;

class CourseController extends Controller
{
   protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
        
        // الصلاحيات المطلوبة
        $this->middleware('permission:create courses')->only(['store']);
        $this->middleware('permission:manage courses')->only(['update', 'destroy']);
    }

    /**
     * عرض الكورسات للمستخدمين حسب التصنيفات
     */
    public function indexForUsers()
    {
        $courses = $this->courseService->getCoursesByCategoriesForUsers();
        return response()->json($courses);
    }

    /**
     * عرض جميع كورسات الطبيب (vet)
     */
    public function indexForVet(Request $request)
    {
        $courses = $this->courseService->getCoursesForDoctors($request->user()->id);
        return response()->json($courses);
    }

    /**
     * إنشاء كورس جديد (بواسطة admin أو vet)
     */
    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->createCourse(
            $request->validated(), 
            $request->user()->id
        );

        return response()->json([
            'message' => 'Course created successfully',
            'data' => $course
        ], 201);
    }

    /**
     * حذف كورس (بواسطة admin أو vet صاحب الكورس)
     */
    public function destroy(Request $request, $id)
    {
        $this->courseService->deleteCourse($id);
        
        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }
}
