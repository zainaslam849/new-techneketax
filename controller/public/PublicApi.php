<?php
require("config/env.php");
header('Content-Type: application/json');
//NAVBAR FOR SALE CITIS
if($route == '/api/nav_cities'){
//FETCH Areas
    $uniqueAreas = $h->table('listings')
        ->select('Municipality')
        ->distinct()->fetchAll();

    $response = [];
    foreach ( $uniqueAreas as $row) {
        $html = ' <div class="col-6"> <a class="dropdown-item col-6" href="/real-estate?area='.$row['Municipality'].'">'.$row['Municipality'].'</a></div>';
        $response[] = $html;
    }
    echo json_encode([
        'areas' => $response
    ]);
}


if($route == '/api/buy'){
    if( ! is_csrf_GET_script()){
        http_response_code(202);
        return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='javascript:refresh_page()' onclick='refresh_page();return false;'> Refresh Page.</a>"));
        exit();
    }
    $perPage = 6;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    // Get search criteria from the request
    $location = !empty($_GET['location']) ? $_GET['location'] : '';
    $propertyType = !empty($_GET['propertyType']) ? $_GET['propertyType'] : '';
    $minPrice = !empty($_GET['minPrice']) ? $_GET['minPrice'] : '';
    $maxPrice = !empty($_GET['maxPrice']) ? $_GET['maxPrice'] : '';
    $searchQuery = !empty($_GET['searchQuery']) ? $_GET['searchQuery'] : '';
    $offset = ($page - 1) * $perPage;


    // Fetch listings from the database with pagination
//    $listings = $h->table('listings')
//        ->select()
//        ->orderBy('id', 'DESC')
//        ->limit($perPage)
//        ->offset($offset)
//        ->fetchAll();

    $query = $h->table('listings')
        ->select('listings.*', 'categories.*', 'categories.name as Property_type')
        ->innerJoin('categories')->on('categories.id', 'listings.Cat_id');
// Add location filter if provided
    if (isset($location) && !empty($location)) {
        $query->where('Municipality', $location);
    }

// Add property type filter if provided
    if (isset($propertyType) && !empty($propertyType)) {
        $query->orWhere('categories.name',$propertyType);
    }

// Add min and max price filters
    if (!empty($minPrice)) {

        $query->where('Lp_dol', '>=', $minPrice);
    }
    if (!empty($maxPrice)) {

        $query->where('Lp_dol', '<=', $maxPrice);
    }

// Add search query filter if provided
    if (!empty($searchQuery)) {
        $query->where('Addr', 'LIKE', '%' . $searchQuery . '%');
        $query->orWhere('County', 'LIKE', '%' . $searchQuery . '%');
        $query->orWhere('Area', 'LIKE', '%' . $searchQuery . '%');
        $query->orWhere('Municipality', 'LIKE', '%' . $searchQuery . '%');
        $query->orWhere('Zip', 'LIKE', '%' . $searchQuery . '%');
        $query->orWhere('Municipality', 'LIKE', '%' . $searchQuery . '%');
        $query->orWhere('Ml_num', 'LIKE', '%' . $searchQuery . '%');

    }

// Fetch listings with pagination
    $listings = $query
        ->orderBy('listings.id', 'DESC')
        ->limit($perPage)
        ->offset($offset)
        ->fetchAll();

//print_r($query);

    $response = [];
    foreach ($listings as $listing) {
        //TITLE
        if(isset($listing['Apt_num']) && !empty($listing['Apt_num'])){
            $title =  ('#'.$listing['Apt_num'] ?? null) . ' -' .($listing['Addr'] ?? null) . ', ' . ($listing['Area'] ?? null) . ', ' . ($listing['County'] ?? null) . ', ' . ($listing['Zip'] ?? null);
        }else{
            $title =  ($listing['Addr'] ?? null) . ', ' . ($listing['Area'] ?? null) . ', ' . ($listing['County'] ?? null) . ', ' . ($listing['Zip'] ?? null);
        }
        //IMAGE
        $Images=json_decode($listing['Images'],true);

        $html = '
    <div class="col-sm-6 col-lg-4 col-xl-4 d-flex">
        <div class="border-0 card card-property rounded-3 shadow w-100 flex-fill overflow-hidden">
            <a aria-label="' . $listing['Ml_num'] . '" href="/listing/' . $listing['Ml_num'] . '" class="card-link"></a>
            <div class="property-img card-image-hover overflow-hidden">
                <img  src="'.$assets_url.'/uploads/listings/' . $Images['image_names'][0] . '" alt="" class="img-fluid lozad">
                <div class="bg-white card-property-badge d-inline-block end-1 fs-13 fw-semibold position-absolute property-tags px-2 py-1 rounded-3 text-dark top-1">
                    ' . getStatusDescription($listing['Status']) . '
                </div>
            </div>
            <div class="card-property-content-wrap d-flex flex-column h-100 position-relative p-4">
                <div class="align-items-end card-property-price d-flex flex-row mb-1 gap-1">
                    <h3 class="m-0 fw-bold text-primary">$' . number_format($listing['Lp_dol'], 0, '.', ',') . '</h3>
                  
                </div>
                <h4 class="property-card-title mb-3">' . $title . '</h4>
                <div class="card-property-description mb-3">' . get_words($listing['Ad_text']) . '</div>
                <div class="border card-property-facilities gap-2 hstack mt-auto p-3 pt-3 rounded-3 text-center">
                    <div><i class="fa-solid fa-bed text-dark me-1"></i><span>' . $listing['Br'] . ' Beds</span></div>
                    <span class="vr"></span>
                    <div><i class="fa-solid fa-bath text-dark me-1"></i><span>' . $listing['Bath_tot'] . ' Baths</span></div>
                    <span class="vr"></span>
                    <div><i class="fa-solid fa-vector-square text-dark me-1"></i><span>' . $listing['Sqft'] . ' Ft</span></div>
                </div>
            </div>
        </div>
    </div>';
        $response[] = $html;
    }
    $totalListings =$query->orderBy('listings.id', 'DESC')->count();
    $totalPages = ceil($totalListings / $perPage);

    echo json_encode([
        'listings' => $response,
        'totalPages' => $totalPages,
        'currentPage' => $page,
    ]);
}


if($route == '/api/list/area'){
    $perPage = 6;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    // Get search criteria from the request
    $location = isset($_GET['location']) ? $_GET['location'] : '';
    $propertyType = isset($_GET['propertyType']) ? $_GET['propertyType'] : '';
    $minPrice = isset($_GET['minPrice']) ? $_GET['minPrice'] : '';
    $maxPrice = isset($_GET['maxPrice']) ? $_GET['maxPrice'] : '';
    $searchQuery = isset($_GET['searchQuery']) ? $_GET['searchQuery'] : '';
    $offset = ($page - 1) * $perPage;


    // Fetch listings from the database with pagination
//    $listings = $h->table('listings')
//        ->select()
//        ->orderBy('id', 'DESC')
//        ->limit($perPage)
//        ->offset($offset)
//        ->fetchAll();

    $query = $h->table('listings')
        ->select('listings.*', 'categories.*', 'categories.name as Property_type')
        ->innerJoin('categories')->on('categories.id', 'listings.Cat_id');
// Add location filter if provided
    if (isset($location) && !empty($location)) {
        $query->where('County', $location);
    }

// Add property type filter if provided
    if (isset($propertyType) && !empty($propertyType)) {
        $query->where('listings.Cat_id', $propertyType);
    }

// Add min and max price filters
    if (!empty($minPrice)) {

        $query->where('Lp_dol', '>=', floatval($minPrice));
    }
    if (!empty($maxPrice)) {

        $query->where('Lp_dol', '<=', $maxPrice);
    }

// Add search query filter if provided
    if (!empty($searchQuery)) {
        $query->whereLike('Addr', '%' . $searchQuery . '%');
    }

// Fetch listings with pagination
    $listings = $query
        ->where('Municipality', $_GET['area'])
        ->orderBy('listings.id', 'DESC')
        ->limit($perPage)
        ->offset($offset)
        ->fetchAll();

//print_r($query);

    $response = [];
    foreach ($listings as $listing) {
//TITLE
        if(isset($listing['Apt_num']) && !empty($listing['Apt_num'])){
            $title =  ('#'.$listing['Apt_num'] ?? null) . ' -' .($listing['Addr'] ?? null) . ', ' . ($listing['Area'] ?? null) . ', ' . ($listing['County'] ?? null) . ', ' . ($listing['Zip'] ?? null);
        }else{
            $title =  ($listing['Addr'] ?? null) . ', ' . ($listing['Area'] ?? null) . ', ' . ($listing['County'] ?? null) . ', ' . ($listing['Zip'] ?? null);
        }
        //IMAGE
        $Images=json_decode($listing['Images'],true);

        $html = '
    <div class="col-sm-6 col-lg-4 col-xl-4 d-flex">
        <div class="border-0 card card-property rounded-3 shadow w-100 flex-fill overflow-hidden">
            <a href="/listing/' . $listing['Ml_num'] . '" class="card-link"></a>
            <div class="property-img card-image-hover overflow-hidden">
                <img  src="'.$assets_url.'/uploads/listings/' . $Images['image_names'][0] . '" alt="" class="img-fluid lozad">
                <div class="bg-white card-property-badge d-inline-block end-1 fs-13 fw-semibold position-absolute property-tags px-2 py-1 rounded-3 text-dark top-1">
                    ' . getStatusDescription($listing['Status']) . '
                </div>
            </div>
            <div class="card-property-content-wrap d-flex flex-column h-100 position-relative p-4">
                <div class="align-items-end card-property-price d-flex flex-row mb-1 gap-1">
                    <h3 class="m-0 fw-bold text-primary">$' . number_format($listing['Lp_dol'], 0, '.', ',') . '</h3>
                  
                </div>
                <h4 class="property-card-title mb-3">' . $title . '</h4>
                <div class="card-property-description mb-3">' . get_words($listing['Ad_text']) . '</div>
                <div class="border card-property-facilities gap-2 hstack mt-auto p-3 pt-3 rounded-3 text-center">
                    <div><i class="fa-solid fa-bed text-dark me-1"></i><span>' . $listing['Br'] . ' Beds</span></div>
                    <span class="vr"></span>
                    <div><i class="fa-solid fa-bath text-dark me-1"></i><span>' . $listing['Bath_tot'] . ' Baths</span></div>
                    <span class="vr"></span>
                    <div><i class="fa-solid fa-vector-square text-dark me-1"></i><span>' . $listing['Sqft'] . ' Ft</span></div>
                </div>
            </div>
        </div>
    </div>';
        $response[] = $html;
    }
    $totalListings = $h->table('listings')->count();
    $totalPages = ceil($totalListings / $perPage);

    echo json_encode([
        'listings' => $response,
        'totalPages' => $totalPages,
        'currentPage' => $page,
    ]);
}

//Related or Same Area Listings
if($route == '/api/relatedListings') {
    if( ! is_csrf_GET_script()){
        http_response_code(202);
        return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='javascript:refresh_page()' onclick='refresh_page();return false;'> Refresh Page.</a>"));
        exit();
    }
    $listings = $h->table('listings')
        ->select()
        ->where('Area', $_GET['area'])
        ->where('Ml_num','!=', $_GET['mls'])
        ->orderBy('id', 'DESC')
        ->limit(4)
        ->fetchAll();
    $response = [];
    foreach ($listings as $listing) {
        //TITLE
        if(isset($listing['Apt_num']) && !empty($listing['Apt_num'])){
            $title =  ('#'.$listing['Apt_num'] ?? null) . ' -' .($listing['Addr'] ?? null) . ', ' . ($listing['Area'] ?? null) . ', ' . ($listing['County'] ?? null) . ', ' . ($listing['Zip'] ?? null);
        }else{
            $title =  ($listing['Addr'] ?? null) . ', ' . ($listing['Area'] ?? null) . ', ' . ($listing['County'] ?? null) . ', ' . ($listing['Zip'] ?? null);
        }
        //IMAGE
        $Images=json_decode($listing['Images'],true);

        $html = '<div class=" card mb-4 overflow-hidden bg-grey border-0 shadow rounded-3">
                        <a aria-label="' . $listing['Ml_num'] . '" href="/listing/' . $listing['Ml_num'] . '" class="card-link"></a>
                        <div class="card-body p-0">
                            <div class="g-0 row">
                                <div class="bg-white col-lg-5 col-md-6 col-xl-3 position-relative">
                                    <div class="card-image-hover overflow-hidden position-relative h-100">
                                        <!-- Start Image -->
                                        <img src="'.$assets_url.'/uploads/listings/' . $Images['image_names'][0] . '" alt="" class="h-100 w-100 object-fit-cover">
                                        <!-- /. End Image -->
                                        <!-- Start Tag -->
                                        <div class="bg-primary card-property-badge d-inline-block end-1 fs-13 fw-semibold position-absolute property-tags px-2 py-1 rounded-3 text-white top-1">' . getStatusDescription($listing['Status']) . '</div>
                                        <!--  /. End Tag -->
                                    </div>
                                </div>
                                <div class="bg-white col-lg-7 col-md-6 col-xl-6 p-3 p-lg-4 p-md-3 p-sm-4">
                                    <div class="d-flex flex-column h-100">
                                        <div class="mb-4">
                                            <!-- Start Property Name -->
                                            <h6 class="fs-23 mb-2">' . $title . '</h6>
                                            <!-- /.End Property Name -->
                                            <div class="fs-16"><i class="fa-solid fa-location-dot"></i> <span>'.$listing['Community'].', '.$listing['Area'].', '.$listing['County'] .', '.$listing['Zip'].'</span></div>
                                            <!-- Start Property Description -->
                                            <div class="mt-3">' . get_words($listing['Ad_text']) . '</div>
                                            <!-- /.End Property Description -->
                                        </div>
                                        <!-- Start Card Property Facilities -->
                                        <div class="border card-property-facilities gap-2 hstack mt-auto p-3 pt-3 rounded-3 text-center">
                                            <div class="">
                                                <i class="fa-solid fa-bed text-dark me-1"></i><span>' . $listing['Br'] . ' bedroom</span>
                                            </div>
                                            <span class="vr"></span>
                                            <div class=""><i class="fa-solid fa-bath text-dark me-1"></i><span>' . $listing['Bath_tot'] . ' bathroom</span></div>
                                            <span class="vr"></span>
                                            <div class=""><i class="fa-solid fa-vector-square text-dark me-1"></i><span>' . $listing['Sqft'] . ' ft</span></div>
                                        </div>
                                        <!-- /. End Card Property Facilities -->
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-xl-3 p-3 p-lg-4 p-md-3 p-sm-4">
                                    <div class="row h-100 align-items-center justify-content-center gap-2">
                                        <!-- Start price -->
                                        <div class="col col-xl-12">
                                            <div class="align-items-sm-center d-sm-flex d-xl-block">
                                                <div class="d-flex justify-content-center align-items-end card-property-price flex-row gap-1">
                                                    <h2 class="m-0 fw-semibold text-primary">$' . number_format($listing['Lp_dol'], 0, '.', ',') . '</h2>
                                                   
                                                </div>
                                                <div class="flex-grow-1 mt-2 ms-sm-3 ms-xl-0 mt-xl-2 text-center"> <strong class="small fw-semibold">Taxes</strong>
                                                    <div class="small">$' . number_format($listing['Taxes'], 0, '.', ',') . '</div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.End price -->
                                        <!-- Start button -->
                                        <div class="col-auto d-flex flex-wrap gap-1 justify-content-center position-relative z-1">
                                            <button type="button" class="border-0 btn btn-outline-default btn-sm fw-medium shadow-sm">
                                                <i class="fa fa-phone fs-14 me-1"></i>Call
                                            </button>
                                            <button type="button" class="border-0 btn btn-outline-default btn-sm fw-medium shadow-sm">
                                                <i class="fa fa-user-tie fs-14 fs-e me-1"></i>Email
                                            </button>
                                            <button type="button" class="border-0 btn btn-outline-default btn-sm fw-medium shadow-sm">
                                                <i class="fa fa-phone fs-14 me-1"></i>WhatsApp
                                            </button>
                                        </div>
                                        <!-- /.End button -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
        $response[] = $html;
    }
    echo json_encode([
        'listings' => $response,
    ]);
}


//GALLERY IMAGES BY MLS NUMBER
if($route == '/api/galleryImages') {
    if( ! is_csrf_GET_script()){
        http_response_code(202);
        return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='javascript:refresh_page()' onclick='refresh_page();return false;'> Refresh Page.</a>"));
        exit();
    }
    $listings = $h->table('listings')
        ->select('Images')
        ->where('Ml_num', $_GET['mls'])
        ->limit(1)
        ->fetchAll();
    $response = [];
    $Images=json_decode($listings[0]['Images'],true);

    foreach ($Images['image_names'] as $image) {
        //IMAGE
        $html = '<div class="col-6 col-sm-4 col-lg-3 col-xxl-2">
                                <!-- Galary Item -->
                                <a aria-label="gallery-' . $image . '" href="'.$assets_url.'/uploads/listings/' . $image . '" class="d-block galary-overlay-hover position-relative">
                                    <img src="'.$assets_url.'/uploads/listings/' . $image . '" alt="" class="lozad img-fluid rounded-3">
                                    <div class="galary-hover-element w-100 h-100">
                                        <i class="fa-solid fa-expand text-white position-absolute top-50 start-50 translate-middle bg-dark rounded-1 p-2 lh-1"></i>
                                    </div>
                                </a>
                                <!-- /. End galary Item -->
                            </div>';
        $response[] = $html;
    }
    echo json_encode([
        'listings' => $response,
    ]);
}



//HOME LATEST PROPERTY
if($route == '/api/latest-property'){
    if( ! is_csrf_GET_script()){
    http_response_code(202);
    return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='javascript:refresh_page()' onclick='refresh_page();return false;'> Refresh Page.</a>"));
    exit();
    }
    // Fetch listings from the database with pagination
    $listings = $h->table('listings')
        ->select()
        ->orderBy('id', 'DESC')
        ->limit(8)
        ->fetchAll();

//print_r($query);

    $response = [];
    foreach ($listings as $listing) {
        //TITLE
        if(isset($listing['Apt_num']) && !empty($listing['Apt_num'])){
            $title =  ('#'.$listing['Apt_num'] ?? null) . ' -' .($listing['Addr'] ?? null) . ', ' . ($listing['Area'] ?? null) . ', ' . ($listing['County'] ?? null) . ', ' . ($listing['Zip'] ?? null);
        }else{
            $title =  ($listing['Addr'] ?? null) . ', ' . ($listing['Area'] ?? null) . ', ' . ($listing['County'] ?? null) . ', ' . ($listing['Zip'] ?? null);
        }
        //IMAGE
        $Images=json_decode($listing['Images'],true);

        $html = '<div class="col-sm-6 col-lg-4 col-xl-3 d-flex" data-aos="fade-up" data-aos-delay="300">
                    <!-- Start Card Property -->
                    <div class="border-0 card card-property rounded-3 shadow w-100 flex-fill overflow-hidden">
                        <!-- Start Card Link -->
                        <a aria-label="' . $listing['Ml_num'] . '" href="/listing/' . $listing['Ml_num'] . '" class="card-link"></a>
                        <!-- /. End Card Link -->
                        <!-- Start Property Image -->
                        <div class="property-img card-image-hover overflow-hidden">
                            <img src="uploads/listings/' . $Images['image_names'][0] . '" alt="" class="img-fluid lozad">
                            <div class="bg-white card-property-badge d-inline-block end-1 fs-13 fw-semibold position-absolute property-tags px-2 py-1 rounded-3 text-dark top-1">
                               ' . getStatusDescription($listing['Status']) . '
                            </div>
                        </div>
                        <!-- /. End Property Image -->
                        <div class="card-property-content-wrap d-flex flex-column h-100 position-relative p-4">
                            <!-- Start Card Property Price -->
                            <div class="align-items-end card-property-price d-flex flex-row mb-1 gap-1">
                                <h3 class="m-0 fw-semibold text-primary">$' . number_format($listing['Lp_dol'], 0, '.', ',') . '</h3>
                               
                            </div>
                            <!-- /. End Card Property Price -->
                            <h4 class="property-card-title mb-3">' .$title . '</h4>
                            <div class="card-property-description mb-3">' . get_words($listing['Ad_text']) . '</div>
                            <!-- Start Card Property Facilities -->
                            <div class="border card-property-facilities gap-2 hstack mt-auto p-3 pt-3 rounded-3 text-center">
                                <div class="">
                                    <i class="fa-solid fa-bed text-dark me-1"></i><span>' . $listing['Br'] . ' Beds</span>
                                </div>
                                <span class="vr"></span>
                                <div class=""><i class="fa-solid fa-bath text-dark me-1"></i><span>' . $listing['Bath_tot'] . ' Baths</span></div>
                                <span class="vr"></span>
                                <div class=""><i class="fa-solid fa-vector-square text-dark me-1"></i><span>' . $listing['Sqft'] . ' ft</span></div>
                            </div>
                            <!-- /. End Card Property Facilities -->
                        </div>
                    </div>
                    <!-- /. End Card Property -->
                </div>';
        $response[] = $html;
    }
    echo json_encode([
        'listings' => $response
    ]);
}



///api/schedule-tour
if($route == '/api/schedule-tour'){
    if( ! is_csrf_GET_script()){
    http_response_code(202);
    return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='javascript:refresh_page()' onclick='refresh_page();return false;'> Refresh Page.</a>"));
    exit();
    }
    $h->table('appointments')
        ->insertOne([
            'name' => $_GET['name'],
            'email' => $_GET['email'],
            'phone' => $_GET['phone'],
            'date' => date('Y-m-d', strtotime($_GET['date'])),
            'ml_num' => $_GET['ml_num'],
            'message' => $_GET['message'],
        ]);

// MATTERMOST SEND MSG
    @$MMtoken="484wc1mgztfb7g6r9ra63iztfh";
    @$MMchannel_id="q57jk667epg4jfzyj3dx8daixa";
    $MM_MSG = generateMessage($_GET['name'], $_GET['email'], $_GET['phone'], $_GET['date'], $_GET['message'],$_GET['ml_num'], getIPAddress(), getOS());
    SentMessgeToMatterMost($MM_MSG, $MMtoken, $MMchannel_id);

    echo json_encode([
        'status' => true
    ]);
}