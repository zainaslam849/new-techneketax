<?php
require("config/env.php");
header('Content-Type: application/json');
date_default_timezone_set('America/New_York');
use PHRETS\Configuration;
use PHRETS\Session;
use PHRETS\Models\Search\Results;
$config = new \PHRETS\Configuration;
$config->setLoginUrl('http://rets.torontomls.net:6103/rets-treb3pv/server/login')
    ->setUsername('D24cos')
    ->setPassword('M96$b71');


//IMAGE CONFIGURATION
$configImage = new \PHRETS\Configuration;
$configImage->setLoginUrl('http://3pv.torontomls.net:6103/rets-treb3pv/server/login')
    ->setUsername('Pt024cos')
    ->setPassword('M96$b71');



//CondoProperty
//ResidentialProperty
//CommercialProperty

// Function to fetch images for a given MLS number and return as JSON
function fetchAndReturnImages($config, $mlsNumber) {
    $rets = new Session($config);
    try {
        $connect = $rets->Login();
        $objects = $rets->GetObject('Property', 'Photo', $mlsNumber, '*', 1);
        $imageData = [];

        foreach ($objects as $object) {

            if ($object->isError()) {
                $error = $object->getError();
                $imageData['error'] = "Error: " . $error->getMessage();
            } else {

                $contentId = $object->getContentId();
                $objectId = $object->getObjectId();
                $contentType = $object->getContentType();
                $contentDescription = $object->getContentDescription();
                $contentSubDescription = $object->getContentSubDescription();
                $content = $object->getContent();
                $size = $object->getSize();
                $isPreferred = $object->isPreferred();
                $location = $object->getLocation();
                $imageData[] = [
                    'content_id' => $contentId,
                    'object_id' => $objectId,
                    'content_type' => $contentType,
                    'content_description' => $contentDescription,
                    'content_sub_description' => $contentSubDescription,
                    'size' => $size,
                    'is_preferred' => $isPreferred,
                    'location' => $location
                ];
            }
        }
        $rets->Logout();
        $rets->Disconnect();
        return $imageData;
    } catch (\Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}



function fetchListingData($config, $property_type,$price_range,$area, $limit) {
global $h;



    $rets = new Session($config);
    try {
        // Login to the RETS server
        $connect = $rets->Login();

        // Fetch the full set of available listing data
        //$results = $rets->Search('Property', 'ResidentialProperty', "(Ml_num=$mlsNumber)");
        $results = $rets->Search('Property', $property_type, '(Lp_dol='.$price_range.') (Area='.$area.')', ['Limit' => $limit]);

        // Process the results
//        $listingData = [];
//        $listingData[] = [
//                 'mls_number' => $data['fields']['Ml_num'],
//                 'list_price' => $data['fields']['Lp_dol'],
//                 'address' => $data['fields']['Addr'],
//            ];

       foreach ($results as $data) {
            // Access listing fields and add to data array

            $listingData[] = [
                'ml_num' => $data->get('Ml_num'),
                'parcel_id' => $data->get('Parcel_id'),
                'ad_text' => $data->get('Ad_text'),
                'list_price' => $data->get('Lp_dol'),
                'taxes' => $data->get('Taxes'),
                'street' => $data->get('St'),
                'address' => $data->get('Addr'),
                'area' => $data->get('Area'),
                'zip' => $data->get('Zip'),
                'county' => $data->get('County'),
                'front_ft'=> $data->get('Front_ft'),
                'sqft'=> $data->get('Sqft'),
                'community'=> $data->get('Community'),
                'rltr' => $data->get('Rltr'),
                'municipality' => $data->get('Municipality'),
                'municipality_district' => $data->get('Municipality_district'),
                'pool' => $data->get('Pool'),
                'gas' => $data->get('Gas'),
                'electricity' => $data->get('Elec'),
                'util_tel' => $data->get('Util_tel'),
                'util_cable' => $data->get('Util_cable'),
                'water' => $data->get('Water'),
                'style' => $data->get('Style'),
                'bedrooms' => $data->get('Br'),
                'rms' => $data->get('Rms'),
                //ROOMS
                'rm1_out' => $data->get('Rm1_out'),
                'rm1_len' => $data->get('Rm1_len'),
                'rm1_wth' => $data->get('Rm1_wth'),
                'rm2_out' => $data->get('Rm2_out'),
                'rm2_len' => $data->get('Rm2_len'),
                'rm2_wth' => $data->get('Rm2_wth'),
                'rm3_out' => $data->get('Rm3_out'),
                'rm3_len' => $data->get('Rm3_len'),
                'rm3_wth' => $data->get('Rm3_wth'),
                'rm4_out' => $data->get('Rm4_out'),
                'rm4_len' => $data->get('Rm4_len'),
                'rm4_wth' => $data->get('Rm4_wth'),
                'rm5_out' => $data->get('Rm5_out'),
                'rm5_len' => $data->get('Rm5_len'),
                'rm5_wth' => $data->get('Rm5_wth'),
                'rm6_out' => $data->get('Rm6_out'),
                'rm6_len' => $data->get('Rm6_len'),
                'rm6_wth' => $data->get('Rm6_wth'),
                'rm7_out' => $data->get('Rm7_out'),
                'rm7_len' => $data->get('Rm7_len'),
                'rm7_wth' => $data->get('Rm7_wth'),
                'rm8_out' => $data->get('Rm8_out'),
                'rm8_len' => $data->get('Rm8_len'),
                'rm8_wth' => $data->get('Rm8_wth'),
                'rm9_out' => $data->get('Rm9_out'),
                'rm9_len' => $data->get('Rm9_len'),
                'rm9_wth' => $data->get('Rm9_wth'),
                'rm10_out' => $data->get('Rm10_out'),
                'rm10_len' => $data->get('Rm10_len'),
                'rm10_wth' => $data->get('Rm10_wth'),
                'rm11_out' => $data->get('Rm11_out'),
                'rm11_len' => $data->get('Rm11_len'),
                'rm11_wth' => $data->get('Rm11_wth'),
                'rm12_out' => $data->get('Rm12_out'),
                'rm12_len' => $data->get('Rm12_len'),
                'rm12_wth' => $data->get('Rm12_wth'),
                'rooms_plus' => $data->get('Rooms_plus'),

                'baths' => $data->get('Bath_tot'),
                'year_built' => $data->get('Yr'),
                'ass_year' => $data->get('Ass_year'),
                'tot_park_spcs' => $data->get('Tot_park_spcs'),
                'timestamp_sql' => $data->get('Timestamp_sql'),
                'pix_updt' => $data->get('Pix_updt'),
                'idx_dt' => $data->get('Idx_dt'),
                'type_own1_out' => $data->get('Type_own1_out'),
                //FEATURES
                'prop_feat1_out' => $data->get('Prop_feat1_out'),
                'prop_feat2_out' => $data->get('Prop_feat2_out'),
                'prop_feat3_out' => $data->get('Prop_feat3_out'),
                'prop_feat4_out' => $data->get('Prop_feat4_out'),
                'prop_feat5_out' => $data->get('Prop_feat5_out'),
                'prop_feat6_out' => $data->get('Prop_feat6_out'),

                'access_prop1' => $data->get('Access_prop1'),
                'access_prop2' => $data->get('Access_prop2'),
                'acres' => $data->get('Acres'),
                'addl_mo_fee' => $data->get('Addl_mo_fee'),
                'a_c' => $data->get('A_c'),
                'All_inc' => $data->get('All_inc'),
                'alt_power1' => $data->get('Alt_power1'),
                'alt_power2' => $data->get('Alt_power2'),
                'apt_num' => $data->get('Apt_num'),
                'area_code' => $data->get('Area_code'),
                'bsmt1_out' => $data->get('Bsmt1_out'),
                'bsmt2_out' => $data->get('Bsmt2_out'),
                'br_plus' => $data->get('Br_plus'),

                'cable' => $data->get('Cable'),
                'cac_inc' => $data->get('Cac_inc'),
                'central_vac' => $data->get('Central_vac'),
                'comel_inc' => $data->get('Comel_inc'),
                'community_code' => $data->get('Community_code'),
                'cross_st' => $data->get('Cross_st'),
                'disp_addr' => $data->get('Disp_addr'),
                'drive' => $data->get('Drive'),
                'easement_rest1' => $data->get('Easement_rest1'),
                'easement_rest2' => $data->get('Easement_rest2'),
                'easement_rest3' => $data->get('Easement_rest3'),
                'easement_rest4' => $data->get('Easement_rest4'),
                'elevator' => $data->get('Elevator'),
                'constr1_out' => $data->get('Constr1_out'),
                'constr2_out' => $data->get('Constr2_out'),
                'extras' => $data->get('Extras'),
                'den_fr' => $data->get('Den_fr'),
                'farm_agri' => $data->get('Farm_agri'),
                'fpl_num' => $data->get('Fpl_num'),
                'fractional_ownership' => $data->get('Fractional Ownership'),
                'comp_pts' => $data->get('Comp_pts'),
                'furnished' => $data->get('Furnished'),
                'gar_spaces' => $data->get('Gar_spaces'),
                'gar_type' => $data->get('Gar_type'),
                'heat_inc' => $data->get('Heat_inc'),
                'fuel' => $data->get('Fuel'),
                'heating' => $data->get('Heating'),
                'hydro_inc' => $data->get('Hydro_inc'),
                'num_kit' => $data->get('Num_kit'),
                'kit_plus' => $data->get('Kit_plus'),
                'laundry' => $data->get('Laundry'),
                'laundry_lev' => $data->get('Laundry_lev'),
                'llse_terms' => $data->get('Lse_terms'),
                'legal_desc' => $data->get('Legal_desc'),
                'level1' => $data->get('Level1'),
                'level2' => $data->get('Level2'),
                'level3' => $data->get('Level3'),
                'level4' => $data->get('Level4'),
                'level5' => $data->get('Level5'),
                'level6' => $data->get('Level6'),
                'level7' => $data->get('Level7'),
                'level8' => $data->get('Level8'),
                'level9' => $data->get('Level9'),
                'level10' => $data->get('Level10'),
                'level11' => $data->get('Level11'),
                'level12' => $data->get('Level12'),
                'link_yn' => $data->get('Link_yn'),
                'link_comment' => $data->get('Link_Comment'),
                'outof_area' => $data->get('Outof_area'),
                'portion_of_property_for_lease' => $data->get('Portion of Property for Lease'),
                'portion_for_lease_comments' => $data->get('Portion for Lease Comments'),
                'potl' => $data->get('Potl'),
                'park_chgs' => $data->get('Park_chgs'),
                'prkg_inc' => $data->get('Prkg_inc'),
                'park_spcs' => $data->get('Park_spcs'),
                'retirement' => $data->get('Retirement'),
                'rm1_dc1_out' => $data->get('Rm1_dc1_out'),
                'rm1_dc2_out' => $data->get('Rm1_dc2_out'),
                'rm1_dc3_out' => $data->get('Rm1_dc3_out'),
                'rm1_len' => $data->get('Rm1_len'),
                'rm1_wth' => $data->get('Rm1_wth'),
                'rm2_dc1_out' => $data->get('Rm2_dc1_out'),
                'rm2_dc2_out' => $data->get('Rm2_dc2_out'),
                'rm2_dc3_out' => $data->get('Rm2_dc3_out'),
                'rm2_len' => $data->get('Rm2_len'),
                'rm2_wth' => $data->get('Rm2_wth'),
                'zoning' => $data->get('Zoning'),
                'status' => $data->get('Status'),
            ];
        }

        // Logout from the RETS server
        $rets->Logout();

        // Disconnect from the RETS server
        $rets->Disconnect();

        //GET IMAGE
        //$imageDataJson = fetchSaveAndReturnUniqueImageNames($configImage, $mlsNumber);


        $agent_id=1;
        $cat_id=2;

        // Return listing data
//        $h->table('listings')->insertOne([
//                'Agent_id' =>$agent_id, 'Cat_id'=>$cat_id, 'Ml_num'=>$listingData['ml_num'],
//                'Addr'=>$listingData['address'],'Ad_text'=>$listingData['ad_text'],'Lp_dol'=>$listingData['list_price'],
//                'Taxes'=>$listingData['taxes'], 'St'=>$listingData['street'],'Area'=>$listingData['area'],
//                'Zip'=>$listingData['zip'],'County'=>$listingData['county'],'Front_ft'=>$listingData['front_ft'],
//                'Sqft'=>$listingData['sqft'],'Community'=>$listingData['community'],'Rltr'=>$listingData['rltr'],
//                'Municipality'=>$listingData['municipality'],'Municipality_district'=>$listingData['municipality_district'],
//                'Pool'=>$listingData['pool'],'Gas'=>$listingData['gas'],'Elec'=>$listingData['electricity'],
//                'Util_tel'=>$listingData['util_tel'],'Util_cable'=>$listingData['util_cable'],
//                'Water'=>$listingData['water'],'Style'=>$listingData['style'],'Br'=>$listingData['bedrooms'],
//                'Rm1_out'=>$listingData['rm1_out'],'Rm2_out'=>$listingData['rm2_out'],'Rm3_out'=>$listingData['rm3_out'],
//                'Rm4_out'=>$listingData['rm4_out'],'Rm5_out'=>$listingData['rm5_out'],'Rm6_out'=>$listingData['rm6_out'],
//                'Rm7_out'=>$listingData['rm7_out'],'Rm8_out'=>$listingData['rm8_out'],'Rm9_out'=>$listingData['rm9_out'],
//                'Rm10_out'=>$listingData['rm10_out'],'Rm11_out'=>$listingData['rm11_out'],'Rm12_out'=>$listingData['rm12_out'],
//                'Rooms_plus'=>$listingData['rooms_plus'],'Bath_tot'=>$listingData['baths'],'Yr'=>$listingData['year_built'],
//                'Ass_year'=>$listingData['ass_year'],'Tot_park_spcs'=>$listingData['tot_park_spcs'],
//                'Timestamp_sql'=>$listingData['timestamp_sql'],'Pix_updt'=>$listingData['pix_updt'],
//                'Idx_dt'=>$listingData['idx_dt'],'Type_own1_out'=>$listingData['type_own1_out'],
//                'Prop_feat1_out'=>$listingData['prop_feat1_out'],'Prop_feat2_out'=>$listingData['prop_feat2_out'],
//                'Prop_feat3_out'=>$listingData['prop_feat3_out'],'Prop_feat4_out'=>$listingData['prop_feat4_out'],
//                'Prop_feat5_out'=>$listingData['prop_feat5_out'],'Prop_feat6_out'=>$listingData['prop_feat6_out'],
//                'Status'=>$listingData['status'], 'Images'=>$imageDataJson
//        ]);
        return $listingData;
    } catch (\PHRETS\Exceptions\CapabilityUnavailable $e) {
        // If maximum attempts to retrieve full data reached, fetch updates instead
        return fetchUpdates($config, $mlsNumber);
    } catch (\Exception $e) {
        // Handle any other exceptions
        return ['error' => $e->getMessage()];
    }
}

// Function to fetch updates for listing data based on MLS number
function fetchUpdates($config, $mlsNumber, $lastFetchTimestamp) {
    global $h;
    $rets = new Session($config);
    try {
        $connect = $rets->Login();
        $query = "(Ml_num=$mlsNumber)";
        if ($lastFetchTimestamp) {
            // Add condition to fetch updates since the last fetch timestamp
            $query .= '+Timestamp_sql=' .  $lastFetchTimestamp . ',';
        }
        // Perform search
        $results = $rets->Search('Property', 'CondoProperty', $query);
        if ($results) {
            $response = [];
            // Process the results
            foreach ($results as $record) {
                // Update the listing information
                $updatedListings = $h->table('listings')->update([
                    'Lp_dol' => $record->get('Lp_dol'),
                    'St_num' => $record->get('St_num'),
                    'Addr' => $record->get('Addr'),
                    'Area' => $record->get('Area'),
                    'Zip' => $record->get('Zip'),
                    'Apt_num' => $record->get('Apt_num'),
                    'County' => $record->get('County'),
                    'Municipality' => $record->get('Municipality'),
                    'Rltr' => $record->get('Rltr'),
                    'Timestamp_sql' => $record->get('Timestamp_sql'),
                    'Status' => $record->get('Status'),
                ])->where('Ml_num', $record->get('Ml_num'))->run();

                // Check if the update was successful
                if ($updatedListings) {
                    $response[] = ['success' => true];
                } else {
                    $response[] = ['success' => false];
                }
            }

            // Logout from the RETS server
            $rets->Logout();
            $rets->Disconnect();

            // Return the response
            return $response;
        } else {
            // No results found
            return ['success' => false, 'message' => 'No updates found'];
        }

    } catch (\Exception $e) {
        // Handle any exceptions
        return ['error' => $e->getMessage()];
    }
}



function CreateSessionRETS($config, $property_type='CondoProperty',$price_range='300000-500000', $limit=5){
    $rets = new Session($config );
    try {
        $connect = $rets->Login();
        $select_fields = [
            'Lp_dol',
            'Addr',
            'Area',
            'Sqft',
            'Yr_built',
            'Rms',
            'Br',
            'Bath_tot',
            'Type_own1_out',
            'Style',
            'Taxes',
            'Tot_park_spcs',
            'Gar_type',
            'Heating',
            'Fuel',
            'Pool',
            'Ad_text',
            'Ml_num',
            'Status',
            'Rooms_plus',
            'Zoning',
            'Mmap_page'
        ];

        $select_str = commaSeperated($select_fields);

        // Perform a search
        $results = $rets->Search('Property', $property_type, '(Lp_dol='.$price_range.') (Area=Vancouver)', ['Limit' => $limit, 'Select' => [$select_str]]);

        // Process the results
        if ($results instanceof Results) {
            foreach ($results as $record) {
                // Access the fields in the record
                // Accessing the fields from the record

                // Do something with the retrieved data

                // Example usage
                $loanAmount = $record->get('Lp_dol');
                $years = 30;
                $annualInsurance = 1498000;
                $interestRate = 5;
                $annualTax = 2000;
                $monthlyHOA = 36;

                $totalPayment = calculateMonthlyPayment($loanAmount, $years, $annualInsurance, $interestRate, $annualTax, $monthlyHOA);



                $listings[] = [
                    'list_price' => $record->get('Lp_dol'),
                    'address' => $record->get('Addr'),
                    'area' => $record->get('Area'),
                    'sqft' => $record->get('Sqft'),
                    'year_built' => $record->get('Yr_built'),
                    'rooms' => $record->get('Rms'),
                    'bedrooms' => $record->get('Br'),
                    'bathrooms' => $record->get('Bath_tot'),
                    'type_of_ownership' => $record->get('Type_own1_out'),
                    'style' => $record->get('Style'),
                    'taxes' => $record->get('Taxes'),
                    'parking_spaces' => $record->get('Tot_park_spcs'),
                    'garage_type' => $record->get('Gar_type'),
                    'heating' => $record->get('Heating'),
                    'fuel' => $record->get('Fuel'),
                    'pool' => $record->get('Pool'),
                    'ad_text' => $record->get('Ad_text'),
                    'ml_num' => $record->get('Ml_num'),
                    'status' => $record->get('Status'),
                    'rooms_plus' => $record->get('Rooms_plus'),
                    'zoning' => $record->get('Zoning'),
                    'morgage' => $totalPayment,
                    'map'=>$record->get('Mmap_page'),

                ];

            }
            echo json_encode($listings);
        }

        $rets->Logout();
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

    $rets->Disconnect();
}
//CreateSessionRETS($config, 'ResidentialProperty','900000-100000000', 2);

//$imageDataJson = fetchAndReturnImages($config, $mlsNumber);
//fetchListingData($config, 'X7359742');


// Call the function to fetch listing data or updates for a specific MLS number
//$mlsNumber = 'C8086618'; // Replace with the actual MLS number

$property_type =str_replace(' ', '', $_GET['type']);
$price_range = $_GET['min_price'].'-'.$_GET['max_price'];
$limit = $_GET['limit'];
echo $area= $_GET['area'];
//
$Data = fetchListingData($config,$property_type,$price_range,$area, $limit);
saveJsonToFile($Data,'json/data.json');

print_r($Data);



//$listingData = fetchListingData($config, $mlsNumber);
//$res=fetchUpdates($config, 'C8235580', '2024-04-15 13:58:51.0');

//print_r($res);
//apiRequestCounter();
echo json_encode(array('status' => 'success'));



