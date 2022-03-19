<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\QtemplateRequest;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationTemplateRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class QtemplateController extends UserController
{
    /**
     * @var QuotationTemplateRepository
     */
    private $quotationTemplateRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * QtemplateController constructor.
     * @param QuotationTemplateRepository $qtemplateRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(QuotationTemplateRepository $quotationTemplateRepository,
                                ProductRepository $productRepository)
    {
        parent::__construct();
        $this->quotationTemplateRepository = $quotationTemplateRepository;
        $this->productRepository = $productRepository;

        view()->share('type', 'qtemplate');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('qtemplate.qtemplates');
        return view('user.qtemplate.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('qtemplate.new');

        $this->generateParams();

        return view('user.qtemplate.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param QtemplateRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(QtemplateRequest $request)
    {
        if ($request->quotation_duration==""){
            $request->merge(['quotation_duration'=>0]);
        }
        $this->quotationTemplateRepository->createQtemplate($request->all());
        return redirect("qtemplate");
    }


    public function edit($qtemplate)
    {
        $qtemplate = $this->quotationTemplateRepository->find($qtemplate);
        $title = trans('qtemplate.edit');

        $this->generateParams();

        return view('user.qtemplate.create', compact('title', 'qtemplate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(QtemplateRequest $request, $qtemplate)
    {
        if ($request->quotation_duration==""){
            $request->merge(['quotation_duration'=>0]);
        }
        $qtemplate_id = $qtemplate;
        $this->quotationTemplateRepository->updateQtemplate($request->all(),$qtemplate_id);
        return redirect('qtemplate');
    }


    public function delete($qtemplate)
    {
        $qtemplate = $this->quotationTemplateRepository->find($qtemplate);
        $title = trans('qtemplate.delete');
        return view('user.qtemplate.delete', compact('title', 'qtemplate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($qtemplate)
    {
        $this->quotationTemplateRepository->deleteQtemplate($qtemplate);
        return redirect('qtemplate');
    }

    /**
     * @return mixed
     */
    public function data(Datatables $datatables)
    {
        $qtemplates = $this->quotationTemplateRepository->getAll()->select('id', 'quotation_template', 'quotation_duration')->get();

        return $datatables->collection($qtemplates)
            ->addColumn('actions', '<a href="{{ url(\'qtemplate/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i>  </a>
                                     <a href="{{ url(\'qtemplate/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i></a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
        $sales_tax = Settings::get('sales_tax');
        $products = $this->productRepository->orderBy("id", "desc")->all();
        view()->share('products', $products);
        view()->share('sales_tax', isset($sales_tax) ? floatval($sales_tax) : 1);
    }
}
