<?php
require("config/env.php");

if($route == '/admin/listing/category'){
    $seo = array(
        'title' => 'Categories  | Chaisbek Real Estate',
        'description' => 'Learn about Chaisbek Real Estates commitment to integrity, compassion, and excellence. Discover our mission to empower clients and communities through real estate.',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('admin/listings/category.twig', ['seo'=>$seo]);
}
if($route == '/admin/listing/add'){
    $seo = array(
        'title' => 'Add New Listing  | Chaisbek Real Estate',
        'description' => 'Learn about Chaisbek Real Estates commitment to integrity, compassion, and excellence. Discover our mission to empower clients and communities through real estate.',
        'keywords' => 'Admin Panel'
    );
    $ApiCounter=$h->table('settings')->select('api_request_count')->fetchAll();

    $categories=$h->table('categories')->select()->fetchAll();
    echo $twig->render('admin/listings/add.twig', ['seo'=>$seo,'categories' => $categories, 'ApiCounter' => $ApiCounter[0]['api_request_count']]);
}

if($route == '/admin/import'){

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $index = isset($_POST['index']) ? intval($_POST['index']) : null;
            if ($index !== null) {
                $jsonFilePath = 'json/data.json';
                $jsonData = file_get_contents($jsonFilePath);
                $dataArray = json_decode($jsonData, true);

                if (isset($dataArray[$index])) {
                    $listingData = $dataArray[$index];

                    //SAVE IMAGES
                    $configImage = new \PHRETS\Configuration;
                    $configImage->setLoginUrl('http://3pv.torontomls.net:6103/rets-treb3pv/server/login')
                        ->setUsername('Pt024cos')
                        ->setPassword('M96$b71');
                    $imageDataJson=fetchSaveAndReturnUniqueImageNames($configImage, $listingData['ml_num']);

                    //SAVE DATA in DB
                    // Return listing data
                $h->table('listings')->insertOne([
                    'Agent_id' => '1',
                    'Cat_id' => '2',
                    'Ml_num' => $listingData['ml_num'],
                    'Addr' => $listingData['address'],
                    'Ad_text' => $listingData['ad_text'],
                    'Lp_dol' => $listingData['list_price'],
                    'Taxes' => $listingData['taxes'],
                    'St' => $listingData['street'],
                    'Area' => $listingData['area'],
                    'Zip' => $listingData['zip'],
                    'County' => $listingData['county'],
                    'Front_ft' => $listingData['front_ft'],
                    'Sqft' => $listingData['sqft'],
                    'Community' => $listingData['community'],
                    'Rltr' => $listingData['rltr'],
                    'Municipality' => $listingData['municipality'],
                    'Municipality_district' => $listingData['municipality_district'],
                    'Pool' => $listingData['pool'],
                    'Gas' => $listingData['gas'],
                    'Elec' => $listingData['electricity'],
                    'Util_tel' => $listingData['util_tel'],
                    'Util_cable' => $listingData['util_cable'],
                    'Water' => $listingData['water'],
                    'Style' => $listingData['style'],
                    'Br' => $listingData['bedrooms'],
                    'Rms' => $listingData['rms'],
                    'Rm1_out' => $listingData['rm1_out'],
                    'Rm1_len' => $listingData['rm1_len'],
                    'Rm1_wth' => $listingData['rm1_wth'],
                    'Rm2_out' => $listingData['rm2_out'],
                    'Rm2_len' => $listingData['rm2_len'],
                    'Rm2_wth' => $listingData['rm2_wth'],
                    'Rm3_out' => $listingData['rm3_out'],
                    'Rm3_len' => $listingData['rm3_len'],
                    'Rm3_wth' => $listingData['rm3_wth'],
                    'Rm4_out' => $listingData['rm4_out'],
                    'Rm4_len' => $listingData['rm4_len'],
                    'Rm4_wth' => $listingData['rm4_wth'],
                    'Rm5_out' => $listingData['rm5_out'],
                    'Rm5_len' => $listingData['rm5_len'],
                    'Rm5_wth' => $listingData['rm5_wth'],
                    'Rm6_out' => $listingData['rm6_out'],
                    'Rm6_len' => $listingData['rm6_len'],
                    'Rm6_wth' => $listingData['rm6_wth'],
                    'Rm7_out' => $listingData['rm7_out'],
                    'Rm7_len' => $listingData['rm7_len'],
                    'Rm7_wth' => $listingData['rm7_wth'],
                    'Rm8_out' => $listingData['rm8_out'],
                    'Rm8_len' => $listingData['rm8_len'],
                    'Rm8_wth' => $listingData['rm8_wth'],
                    'Rm9_out' => $listingData['rm9_out'],
                    'Rm9_len' => $listingData['rm9_len'],
                    'Rm9_wth' => $listingData['rm9_wth'],
                    'Rm10_out' => $listingData['rm10_out'],
                    'Rm10_len' => $listingData['rm10_len'],
                    'Rm10_wth' => $listingData['rm10_wth'],
                    'Rm11_out' => $listingData['rm11_out'],
                    'Rm11_len' => $listingData['rm11_len'],
                    'Rm11_wth' => $listingData['rm11_wth'],
                    'Rm12_out' => $listingData['rm12_out'],
                    'Rm12_len' => $listingData['rm12_len'],
                    'Rm12_wth' => $listingData['rm12_wth'],
                    'Rooms_plus' => $listingData['rooms_plus'],
                    'Bath_tot' => $listingData['baths'],
                    'Yr' => $listingData['year_built'],
                    'Ass_year' => $listingData['ass_year'],
                    'Tot_park_spcs' => $listingData['tot_park_spcs'],
                    'Timestamp_sql' => $listingData['timestamp_sql'],
                    'Pix_updt' => $listingData['pix_updt'],
                    'Idx_dt' => $listingData['idx_dt'],
                    'Type_own1_out' => $listingData['type_own1_out'],
                    'Prop_feat1_out' => $listingData['prop_feat1_out'],
                    'Prop_feat2_out' => $listingData['prop_feat2_out'],
                    'Prop_feat3_out' => $listingData['prop_feat3_out'],
                    'Prop_feat4_out' => $listingData['prop_feat4_out'],
                    'Prop_feat5_out' => $listingData['prop_feat5_out'],
                    'Prop_feat6_out' => $listingData['prop_feat6_out'],
                    'Access_prop1' => $listingData['access_prop1'],
                    'Access_prop2' => $listingData['access_prop2'],
                    'Acres' => $listingData['acres'],
                    'Addl_mo_fee' => $listingData['addl_mo_fee'],
                    'A_c' => $listingData['a_c'],
                    'All_inc' => $listingData['all_inc'],
                    'Alt_power1' => $listingData['alt_power1'],
                    'Alt_power2' => $listingData['alt_power2'],
                    'Apt_num' => $listingData['apt_num'],
                    'Area_code' => $listingData['area_code'],
                    'Bsmt1_out' => $listingData['bsmt1_out'],
                    'Bsmt2_out' => $listingData['bsmt2_out'],
                    'Br_plus' => $listingData['br_plus'],
                    'Cable' => $listingData['cable'],
                    'Cac_inc' => $listingData['cac_inc'],
                    'Central_vac' => $listingData['central_vac'],
                    'Comel_inc' => $listingData['comel_inc'],
                    'Community_code' => $listingData['community_code'],
                    'Cross_st' => $listingData['cross_st'],
                    'Disp_addr' => $listingData['disp_addr'],
                    'Drive' => $listingData['drive'],
                    'Easement_rest1' => $listingData['easement_rest1'],
                    'Easement_rest2' => $listingData['easement_rest2'],
                    'Easement_rest3' => $listingData['easement_rest3'],
                    'Easement_rest4' => $listingData['easement_rest4'],
                    'Elevator' => $listingData['elevator'],
                    'Constr1_out' => $listingData['constr1_out'],
                    'Constr2_out' => $listingData['constr2_out'],
                    'Extras' => $listingData['extras'],
                    'Den_fr' => $listingData['den_fr'],
                    'Farm_agri' => $listingData['farm_agri'],
                    'Fpl_num' => $listingData['fpl_num'],
                    'Fractional_ownership' => $listingData['fractional_ownership'],
                    'Comp_pts' => $listingData['comp_pts'],
                    'Furnished' => $listingData['furnished'],
                    'Gar_spaces' => $listingData['gar_spaces'],
                    'Gar_type' => $listingData['gar_type'],
                    'Heat_inc' => $listingData['heat_inc'],
                    'Fuel' => $listingData['fuel'],
                    'Heating' => $listingData['heating'],
                    'Hydro_inc' => $listingData['hydro_inc'],
                    'Num_kit' => $listingData['num_kit'],
                    'Kit_plus' => $listingData['kit_plus'],
                    'Laundry' => $listingData['laundry'],
                    'Laundry_lev' => $listingData['laundry_lev'],
                    'Lse_terms' => $listingData['llse_terms'],
                    'Legal_desc' => $listingData['legal_desc'],
                    'Level1' => $listingData['level1'],
                    'Level2' => $listingData['level2'],
                    'Level3' => $listingData['level3'],
                    'Level4' => $listingData['level4'],
                    'Level5' => $listingData['level5'],
                    'Level6' => $listingData['level6'],
                    'Level7' => $listingData['level7'],
                    'Level8' => $listingData['level8'],
                    'Level9' => $listingData['level9'],
                    'Level10' => $listingData['level10'],
                    'Level11' => $listingData['level11'],
                    'Level12' => $listingData['level12'],
                    'Link_yn' => $listingData['link_yn'],
                    'Link_comment' => $listingData['link_comment'],
                    'Outof_area' => $listingData['outof_area'],
                    'Portion_of_property_for_lease' => $listingData['portion_of_property_for_lease'],
                    'Portion_for_lease_comments' => $listingData['portion_for_lease_comments'],
                    'Potl' => $listingData['potl'],
                    'Park_chgs' => $listingData['park_chgs'],
                    'Prkg_inc' => $listingData['prkg_inc'],
                    'Park_spcs' => $listingData['park_spcs'],
                    'Retirement' => $listingData['retirement'],
                    'Rm1_dc1_out' => $listingData['rm1_dc1_out'],
                    'Rm1_dc2_out' => $listingData['rm1_dc2_out'],
                    'Rm1_dc3_out' => $listingData['rm1_dc3_out'],
                    'Rm2_dc1_out' => $listingData['rm2_dc1_out'],
                    'Rm2_dc2_out' => $listingData['rm2_dc2_out'],
                    'Rm2_dc3_out' => $listingData['rm2_dc3_out'],
                    'Zoning' => $listingData['zoning'],
                    'Images'=>@$imageDataJson,
                    'Status'=>@$listingData['status'],
        ]);

                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'success'));
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Index out of bounds']);
                }
            } else {
                // Send an error response if the index is not provided
                http_response_code(400);
                echo json_encode(['error' => 'Index not provided']);
            }
        }
}

//CHECK MLS STATUS IN DB
if($route == '/admin/check-mlnum'){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ml_num = $_POST['ml_num'];
        $counter=$h->table('listings')->select('ml_num')->where('ml_num', $_POST['ml_num'])->count();
        $exists = ($counter > 0);
        header('Content-Type: application/json');
        echo json_encode(['exists' => $exists]);
    }
}

//MONTHLY MORGAGE CALCULATOR
if($route == '/admin/morgage-calc'){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        calculateMonthlyPayment($_POST['price'], $_POST['term'], $_POST['rate']);
    }
}