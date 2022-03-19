<?php

namespace App\Http\Controllers\Users;

use App\Helpers\ExcelfileValidator  ;
use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Requests\ProductRequest;
use App\Repositories\CategoryRepository;
use App\Repositories\ExcelRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Models\Partner;
use App\Models\Product;
use App\Models\User;

use App\Models\PartnerUser;
use Illuminate\Support\Facades\Storage;
use Excel;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class ProductController extends UserController
{

    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
     /**
     * @var userRepository
     */
    private $userRepository;
    

    /**
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param ExcelRepository $excelRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(ProductRepository $productRepository,
                                CategoryRepository $categoryRepository,
                                ExcelRepository $excelRepository,
                                OptionRepository $optionRepository, UserRepository $userRepository)
    {

        $this->middleware('authorized:products.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:products.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:products.delete', ['only' => ['delete']]);

        parent::__construct();

        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->excelRepository = $excelRepository;
        $this->optionRepository = $optionRepository;
        $this->userRepository = $userRepository;
        

        view()->share('type', 'product');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $keyword = addslashes($request->keyword);
        $category_id = addslashes($request->category_id) ;
        $status = addslashes($request->status) ;
        $user_id = addslashes($request->user_id) ;
        $user=$this->userRepository->getUser();
        $this->user_id=$user->id;
        $this->partner_id=$user->partner_id;
        $this->generateParams();

        $categories = $this->categoryRepository->getAll()->where('partner_id', $this->partner_id)
            ->orderBy("id", "desc")
            ->get()
	        ->map(function ($category) {
		        return [
			        'title' => $category->name,
			        'value'   => $category->id,
		        ];
	        })->pluck('title','value')->prepend(trans('product.category_id'), '');

        $title = trans('product.products');
        $partner=Partner::where('id',$this->partner_id)->first();
        $productQuery = Product::select('products.*', 'categories.name as title_category')
            ->leftJoin('categories','categories.id','=','products.category_id')
            //->leftJoin('users','users.id','=','products.staff_care')
			->where('products.partner_id', $this->partner_id)
			->where(function ($query)  use ($category_id, $keyword, $status, $user_id){
				if($category_id!=""){
					$query->where('products.category_id',$category_id);
				}
				if($keyword!=""){
                    $query->where('products.product_name', 'LIKE', "%{$keyword}%");
                }
                if($status!=""){
					$query->where('products.status',$status);
                }
                if($user_id!=""){
					$query->where('products.user_id',$user_id);
				}
            })->distinct()
            ->orderBy('products.updated_at', 'DESC');
            $totalProduct=count($productQuery->get());
            $productsPage=$productQuery->paginate(20)->appends(request()->query());

            $productList=$productsPage->map( function ( $product){
                return [
                    'id'           => $product->id,
					'product_name' => $product->product_name,
                    'title_category' => $product->title_category,
                    'category_id' => $product->category_id,
                    'user_care_text' => $product->user_care_text,
                    'user_care_fullname' => "",///$product->first_name." ".$product->last_name,
                    'user_care_id' => $product->user_id,
					'product_type'  => $product->product_type,
					'status'  => $product->status,
					'quantity_on_hand'  => $product->quantity_on_hand,
                    'quantity_available'   => $product->quantity_available,
                    'start_date'  => $product->start_date,
					'end_date'  => $product->end_date,
                ];
            });
        return view('user.product.index', compact('title', 'categories', 'productsPage', 'productList', 'totalProduct', 'partner'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('product.new');

        $this->generateParams();

        return view('user.product.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        if ($request->hasFile('product_image_file')) {
            $file = $request->file('product_image_file');
            $file = $this->productRepository->uploadProductImage($file);

            $request->merge([
                'product_image' => $file->getFileInfo()->getFilename(),
            ]);

            $this->generateProductThumbnail($file);
        }
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        if($userData && $partner_id){
            $request->merge([
                'partner_id' => $partner_id,
            ]);
        }
        if(isset($request->staff_care) && $request->staff_care!=""){
            $user_care_text=$this->fullname($request->staff_care);
            $request->merge([
                'user_care_text' => $user_care_text,
            ]);
        }
        

        $this->productRepository->create($request->except('product_image_file'));

        return redirect("product");
    }

    public function edit($product)
    {
        $product = $this->productRepository->find($product);
        $title = trans('product.edit');

        $this->generateParams();

        return view('user.product.edit', compact('title', 'product'));
    }

    public function update(ProductRequest $request, $product)
    {
        $product = $this->productRepository->find($product);
        if ($request->hasFile('product_image_file')) {
            $file = $request->file('product_image_file');
            $file = $this->productRepository->uploadProductImage($file);
            $request->merge([
                'product_image' => $file->getFileInfo()->getFilename(),
            ]);

            $this->generateProductThumbnail($file);
        }
        $user=$this->userRepository->getUser();
        $partner_id=$user->partner_id;
        if($user && $partner_id){
            $request->merge([
                'partner_id' => $partner_id,
            ]);
        }
        if(isset($request->staff_care) && $request->staff_care!=""){
            $user_care_text=$this->fullname($request->staff_care);
            $request->merge([
                'user_care_text' => $user_care_text,
            ]);
        }
        $product->update($request->except('product_image_file'));

        return redirect("product");
    }


    public function show($product)
    {
        $product = $this->productRepository->find($product);
        $action = "show";
        $title = trans('product.view');
        return view('user.product.show', compact('title', 'product', 'action'));
    }

    public function delete($product)
    {
        $product = $this->productRepository->find($product);
        $title = trans('product.delete');
        return view('user.product.delete', compact('title', 'product'));
    }

    public function destroy($product)
    {
        $product = $this->productRepository->find($product);
        $product->delete();
        return redirect("product");
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
        $products = $this->productRepository
            ->with('category','invoiceProducts','quotationProducts','qTemplateProducts','salesOrderProducts')->all()->where('partner_id', $this->partner_id)
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'product_name' => $p->product_name,
                    'category' => is_null($p->category) ? '': $p->category->name,
                    'product_type' => $p->product_type,
                    'status' => $p->status,
                    'quantity_on_hand' => $p->quantity_on_hand,
                    'quantity_available' => $p->quantity_available,
                    'count_uses' => $p->invoiceProducts->count() +
                                    $p->quotationProducts->count() +
                                    $p->qTemplateProducts->count() +
                                    $p->salesOrderProducts->count()
                ];
            });
        return $datatables->collection($products)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'products.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'product/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                     @endif
                                     <a href="{{ url(\'product/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i></a>
                                     @if((Sentinel::getUser()->hasAccess([\'products.delete\']) || Sentinel::inRole(\'admin\')) && $count_uses==0)
                                        <a href="{{ url(\'product/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif
                                     <a href="{{ url(\'product/\' . $id . \'/export-code\' ) }}" title="Download code gắn vào website">
                                            <i class="fa fa-fw fa-download text-primary"></i></a>'
                                     )
            ->removeColumn('id')
            ->removeColumn('count_uses')
            ->rawColumns(['actions'])->make();
    }

    /**
     * @param $file
     */
    private function generateProductThumbnail($file)
    {
        $sourcePath = $file->getPath() . '/' . $file->getFilename();
        $thumbPath = $file->getPath() . '/thumb_' . $file->getFilename();
        Thumbnail::generate_image_thumbnail($sourcePath, $thumbPath);
    }

    private function generateParams()
    {
        $statuses = $this->optionRepository->getAll()
            ->where('category', 'product_status')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value')->prepend(trans('Select Status'), '');
        $product_types = $this->optionRepository->getAll()
            ->where('category', 'product_type')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value')->prepend(trans('product.product_type'), '');
        
        $partner_id=0;
        $userData=$this->userRepository->getUser();
        if($userData){
            $partner_id= $userData->partner_id;
        }

        $categories = $this->categoryRepository->getAll()->where('partner_id',$partner_id)
            ->orderBy("id", "desc")
            ->get()
	        ->map(function ($category) {
		        return [
			        'title' => $category->name,
			        'value'   => $category->id,
		        ];
            })->pluck('title','value')->prepend(trans('product.category_id'), '');

        $unitmass = $this->optionRepository->getAll()->where('partner_id',$partner_id)
            ->where('category', 'unit_mass')
            ->orderBy("id", "desc")
            ->get()
	        ->map(function ($category) {
		        return [
			        'title' => $category->title,
			        'value'   => $category->value,
		        ];
            })->pluck('title','value')->prepend(trans('product.unit_mass'), '');
        $unitprice = $this->optionRepository->getAll()->where('partner_id',$partner_id)
            ->where('category', 'unit_price')
            ->orderBy("id", "desc")
            ->get()
	        ->map(function ($category) {
		        return [
			        'title' => $category->title,
			        'value'   => $category->value,
		        ];
            })->pluck('title','value')->prepend(trans('product.unit_price'), '');
            $staff_care=$this->userRepository->getAllUserOnPartner($userData->id);
        //salesList
		$staff_care=User::select("first_name","last_name","id")->join('partner_user','partner_user.user_id','=','users.id')
        ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
        ->where('partner_user.partner_id','=',$partner_id)
        ->get()
        ->map( function ( $salesList ) {
            return [ 
                'title' => $salesList->first_name." ".$salesList->last_name,
                'value' => $salesList->id,
            ];
        } )->pluck( 'title', 'value')
        ->prepend(trans('lead.all'), '');

        view()->share('staff_care', $staff_care);
        view()->share('unitprice', $unitprice);
        view()->share('unitmass', $unitmass);
        view()->share('statuses', $statuses);
        view()->share('product_types', $product_types);
        view()->share('categories', $categories);
    }

    public function getImport()
    {
		//return 'jimmy';
        $title = trans('product.import');
        $user=$this->userRepository->getUser();
        $this->user_id=$user->id;
        $this->partner_id=$user->partner_id;
        $categories = $this->categoryRepository->getAll()->where('partner_id', $this->partner_id)
        ->orderBy("id", "desc")
        ->get()
        ->map(function ($category) {
            return [
                'title' => $category->name,
                'value'   => $category->id,
            ];
        })->pluck('title','value')->prepend(trans('product.category_id'), '');

        $productTypes = $this->optionRepository->getAll()
            ->where('category', 'product_type')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value');
        $partner=Partner::where('id',$this->partner_id)->first();
        $statuses = $this->optionRepository->getAll()
            ->where('category', 'product_status')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value');
            return view('user.product.import', compact('title', 'categories','productTypes','statuses', 'partner'));
    }

    public function postImport2(Request $request)
    {

        if(! ExcelfileValidator::validate( $request ))
        {
            return response('invalid File or File format', 500);
        }
        $user=$this->userRepository->getUser();
        $user_id=$user->id;
        $partner_id=$user->partner_id;
        $category_id=$request->category_id;
        $product_type=$request->product_type;
        $status=$request->status;
        
       // $reader = $this->excelRepository->load($request->file('file'));
        $path = $request->file('file')->getRealPath();
        $data = Excel::load($path)->get();

        if($data->count() > 0)
		{
            $totalProducts=0;
            $listInsert2=null;
            
			foreach($data->toArray() as $key => $value)
			{
                
				if ($value['product_name']!="") {
                    $product_image="";
                    $sale_price ="";
                    $description ="";
                    $quantity_on_hand =0;
                    $quantity_available =0;
					$insert_data=null;
					$product_name  = $value['product_name'];
                    if(isset($value['quantity_on_hand'])){
                        $quantity_on_hand   = $value['quantity_on_hand'];
                    }
                    if(isset($value['quantity_available'])){
                        $quantity_available   = $value['quantity_available'];
                    }
                    if(isset($value['sale_price'])){
                        $sale_price   = $value['sale_price'];
                    }
                    if(isset($value['description'])){
                        $description   = $value['description'];
                    }
                    if(isset($value['product_image'])){
                        $product_image   = $value['product_image'];
                    }
                    if(isset($value['vat'])){
                        $vat   = $value['vat'];
                    }
                    
					$emailProductCheck = new Product;
					$countProduct=Product::where('product_name',$product_name)->where('partner_id',$partner_id)->first();
					if(!isset($countProduct) || $countProduct==""){
                        $listInsert2[]=['product_name'=>$product_name, 'product_type'=>$product_type, 'category_id'=>$category_id, 'status'=>$status, 'quantity_on_hand'=>$quantity_on_hand, 'quantity_available'=>$quantity_available, 'sale_price'=>$sale_price, 'description'=>$description, 'product_image'=>$product_image, 'vat'=>$vat, 'partner_id'=>$user->partner_id, 'user_id'=>$user->id, 'product_import'=>1]; 
                        /*
                        $emailProductCheck->product_name= $product_name;
                        $emailProductCheck->product_type= $product_type;
                        $emailProductCheck->category_id= $category_id;
                        $emailProductCheck->status= $status;
                        $emailProductCheck->quantity_on_hand= $quantity_on_hand;
                        $emailProductCheck->quantity_available= $quantity_available;
                        $emailProductCheck->sale_price= $sale_price;
                        $emailProductCheck->description= $description;
                        $emailProductCheck->product_image= $product_image;
                        $emailProductCheck->vat=$vat;
                        $emailProductCheck->partner_id=$user->partner_id;
                        $emailProductCheck->user_id=$user->id;
                        $emailProductCheck->save(); */
                        
					}else{
                        Product::where('id',$countProduct["id"])->update(['sale_price'=>$sale_price]);
                    }
				}
				
            }
            if(count($listInsert2)>0){
                Product::insert($listInsert2);
                $totalProducts=count($listInsert2);
            }
		}
        return redirect( "product?messenger=Đã import được ".$totalProducts);

    }

    public function postImport(Request $request)
    {

        if(! ExcelfileValidator::validate( $request ))
        {
            return response('invalid File or File format', 500);
        }
        $user=$this->userRepository->getUser();
        $user_id=$user->id;
        $partner_id=$user->partner_id;
        $category_id=$request->category_id;
        $product_type=$request->product_type;
        $status=$request->status;
        
       // $reader = $this->excelRepository->load($request->file('file'));
        $path = $request->file('file')->getRealPath();
        $data = Excel::load($path)->get();
        if($data->count() > 0)
		{
            $totalProducts=0;
            $listInsert2=null;
                foreach($data->toArray() as $key => $value)
                {
                    
                    if ($value['ten_san_pham']!="") {
                        $product_image="";
                        $sale_price ="";
                        $description ="";
                        $quantity_on_hand =0;
                        $quantity_available =0;
                        $insert_data=null;
                        $product_name  = $value['ten_san_pham'];
                        $product_name_alias = $value['ten_phien_ban_san_pham'];
                        $sku  = $value['ma_sku'];
                        $description  = $value['mo_ta_san_pham'];
                        $barcode  = $value['barcode'];
                        $product_image  = $value['anh_dai_dien'];
                        $label_product  = $value['nhan_hieu'];
                        $tags  = $value['tags'];
                        $feature  = $value['thuoc_tinh_1'];
                        $mass  = $value['khoi_luong'];
                        $unit_mass  = $value['don_vi_khoi_luong'];
                        $unit_price  = $value['don_vi'];
                        $vat_type  = $value['ap_dung_thue'];
                        $price_cost  = $value['pl_gia_nhap'];
                        $sale_price  = $value['pl_gia_ban_le'];
                        $price_upsell  = $value['pl_gia_ban_buon'];
                        $quantity_on_hand  = $value['lc_cn1_ton_toi_da'];
                        $quantity_available  = $value['lc_cn1_ton_toi_thieu'];
                        $emailProductCheck = new Product;
                        $countProduct=Product::where('sku',$sku)->where('partner_id',$partner_id)->first();
                        if(!isset($countProduct) || $countProduct==""){
                            $listInsert2[]=['sku'=>$sku, 'product_name'=>$product_name, 'product_type'=>$product_type, 'category_id'=>$category_id, 'status'=>$status, 'quantity_on_hand'=>$quantity_on_hand, 'quantity_available'=>$quantity_available, 'sale_price'=>$sale_price, 'description'=>$description, 'product_image'=>$product_image, 'partner_id'=>$partner_id, 'user_id'=>$user->id, 'product_import'=>1, 'label_product'=>$label_product, 'tags'=>$tags, 'feature'=>$feature, 'mass'=>$mass, 'unit_mass'=>$unit_mass, 'unit_price'=>$unit_price, 'vat_type'=>$vat_type, 'price_cost'=>$price_cost, 'price_upsell'=>$price_upsell, 'product_name_alias'=>$product_name_alias]; 
                        }else{
                            Product::where('id',$countProduct["id"])->update(['sale_price'=>$sale_price]);
                        }
				
                    }
                }
                if(count($listInsert2)>0){
                    Product::insert($listInsert2);
                    $totalProducts=count($listInsert2);
                }
		    }
            return redirect( "product?messenger=Đã import được ".$totalProducts);
    }

    public function postProductEdu(Request $request)
    {

        if(! ExcelfileValidator::validate( $request ))
        {
            return response('invalid File or File format', 500);
        }
        $user=$this->userRepository->getUser();
        $user_id=$user->id;
        $partner_id=$user->partner_id;
        $category_id=$request->category_id;
        $product_type=$request->product_type;
        $status=$request->status;
        
       // $reader = $this->excelRepository->load($request->file('file'));
        $path = $request->file('file')->getRealPath();
        $data = Excel::load($path)->get();
        if($data->count() > 0)
		{
            $totalProducts=0;
            $listInsert2=null;
                foreach($data->toArray() as $key => $value)
                {
                    if ($value['name']!="") {
                        $product_image="";
                        $sale_price ="";
                        $description ="";
                        $quantity_on_hand =0;
                        $quantity_available =0;
                        $insert_data=null;
                        $product_name  = $value['name'];
                        $sku  = $value['sku'];
                        $description  = $value['desc'];
                        $description_01  = $value['desc1'];
                        $description_02  = $value['desc2'];
                        $active  = $value['active'];
                        $product_image  = $value['images'];
                        $tags  = $value['tags'];
                        $unit_price  = $value['unitofmeasurement'];
                        $vat_type  = $value['vat'];
                        $price_cost  = $value['price'];
                        $sale_price  = $value['price'];
                        $user_care_text = $value['user'];
                        $quantity_on_hand  = $value['max'];
                        $quantity_available  = $value['min'];
                        $start_date  ="";
                        if(isset($value['startdate']) && $value['startdate']!=""){
                            $start_date  = date("Y-m-d",strtotime($value['startdate']));

                        }
                        $emailProductCheck = new Product;
                        $countProduct=Product::where('sku',$sku)->where('partner_id',$partner_id)->first();
                        if(!isset($countProduct) || $countProduct==""){
                            $listInsert2[]=['sku'=>$sku, 'product_name'=>$product_name, 'product_type'=>$product_type, 'category_id'=>$category_id, 'status'=>$status, 'quantity_on_hand'=>$quantity_on_hand, 'quantity_available'=>$quantity_available, 'sale_price'=>$sale_price, 'description'=>$description, 'product_image'=>$product_image, 'partner_id'=>$partner_id, 'user_id'=>0, 'product_import'=>1, 'tags'=>$tags, 'feature'=>$description_01,'unit_price'=>$unit_price, 'vat_type'=>$vat_type, 'price_cost'=>$price_cost, 'user_care_text'=>$user_care_text, 'description_02'=>$description_02, 'active'=>$active, 'start_date'=>$start_date]; 

                        }else{
                            Product::where('id',$countProduct["id"])->update(['sale_price'=>$sale_price]);
                        }
				
                    }
                }
                if(count($listInsert2)>0){
                    Product::insert($listInsert2);
                    $totalProducts=count($listInsert2);
                }
		    }
            return redirect( "product?messenger=Đã import được ".$totalProducts);
    }


    public function postAjaxStore(ProductRequest $request)
    {
       $product =  $this->productRepository->create($request->except('created', 'errors', 'selected' , 'variants'));

         if (!empty($request->variants)) {

            foreach ($request->variants as $key => $item) {
                $productVariant = new ProductVariant();
                $productVariant->attribute_name = $item[0];
                $productVariant->product_attribute_value = $item[1] ;
                $product->productVariants()->save($productVariant);
            }
        }
        return response()->json([], 200);
    }

    public function downloadExcelTemplate()
    {
        if (ob_get_length()) ob_end_clean();
        $path = base_path('resources/excel-templates/products.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return 'File not found!';
    }
    // Export code
     // Export code
     public function exportCode($product)
     {
         $user=$this->userRepository->getUser();
         $user_id=$user->id;
         $token=base64_encode($user_id."|".$product);
         $file = base_path('public/uploads/codejs/code_'.$product.'.txt');
         $contents="<script>
         var bLock = false;
         var dLast = 0;
         var cp_script = new Array();
         var cp_token = '".$token."';
         var cp_pid = 100176;
         var cp_psid = 726;
         var cp_peid = '';
         var sd_product_id = '".$product."';
         var blackListForm = []; 
         var blackListField = []; 
         var cp_tracking = true; </script>
        <!-- SMARTCRM Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-NS27Z5D');</script>
        <!-- End SMARTCRM Manager -->";
        $myfile = fopen($file, "w") or die("Unable to open file!");
        @fwrite($myfile, $contents);
         //Storage::put($file, $contents);
         if (file_exists($file)) {
             return response()->download($file);
         }
         return 'File not found!';
     }

    private function getProductVariants($variants = [])
    {
        if (isset($variants)) {
            $variants = array_map(
                function ($v) {
                    return explode(':', $v);
                },
                explode(',', $variants)
            );
        }

        return $variants;
    }
    private function fullname($userid){
        if($userid!="" && $userid>0){
            $user=User::where('id',$userid)->first();
            if($user){
                return $user["first_name"]." ".$user["last_name"];
            }
        }
        return "";
    }
}
