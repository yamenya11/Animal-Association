<?php
namespace App\Services;

use App\Models\Adoption;
use Illuminate\Http\Request;
use  App\Models\Animal;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AdobtStatusAccept;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\AnimalType;
use App\Services\NotificationService;
use App\Models\TemporaryCareRequest;
class AdoptionService
 {
        public function createAdoption(Request $request, $userId): array
        {
            $validated = $request->validate([
                'animal_name' => 'required|string|max:50',
                'animal_type' => 'required|string|max:50',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'reason' => 'required|string|max:500',
            ]);

            DB::beginTransaction();
            try {
                
                $animalType = AnimalType::firstOrCreate(
                    ['name' => $validated['animal_type']],
                    ['name' => $validated['animal_type']]
                );

              
                $animal = Animal::where('name', $validated['animal_name'])
                            ->where('type_id', $animalType->id)
                            ->where('is_adopted', false)
                            ->firstOrFail();

              
                $adoption = Adoption::create([
                    'user_id' => $userId,
                    'animal_id' => $animal->id,
                    'type_id' => $animalType->id,
                    'address' => $validated['address'],
                    'phone' => $validated['phone'],
                    'reason' => $validated['reason'],
                    'status' => 'pending',
                ]);

                DB::commit();

                return [
                    'status' => true,
                    'message' => 'تم إنشاء طلب التبني بنجاح',
                    'data' => $this->formatAdoptionRequest($adoption),
                ];

            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'الحيوان غير موجود أو تم تبنيه بالفعل'
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'حدث خطأ أثناء إنشاء الطلب'
                ];
            }
        }

                protected function formatAdoptionRequest(Adoption $adoption): array
                {
                    return [
                        'request_id' => $adoption->id,
                        'animal' => [
                            'id' => $adoption->animal->id,
                            'name' => $adoption->animal->name,
                            'type' => $adoption->animalType->name, 
                            'image_url' => $adoption->animal->image ? asset('storage/'.$adoption->animal->image) : null
                        ],
                        'user' => [
                            'id' => $adoption->user->id,
                            'name' => $adoption->user->name
                        ],
                        'status' => [
                            'code' => $adoption->status,
                            'display' => $this->getStatusDisplay($adoption->status)
                        ],
                        'created_at' => $adoption->created_at->format('Y-m-d H:i')
                    ];
                }

      
                protected function getStatusDisplay(string $status): string
                {
                    return match($status) {
                        'pending' => 'قيد الانتظار',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => $status
                    };
                }

        public function getUserAdoptions()
        {
            $userId = Auth::id();
            
            $adoptions = Adoption::with(['animal.type']) 
                ->where('user_id', $userId)
                ->whereIn('status', ['approved', 'rejected'])
                ->latest()
                ->get()
                ->groupBy('status');

            return [
                'approved' => $adoptions->get('approved', collect())->map(function ($adoption) {
                    return $this->formatAdoption($adoption);
                }),
                'rejected' => $adoptions->get('rejected', collect())->map(function ($adoption) {
                    return $this->formatAdoption($adoption);
                })
            ];
        }
        protected function formatAdoption($adoption)
        {
            return [
                'id' => $adoption->id,
                'animal' => [
                    'name' => $adoption->animal->name,
                    'type' => $adoption->animal->type->name,
                    'image' => $adoption->animal->image ? asset('storage/' . $adoption->animal->image) : null
                ],
                'status' => $adoption->status,
                'created_at' => $adoption->created_at->format('Y-m-d H:i'),
                'processed_at' => $adoption->updated_at->format('Y-m-d H:i')
            ];
        }
        public function getAllAdoptions()
        {
            return Adoption::with(['user', 'animal.type'])
                ->where('status', 'pending')
                ->get()
                ->map(function ($adoption) {
                    return [
                        'id' => $adoption->id,
                        'user' => [
                            'name' => $adoption->user->name,
                            'email' => $adoption->user->email
                        ],
                        'animal' => [
                            'name' => $adoption->animal->name,
                            'type' => $adoption->animal->type->name
                        ],
                        'status' => $adoption->status,
                        'created_at' => $adoption->created_at->format('Y-m-d H:i')
                    ];
                });
        }
        public function accept_Adopt_Admin($adoptionId, $status): array
        {
            $adoptionRequest = Adoption::with(['user', 'animal'])->findOrFail($adoptionId);

            if (!in_array($status, ['approved', 'rejected'])) {
                return [
                    'status' => false,
                    'message' => 'إجراء غير صالح.',
                    'code' => 400
                ];
            }

            DB::beginTransaction();
            try {
                $adoptionRequest->status = $status;
                $adoptionRequest->save();

                if ($status === 'approved') {
                    $animal = $adoptionRequest->animal;
                    $animal->is_adopted = true;
                    $animal->adopted_at = now();
                    $animal->save();

                    // إلغاء أي طلبات أخرى لنفس الحيوان
                    Adoption::where('animal_id', $animal->id)
                        ->where('id', '!=', $adoptionId)
                        ->update(['status' => 'rejected']);
                }

                $adoptionRequest->load(['user', 'animal.type']);

                    $adoptionRequest->user->notify(new \App\Notifications\AdobtStatusAccept($adoptionRequest));

                DB::commit();

                return [
                    'status' => true,
                    'message' => $status === 'approved' 
                        ? 'تمت الموافقة على طلب التبني.' 
                        : 'تم رفض طلب التبني.',
                    'data' => $this->formatAdoptionRequest($adoptionRequest),
                    'code' => 200
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage(),
                    'code' => 500
                ];
            }
        }

        }