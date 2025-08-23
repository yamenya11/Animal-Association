<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Participant;
use App\Models\Recipient;
class ChatController extends Controller
{
  
  
    public function indexConversations(Request $request)
    {
        $user = $request->user();
          $user = $request->user();

    $conversations = Conversation::whereHas('participants', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })->with('lastMessage')->get();

    return response()->json([
        'conversations' => $conversations
    ], 200);
    }


public function createConversation(Request $request)
{
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'participants' => 'required|array', // IDs للمتطوعين
    ]);

    $user = $request->user();

    if (!$user->hasAnyRole(['admin', 'employee'])) {
        return response()->json(['error' => 'غير مسموح لك بإنشاء الغروب'], 403);
    }

    // إنشاء المحادثة
    $conversation = Conversation::create([
        'title' => $data['title'],
        'type' => 'group',
        'created_by' => $user->id,
    ]);

    $participants = [];

    // إضافة الموظف كـ admin
    $participants[] = Participant::create([
        'user_id' => $user->id,
        'conversation_id' => $conversation->id,
        'role' => 'admin',
    ]);

    // إضافة المتطوعين كمشاركين
    foreach ($data['participants'] as $userId) {
        $participants[] = Participant::create([
            'user_id' => $userId,
            'conversation_id' => $conversation->id,
            'role' => 'member',
        ]);
    }

    return response()->json([
        'message' => 'تم إنشاء الغروب بنجاح',
        'conversation' => [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'type' => $conversation->type,
            'created_by' => $conversation->created_by,
            'created_at' => $conversation->created_at,
            'updated_at' => $conversation->updated_at,
        ],
        'participants' => $participants
    ], 201);
}


public function sendMessage(Request $request, $conversationId)
{
    $data = $request->validate([
        'body' => 'nullable|string',
        'type' => 'required|in:text,image,file,audio,video',
        'media' => 'nullable|file', // للتأكد من قبول الملفات
    ]);

    $user = $request->user();
    $conversation = Conversation::findOrFail($conversationId);

    // التأكد أن المستخدم مشارك
    if (!Participant::where('conversation_id', $conversation->id)->where('user_id', $user->id)->exists()) {
        return response()->json(['error' => 'غير مسموح لك بإرسال رسالة في هذا الغروب'], 403);
    }

    // إعداد البيانات الأساسية للرسالة
    $messageData = [
        'body' => $data['body'] ?? null,
        'type' => $data['type'],
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
    ];

    // معالجة الملفات إذا وجدت
    if ($request->hasFile('media') && in_array($data['type'], ['image', 'file', 'audio', 'video'])) {
        $file = $request->file('media');
        $path = $file->store('chat_media', 'public');

        $messageData['media_path'] = $path;
        $messageData['media_original_name'] = $file->getClientOriginalName();
        $messageData['media_size'] = $file->getSize();
        $messageData['media_mime_type'] = $file->getMimeType();
    }

    $message = Message::create($messageData);

    // تحديث آخر رسالة في المحادثة
    $conversation->update(['last_message_id' => $message->id]);

    // الحصول على دور المستخدم في هذه المحادثة
    $participant = Participant::where('conversation_id', $conversation->id)
        ->where('user_id', $user->id)
        ->first();

    // تحضير الاستجابة
    $response = [
        'id' => $message->id,
        'body' => $message->body,
        'type' => $message->type,
        'conversation_id' => $message->conversation_id,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
          //  'email' => $user->email,
            'role' => $participant ? $participant->role : null,
        ],
        'created_at' => $message->created_at,
        'updated_at' => $message->updated_at,
    ];

    // إضافة رابط الوسائط إذا كان موجود
    if ($message->media_path) {
        $response['media_url'] = config('app.url') . '/storage/' . $message->media_path;
        $response['media_original_name'] = $message->media_original_name;
        $response['media_size'] = $message->media_size;
        $response['media_mime_type'] = $message->media_mime_type;
    }

    return response()->json([
        'message' => 'تم إرسال الرسالة بنجاح',
        'data' => $response,
    ], 201);
}

    /**
     * Display the specified resource.
     */
 public function messages($conversationId)
{
    $conversation = Conversation::with(['messages.user' => function($query) {
        $query->select('id', 'name', 'profile_image'); // اختر فقط الحقول الضرورية
    }])->findOrFail($conversationId);

    $messages = $conversation->messages->map(function($message) {
        $data = [
            'id' => $message->id,
            'body' => $message->body,
            'type' => $message->type,
            'conversation_id' => $message->conversation_id,
            'user_id' => $message->user_id,
            'role' => $message->participant_role ?? null, // يمكنك إضافة علاقة participant لتجلب الدور
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
        ];

        if ($message->media_path) {
            $data['media_url'] = config('app.url') . '/storage/' . $message->media_path;
            $data['media_original_name'] = $message->media_original_name;
            $data['media_size'] = $message->media_size;
            $data['media_mime_type'] = $message->media_mime_type;
        }

        $data['user'] = $message->user; // يضم id, name, profile_image فقط
        return $data;
    });

    return response()->json([
        'conversation_id' => $conversation->id,
        'messages' => $messages
    ], 200);
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
public function deleteMessage(Request $request, $messageId)
{
    $user = $request->user();

    // جلب الرسالة مع المستلمين و المحادثة
    $message = Message::with(['recipients', 'conversation'])->find($messageId);

    if (!$message) {
        return response()->json(['error' => 'الرسالة غير موجودة'], 404);
    }

    $conversation = $message->conversation;

    // ✅ تحقق إذا المستخدم أدمن بالغروب
    $isAdminInConversation = $conversation->users()
        ->where('user_id', $user->id)
        ->whereIn('role', ['admin', 'owner'])
        ->exists();

    if ($isAdminInConversation) {
        // الأدمن في الغروب يقدر يحذف الرسالة نهائياً
        Recipient::where('message_id', $message->id)->delete();
        $message->delete();

        return response()->json(['message' => 'تم حذف الرسالة نهائياً بواسطة أدمن الغروب']);
    }

    // ✅ إذا المستخدم مستلم
    $recipient = $message->recipients->where('user_id', $user->id)->first();
    if ($recipient) {
        $recipient->update(['deleted_at' => now()]);
        return response()->json(['message' => 'تم حذف الرسالة من نسختك فقط']);
    }

    // ✅ إذا المستخدم هو المرسل
    if ($message->user_id === $user->id) {
        $message->update(['deleted_by_sender' => now()]);
        return response()->json(['message' => 'تم حذف الرسالة من جانبك فقط']);
    }

    return response()->json(['error' => 'لا تملك صلاحية لحذف هذه الرسالة'], 403);
}


public function addParticipant(Request $request, $conversationId)
{
    $conversation = Conversation::findOrFail($conversationId);
    $user = $request->user();

    // السماح فقط للمستخدمين admins في المحادثة
    $adminParticipant = Participant::where('conversation_id', $conversation->id)
                              ->where('user_id', $user->id)
                              ->first();

    if (!$adminParticipant || $adminParticipant->role !== 'admin') {
        return response()->json(['error' => 'غير مسموح لك بإضافة متطوعين'], 403);
    }

    // التحقق من صحة الطلب
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    // إضافة المتطوع كـ member فقط
    $newParticipant = Participant::firstOrCreate([
        'user_id' => $request->user_id,
        'conversation_id' => $conversation->id,
    ], ['role' => 'member']);

    // تحميل بيانات المستخدم المرتبط بالمتطوع الجديد
    $newParticipant->load('user');

    return response()->json([
        'message' => 'تم إضافة المتطوع بنجاح',
        'participant' => [
            'id' => $newParticipant->id,
            'role' => $newParticipant->role,
            'user' => [
                'id' => $newParticipant->user->id,
                'name' => $newParticipant->user->name,
                //'email' => $newParticipant->user->email, // يمكن إضافته إذا أردت
            ]
        ]
    ], 201);
}


//عرض المتطوعين لاضافتهم لغروب
public function getVolunteersByType($volunteerTypeId)
{
    // جلب المستخدمين الذين لديهم دور متطوع ويطابق نوع المتطوع المطلوب
    $volunteers = \App\Models\User::role('Client') // أو 'volunteer' حسب تعريفك في Spatie
        ->whereHas('volunteerRequest', function ($q) use ($volunteerTypeId) {
            $q->where('volunteer_type_id', $volunteerTypeId)
              ->where('status', 'approved'); // يمكن تعديل الحالة حسب الحاجة
        })
        ->select('id', 'name', 'email')
        ->get();

    return response()->json([
        'message' => 'قائمة المتطوعين',
        'data' => $volunteers
    ]);
}


 
public function changeUserRole(Request $request, Conversation $conversation, $userId)
{
    $request->validate([
        'role' => 'required|in:admin,member',
    ]);

    // التحقق إذا كان المستخدم موجود ضمن الغروب
    $isMember = $conversation->users()->where('user_id', $userId)->exists();

    if (!$isMember) {
        return response()->json([
            'message' => 'المستخدم غير موجود في هذا الغروب'
        ], 404);
    }

    // تحديث الدور إذا كان موجود
    $conversation->users()->updateExistingPivot($userId, [
        'role' => $request->role
    ]);

    return response()->json([
        'message' => 'تم تحديث الدور بنجاح',
        'user_id' => $userId,
        'role' => $request->role
    ]);
}



      public function markAsRead(Conversation $conversation, Message $message)
    {
        // تأكد أن الرسالة ضمن نفس المحادثة
        if ($message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'الرسالة لا تنتمي لهذه المحادثة'], 403);
        }

        // تحديث حالة القراءة
        $message->update(['is_read' => true]);

        return response()->json([
            'message' => 'تم تعليم الرسالة كمقروءة',
            'message_id' => $message->id,
            'is_read' => true
        ]);
    }

    //ازالة مشارك
  public function removeParticipant($conversationId, $userId)
{
    $conversation = Conversation::findOrFail($conversationId);

    // تحقق: هل المستخدم الحالي أدمن في هذه المحادثة؟
    $isAdmin = Participant::where('conversation_id', $conversation->id)
        ->where('user_id', auth()->id())
        ->where('role', 'admin')
        ->exists();

    if (! $isAdmin) {
        return response()->json(['message' => 'غير مصرح لك بإزالة مشارك'], 403);
    }

    // لا يمكن إزالة منشئ المحادثة
    if ($conversation->created_by == $userId) {
        return response()->json(['message' => 'لا يمكن إزالة منشئ المحادثة'], 403);
    }

    // تحقق إذا المشارك موجود
    $participant = Participant::where('conversation_id', $conversation->id)
        ->where('user_id', $userId)
        ->first();

    if (! $participant) {
        return response()->json(['message' => 'المشارك غير موجود في هذه المحادثة'], 404);
    }

    // حذف المشارك
    $participant->delete();

    return response()->json(['message' => 'تمت إزالة المشارك بنجاح']);
}

  ///حذف الغروب
  public function deleteConversation($conversationId)
{
    $conversation = Conversation::findOrFail($conversationId);

    // تحقق إن المستخدم الحالي هو المنشئ
    if ($conversation->created_by !== auth()->id()) {
        return response()->json(['message' => 'فقط منشئ المحادثة يمكنه الحذف'], 403);
    }

    // حذف المحادثة وكل علاقاتها
    $conversation->users()->detach(); // يفصل المشاركين
    $conversation->messages()->delete(); // يحذف الرسائل
    $conversation->delete(); // يحذف المحادثة نفسها

    return response()->json(['message' => 'تم حذف المحادثة بنجاح']);
}

//عرض جميع الاعضاء
// عرض جميع الأعضاء في المحادثة
public function getParticipants($conversationId)
{
    $conversation = Conversation::findOrFail($conversationId);

    $participants = $conversation->users()
        ->select('users.id', 'users.name', 'participants.role')
        ->get();

    return response()->json([
        'participants' => $participants
    ]);
}


}
