<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BaseProfile;
use App\Models\User;
use App\Models\Media;
use Illuminate\Support\Facades\Hash;
use Modules\Escort\app\Models\ProfileRates;
use App\Models\Location;

class ImportProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-profiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import profiles from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define the path to the CSV file.
        $filePath = storage_path('scraped_data_test.csv');

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1;
        }

        $total = 786; // total number of items, e.g., number of rows in CSV
        $bar = $this->output->createProgressBar($total);

        $priceMapping = [
            "A Levels" => 100,
            "BDSM" => 200,
            "DUO" => 300,
            "Dining" => 400,
            "Domination" => 500,
            "Escorting" => 600,
            "Fetish" => 700,
            "French Kiss" => 800,
            "GFE" => 900,
            "Massage" => 1000,
            "Modelling" => 1100,
            "OW" => 1200,
            "OWO" => 1300,
            "PSE" => 1400,
            "Phone" => 1500,
            "Quickie" => 1600,
            "Role play" => 1700,
            "Stress Relief" => 1800,
            "Strip-tease" => 1900,
            "Tantric Massage" => 2000,
            "Travel" => 2100,
            "WS" => 2200,
            "Webcam" => 2300,
        ];

        // Open the CSV file for reading.
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Assuming the first row contains headers.
            $header = fgetcsv($handle, 1000, ',');
            //$this->info("\nHeader: " . print_r($header, true));
            $i=1;
            $bar->start();

            

            // Loop through each remaining row.
            while (($row = fgetcsv($handle, 0, ',')) !== false) {

                if (count($row) !== count($header)) {
                    $this->warn("Skipping malformed row: " . implode(',', $row));
                    continue;
                }
                $rowData = array_combine($header, $row);

                $ageString = $rowData['Age'];
                preg_match('/(\d+)\s+years?\s+old/', $ageString, $matches); // Extract the age using regex

                if (!empty($matches[1])) {
                    $age = (int)$matches[1]; // Convert the extracted age to an integer
                    $birthYear = date('Y') - $age; // Calculate the birth year
                    $birthDate = $birthYear . '-01-01'; // Set the date to January 1st of the birth year
                } else {
                    $birthDate = null; // Handle the case where age is not found
            }

                // Assuming $rowData['name'] contains the name
                $name = strtolower(trim($rowData['name'])); // Convert to lowercase and trim spaces
                // Sanitize the username to remove non-ASCII characters
                $username = preg_replace('/[^\x20-\x7E]/', '', $name); // Keep only ASCII characters
                $username = preg_replace('/\s+/', '_', $username); // Replace spaces with underscores
                $randomNumbers = rand(1000, 9999); // Generate two random numbers
                $username = substr($username, 0, 8) . $randomNumbers; // Take first 8 characters and append random numbers


                // Convert the services string to an array
                $servicesArray = explode(',', $rowData['Services']);


                

                // Handle ethnicity
                $ethnicity = $rowData['Ethnicity'];

                if (strpos($ethnicity, ',') !== false) {
                    // Split into an array if there are multiple values
                    $ethnicityArray = explode(',', $ethnicity); // Split into an array

                    // Check for variations of "Other" or "Mixed"
                    if (in_array('Other', $ethnicityArray) || in_array('other', $ethnicityArray) || in_array('Mixed', $ethnicityArray) || in_array('mixed', $ethnicityArray)) {
                       $ethnicity = 'Others'; // Set to "Others" if "Other" or "Mixed" is found
                    } else {
                        $ethnicity = trim($ethnicityArray[0]); // Use the first value if none of those are found
                    }
                } else {
                
                    // If it's a single value, check for "Other"
                   if (stripos($ethnicity, 'other') !== false) {
                      $ethnicity = 'Others'; // Set to "Others" if "Other" is found
                   } else {
                     $ethnicity = trim($ethnicity); // Just trim it
                    }
                  }

                // Map the services to the desired format   
                $extra_services = array_map(function($service) use ($priceMapping) {
                    $service = trim($service); // Trim whitespace
                    $service = trim($service, '"'); // Trim quotes
                    $service = str_replace('"', '', $service); // Remove any remaining quotes
                
                    return [
                    'key' => $service,
                    'price' => $priceMapping[$service] ?? 0 // Default to 0 if not found
                    ];
                }, $servicesArray);


                $email = $username . '@transbunnies.com'; // Create email address

                try{
                    $user= User::create([
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make("12345678"),
                        'user_type' => 2,
                    ]);
                    if(!$user){
                        $this->error("Failed to create user for row : " . $i);
                    }
                    $profile= '';
                    if($user){
                        $profile = BaseProfile::create([
                            'name'=>$rowData['name'],
                            'escort_id'=>$user->id,
                            'phone_number' => ltrim($rowData['phone number'], "'"), // Remove leading apostrophe
                            'gender'=> $rowData['Gender'],
                            'languages'=> explode(',', $rowData['Language']), // Convert to array
                            'offer_services_to'=> explode(',',$rowData['My service is for']),
                            'extra_services' => $extra_services, // Add the formatted extra services
                            'ethnicity'=> $ethnicity,
                            //'nationality'=> $ethnicity,
                            'description'=> $rowData['description'],
                            'verified_status'=> 1,
                            'is_profile'=>1,
                            'is_media'=>1,
                            'date_of_birth'=>$birthDate


                        ]);
                        if(!$profile){
                            $this->error("Failed to create profile for row : " . $i);
                        }
                    }

                // Assuming $rowData['rates table'] contains the rates in JSON format
                $ratesJson = $rowData['rates table'];
                $ratesArray = json_decode($ratesJson, true); // Decode the JSON string into an array


                    // Prepare the formatted rates for the profile update API
                $formattedRates = [];

                // Iterate through each rate category
                foreach ($ratesArray as $rate) {
                    $category = $rate['incall'] !== '-' ? 'Incall' : 'Outcall'; // Determine the category based on incall or outcall

                    $formattedRates[] = [
                        'category' => $category, // Set the category
                        '15_min' => $rate['duration'] === '15 minutes' ? ($rate['incall'] === '-' ? 0 : (int)str_replace('£', '', $rate['incall'])) : 0,
                        '30_min' => $rate['duration'] === '30 minutes' ? ($rate['incall'] === '-' ? 0 : (int)str_replace('£', '', $rate['incall'])) : 0,
                        '1_hour' => $rate['duration'] === '1 hour' ? ($rate['incall'] === '-' ? 0 : (int)str_replace('£', '', $rate['incall'])) : 0,
                        '2_hour' => $rate['duration'] === '2 hours' ? ($rate['incall'] === '-' ? 0 : (int)str_replace('£', '', $rate['incall'])) : 0,
                        '4_hour' => $rate['duration'] === '4 hours' ? ($rate['incall'] === '-' ? 0 : (int)str_replace('£', '', $rate['incall'])) : 0,
                        'overnight' => $rate['duration'] === 'Overnight' ? ($rate['incall'] === '-' ? 0 : (int)str_replace('£', '', $rate['incall'])) : 0,
                        ];
                        if($category=="Incall"){
                            $profile->update([
                                'is_incall_enabled'=>1
                            ]);
                        }else if($category=="Outcall"){
                            $profile->update([
                                'is_outcall_enabled'=>1
                            ]);
                        }
                }

                if($profile){
                    foreach ($formattedRates as $rate) {
                        $category = strtolower($rate['category']);
                        $profile_rates = ProfileRates::where('escort_id', $profile->escort_id)
                            ->where('category', $category)
                            ->first();
        
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
                                $rate_data['escort_id'] = $profile->escort_id;
                                ProfileRates::create($rate_data);
                            }
                        }
                }


                $imageLinks = explode(',', $rowData['imagelinks']); // Convert to array

                if($profile){
                    foreach ($imageLinks as $link) {
                        $link = trim($link); // Trim whitespace
                    
                        // Create a media entry for each image link
                        Media::create([
                            'type' => 'gallery', // Assuming type is 'gallery'
                            'path' => $link, // The image URL
                            'is_temp'=>0,
                            'escort_id' => $profile->escort_id, // Assuming you want to link it to the user
                        ]);
                    }
                }


                // Handle location
                $location = $rowData['Location'];
                $locationParts = explode('-', $location); // Split the string by the hyphen
                $city = trim($locationParts[0]); // Get the first part and trim whitespace
                $county_data='';
                $location_data=Location::where('name','like','%'.$city.'%')->
                                        where('type','city')->first();
                if($location_data){
                    $county_data=Location::where('id',$location_data->parent_id)->
                                            where('type','county')->first();
                }

                if($location_data && $county_data){
                    $profile->update([
                        'city_id'=>$location_data->id,
                        'county_id'=>$county_data->id,
                        'region_id'=>$county_data->parent_id
                    ]);
                }
                }catch(\Exception $e){
                    $this->error("Failed to create user for row : " . $i);
                }

                if($i==4){
                    //print_r($formattedRates);
                    //print_r($rowData);
                    //$this->info($username."   ".$email);
                }
                $i++;                
                $bar->advance();

            }

            fclose($handle);
            $bar->finish();
            $this->info("\nProfiles imported successfully.");
            $this->info('Total profiles: ' . $i);
        } else {
            $this->error('Unable to open the CSV file.');
        }
    }
}
