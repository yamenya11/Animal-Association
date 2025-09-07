<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Course;
use App\Models\Post;
use App\Models\CatgoryCourse;
use Illuminate\Support\Facades\Storage;
class SearchService
{

     public function search($query, $modelType = null)
    {

          if (empty($query)) {
            return collect();
        }

           if ($modelType) {
            return $this->searchSingleModel($query, $modelType)
                        ->sortByDesc('relevance')
                        ->values();
        }

            $results = collect()
            ->merge($this->searchAnimals($query))
            ->merge($this->searchCourses($query))
            ->merge($this->searchPosts($query))
           ;

        return $results->sortByDesc('relevance')->values();

    }

     protected function searchSingleModel($query, $modelType)
    {
        switch ($modelType) {
            case 'animals': return $this->searchAnimals($query);
            case 'courses': return $this->searchCourses($query);
            case 'posts':   return $this->searchPosts($query);
          
            default: return collect();
        }
    }
    

      protected function searchAnimals($query)
    {
        return Animal::where('name', 'LIKE', "%{$query}%")
            ->orWhere('breed', 'LIKE', "%{$query}%")
            ->orWhere('health_info', 'LIKE', "%{$query}%")
            ->orWhere('describtion', 'LIKE', "%{$query}%")
            ->orWhere('purpose', 'LIKE', "%{$query}%")
            ->get()
            ->map(function ($animal) use ($query) {
                $animal->model_type = 'animals';
                $animal->relevance = $this->calculateRelevance($animal, $query, ['name', 'breed', 'describtion']);
                 if ($animal->image) {
                    $animal->image_url = config('app.url') . '/storage/' . $animal->image;
                }
                return $animal;
            });
    }

    protected function searchCourses($query)
    {
        return Course::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('duration', 'LIKE', "%{$query}%")
            ->get()
            ->map(function ($course) use ($query) {
                $course->model_type = 'courses';
                $course->relevance = $this->calculateRelevance($course, $query, ['name', 'description']);
                 if ($course->video) {
                    $course->video_url =  config('app.url') . '/storage/' . $course->video;
                }
                return $course;
            });
    }

    protected function searchPosts($query)
    {
      return Post::where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('content', 'LIKE', "%{$query}%");
        })
        ->where('status', 'approved') 
        ->get()
            ->map(function ($post) use ($query) {
                $post->model_type = 'posts';
                $post->relevance = $this->calculateRelevance($post, $query, ['title', 'content']);
                 if ($post->image) {
                    $post->image_url =  config('app.url') . '/storage/' . $post->image;
                }
                return $post;
            });
    }


     protected function calculateRelevance($item, $query, $fields)
    {
        $relevance = 0;
        $query = strtolower($query);

        foreach ($fields as $field) {
            if (!isset($item->$field)) continue;

            $text = strtolower($item->$field);

            if ($text === $query) {
                $relevance += 100; 
            } elseif (strpos($text, $query) === 0) {
                $relevance += 50; 
            } elseif (strpos($text, $query) !== false) {
                $relevance += 20;
            }
        }

        return $relevance;
    }




}