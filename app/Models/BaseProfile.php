<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\MasterData\Gender;
use App\MasterData\Orientation;
use App\MasterData\Ethnicity;
use App\MasterData\Hair;
use App\MasterData\Eyes;
use App\MasterData\BreastsSize;
use App\MasterData\BreastsCup;
use App\MasterData\Butt;
use App\MasterData\Body;
use App\MasterData\CockSize;
use App\MasterData\Languages;
use App\MasterData\OfferServicesTo;
use App\MasterData\ExtraServices;
use Modules\Auth\app\Models\AuthUser;
use Illuminate\Validation\Rule;
use Modules\Escort\app\Models\EscortSubscription;
use Modules\Escort\app\Models\Profile;
class BaseProfile extends Model
{

    protected $table = 'profile';
    protected $fillable = [
        'name', 
        'phone_number',
        'gender',
        'date_of_birth',
        'orientation',
        'ethnicity', 
        'height', 
        'weight', 
        'hair', 
        'eyes', 
        'breasts_size',
        'breasts_cup', 
        'butt', 
        'body', 
        'cock_size',
        'languages',
        'offer_services_to',
        'has_twitter',
        'has_snapchat',
        'has_instagram',
        'has_tiktok',
        'twitter_handle',
        'snapchat_handle',
        'instagram_handle',
        'tiktok_handle',
        'extra_services',
        'escort_id',
        'city_id',
        'region_id',
        'county_id',
        'has_onlyfans',
        'has_manyvids',
        'has_fancentro',
        'onlyfans_handle',
        'manyvids_handle',
        'fancentro_handle',
        'is_incall_enabled',
        'is_outcall_enabled',
        'allow_whatsapp',
        'whatsapp_number',
        'country_code',
        'is_profile',
        'is_media',
        'description',
        'rates',
        'nationality',
        'verified_status',
        'age',
        
        
    ];

    protected $casts = [
        'languages' => 'json',
        'offer_services_to' => 'json',
        'extra_services' => 'json',
        'whatsapp_number' => 'integer',
        'phone_number' => 'string',
    ];


    public static function rules () {
        return [
        'name' => 'string|max:255',
        'phone_number' => 'required|string',
        'allow_whatsapp' => 'boolean',
        //'gender' => 'required|in:'.implode(',',Gender::getValues()),
        'date_of_birth' => 'string',
        //'orientation'=>'required|in:'.implode(',',Orientation::getValues()),
        //'ethnicity'=>'required|in:'.implode(',',Ethnicity::getValues()),
        'ethnicity' => 'required',
        //'nationality' => 'required',
        'height'=>'required|integer',
        'weight'=>'required|integer',
        //'hair'=>'required|in:'.implode(',',Hair::getValues()),
        //'eyes'=>'required|in:'.implode(',',Eyes::getValues()),
        //'breasts_size'=>'required|in:'.implode(',',BreastsSize::getValues()),
        //'breasts_cup'=>'required|in:'.implode(',',BreastsCup::getValues()),
        //'butt'=>'required|in:'.implode(',',Butt::getValues()),
        //'body'=>'required|in:'.implode(',',Body::getValues()),
        //'cock_size'=>'required|in:'.implode(',',CockSize::getValues()),
        //'languages'=>'required|in:'.implode(',',Languages::getValues()),
        'languages' => 'required',
        //'languages.*' => 'in:'.implode(',', Languages::getValues()),
        'offer_services_to'=>'required|array',
        'offer_services_to.*'=>'in:'.implode(',',OfferServicesTo::getValues()),
        'has_twitter' => 'boolean|nullable',
        'has_snapchat' => 'boolean|nullable',
        'has_instagram' => 'boolean|nullable',
        'has_tiktok' => 'boolean|nullable',
        'twitter_handle' => 'required_if:has_twitter,1|string|nullable',
        'snapchat_handle' => 'required_if:has_snapchat,1|string|nullable',
        'instagram_handle' => 'required_if:has_instagram,1|string|nullable',
        'tiktok_handle' => 'required_if:has_tiktok,1|string|nullable',
        'is_incall_enabled' => 'required|boolean',
        'is_outcall_enabled' => 'required|boolean',
        // 'verified_status' => 'nullable|integer',
        // 'extra_services' => 'nullable',
        //'extra_services.*.key'=>'in:'.implode(',',ExtraServices::getKeys()),
        'description' => 'nullable|string',
        'city_id' => [
            'required',
            'integer',
            Rule::exists('locations', 'id')->where(function ($query) {
                $query->where('type', 'city');
            }),
            ]
        ];
    }

    public function user(){
        return $this->belongsTo(AuthUser::class,'escort_id','id');
    }
    public function reviews(){
        return $this->hasMany(BaseReviews::class,'escort_id','escort_id')->where('status',1);
    }
    public function media(){
        return $this->hasMany(Media::class,'escort_id','escort_id')
        ->where('is_temp', 0);
    
    }
    public function subscriptions(){
        return $this->hasMany(BaseSubscription::class,'escort_id','escort_id');
    }

     public function profile(){
        return $this->belongsTo(Profile::class,'escort_id','id');
    }
}



