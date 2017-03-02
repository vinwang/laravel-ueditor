<?php
namespace Wanglu\Ueditor;

use Illuminate\Support\ServiceProvider;

class UeditorServiceProvider extends ServiceProvider{
	
	/**
     * Bootstrap the application events.
     * @return void
     */
    public function boot(){
		$viewPath = realpath(__DIR__ . '/../resources/views');
		$this->loadViewsFrom($viewPath, 'ueditor');
		//视图发布
		$this->publishes([
			$viewPath => base_path('resources/views/vendor/ueditor');
		]);
		//公共资源
		$this->publishes([
			realpath(__DIR__ . '/../resources/public/') => public_path('ueditor');
		], 'public');
		
		$router = app('router');
		//auth
		$config = config('ueditor.core.route', []);
		$config['namespace'] =  __NAMESPACE__;
		
		//路由
		$router->group($config, function($router){
			$router->any('ueditor', 'UeditorController@index');
		});
		
	}
	/**
     * Register the service provider.
     * @return void
     */
	public function register(){
		//配置文件
		$configPath = realpath(__DIR__. '/../config/ueditor.php');
		$this->mergeConfigFrom($configPath, 'ueditor');
		$this->publishes([
			$configPath => config_path('ueditor.php');
		]);
	}
}