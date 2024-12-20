<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BaseReviews;
use Illuminate\Http\Request;
use Modules\Admin\app\Models\Plan;
use App\Services\Resp;
use Illuminate\Support\Facades\Validator;
use Modules\Escort\app\Models\Profile;
use Modules\Escort\app\Models\ProfileRates;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Models\AuthUser;
use Modules\Escort\app\Models\Inquiry;
use Modules\Admin\app\Models\Permissions;
use Illuminate\Support\Facades\Mail;
use App\Services\EmailService as Mailer;
use App\Models\Location;
use App\Models\Subscription as subscriptions;
use Modules\Escort\app\Models\Subscription;
use Illuminate\Support\Facades\Log;
use App\Models\Image;
use App\Models\Media;
use Illuminate\Support\Facades\File;
use Stripe\Service\SubscriptionService;
use App\Models\User;
use Modules\Admin\app\Models\Blog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\app\Models\Forum;
use Modules\Admin\app\Models\Master;
use Modules\Admin\app\Models\Verify;
use Modules\Escort\app\Models\Verify as ModelsVerify;
use Modules\Admin\app\Models\Comment;
use Modules\Admin\app\Models\Reminder;
use Modules\Admin\app\Models\Remindercomment;   
use Modules\Admin\app\Models\Remindercatagory;
use Modules\Admin\app\Models\EmailTemplate;
use Modules\Admin\app\Models\EmailTemplates;
use Illuminate\Validation\Rule;
use App\Models\BaseSubscription;
use Modules\Admin\app\Models\Pages;
use Modules\Admin\app\Models\Setting;
class AdminController extends Controller
{


    public function userDelete($id,Request $request){
        die('ok');
        // $user = User::find($id);
        // if(!$user){
        //     return Resp::error(['message' => 'User not found']);
        // }
        // if ($user->user_type !== 1 && $user->user_type !== 2) {
        //     return Resp::error(['message' => 'Only ES or Fan users can be deleted']);
        // }
        // $user->delete();
        // return Resp::success(['message' => 'User deleted successfully']);
    }

    public function getParallaxImage(Request $request){
        $id = $request->query('id');
        $setting = Setting::where('type','home_parallax')
                          ->when($id, function ($query) use ($id) {
                              $query->where('id', $id);
                          })
                          ->first();
        if (!$setting) {
            return Resp::error(['message' => 'Invalid or non-existent ID']);
        }
        $setting->load('media');
        return Resp::success(['setting' => $setting]);
    }



public function parallaxImage(Request $request){
    $validator = Validator::make($request->all(), [
        'value' => 'required|integer|exists:media,id',
    ]);
    if($validator->fails()){
        return Resp::error(['message' => $validator->errors()]);
    }
    $setting = Setting::where('type','home_parallax')->first();
    if(!$setting){
        $setting = new Setting();
        $setting->type = 'home_parallax';
    }
    $setting->value = $request->value;
    $setting->save();
    $setting->load('media');
    return Resp::success(['message' => 'Parallax image updated successfully','setting' => $setting]);
}




public function emailTemplateStatus($id,Request $request){
    $emailTemplate = EmailTemplates::find($id);
    $validator = Validator::make($request->all(), [
        'status' => 'required|integer|in:1,0',
    ]);
    if($validator->fails()){
        return Resp::error(['message' => $validator->errors()]);
    }
    if(!$emailTemplate){
        return Resp::error(['message' => 'Email template not found']);
    }
    $emailTemplate->status = $request->status;
    $emailTemplate->save();
    return Resp::success(['message' => 'Email template status updated successfully']);
}


public function deleteUpdateDynamicPage($id){
    $page = Pages::find($id);
    if(!$page){
        return Resp::error(['message' => 'Page not found']);
    }
    $page->delete();
    return Resp::success(['message' => 'Page deleted successfully']);
}


    public function reminderDelete($id){
        $reminder = Reminder::find($id);
        if(!$reminder){
            return Resp::error(['message' => 'Reminder not found']);
        }
        $reminder->delete();
        return Resp::success(['message' => 'Reminder deleted successfully']);
    }

    public function updateDynamicPage($id,Request $request){
        $page = Pages::find($id);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|integer|in:1,0',
            'featured_image' => 'required|integer|exists:media,id',
        ]);
        if($validator->fails()){
            return Resp::error(['message' => $validator->errors()]);
        }
        if(!$page){
            return Resp::error(['message' => 'Page not found']);
        }
        $page->update($validator->validated());
        $page->media()->associate(Media::find($request->input('featured_image')));
        $page->save();
        $page->load('media'); // Load the related Media model
        return Resp::success(['message' => 'Page updated successfully','page' => $page]);
    }

    public function dynamicPage(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|integer|in:1,0',
            'featured_image' => 'required|integer|exists:media,id',
        ]);
        if($validator->fails()){
            return Resp::error(['message' => $validator->errors()]);
        }
        $page = Pages::create($validator->validated());
        $page->media()->associate(Media::find($request->input('featured_image')));
        $page->save();
        $page->load('media'); // Load the related Media model
        return Resp::success(['message' => 'Page created successfully','page' => $page]);
    }


    public function media(Request $request)
    {
        $search_term = $request->query('s');
        $video = $request->query('video');
        $image = $request->query('image');
        $perPage = $request->query('per_page', 12);
        $page = $request->query('page', 1);
    
        $media = Media::with('escort') // Add this line to include the 'escort' relationship
            ->when($video || $image, function ($query) use ($video, $image) {
                // If either video or image is set, we filter by type first
                $types = [];
                if ($video) {
                    $types[] = 'promo_video';
                }
                if ($image) {
                    $types[] = 'gallery';
                    $types[] = 'private_gallery';
                }
                $query->whereIn('type', $types);
            })
            //->when($search_term, function ($query, $search_term) {
                // Apply search term to filtered results (video or image or both)
              //  $query->where('path', 'like', '%' . $search_term . '%');
              ->when($search_term, function ($query, $search_term) {
                // Apply search term to multiple fields (a, b, or c)
                $query->where(function($q) use ($search_term) {
                    $q->where('path', 'like', '%' . $search_term . '%')
                      ->orWhere('title', 'like', '%' . $search_term . '%')
                      ->orWhere('description', 'like', '%' . $search_term . '%')
                      ->orWhere('alternative_text', 'like', '%' . $search_term . '%')
                      ->orWhere('caption', 'like', '%' . $search_term . '%');
                });
            })
            ->orderBy('created_at', 'desc');
    
        $total_results = $media->count();
        $total_pages = ceil($total_results / $perPage);
    
        $media = $media->skip(($page - 1) * $perPage)->take($perPage)->get();
    
        $pagination = [
            'total_results' => $total_results,
            'total_pages' => $total_pages,
            'page' => (int)$page,
            'page_size' => $perPage
        ];
    
        return response()->json([
            'media' => $media,
            'pagination' => $pagination
        ]);
    }
    
public function deleteSubscription($id){
    $subscription = BaseSubscription::find($id);
    if(!$subscription){
        return Resp::error(['message' => 'Subscription not found']);
    }
    $subscription->delete();
    return Resp::success(['message' => 'Subscription deleted successfully']);
}


    public function updateEmailTemplate(Request $request, $id)
    {
        $emailTemplate = EmailTemplates::find($id);
        if (!$emailTemplate) {
            return Resp::error(['message' => 'Email template not found'], 404);
        }
        $request->validate([
            'subject' => 'required',
            'content' => 'required',
            'status' => 'required | in:1,0',
        ]);
        $emailTemplate->subject = $request->input('subject');
        $emailTemplate->content = $request->input('content');
        $emailTemplate->status = $request->input('status');
        if($request->input('status') == 1){
            $emailTemplate->status = 1;
        }else{
            $emailTemplate->status = 0;
        }
        
        if ($emailTemplate->save()) {
            return Resp::success(['message' => 'Email template updated successfully']);
        } else {
            return Resp::error(['message' => 'Failed to update email template'], 500);
        }
    }



    
    public function getEmail(Request $request)
    {
        $id = $request->query('id');
        if ($id) {
            $emailTemplate = EmailTemplates::find($id);
            if (!$emailTemplate) {
                return Resp::error(['message' => 'Email template not found'], 404);
            }
            return Resp::success(['emailTemplate' => $emailTemplate]);
        } else {
            $emailTemplates = EmailTemplates::all();
            return Resp::success(['emailTemplates' => $emailTemplates]);
        }
    }


public function reminderDone($id){
    $reminder = Reminder::find($id);
    if(!$reminder){
        return Resp::error(['message' => 'Reminder not found']);
    }
    $reminder->status = 1;
    $reminder->save();
    if($reminder){
        return Resp::success(['message' => 'Reminder aprooved successfully']);
    }else{
        return Resp::error(['message' => 'Reminder not found']);
    }
}

    public function getForum(Request $request){
        
        $forums = Forum::query();
        if (!is_null($request->query('category'))) {
            $forums->where('category', $request->query('category'));
        }
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $perPage;
        $totalForums = $forums->count();
        $totalPages = ceil($totalForums / $perPage);
        $forums = $forums->orderBy('created_at', 'desc')->offset($offset)->limit($perPage)->get();
        $forums->load('postComments');
        $forums->load('getAuthor');
        return Resp::success([
            'forums' => $forums,
            'pagination' => [
                'total' => $totalForums,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $totalPages,
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $totalForums),
            ],
        ]);
    }

public function aprooveForum($id){
    $forum = Forum::find($id);
    if(!$forum){
        return Resp::error(['message' => 'Forum not found']);
    }
    $forum->is_approved = 1;
    $forum->save();
    return Resp::success(['message' => 'Forum aprooved successfully']);
}


public function rejectForum($id){
    $forum = Forum::find($id);
    if(!$forum){
        return Resp::error(['message' => 'Forum not found']);
    }
    $forum->is_approved = 0;
    $forum->save();
    return Resp::success(['message' => 'Forum rejected successfully']);
}

public function aprooveComment($id){
    $comment = Comment::find($id);
    if(!$comment){
        return Resp::error(['message' => 'Comment not found']);
    }
    $comment->is_approved = 1;
    $comment->save();
    return Resp::success(['message' => 'Comment aprooved successfully']);
}

public function rejectComment($id){
    $comment = Comment::find($id);
    if(!$comment){
        return Resp::error(['message' => 'Comment not found']);
    }
    $comment->is_approved = 0;
    $comment->save();
    return Resp::success(['message' => 'Comment rejected successfully']);
}


public function getForumComments($id, Request $request)
{
    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);
    $offset = ($page - 1) * $perPage;

    $comments = Comment::with('forum')->where('forum_id', $id)
        ->offset($offset)
        ->limit($perPage)
        ->get();

    if ($comments->count() == 0) {
        return Resp::error(['message' => 'Comments not found']);
    }

    $totalResults = Comment::where('forum_id', $id)->count();

    if ($perPage == 0) {
        $totalPages = 1;
    } else {
        $totalPages = ceil($totalResults / $perPage);
    }

    return Resp::success([
        'comments' => $comments,
        'pagination' => [
            'total_results' => $totalResults,
            'total_pages' => $totalPages,
            'page' => $page,
            'page_size' => $perPage,
        ]
    ]);
}

public function getForumSlugList($slug){
$forum = Forum::where('slug',$slug)->first();
if(!$forum){
    return Resp::error(['message' => 'Forum not found']);
}
$forum->load('postComments');
$forum->load('getAuthor');
return Resp::success(['forum' => $forum]);
}


    public function addComment( $id,Request $request){
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string'
        ]);
        $forum = Forum::find($id);
        if(!$forum){
            return Resp::error(['message' => 'Forum not found']);
        }
        if($validator->fails()){
            return Resp::error(['message' => $validator->errors()]);
        }
        $comment = Comment::create([
            'comment' => $request->comment,
            'forum_id' => $id,
            'commentator_id' => auth()->user()->id
        ]);
        if($comment){
            return Resp::success(['message' => 'Comment added successfully','comment' => $comment]);
        }else{
            return Resp::error(['message' => 'Comment not added']);
        }
    }
public function removeComment( $id,Request $request){
    $comment = Comment::find($id);
    if($comment){
        $comment->delete();
        return Resp::success(['message' => 'Comment removed successfully']);
    }else{
        return Resp::error(['message' => 'Comment not found']);
    }
}
public function postEmailTemplate(Request $request){
    $validator = Validator::make($request->all(), [
        'subject' => 'required|string',
        'message' => 'required|string',
        'type' => 'required|string',
    ]);
    if($validator->fails()){
        return Resp::error(['message' => $validator->errors()]);
    }
    $emailTemplate = EmailTemplate::create($validator->validated());
    return Resp::success(['message' => 'Email template created successfully','emailTemplate' => $emailTemplate]);
}
public function getEmailTemplate(){
    $emailTemplate = EmailTemplate::get();
    return Resp::success(['emailTemplate' => $emailTemplate]);
}
public function verifiedStatusForm(Request $request){
    $validator = Validator::make($request->all(), [
        'forum_id' => 'required|exists:forum,id',
        'verified_status' => 'required|integer|in:1,0',
    ]);
    if($validator->fails()){
        return Resp::error(['message' => $validator->errors()]);
    }
    $forum = Forum::find($request->forum_id);
    $forum->verified_status = $request->verified_status;
    $forum->save();
    return Resp::success(['message' => 'Forum verified status updated successfully']);
}

    public function reminderCategory(){
    $reminderCategory =Remindercatagory::get();
    return Resp::success(['reminderCategory' => $reminderCategory]);
    }


    public function getReminder(Request $request, $page = null){

        // Pagination settings
        if ($page !== null) {
        } else {
            $page = $request->query('page', 1);
        }
        $perPage = $request->query('per_page', 10);
    
        try {
            // Query for reminders with optional filtering by status
            $reminderQuery = Reminder::with('category');
            if ($request->has('status')) {
                $status = $request->query('status'); // Get the value of 'status'
                $reminderQuery->where('status', $status);
            }
            $totalResults = $reminderQuery->count();
            $reminder = $reminderQuery->orderBy('id', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();
    
    
            // Calculate the total number of pages
            $totalPages = ceil($totalResults / $perPage);
    
            // Return the response with reminder data and pagination details
            return Resp::success([
                'reminder' => $reminder,
                'pagination' => [
                    'total_results' => $totalResults,
                    'total_pages' => $totalPages,
                    'page' => $page,
                    'page_size' => $perPage,
                ]
            ]);
        } catch (\Exception $e) {
            // Log any errors and return an error response
            Log::error($e->getMessage());
            return Resp::error(['message' => 'Error fetching reminders']);
        }
    }
    

public function postReminderComment(Request $request){
   $validator = Validator::make($request->all(), [
    'reminder_comment' => 'required|string',
    'reminder_id' => 'required|exists:reminder,id',
    'admin_id' => 'required|exists:users,id',
   ]);
   if($validator->fails()){
    return Resp::error(['message' => $validator->errors()]);
   }
   $reminderComment = Remindercomment::create($validator->validated());
   return Resp::success(['message' => 'Reminder comment posted successfully']);
}


public function getReminderComment(){
    $reminderComment = Remindercomment::get();
    return Resp::success(['reminderComment' => $reminderComment]);
}


public function createReminder(Request $request){
    $validator = Validator::make($request->all(), [
        'title' => 'required|string',
        'description' => 'required|string',
        'category_id' => 'required|integer|exists:reminder_category,id',
        'priority' => 'required|string',
        'admin_id' => 'required|array|exists:users,id',
    ]);

    if($validator->fails()){
        return Resp::error(['message' => $validator->errors()]);
    }

    $adminIds = $request->input('admin_id');
    $reminders = [];

    foreach ($adminIds as $adminId) {
        $reminder = Reminder::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $request->input('category_id'),
            'priority' => $request->input('priority'),
            'admin_id' => $adminId,
        ]);

        // Join reminder table with reminder_category table
        $reminderWithCategory = Reminder::join('reminder_category', 'reminder.category_id', '=', 'reminder_category.id')
            ->select('reminder.*', 'reminder_category.name as category_name')
            ->find($reminder->id);

        // Retrieve the admin user's data
        $adminUser = User::find($adminId);

        $reminders[] = [
            'admin' => $adminUser,
            'reminder' => $reminderWithCategory,
        ];
    }

    return Resp::success([
        'message' => 'Reminders created successfully',
        'reminders' => $reminders,
    ]);
}

public function escortVarificationList(Request $request){
    $verifications = ModelsVerify::with(['escort', 'user'])->paginate(10);
    return Resp::success(['verifications' => $verifications]);
}   

public function fanVarificationList(Request $request){
    $verifications = ModelsVerify::with(['user', 'fan'])->paginate(10);
    return Resp::success(['verifications' => $verifications]);
}

    
public function verifiedStatus(Request $request, $id){
    $validator = Validator::make($request->all(), [
        'action' => 'required|integer|in:1,0',
    ]);
    if ($validator->fails()) {
        return Resp::error(['message' => $validator->errors()]);
    }
    $verify = ModelsVerify::where('escort_id', $id )->first();
    if (!$verify) {
        return Resp::error(['message' => 'Verification record not found']);
    }
    
    if ($request->action == 1) {
        $verify->verified_status = 1;
    } elseif ($request->action == 0) {
        $verify->verified_status = 4;
    }
    $verify->escort()->update(['escort_id' => $verify->verified_status]);
    $verify->save();
    return Resp::success(['message' => 'Verification status updated successfully']);
}

    public function getComments(Request $request){
        $comments = Comment::query();
        if (!is_null($request->query('forum_id'))) {
            $comments->where('forum_id', $request->query('forum_id'));
        }
        $comments = $comments->get();
        return Resp::success(['comments' => $comments]);
    }

   public function postComment(Request $request){
    $validator = Validator::make($request->all(), [
        'comment' => 'required|string',
        'forum_id' => 'required|exists:forum,id',
        'commentator_id' => 'required|exists:users,id',
        'status' => 'required|integer|in:1,2,3',
        'message' => 'required|string',
    ]);
    if($validator->fails()){
        return Resp::error(['message' => $validator->errors()]);
    }
    $comment =Comment::create([
        'comment' => $request->comment,
        'forum_id' => $request->forum_id,
        'commentator_id' => $request->commentator_id,
        'status' => $request->status,
        'message' => $request->message,
    ]);
    $saved = $comment->save();
    if($saved){
        return Resp::success(['message' => 'Comment posted successfully', 'comment' => $comment]);
    }else{
        return Resp::error(['message' => 'Comment not posted']);
    }
   }

   public function getVarifiacationList(Request $request)
   {
       try {
           // Initialize the query on ModelsVerify and eager load related 'escort' and 'user'
           $query = ModelsVerify::with(['escort', 'user']);
           
           // Filter by verified status if provided
           if ($request->has('verified_status')) {
               $verifiedStatus = explode(',', $request->query('verified_status'));
               $query->whereIn('verified_status', $verifiedStatus);
           } else {
               // Default to verified statuses 1 and 4 if not provided
               $query->whereIn('verified_status', [1, 4]);
           }
   
           // Filter by escort name if 's' parameter is provided
           if (!is_null($request->query('s'))) {
               $query->whereHas('escort', function ($q) use ($request) {
                   $q->where('name', 'like', '%' . $request->query('s') . '%');
               });
           }
   
           // Pagination parameters
           $perPage = (int)$request->query('per_page', 10);
           $page = (int)$request->query('page', 1);
           $offset = ($page - 1) * $perPage;
           
           // Fetch results with pagination
           $verifications = $query->offset($offset)->limit($perPage)->get();
           
           // Calculate total results and total pages
           $totalResults = $query->count();
           $totalPages = ceil($totalResults / $perPage);
           
           // Build pagination response
           $pagination = [
               'total_results' => $totalResults,
               'total_pages' => $totalPages,
               'page' => $page,
               'page_size' => $perPage,
           ];
           
           // Return the successful response with verification list and pagination
           return Resp::success(['verifications' => $verifications, 'pagination' => $pagination]);
   
       } catch (\Exception $e) {
           // Return an error if something goes wrong
           return Resp::error(['message' => 'Something went wrong: ' . $e->getMessage()]);
       }
   }
   
   
   public function createForum(Request $request)
   {
       $validator = Validator::make($request->all(), [
           'title' => 'required|string',
           'category' => 'required|string',
           'description' => 'required|string',
           'status' => 'required|integer|in:1,2,3',
           'tags' => 'required|string',
           'region' => 'required|string',
       ]);
       if ($validator->fails()) {
           return Resp::error(['message' => $validator->errors()]);
       }
       $slug = Str::slug($request->input('title'), '-');
       $slug = $this->genrateForumSlug($slug);
       $forumData = $validator->validated();
       $forumData['slug'] = $slug;
       $forumData['author_id'] = auth()->user()->id; 
       $forum = new Forum();
       $forum->title = $forumData['title'];
       $forum->category = $forumData['category'];
       $forum->description = $forumData['description'];
       $forum->status = $forumData['status'];
       $forum->tags = $forumData['tags'];
       $forum->region = $forumData['region'];
       $forum->slug = $forumData['slug'];
       $forum->author_id = $forumData['author_id'];
       $forum->save();
       return Resp::success([
           'message' => 'Forum created successfully',
           'forum' => $forum,
           'author' => $forum->getAuthor,
           'slug' => $forum->slug
       ]);
   }
   
   private function genrateForumSlug($slug)
   {
       $baseSlug = $slug;
       $counter = 1;
       while (Forum::where('slug', $slug)->exists()) {
           $slug = $baseSlug . '-' . $counter;
           $counter++;
       }
       return $slug;
   }

   public function userProfile($id, Request $request)
   {
       $validator = Validator::make($request->all(), [
           'first_name' => 'required|string|max:255',
           'last_name' => 'required|string|max:255',
           'password' => 'required|string|min:8',
           'user_type' => 'required|integer|in:1,2,3', // Only allow 1 (fan) or 2 (escort)
           'username' => 'required|string|max:255',
           'email' => 'required|email',
       ]);
   
       if ($validator->fails()) {
           return Resp::error(['message' => $validator->errors()]);
       }
   
       $admin = auth()->user();
       $user = AuthUser::find($id);
   
       if (!$user) {
           return Resp::error(['message' => 'User not found']);
       }
   
       // Check if user_type is the same as the current user's type
       if ($user->user_type !== $request->input('user_type')) {
           return Resp::error(['message' => 'User type cannot be changed']);
       }
   
       if ($user->username == $request->input('username')) {
           return Resp::error(['message' => 'Username cannot be the same as the current username']);
       }
   
       $user->update([
           'username' => $request->input('username'),
           'email' => $request->input('email'),
           'password' => Hash::make($request->input('password')),
           'firstname' => $request->input('first_name'),
           'lastname' => $request->input('last_name'),
       ]);
   
       return Resp::success(['message' => 'Profile updated successfully']);
   }
    public function newUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'user_type' => 'required|integer|in:1,2,3',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $user = AuthUser::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'firstname' => $request->first_name,
            'lastname' => $request->last_name,
        ])->load('profile'); // eager load the profile relationship
        
        return Resp::success(['message' => 'User created successfully', 'user' => $user]);
    }


    public function deleteBlog($id, Request $request)
    {
        $admin = auth()->user();
        if ($admin->user_type != 3) {
            return Resp::error(['Unauthorized user is not an admin']);
        }
        $blog = Blog::find($id);
        if (!$blog) {
            return Resp::error(['Blog not found']);
        }
        $blog->delete();
        return Resp::success(['message' => 'Blog deleted successfully']);
    }
    public function getBlog($id,)
    {
        $blog = Blog::with('media')->find($id);
        return Resp::success(['blog' => $blog]);
    }

    public function getBlogBySlug($slug)
    {
        $blog = Blog::with('media')->where('slug', $slug)->first();
        if (!$blog) {
            return Resp::error(['Blog not found']);
        }
        return Resp::success(['blssog' => $blog, 'slug' => $slug]);
    }


    public function editBlog($id, Request $request)
    {
        $admin = auth()->user();
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'media_id' => 'required|exists:media,id',
            'date' => 'required|date',
        ]);
        if ($admin->user_type != 3) {
            return Resp::error(['Unauthorized user is not an admin']);
        }
        $blog = Blog::find($id);
        if (!$blog) {
            return Resp::error(['Blog not found']);
        }
        $blog->update($request->all());
        return Resp::success(['message' => 'Blog updated successfully']);
    }

    public function deleteReview($id, Request $request)
    {
        $admin = auth()->user();
        if ($admin->user_type != 3) {
            return Resp::error(['Unauthorized user is not an admin']);
        }
        $review = BaseReviews::find($id);
        if (!$review) {
            return Resp::error(['Review not found']);
        }
        $review->delete();
        return Resp::success(['message' => 'Review deleted successfully']);
    }


    public function disapproveReview($id, Request $request)
    {
        $admin = auth()->user();
        if ($admin->user_type != 3) {
            return Resp::error(['Unauthorized user is not an admin']);
        }
        $review = BaseReviews::find($id);
        if (!$review) {
            return Resp::error(['Review not found']);
        }
        $review->status = 2;
        $review->save();
        return Resp::success(['message' => 'Review disapproved successfully']);
    }

    public function approveReview($id, Request $request)
    {
        $admin = auth()->user();
        if ($admin->user_type != 3) {
            return Resp::error(['Unauthorized user is not an admin']);
        }
        $review = BaseReviews::find($id);
        if (!$review) {
            return Resp::error(['Review not found']);
        }
        $review->status = true;
        $review->save();
        return Resp::success(['message' => 'Review approved successfully']);
    }



    public function recentPurchases(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $purchases = Subscription::with('escort')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return Resp::success([
            'list' => $purchases->items(),
            'pagination' => [
                'total_results' => $purchases->total(),
                'total_pages' => $purchases->lastPage(),
                'page_number' => $purchases->currentPage(),
                'page_size' => $purchases->perPage()
            ]
        ]);
    }

    public function blog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'media_id' => 'required|exists:media,id',
            'date' => 'required|date',
            'status' => 'required|integer|in:1,2,3',
            'seo_title' => 'nullable|string',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Resp::error(['message' => $validator->errors()]);
        }
        $slug = Str::slug($request->input('title'));
        $slug = $this->generateUniqueSlug($slug);
        $blog = Blog::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'media_id' => $request->input('media_id'),
            'date' => $request->input('date'),
            'slug' => $slug, // Add the slug to the data array
            'status' => $request->input('status'),
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
            'seo_keywords' => $request->input('seo_keywords'),
        ]);

        return Resp::success(['message' => 'Blog created successfully']);
    }

    private function generateUniqueSlug($slug)
    {
        $baseSlug = $slug;
        $counter = 1;
        while (Blog::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }



    public function spotlightMedia(Request $request)
    {

        $subs_data = Subscription::leftJoin('users', 'users.id', '=', 'subscriptions.escort_id')
            ->where('plan_code', 'P104')->where('status', 'ACTIVE')->get();
        return Resp::success([
            'subscribers' => $subs_data
        ]);
    }

    public function updatePlanDetails($plan_code, Request $request)
    {

        $plan = Plan::where('code', $plan_code)->first();
        if (!$plan) {

            return Resp::error(['message' => 'Plan not found']);
        }

        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'advert_spaces' => 'nullable|integer',
            'checkout_text' => 'nullable|string',
            'desktop_placeholder' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000000',
            'mobile_placeholder' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000000'


        ]);

        if ($validator->fails()) {
            return Resp::error(['message' => $validator->errors()]);
        }

        $plan->update($request->only(['price', 'description', 'advert_spaces', 'checkout_text']));

        // Handle desktop placeholder
        if ($request->hasFile('desktop_placeholder')) {
            $desktopImage = $request->file('desktop_placeholder');
            $desktopImageName = $plan_code . '_desktop_' . time() . '_' . $plan->id . '.' . $desktopImage->getClientOriginalExtension();
            $userFolder = 'uploads/media/plan/' . $plan_code;

            if (!File::isDirectory(public_path($userFolder))) {
                File::makeDirectory(public_path($userFolder), 0755, true);
            }

            if ($plan->desktop_placeholder) {
                $oldPath = public_path($plan->desktop_placeholder);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $desktopImage->move(public_path($userFolder), $desktopImageName);
            $plan->desktop_placeholder = $userFolder . '/' . $desktopImageName;
        }

        // Handle mobile placeholder
        if ($request->hasFile('mobile_placeholder')) {
            $mobileImage = $request->file('mobile_placeholder');
            $mobileImageName = $plan_code . '_mobile_' . time() . '_' . $plan->id . '.' . $mobileImage->getClientOriginalExtension();
            $userFolder = 'uploads/media/plan/' . $plan_code;

            if (!File::isDirectory(public_path($userFolder))) {
                File::makeDirectory(public_path($userFolder), 0755, true);
            }

            if ($plan->mobile_placeholder) {
                $oldPath = public_path($plan->mobile_placeholder);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $mobileImage->move(public_path($userFolder), $mobileImageName);
            $plan->mobile_placeholder = $userFolder . '/' . $mobileImageName;
        }

        $plan->save();

        return Resp::success([
            'message' => 'Plan updated successfully',
            'plan' => $plan
        ]);
    }

    public function userQuickList(Request $request)
    {
        $user_type = $request->query('user_type');
        if (!$user_type) {
            $quick_user_list = AuthUser::select('username', 'id')->get();
        } else {
            $quick_user_list = AuthUser::select('username', 'id')
                ->where('user_type', $user_type)
                ->get();
            if ($quick_user_list->isEmpty()) {
                return Resp::error(['message' => 'No users found for this user type']);
            }
        }
        return Resp::success(['list' => $quick_user_list]);
    }


    public function createSubscription(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'plan_code' => 'required|exists:plans,code',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'image_id' => 'required|exists:media,id',
        ]);

        if ($validated->fails()) {
            return Resp::error(['message' => $validated->errors()]);
        }
        

        try {

            $plan = Plan::where('code', $request->input('plan_code'))->first();
            $subscription = Subscription::create([
                'escort_id' => $request->input('user_id'),
                'plan_code' => $request->input('plan_code'),
                'status' => 'ACTIVE',
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'created_by' => auth()->user()->id,
                'image_id' => $request->input('image_id'),
                'created_mode' => 'Admin',

            ]);
            return Resp::success([

                'message' => 'Subscription created successfully',
                'subscription' => $subscription
            ]);
        } catch (\Exception $e) {
            return Resp::error(['message' => $e->getMessage()]);
        }
    }

    public function assignPermissions($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'required|integer|min:1|max:100',
            'permission_ids.*' => 'exists:permissions,id'
        ]);
        if ($validator->fails()) {
            return Resp::error([$validator->errors()]);
        }
        $user = AuthUser::find($id);
        if (!$user || $user->user_type != 3) {
            return Resp::error(['Invalid user or user type']);
        }

        $updated_user = $user->update([
            "firstname" => $request->first_name,
            "lastname" => $request->last_name,
            "email" => $request->user_email,
            "username" => $request->user_name,
            "password" => Hash::make($request->user_pass)
        ]);
        $user->permission_ids = $request->permission_ids;
        $user->save();
        return Resp::success(['message' => 'Permissions assigned successfully']);
    }

    public function getPermissions(Request $request)
    {
        $permissions = Permissions::get();
        return Resp::success(['list' => $permissions]);
    }


    public function inquiryFormList(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $inquiries = Inquiry::orderBy('created_at', 'desc')->paginate($perPage);

        return Resp::success([
            'list' => $inquiries->items(),
            'pagination' => [
                'total_results' => $inquiries->total(),
                'total_pages' => $inquiries->lastPage(),
                'page_number' => $inquiries->currentPage(),
                'page_size' => $inquiries->perPage()
            ]
        ]);
    }

    public function recentSignups(Request $request)
    {
        $users = AuthUser::latest()
            ->when($request->query('user_type'), function ($query) use ($request) {
                $query->where('user_type', $request->query('user_type'));
            })
            ->paginate(10); // Update this line to show only 10 signups
    
        $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'created_at' => $user->created_at
            ];
        });
    
        return Resp::success([
            'total_count' => $users->total(),
            'users' => $users->items(),
            'pagination' => [
                'total_results' => $users->total(),
                'total_pages' => $users->lastPage(),
                'page_number' => $users->currentPage(),
                'page_size' => $users->perPage()
            ]
        ]);
    }
   
    public function updatePlan($plan_code, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'string|required',
            'price' => 'decimal:2|required',
            'description' => 'required',
            'days' => 'integer|required',
            'allowed_user_account' => 'integer|required',
        ]);
        if ($validator->fails()) {
            return Resp::error([$validator->errors()]);
        }
        $code = $plan_code;
        $plan = Plan::where('code', $code)->first();
        if (!$plan) {
            return Resp::error(['Plan not found']);
        }
        $updated_plan = $plan->update([
            'title' => $request->title,
            'price' => $request->price,
            'description' => $request->description,
            'days' => $request->days,
            'allowed_user_account' => $request->allowed_user_account,
        ]);
        if (!$updated_plan) {
            return Resp::error(['Failed to update plan']);
        }
        $updated_plan = Plan::where('code', $code)->first();
        return Resp::success(['details' => $updated_plan]);
    }

    public function getPlan($id, Request $request)
    {


        $plan = Plan::where('code', $id)->first();
        if (!$plan) {
            return Resp::error(['Plan not found']);
        }
        return Resp::success(['details' => $plan]);
    }

    public function updateProfile($id, Profile $profile, Request $request)
    {
        $admin = auth()->user();

        if ($admin->user_type != 3) {
            return Resp::error(['Unauthorized user is not an admin']);
        }

        $request_data = $request->all();

        $validator = Validator::make($request->all(), $profile->rules());

        if ($validator->fails()) {
            return Resp::error(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }


        $user_id = $id;
        $user_exists = AuthUser::find($user_id);
        if (!$user_exists) {
            return Resp::error(['Profile not found']);
        }

        $profile = AuthUser::find($user_id)->profile;

        if (!$profile) {
            return Response::json(['error' => 'Profile not found'], 404);
        }

        $city_id = $request->input('city_id');
        $city_exists = Location::where('id', $city_id)->where('type', 'city')->first();

        $county_id = $city_exists->parent_id;
        $county_exists = Location::where('id', $county_id)->where('type', 'county')->first();
        if (!$county_exists) {
            return Resp::error(['County not found']);
        }
        $region_id = $county_exists->parent_id;
        $region_exists = Location::where('id', $region_id)->where('type', 'region')->first();
        if (!$region_exists) {
            return Resp::error(['Region not found']);
        }

        $whatsapp_number = 0;
        $country_code = 0;
        $allow_whatsapp = $request->input('allow_whatsapp');
        if ($allow_whatsapp) {
            $whatsapp_number = $request->input('whatsapp_number');
            $country_code = $request->input('country_code');
        }
        $name = $request->input('name');
        if (empty($name)) {
            $name = $user_exists->username;
        }
        $languages = $request->input('languages');
        $updated = $profile->update([
            'name' => $name,
            'phone_number' => $request->input('phone_number'),
            'gender' => $request->input('gender'),
            'date_of_birth' => $request->input('date_of_birth'),
            'orientation' => $request->input('orientation'),
            'ethnicity' => $request->input('ethnicity'),
            'height' => $request->input('height'),
            'weight' => $request->input('weight'),
            'hair' => $request->input('hair'),
            'eyes' => $request->input('eyes'),
            'breasts_size' => $request->input('breasts_size'),
            'breasts_cup' => $request->input('breasts_cup'),
            'butt' => $request->input('butt'),
            'body' => $request->input('body'),
            'cock_size' => $request->input('cock_size'),
            'languages' => $request->input('languages'),
            'offer_services_to' => $request->input('offer_services_to'),
            'has_twitter' => $request->input('has_twitter'),
            'has_snapchat' => $request->input('has_snapchat'),
            'has_instagram' => $request->input('has_instagram'),
            'has_tiktok' => $request->input('has_tiktok'),
            'twitter_handle' => $request->input('twitter_handle'),
            'snapchat_handle' => $request->input('snapchat_handle'),
            'instagram_handle' => $request->input('instagram_handle'),
            'tiktok_handle' => $request->input('tiktok_handle'),
            'extra_services' => $request->input('extra_services'),
            'is_incall_enabled' => $request->input('is_incall_enabled'),
            'is_outcall_enabled' => $request->input('is_outcall_enabled'),
            'has_onlyfans' => $request->input('has_onlyfans'),
            'has_manyvids' => $request->input('has_manyvids'),
            'has_fancentro' => $request->input('has_fancentro'),
            'onlyfans_handle' => $request->input('onlyfans_handle'),
            'manyvids_handle' => $request->input('manyvids_handle'),
            'fancentro_handle' => $request->input('fancentro_handle'),
            'city_id' => $city_id,
            'region_id' => $region_id,
            'county_id' => $county_id,
            'allow_whatsapp' => $allow_whatsapp
        ]);
        if (!$updated) {
            return Resp::error(['error' => 'Failed to update profile'], 500);
        }
        // Find the updated escort profile
        //$data = Profile::where('escort_id', $user_id)->get();
        $profile_data = Profile::where('escort_id', $user_id)->first();

        $is_incall_enabled = $request->input('is_incall_enabled');
        $is_outcall_enabled = $request->input('is_outcall_enabled');
        // Define base validation rules
        $baseRules = [
            'rates' => 'required|array',

        ];
        $customMessages = [];
        $rateFields = ['15_min', '30_min', '1_hour', '2_hour', '4_hour', 'overnight'];

        if ($is_incall_enabled) {

            $baseRules["rates.*.category"] = [
                'required',
                'in:Incall,Outcall',
            ];
            foreach ($rateFields as $field) {
                $baseRules["rates.*.{$field}"] = [
                    'required',
                ];
                $customMessages["rates.*.{$field}.required"] = "The {$field} field is required for Incall rates.";
            }
        }

        if ($is_outcall_enabled) {
            $baseRules["rates.*.category"] = [
                'required',
                'in:Outcall,Incall',
            ];
            foreach ($rateFields as $field) {
                $baseRules["rates.*.{$field}"] = [
                    'required',
                ];
                $customMessages["rates.*.{$field}.required"] = "The {$field} field is required for Outcall rates.";
            }
        }

        // Validate request data
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {

            return Response::json(['error' => $validator->errors()],);
        }



        $profile_rates = ProfileRates::where('escort_id', $profile_data->id)->get();
        $rates_data = $request->input('rates');
        if (!$profile_rates) {
            $profile_rates = ProfileRates::create([
                'escort_id' => $profile_data->id,
            ]);
        }
        foreach ($rates_data as $rate) {
            $category = strtolower($rate['category']);
            $profile_rates = ProfileRates::where('escort_id', $user_id)
                ->where('category', $category)
                ->first();


            if (($category == 'outcall' && $is_outcall_enabled) || ($category == 'incall' && $is_incall_enabled)) {
                $rate_data = [
                    'category' => $rate['category'],
                    '15_min' => $rate['15_min'],
                    '30_min' => $rate['30_min'],
                    '1_hour' => $rate['1_hour'],
                    '2_hour' => $rate['2_hour'],
                    '4_hour' => $rate['4_hour'],
                    'overnight' => $rate['overnight'],
                ];

                if ($profile_rates) {
                    $profile_rates->update($rate_data);
                } else {
                    $rate_data['escort_id'] = $user_id;
                    ProfileRates::create($rate_data);
                }
            }
        }
        $profile_data = Profile::where('escort_id', $user_id)->first();
        $profile_data->rates;
        return Resp::success(['details' => $profile_data]);
    }


    public function getProfile($id)
    {
        $profile = AuthUser::with(['profile', 'profile.rates'])->find($id);
        if (!$profile) {
            return Resp::error(['Profile not found']);
        }

        return Resp::success(['details' => $profile]);
    }


    public function getUsers(Request $request)
    {
        $user_type = $request->query('user_type');
        $search = $request->query('s');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
    
        $users = AuthUser::query()
            ->select('users.*') // Select all fields from users table
            ->when($user_type, function ($query) use ($user_type) {
                $userTypes = explode(',', $user_type); // Split the comma-separated string into an array
                return $query->whereIn('users.user_type', $userTypes);
            })
            // Left join with subscriptions to preserve all users
            ->leftJoin('subscriptions', 'users.id', '=', 'subscriptions.escort_id')
            // Select subscription fields with distinct prefixes
            ->selectRaw('subscriptions.id as subscription_id,
                        subscriptions.status as subscription_status,
                        subscriptions.plan_code,
                        subscriptions.start_date,
                        subscriptions.end_date')
    
            ->orderBy('users.id', 'desc'); // Add this line to order results in descending order
    
        // Add search filter
        if ($search) {
            $users->where(function ($query) use ($search) {
                $query->where('email', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }
    
        // Pagination
        $totalCount = $users->count();
    
        $result = $users->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();
    
        return Resp::success([
            'list' => $result,
            'total_count' => $totalCount,
            'page' => (int)$page,
            'per_page' => (int)$perPage
        ]);
    }

    public function getLiveAdvertsUsers(Request $request)
    {
        $users = AuthUser::with('profile')->get();
        return Resp::success(['list' => $users]);
    }

    public function getAdminUsers(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $page = $request->query('page', 1); // Default to page 1
        $offset = ($page - 1) * $perPage;

        $users = AuthUser::where('user_type', 3)
            ->when($request->query('email'), function ($query, $email) {
                $query->where('email', 'like', '%' . $email . '%');
            })
            ->when($request->query('username'), function ($query, $username) {
                $query->where('username', 'like', '%' . $username . '%');
            })
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $totalCount = AuthUser::where('user_type', 3)->count();

        return Resp::success([
            'list' => $users,
            'total_count' => $totalCount,
            'page' => (int)$page,
            'per_page' => $perPage
        ]);
    }


    public function getUserPermissions($id, Request $request)
    {
        $user = AuthUser::find($id);
        if (!$user) {
            return Resp::error(['User not found']);
        } elseif ($user->user_type != 3) {
            return Resp::error(['Unauthorized user is not an admin']);
        }

        $permissions = [];
        // Convert permission_ids to array if it's a string
        $permission_ids = is_string($user->permission_ids)
            ? json_decode($user->permission_ids, true)
            : $user->permission_ids;

        if (!empty($permission_ids)) {
            $permissions = Permissions::whereIn('id', $permission_ids)->get();
        }

        return Resp::success(['list' => $permissions, 'user' => $user]);
    }


    public function getForumPost(Request $request,$id)
    {
        $post = Forum::with('PostComments')->find($id);
        if (!$post) {
            return Resp::error(['Post not found']);
        }
        return Resp::success(['data' => $post]);
    }

    public function updateMedia(Request $request, $id){
        try{

            $validator=Validator::make($request->all(),[
                'title'=>'required',
                'description'=>'required',
                'alternative_text'=>'required',
                'caption'=>'required'
            ]);
            if($validator->fails()){
                return Resp::error(['message'=>$validator->errors()]);
            }
            $media = Media::find($id);
            if (!$media) {
                return Resp::error(['Media not found']);
            }
            $media->update([
                'title'=>$request->title,
                'description'=>$request->description,
                "alternative_text"=>$request->alternative_text,
                'caption'=>$request->caption
            ]);
            return Resp::success(['message' => 'Media updated successfully']);
        }catch(\Exception $e){
            return Resp::error(['message' => $e->getMessage()]);
        }
    }
}
