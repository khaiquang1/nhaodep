<?php

namespace App\Repositories;


use Illuminate\Support\Facades\DB;

class InstallRepositoryEloquent implements InstallRepository
{

	public function getRequirements()
	{
		$requirements = [
            'PHP Version (>= 7.1)' => version_compare(phpversion(), '7.1', '>='),
			'OpenSSL Extension'   => extension_loaded('openssl'),
			'PDO Extension'       => extension_loaded('PDO'),
			'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
			'Mbstring Extension'  => extension_loaded('mbstring'),
			'Tokenizer Extension' => extension_loaded('tokenizer'),
			'GD Extension' => extension_loaded('gd'),
			'Fileinfo Extension' => extension_loaded('fileinfo')
		];

		if (extension_loaded('xdebug')) {
			$requirements['Xdebug Max Nesting Level (>= 500)'] = (int)ini_get('xdebug.max_nesting_level') >= 500;
		}

		return $requirements;
	}

	public function allRequirementsLoaded()
	{
		$allLoaded = true;

		foreach ($this->getRequirements() as $loaded) {
			if ($loaded == false) {
				$allLoaded = false;
			}
		}

		return $allLoaded;
	}

	public function getPermissions()
	{
		return [
			'public/uploads/avatar' => is_writable(public_path('uploads/avatar')),
			'public/uploads/company' => is_writable(public_path('uploads/company')),
			'public/uploads/contract' => is_writable(public_path('uploads/contract')),
			'public/uploads/customer' => is_writable(public_path('uploads/customer')),
			'public/uploads/pdf' => is_writable(public_path('uploads/pdf')),
			'public/uploads/products' => is_writable(public_path('uploads/products')),
			'public/uploads/site' => is_writable(public_path('uploads/site')),
			'public/pdf' => is_writable(public_path('pdf')),
			'storage/app' => is_writable(storage_path('app')),
			'storage/framework/cache' => is_writable(storage_path('framework/cache')),
			'storage/framework/sessions' => is_writable(storage_path('framework/sessions')),
			'storage/framework/views' => is_writable(storage_path('framework/views')),
			'storage/logs' => is_writable(storage_path('logs')),
			'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
			'.env file' => is_writable(base_path('.env')),
		];
	}

	public function allPermissionsGranted()
	{
		$allGranted = true;

		foreach ($this->getPermissions() as $permission => $granted) {
			if ($granted == false) {
				$allGranted = false;
			}
		}

		return $allGranted;
	}

	public function getDisablePermissions()
	{
		return [
			'Base Directory' => !is_writable(base_path('')),
		];
	}

	public function allDisablePermissionsGranted()
	{
		$allNotGranted = true;

		foreach ($this->getDisablePermissions() as $permission => $granted) {
			if ($granted == true) {
				$allNotGranted = false;
			}
		}

		return $allNotGranted;
	}

	public function dbCredentialsAreValid($credentials)
	{
		$this->setDatabaseCredentials($credentials);

		try {
			DB::statement("SHOW TABLES");
			/*DB::statement("CREATE TABLE IF NOT EXISTS `settings` (
								 `setting_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
								 `setting_value` text COLLATE utf8_unicode_ci NOT NULL
							   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");*/
		} catch (\Exception $e) {
			\Log::info($e->getMessage());
			return false;
		}

		return true;
	}

	public function dbDropSettings()
	{
		try {
			DB::statement("DROP TABLE `settings`;");
		} catch (\Exception $e) {
			\Log::info($e->getMessage());
			return false;
		}

		return true;
	}

	public function setDatabaseCredentials($credentials)
	{
		$default = config('database.default');

		config([
			"database.connections.{$default}.host" => $credentials['host'],
			"database.connections.{$default}.database" => $credentials['database'],
			"database.connections.{$default}.username" => $credentials['username'],
			"database.connections.{$default}.password" => $credentials['password']
		]);

		$path = base_path('.env');
		$env = file($path);

		$env = str_replace('DB_HOST=' . env('DB_HOST'), 'DB_HOST=' . $credentials['host'] . PHP_EOL, $env);
		$env = str_replace('DB_DATABASE=' . env('DB_DATABASE'), 'DB_DATABASE=' . $credentials['database'] . PHP_EOL, $env);
		$env = str_replace('DB_USERNAME=' . env('DB_USERNAME'), 'DB_USERNAME=' . $credentials['username'] . PHP_EOL, $env);
		$env = str_replace('DB_PASSWORD=' . env('DB_PASSWORD'), 'DB_PASSWORD=' . $credentials['password'] . PHP_EOL, $env);

		file_put_contents($path, $env);
	}
}