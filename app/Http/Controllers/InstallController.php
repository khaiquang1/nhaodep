<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstallSettingsEmailRequest;
use App\Http\Requests\InstallSettingsRequest;
use App\Models\Option;
use App\Repositories\InstallRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Swift_SmtpTransport;
use Swift_TransportException;

class InstallController extends Controller
{
	/**
	 * @var InstallRepository
	 */
	private $installRepository;

	/**
	 * InstallController constructor.
	 * @param InstallRepository $installRepository
	 */
	public function __construct(InstallRepository $installRepository)
	{
		ini_set("memory_limit", "-1");
		set_time_limit(1000000);
		$this->installRepository = $installRepository;
	}

	public function index()
	{
		return view('install.start');
	}

	public function requirements()
	{
		$requirements = $this->installRepository->getRequirements();
		$allLoaded = $this->installRepository->allRequirementsLoaded();

		return view('install.requirements', compact('requirements', 'allLoaded'));
	}

	public function permissions()
	{
		if (!$this->installRepository->allRequirementsLoaded()) {
			return redirect('install/requirements');
		}

		$folders = $this->installRepository->getPermissions();
		$allGranted = $this->installRepository->allPermissionsGranted();

		return view('install.permissions', compact('folders', 'allGranted'));
	}

	public function database()
	{
		if (!$this->installRepository->allRequirementsLoaded()) {
			return redirect('install/requirements');
		}

		if (!$this->installRepository->allPermissionsGranted()) {
			return redirect('install/permissions');
		}

		return view('install.database');
	}

	public function installation(Request $request)
	{
		if (!$this->installRepository->allRequirementsLoaded()) {
			return redirect('install/requirements');
		}

		if (!$this->installRepository->allPermissionsGranted()) {
			return redirect('install/permissions');
		}

		$link = @mysqli_connect($request->host, $request->username, $request->password);

		if (!$link)
			return back()->withErrors('Connection could not be established!!');
		else {
			if (mysqli_select_db($link, $request->database)) {
				$dbCredentials = $request->only('host', 'username', 'password', 'database');
				$this->installRepository->setDatabaseCredentials($dbCredentials);
			} else {
				return back()->withErrors('Could not select database');
			}
		}

		return view('install.installation');
	}

	public function install()
	{
		try {
			Artisan::call('key:generate');

			Artisan::call('migrate', ['--force' => true]);
			Artisan::call('db:seed', ['--force' => true]);

			return redirect('install/settings');

		} catch (\Exception $e) {
			\Log::error($e->getMessage());
			\Log::error($e->getTraceAsString());

			return redirect('install/error');
		}
	}

	public function disable()
	{
		$foldersDisable = $this->installRepository->getDisablePermissions();
		$allDisableGranted = $this->installRepository->allDisablePermissionsGranted();

		return view('install.disable', compact('foldersDisable','allDisableGranted'));
	}
	public function settings()
	{
        Settings::forget('install.db_credentials');

        $currency = Option::where('category', 'currency')->pluck('title', 'value');

		return view('install.settings', compact('currency'));
	}

	public function settingsSave(InstallSettingsRequest $request)
	{
		Settings::set('site_name', $request->site_name);

		Settings::set('site_email', $request->site_email);

		Settings::set('currency', $request->currency);

		$admin = Sentinel::registerAndActivate(array(
			'email' => $request->email,
			'password' => $request->password,
			'first_name' => $request->first_name,
			'last_name' => $request->last_name,
		));
		$admin->user_id = $admin->id;
		$admin->save();

		$role = Sentinel::findRoleBySlug('admin');
		$role->users()->attach($admin);

		return redirect('install/email_settings');
	}

	public function settingsEmail()
	{
		return view('install.mail_settings');
	}

	public function settingsEmailSave(InstallSettingsEmailRequest $request)
	{
		try {
            if ('smtp' == $request->email_driver) {
                $transport = (new Swift_SmtpTransport($request->email_host, $request->email_port))
                    ->setUsername($request->email_username)
                    ->setPassword($request->email_password)
                ;
                $transport->start();
            }
			foreach ($request->except('_token') as $key => $value) {
				Settings::set($key, $value);
			}
			file_put_contents(storage_path('installed'), 'Welcome to LCRM');

			return redirect('install/complete');

		} catch (Swift_TransportException $e) {
			return redirect()->back()->withErrors($e->getMessage());
		} catch (\Exception $e) {
			return redirect()->back()->withErrors($e->getMessage());
		}
	}

	public function complete()
	{
		return view('install.complete');
	}

	public function error()
	{
		return view('install.error');
	}
}
