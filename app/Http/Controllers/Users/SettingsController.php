<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\SettingRequest;
use App\Models\PrintTemplate;
use App\Models\Setting;
use App\Models\User;

use App\Repositories\OptionRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends UserController
{
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * SettingsController constructor.
     * @param OptionRepository $optionRepository
     */
    public function __construct(OptionRepository $optionRepository)
    {
        parent::__construct();

        $this->optionRepository = $optionRepository;

        view()->share('type', 'setting');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('settings.settings');
        $max_upload_file_size = array(
            '1000' => '1MB',
            '2000' => '2MB',
            '3000' => '3MB',
            '4000' => '4MB',
            '5000' => '5MB',
            '6000' => '6MB',
            '7000' => '7MB',
            '8000' => '8MB',
            '9000' => '9MB',
            '10000' => '10MB',
        );
        $currency_positions = array('left'=> trans("settings.left"), 'right'=> trans("settings.right"));
        $currency = $this->optionRepository->getAll()
            ->where('category', 'currency')
            ->get()
            ->map(
                function ($title) {
                    return [
                        'text' => $title->title,
                        'id' => $title->value,
                    ];
                }
            )->pluck('text', 'id');

        $backup_type = $this->optionRepository->getAll()
            ->where('category', 'backup_type')
            ->get()
            ->map(
                function ($title) {
                    return [
                        'text' => $title->value,
                        'id' => $title->title,
                    ];
                }
            );
        $languages = $this->optionRepository->getAll()->where('category','language')->pluck('title','value');

		$invoice_template = PrintTemplate::where('type','invoice')->pluck('name','slug');
		$saleorder_template = PrintTemplate::where('type','saleorder')->pluck('name','slug');
		$quotation_template = PrintTemplate::where('type','quotation')->pluck('name','slug');

		return view('user.setting.index', compact('title', 'max_upload_file_size', 'backup_type',
				'currency','invoice_template','saleorder_template','quotation_template','currency_positions','languages'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param SettingRequest|Request $request
     * @param Setting $setting
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function update(SettingRequest $request)
    {
        if ($request->hasFile('site_logo_file') != "") {
            $file = $request->file('site_logo_file');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $picture = Str::slug(substr($filename, 0, strrpos($filename, "."))) . '_' . time() . '.' . $extension;

			$destinationPath = public_path().'/uploads/site/';
			$file->move($destinationPath, $picture);
			$request->merge(['site_logo' => $picture]);
		}
		if ($request->hasFile('pdf_logo_file') != "") {
			$file = $request->file('pdf_logo_file');
			$filename = $file->getClientOriginalName();
			$extension = $file->getClientOriginalExtension();
			$picture = Str::slug(substr($filename, 0, strrpos($filename, "."))).'_'.time().'.'.$extension;

			$destinationPath = public_path().'/uploads/site/';
			$file->move($destinationPath, $picture);
			$request->merge(['pdf_logo' => $picture]);
		}

		Settings::set('modules', []);
        $request->date_format = $request->date_format_custom;
        $request->time_format = $request->time_format_custom;
        if ($request->date_format == "") {
            $request->date_format = 'd.m.Y';
        }
        if ($request->time_format == "") {
            $request->time_format = 'H:i';
        }
        $request->merge([
            'jquery_date' => $this->dateformat_PHP_to_jQueryUI($request->date_format),
            'jquery_date_time' => $this->dateformat_PHP_to_jQueryUI($request->date_format . ' ' . $request->time_format),
        ]);

        foreach ($request->except('_token', 'site_logo_file','pdf_logo_file', 'date_format_custom', 'time_format_custom', 'pages') as $key => $value) {
            Settings::set($key, $value);
        }

        editEnv([
            'MAIL_HOST' => isset($request->email_host) ? $request->email_host : env('MAIL_HOST'),
            'MAIL_PORT' => isset($request->email_port) ? $request->email_port : env('MAIL_PORT'),
            'MAIL_USERNAME' => isset($request->email_username) ? $request->email_username : env('MAIL_USERNAME'),
            'MAIL_PASSWORD' => isset($request->email_password) ? $request->email_password : env('MAIL_PASSWORD'),
            'PAYPAL_MODE' => isset($request->paypal_mode) ? $request->paypal_mode : env('PAYPAL_MODE'),
            'PAYPAL_SANDBOX_API_USERNAME' => isset($request->paypal_sandbox_username) ? $request->paypal_sandbox_username : env('PAYPAL_SANDBOX_API_USERNAME'),
            'PAYPAL_SANDBOX_API_PASSWORD' => isset($request->paypal_sandbox_password) ? $request->paypal_sandbox_password : env('PAYPAL_SANDBOX_API_PASSWORD'),
            'PAYPAL_SANDBOX_API_SECRET' => isset($request->paypal_sandbox_signature) ? $request->paypal_sandbox_signature : env('PAYPAL_SANDBOX_API_SECRET'),
            'PAYPAL_LIVE_API_USERNAME' => isset($request->paypal_live_username) ? $request->paypal_live_username : env('PAYPAL_LIVE_API_USERNAME'),
            'PAYPAL_LIVE_API_PASSWORD' => isset($request->paypal_live_password) ? $request->paypal_live_password : env('PAYPAL_LIVE_API_PASSWORD'),
            'PAYPAL_LIVE_API_SECRET' => isset($request->paypal_live_signature) ? $request->paypal_live_signature : env('PAYPAL_LIVE_API_SECRET')
        ]);

		return redirect()->to('/setting');
	}

	/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 * @author Stojan Kukrika
 */
	function dateformat_PHP_to_jQueryUI($php_format)
	{
		$SYMBOLS_MATCHING = array(
			// Day
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'do',
			'S' => 'do',
			'w' => 'd',
			'z' => 'DDD',
			// Week
			'W' => 'w',
			// Month
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '',
			// Year
			'L' => '',
			'o' => '',
			'Y' => 'GGGG',
			'y' => 'GG',
			// Time
			'a' => 'a',
			'A' => 'A',
			'B' => '',
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => ''
		);


		$jqueryui_format = "";
		$escaping = false;
		for($i = 0; $i < strlen($php_format); $i++)
		{
			$char = $php_format[$i];
			if($char === '\\') // PHP date format escaping character
			{
				$i++;
				if($escaping) $jqueryui_format .= $php_format[$i];
				else $jqueryui_format .= '\'' . $php_format[$i];
				$escaping = true;
			}
			else
			{
				if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
				if(isset($SYMBOLS_MATCHING[$char]))
					$jqueryui_format .= $SYMBOLS_MATCHING[$char];
				else
					$jqueryui_format .= $char;
			}
		}
		return $jqueryui_format;
    }
    
    
}
